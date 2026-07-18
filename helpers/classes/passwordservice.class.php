<?php

/**
 * Class file to hash and verify user passwords.
 *
 * Introduced to migrate away from DataFilter::pwdHash() (a custom
 * SHA256/SHA1/MD5 chain with a single global static salt) to PHP's
 * native password_hash()/password_verify(), without locking out
 * existing users. Legacy hashes are verified via DataFilter::pwdHash()
 * (kept intact) and transparently upgraded to a native hash on
 * successful login. See DataFilter::pwdHash() for the legacy algorithm.
 *
 * PHP version 8.2
 *
 * @category   PasswordService
 * @package    TBF
 */

// Check if PasswordService class exists or not and define if not
if (!class_exists('PasswordService')) {

    /**
     * Class file to hash and verify passwords, and to detect/upgrade
     * legacy DataFilter::pwdHash() hashes to native password_hash() hashes.
     */
    class PasswordService
    {
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
        // {{{ hash()

        /**
         * Hash a (cleaned) plaintext password using PHP's native algorithm.
         * Used for new signups and for admin-initiated password resets.
         *
         * @param string $plainPassword The cleaned plaintext password
         *
         * @return string The native password_hash() hash
         * @access public
         */
        public function hash($plainPassword)
        {
            return password_hash($plainPassword, PASSWORD_DEFAULT);
        }

        // }}}
        // {{{ isLegacyHash()

        /**
         * Detect whether a stored hash came from the legacy
         * DataFilter::pwdHash() chain (32-char hex/MD5 output) rather
         * than password_hash(). Uses password_get_info() instead of a
         * password_version column: password_hash() output always parses
         * to a known algo, a legacy hash never does.
         *
         * @param string $storedHash The hash currently stored in su_pass
         *
         * @return bool True if the hash is a legacy (non-native) hash
         * @access public
         */
        public function isLegacyHash($storedHash)
        {
            $info = password_get_info($storedHash);
            // 'algo' is null on PHP 8+ and int 0 on PHP 7.x for an
            // unrecognized hash, so use empty() to cover both runtimes
            return empty($info['algo']);
        }

        // }}}
        // {{{ verify()

        /**
         * Verify a (cleaned) plaintext password against a stored hash,
         * whether that hash is legacy or native.
         *
         * @param string $plainPassword The cleaned plaintext password supplied at login
         * @param string $storedHash    The hash currently stored in su_pass
         *
         * @return bool True if the password matches
         * @access public
         */
        public function verify($plainPassword, $storedHash)
        {
            if ($this->isLegacyHash($storedHash)) {
                // legacy hashes are not salted per-user, so a direct
                // constant-time string compare is sufficient here
                return hash_equals($storedHash, DataFilter::getObject()->pwdHash($plainPassword));
            }
            return password_verify($plainPassword, $storedHash);
        }

        // }}}
        // {{{ needsRehash()

        /**
         * Whether a native hash was produced with weaker-than-current
         * options and should be regenerated. Legacy hashes are handled
         * separately via isLegacyHash()/upgradeLegacyHash(), not here.
         *
         * @param string $storedHash The hash currently stored in su_pass
         *
         * @return bool True if the hash should be regenerated
         * @access public
         */
        public function needsRehash($storedHash)
        {
            return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
        }

        // }}}
        // {{{ upgradeLegacyHash()

        /**
         * Produce a native hash to replace a verified legacy hash.
         * Caller is responsible for persisting the returned hash to
         * su_pass immediately after a successful legacy verify().
         *
         * @param string $plainPassword The cleaned plaintext password just verified
         *
         * @return string The native password_hash() hash to store
         * @access public
         */
        public function upgradeLegacyHash($plainPassword)
        {
            return $this->hash($plainPassword);
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
