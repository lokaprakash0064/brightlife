<?php

/**
 * Class file to encapsulate all rate-limiting / lockout state for
 * authentication surfaces (Member Login, Admin Login, and future
 * Signup/Forgot-Password throttling) against the bl_auth_rate_limit
 * table.
 *
 * Architecture (frozen, Phase 6C):
 * - bl_auth_rate_limit holds ONE mutable row per tracked bucket, never
 *   a per-failure event log. A bucket is either an ACCOUNT bucket
 *   (keyed by the resolved account id) or an IP bucket (keyed by the
 *   client IP), see BUCKET_TYPE_ACCOUNT / BUCKET_TYPE_IP.
 * - Account buckets are unified across every login surface (Member and
 *   Admin login share one bucket per account, endpoint-agnostic) so an
 *   attacker cannot double their attempt budget by alternating
 *   endpoints. IP buckets stay endpoint-scoped. bl_auth_rate_limit's
 *   uniqueness is (bucket_type, bucket_identifier, endpoint) - to keep
 *   ACCOUNT rows unified under that key, every ACCOUNT-type row is
 *   forced onto one canonical endpoint value, see resolveEndpoint().
 * - An identifier that never resolves to a known account never gets an
 *   ACCOUNT bucket row; only the IP bucket is evaluated/updated. See
 *   the guard at the top of recordFailure()/checkBucket().
 * - PHP is the only authoritative time source. Every timestamp written
 *   or compared here comes from PHP's time()/date(); no NOW() or
 *   CURRENT_TIMESTAMP is ever used.
 * - Cleanup is request-driven (probabilistic), never cron-dependent,
 *   and must never interrupt the authentication request it rides
 *   along with - see cleanupExpiredBuckets().
 * - Audit logging (bl_auth_events) is a fully separate subsystem with
 *   its own table and its own helper; this class never touches it.
 *
 * This class is not yet called from any authentication flow. It is an
 * isolated, independently reviewable unit (Phase 2); wiring it into
 * Member Login, Admin Login, Signup, or Forgot Password is a later,
 * separate phase.
 *
 * PHP version 8.2
 *
 * @category   AuthRateLimiter
 * @package    TBF
 */

