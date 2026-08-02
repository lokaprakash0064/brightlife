<?php

/**
 * This is the common responder file to respond public requests all over the project
 * and it contains the required actions with request data that will be commonly used
 * New functionality and actions can be added using class files 
 * 
 * @author Lokaprakash Behera <lokaprakash.behera@gmail.com>
 * @license http://thebestfreelancer.in The Best Freelancer. India
 * @version Build 2.0
 * @package Brightlife Matrimony
 * @copyright (c) 2012 - 2026, Brightlife Matrimony
 * @outputBuffering enabled
 */
// common include file required MIND THE PATH (__DIR__ INSTEAD OF __FILE__)
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'include.php';

if (!function_exists('loginUser')) {

    function loginUser() {
        csrf_validate();
        $post = DataFilter::getObject()->cleanData($_POST);
        if (!isset($post['id']) or empty($post['id'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter your E-mail Id / BL Id';
            header('Location' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['pwd']) or empty($post['pwd'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please enter your password';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // rate limiting: IP bucket is checked ahead of account resolution so a
        // locked-out IP never even reaches the account query; opportunistic
        // cleanup rides along with every login attempt (see AuthRateLimiter)
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        AuthRateLimiter::getObject()->cleanupExpiredBuckets(AUTH_RATE_LIMIT_CLEANUP_RETENTION_SECONDS);
        $ipBucket = AuthRateLimiter::getObject()->checkBucket(AuthRateLimiter::BUCKET_TYPE_IP, $clientIp, 'member_login');
        if ($ipBucket['isLocked']) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Your E-mail id / BL id or Password may be incorrect, Please try again';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // fetch by identifier only, password is checked in PHP below so that both
        // legacy (pwdHash) and native (password_hash) su_pass values can be verified
        $sql = 'select su_id, su_name, su_dob, su_gender, caste_name, su_email, su_pass from bl_sign_up '
                . ' left join bl_caste on su_caste = caste_id left join bl_partner_preference '
                . ' on su_id = signup_id where (su_email = ? or pp_cust_id = ?)';
        $cusData = DbOperations::getObject()->fetchData($sql, [$post['id'], $post['id']]);
        // known-account rule: an account bucket only ever exists for an
        // identifier that actually resolved to a row here
        $accountResolved = (count($cusData) >= 1) and isset($cusData[0]['su_id']);
        if ($accountResolved) {
            $accountBucket = AuthRateLimiter::getObject()->checkBucket(AuthRateLimiter::BUCKET_TYPE_ACCOUNT, $cusData[0]['su_id'], 'member_login');
            if ($accountBucket['isLocked']) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your E-mail id / BL id or Password may be incorrect, Please try again';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }

        if ((count($cusData) < 1) or ! isset($cusData) or ! PasswordService::getObject()->verify($post['pwd'], $cusData[0]['su_pass'])) {
            AuthRateLimiter::getObject()->recordFailure(AuthRateLimiter::BUCKET_TYPE_IP, $clientIp, 'member_login', AUTH_RATE_LIMIT_WINDOW_SECONDS, AUTH_RATE_LIMIT_MAX_ATTEMPTS, AUTH_RATE_LIMIT_LOCKOUT_SECONDS);
            if ($accountResolved) {
                AuthRateLimiter::getObject()->recordFailure(AuthRateLimiter::BUCKET_TYPE_ACCOUNT, $cusData[0]['su_id'], 'member_login', AUTH_RATE_LIMIT_WINDOW_SECONDS, AUTH_RATE_LIMIT_MAX_ATTEMPTS, AUTH_RATE_LIMIT_LOCKOUT_SECONDS);
            }
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Your E-mail id / BL id or Password may be incorrect, Please try again';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // regenerate the session ID now that authentication succeeded, before
        // any session data is written, to prevent session fixation
        session_regenerate_id(true);

        // rate limiting: a successful login clears only the account bucket;
        // the IP bucket is deliberately left untouched (frozen policy)
        AuthRateLimiter::getObject()->clearAccountBucket($cusData[0]['su_id']);

        // password verified above: transparently upgrade legacy or stale hashes
        if (PasswordService::getObject()->isLegacyHash($cusData[0]['su_pass'])
                or PasswordService::getObject()->needsRehash($cusData[0]['su_pass'])) {
            $newHash = PasswordService::getObject()->upgradeLegacyHash($post['pwd']);
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildUpdateQuery('bl_sign_up', ['su_pass'], ['su_id']);
            $suc = DbOperations::getObject()->runQuery([$newHash, $cusData[0]['su_id']]);
            if ($suc !== false) {
                DbOperations::getObject()->transaction('on');
            } else {
                DbOperations::getObject()->transaction('off');
            }
        }

        $sql = 'select pp_cust_id from bl_partner_preference where signup_id = ?';
        $cData = DbOperations::getObject()->fetchData($sql, [$cusData[0]['su_id']]);
        if (isset($cusData[0]) and ( count($cusData[0]) > 0)) {
            $_SESSION['UID'] = $cusData[0]['su_id'];
            $_SESSION['USERNAME'] = $cusData[0]['su_name'];
            $_SESSION['CID'] = $cData[0]['pp_cust_id'];
            $_SESSION['AGE'] = time() - intval(strtotime($cusData[0]['su_dob']));
            $_SESSION['CASTE'] = $cusData[0]['caste_name'];
            $_SESSION['EMAIL'] = $cusData[0]['su_email'];
            $_SESSION['GENDER'] = $cusData[0]['su_gender'];
            header('Location:' . ACCESS_URL . 'user-index/' . $cusData[0]['su_id'] . '/');
        }
    }

}