// Check if AuthRateLimiter class exists or not and define if not
if (!class_exists('AuthRateLimiter')) {

    /**
     * Class to encapsulate all database interaction with the
     * bl_auth_rate_limit mutable-row-per-bucket table.
     */
    class AuthRateLimiter
    {
        // {{{ constants

        /**
         * Bucket type for an account-scoped bucket, keyed by the
         * resolved account id (su_id) and unified across every login
         * surface.
         *
         * @var string
         */
        const BUCKET_TYPE_ACCOUNT = 'ACCOUNT';

        /**
         * Bucket type for an IP-scoped bucket, keyed by client IP and
         * scoped independently per endpoint.
         *
         * @var string
         */
        const BUCKET_TYPE_IP = 'IP';

        /**
         * Canonical endpoint value forced onto every ACCOUNT-type row,
         * regardless of which login surface the attempt came from.
         * bl_auth_rate_limit's unique key is
         * (bucket_type, bucket_identifier, endpoint); without forcing
         * this, an account attempted through Member Login and then
         * Admin Login would land in two separate rows, breaking the
         * unified-account-bucket requirement.
         *
         * @var string
         */
        const UNIFIED_ACCOUNT_ENDPOINT = 'account';

        /**
         * Default probability (0..1) that cleanupExpiredBuckets() actually
         * runs its delete when not forced, so an ordinary authentication
         * request only occasionally pays the cost of a cleanup scan.
         *
         * @var float
         */
        const DEFAULT_CLEANUP_PROBABILITY = 0.01;

        // }}}
        // {{{ properties

        /**
         * Private static variable to hold singleton class object
         *
         * @access    private
         * @staticvar
         * @var       object  The current class object
         */
        private static $_classObject;

        // }}}
        // {{{ __construct()

        /**
         * Default private constructor, according to singleton pattern
         *
         * @return void
         * @access private
         */
        private function __construct()
        {
        }

        // }}}
        // {{{ getObject()

        /**
         * Method to return singleton class object.
         *
         * @return object  The current class object
         * @access public
         * @static
         */
        public static function getObject()
        {
            if (self::$_classObject === null) {
                self::$_classObject = new self();
            }
            return self::$_classObject;
        }

        // }}}
        // {{{ checkBucket()

        /**
         * Read-only lookup of a bucket's current state. Never writes.
         *
         * @param string $bucketType One of BUCKET_TYPE_ACCOUNT / BUCKET_TYPE_IP
         * @param string $identifier The account id (ACCOUNT buckets) or client IP (IP buckets)
         * @param string $endpoint   The login surface (e.g. 'member_login', 'admin_login').
         *                           Ignored for ACCOUNT buckets - see resolveEndpoint().
         *
         * @return array{exists:bool,attempts:int,windowStart:?string,lastAttemptAt:?string,lockedUntil:?string,isLocked:bool}
         * @access public
         */
        public function checkBucket($bucketType, $identifier, $endpoint)
        {
            $this->assertValidBucketType($bucketType);
            // unknown-account rule: no identifier means no account bucket
            // can ever exist, so there is nothing to look up
            if (($bucketType === self::BUCKET_TYPE_ACCOUNT) && empty($identifier)) {
                return $this->emptyBucketState();
            }
            $endpoint = $this->resolveEndpoint($bucketType, $endpoint);
            $rows = $this->fetchBucketRow($bucketType, $identifier, $endpoint);
            if (($rows === false) or (count($rows) < 1)) {
                return $this->emptyBucketState();
            }
            return $this->toBucketState($rows[0]);
        }

        // }}}
        // {{{ recordFailure()

        /**
         * Record one qualifying authentication failure against a bucket,
         * creating its row if this is the first failure, or updating it
         * in place otherwise (mutable-row model, never an event log).
         *
         * Sliding-window semantics: if the elapsed time since the
         * bucket's tracked window began exceeds $windowSeconds, the
         * streak is treated as expired and restarts (attempts reset to
         * 1, window anchor reset to now, any stale lock cleared).
         * Otherwise the existing streak continues and the attempt count
         * increments; once it reaches $maxAttempts, locked_until is
         * set (or extended) to now + $lockoutSeconds.
         *
         * Unknown-account rule: if $bucketType is BUCKET_TYPE_ACCOUNT and
         * $identifier is empty, this method is a deliberate no-op and
         * returns null - no account bucket row is ever created for an
         * identifier that did not resolve to a known account.
         *
         * @param string $bucketType     One of BUCKET_TYPE_ACCOUNT / BUCKET_TYPE_IP
         * @param string $identifier     The account id (ACCOUNT buckets) or client IP (IP buckets)
         * @param string $endpoint       The login surface. Ignored for ACCOUNT buckets.
         * @param int    $windowSeconds  Length of the sliding tracking window, in seconds
         * @param int    $maxAttempts    Attempts within the window before the bucket locks
         * @param int    $lockoutSeconds How long a lock lasts once triggered, in seconds
         *
         * @return array|null The resulting bucket state (see checkBucket()), or null if skipped
         * @access public
         */
        public function recordFailure($bucketType, $identifier, $endpoint, $windowSeconds, $maxAttempts, $lockoutSeconds)
        {
            $this->assertValidBucketType($bucketType);
            if (($bucketType === self::BUCKET_TYPE_ACCOUNT) && empty($identifier)) {
                return null;
            }
            $endpoint = $this->resolveEndpoint($bucketType, $endpoint);

            $now = time();
            $nowStr = date('Y-m-d H:i:s', $now);
            $existing = $this->fetchBucketRow($bucketType, $identifier, $endpoint);

            DbOperations::getObject()->transaction('start');

            if (($existing === false) or (count($existing) < 1)) {
                // first failure for this bucket: create its row
                DbOperations::getObject()->buildInsertQuery(
                    'bl_auth_rate_limit',
                    array('bucket_type', 'bucket_identifier', 'endpoint', 'attempts', 'window_start', 'last_attempt_at', 'locked_until')
                );
                $ok = DbOperations::getObject()->runQuery(
                    array($bucketType, $identifier, $endpoint, 1, $nowStr, $nowStr, null)
                );
            } else {
                $row = $existing[0];
                $windowExpired = (strtotime($row['window_start']) < ($now - $windowSeconds));
                if ($windowExpired) {
                    // the previous streak has fully aged out: this failure
                    // starts a brand new streak rather than continuing the old one
                    $attempts = 1;
                    $windowStart = $nowStr;
                    $lockedUntil = null;
                } else {
                    $attempts = intval($row['attempts']) + 1;
                    $windowStart = $row['window_start'];
                    $lockedUntil = ($attempts >= $maxAttempts)
                        ? date('Y-m-d H:i:s', $now + $lockoutSeconds)
                        : $row['locked_until'];
                }
                DbOperations::getObject()->buildUpdateQuery(
                    'bl_auth_rate_limit',
                    array('attempts', 'window_start', 'last_attempt_at', 'locked_until'),
                    array('bucket_type', 'bucket_identifier', 'endpoint')
                );
                $ok = DbOperations::getObject()->runQuery(
                    array($attempts, $windowStart, $nowStr, $lockedUntil, $bucketType, $identifier, $endpoint)
                );
            }

            if ($ok !== false) {
                DbOperations::getObject()->transaction('on');
            } else {
                DbOperations::getObject()->transaction('off');
            }

            return $this->checkBucket($bucketType, $identifier, $endpoint);
        }

        // }}}
        // {{{ clearAccountBucket()

        /**
         * Delete an account bucket's row entirely on successful
         * authentication (mutable-row model: a clean slate is the
         * absence of a row, not a zeroed-out one - symmetric with the
         * unknown-account rule). IP buckets are never touched by this
         * method; a successful login never clears an unrelated IP's
         * tracked state, by design.
         *
         * @param mixed $accountId The resolved account id (su_id). No-op if empty.
         *
         * @return void
         * @access public
         */
        public function clearAccountBucket($accountId)
        {
            if (empty($accountId)) {
                return;
            }
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildDeleteQuery(
                'bl_auth_rate_limit',
                array('bucket_type', 'bucket_identifier', 'endpoint')
            );
            $ok = DbOperations::getObject()->runQuery(
                array(self::BUCKET_TYPE_ACCOUNT, $accountId, self::UNIFIED_ACCOUNT_ENDPOINT)
            );
            if ($ok !== false) {
                DbOperations::getObject()->transaction('on');
            } else {
                DbOperations::getObject()->transaction('off');
            }
        }

        // }}}
        // {{{ cleanupExpiredBuckets()

        /**
         * Best-effort, request-driven removal of buckets with no
         * remaining relevance: rows whose last recorded failure is
         * older than $retentionSeconds AND whose lock (if any) has
         * already expired. Not cron-dependent - intended to be called
         * opportunistically from within ordinary request handling.
         *
         * Runs probabilistically (see $probability) so a single request
         * does not pay the cost of a cleanup scan every time. Every
         * failure path here is caught and logged internally rather than
         * surfaced to the caller, so a cleanup failure can never abort
         * or otherwise affect the authentication request it rides along
         * with.
         *
         * Note: this method cannot shield the caller from a genuine
         * database infrastructure failure (e.g. a dropped connection) -
         * DbOperations' own error handling logs and terminates the
         * request on a real PDO exception, exactly as every other
         * database call in this application already does. This method's
         * try/catch guards against errors in its own logic, not against
         * that pre-existing, application-wide behaviour.
         *
         * @param int   $retentionSeconds How long after its last failure a bucket is kept
         * @param float $probability      Chance (0..1) this call actually runs the delete
         * @param bool  $force            Bypass the probability check (for manual testing)
         *
         * @return bool True if a cleanup delete was attempted, false if skipped or it failed
         * @access public
         */
        public function cleanupExpiredBuckets($retentionSeconds, $probability = self::DEFAULT_CLEANUP_PROBABILITY, $force = false)
        {
            try {
                if (!$force and ((mt_rand() / mt_getrandmax()) > $probability)) {
                    return false;
                }
                $now = time();
                $boundary = date('Y-m-d H:i:s', $now - $retentionSeconds);
                $nowStr = date('Y-m-d H:i:s', $now);
                $sql = 'delete from bl_auth_rate_limit where last_attempt_at < ? '
                        . 'and (locked_until is null or locked_until < ?)';
                DbOperations::getObject()->transaction('start');
                DbOperations::getObject()->prepareQuery($sql);
                $ok = DbOperations::getObject()->runQuery(array($boundary, $nowStr));
                if ($ok !== false) {
                    DbOperations::getObject()->transaction('on');
                } else {
                    DbOperations::getObject()->transaction('off');
                }
                return true;
            } catch (Throwable $ex) {
                // cleanup must never interrupt authentication: log internally
                // and swallow the failure completely
                error_log('[BrightLife] AuthRateLimiter cleanup failed - ' . $ex->getMessage());
                try {
                    DbOperations::getObject()->transaction('off');
                } catch (Throwable $ignored) {
                    // nothing further we can safely do here
                }
                return false;
            }
        }

        // }}}
        // {{{ fetchBucketRow()

        /**
         * Fetch the raw row for a bucket, if any.
         *
         * @param string $bucketType One of BUCKET_TYPE_ACCOUNT / BUCKET_TYPE_IP
         * @param string $identifier The account id or client IP
         * @param string $endpoint   The already-resolved endpoint value
         *
         * @return array|false The fetched row(s), or false on failure
         * @access private
         */
        private function fetchBucketRow($bucketType, $identifier, $endpoint)
        {
            $sql = 'select attempts, window_start, last_attempt_at, locked_until from bl_auth_rate_limit '
                    . 'where bucket_type = ? and bucket_identifier = ? and endpoint = ?';
            return DbOperations::getObject()->fetchData($sql, array($bucketType, $identifier, $endpoint));
        }

        // }}}
        // {{{ resolveEndpoint()

        /**
         * Enforce the unified-account-bucket rule against the existing
         * (bucket_type, bucket_identifier, endpoint) unique key: every
         * ACCOUNT-type bucket is forced onto one canonical endpoint
         * value regardless of which login surface reported the failure,
         * so Member Login and Admin Login always share the same row for
         * the same account. IP buckets keep their real, endpoint-scoped
         * value unchanged.
         *
         * @param string $bucketType One of BUCKET_TYPE_ACCOUNT / BUCKET_TYPE_IP
         * @param string $endpoint   The caller-supplied endpoint value
         *
         * @return string The endpoint value to actually store/query with
         * @access private
         */
        private function resolveEndpoint($bucketType, $endpoint)
        {
            if ($bucketType === self::BUCKET_TYPE_ACCOUNT) {
                return self::UNIFIED_ACCOUNT_ENDPOINT;
            }
            return $endpoint;
        }

        // }}}
        // {{{ toBucketState()

        /**
         * Shape a raw fetched row into the public bucket-state array.
         *
         * @param array $row The raw row from fetchBucketRow()
         *
         * @return array{exists:bool,attempts:int,windowStart:?string,lastAttemptAt:?string,lockedUntil:?string,isLocked:bool}
         * @access private
         */
        private function toBucketState($row)
        {
            $lockedUntil = $row['locked_until'];
            $isLocked = ($lockedUntil !== null) && (strtotime($lockedUntil) > time());
            return array(
                'exists' => true,
                'attempts' => intval($row['attempts']),
                'windowStart' => $row['window_start'],
                'lastAttemptAt' => $row['last_attempt_at'],
                'lockedUntil' => $lockedUntil,
                'isLocked' => $isLocked,
            );
        }

        // }}}
        // {{{ emptyBucketState()

        /**
         * The bucket-state array for a bucket that has no row, i.e. a
         * clean slate (never failed, or cleared on a prior success).
         *
         * @return array{exists:bool,attempts:int,windowStart:?string,lastAttemptAt:?string,lockedUntil:?string,isLocked:bool}
         * @access private
         */
        private function emptyBucketState()
        {
            return array(
                'exists' => false,
                'attempts' => 0,
                'windowStart' => null,
                'lastAttemptAt' => null,
                'lockedUntil' => null,
                'isLocked' => false,
            );
        }

        // }}}
        // {{{ assertValidBucketType()

        /**
         * Guard against a caller passing anything other than one of the
         * two defined bucket types.
         *
         * @param string $bucketType The bucket type to validate
         *
         * @return void
         * @access private
         */
        private function assertValidBucketType($bucketType)
        {
            if (($bucketType !== self::BUCKET_TYPE_ACCOUNT) and ($bucketType !== self::BUCKET_TYPE_IP)) {
                die('Oops... invalid bucket type supplied to AuthRateLimiter. At line: ' . __LINE__ . ' in file: ' . __FILE__);
            }
        }

        // }}}
        // {{{ __clone()

        /**
         * According to singleton pattern instance, cloning is prohibited
         *
         * @return void
         * @access public
         */
        private function __clone()
        {
            die('Cloning is prohibited for singleton instance.');
        }

        // }}}
    }
}
