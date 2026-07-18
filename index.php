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
 * @copyright (c) 2012 - 2019, Brightlife Matrimony
 * @outputBuffering enabled
 */
// Common Include file required
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'include.php';
// extract get data to get option by user
$opt = DataFilter::getObject()->cleanData($_GET);
if (isset($opt['acti0n']) and ! empty($opt['acti0n'])) {
    $req = explode('/', $opt['acti0n']);
    switch ($req[0]) {
        case 'about':
            $replaceData = [
                'PageTitle' => 'About Brightlife Matrimony',
                'MetaKeys' => 'About Brightlife Matrimony',
                'MetaDesc' => 'About Brightlife Matrimony',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'about.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'background-verification':
            $replaceData = [
                'PageTitle' => 'Background verification - Brigthtlife Matrimony',
                'MetaKeys' => 'Brigthtlife Matrimony Background verification',
                'MetaDesc' => 'Brigthtlife Matrimony Background verification',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'background-verification.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'online-services':
            $replaceData = [
                'PageTitle' => 'Online Services - Brigthtlife Matrimony',
                'MetaKeys' => 'Brigthtlife Matrimony Online Services',
                'MetaDesc' => 'Brigthtlife Matrimony Online Services',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'online-services.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'offline-services':
            $replaceData = [
                'PageTitle' => 'Offline Services - Brigthtlife Matrimony',
                'MetaKeys' => 'Brigthtlife Matrimony Offline Services',
                'MetaDesc' => 'Brigthtlife Matrimony Offline Services',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'offline-services.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'package':
            $replaceData = [
                'PageTitle' => 'Packages - Brigthtlife Matrimony',
                'MetaKeys' => 'Brigthtlife Matrimony Packages',
                'MetaDesc' => 'Brigthtlife Matrimony Packages',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'package.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'contact-us':
            $replaceData = [
                'PageTitle' => 'Contact Brigthtlife Matrimony',
                'MetaKeys' => 'Contact Brigthtlife Matrimony',
                'MetaDesc' => 'Contact Brigthtlife Matrimony',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-contact.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'contact-us.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'send-enquiry':
            $post = DataFilter::getObject()->cleanData($_POST);
            if (!isset($post['name']) or empty($post['name'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please Enter Your Name';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['mail']) or empty($post['mail'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please Enter Your E-mail Id';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['phn']) or empty($post['phn'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please Enter Your Phone Number';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['msg']) or empty($post['msg'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please Enter Your Message';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            try {
                $contents = '<p>' . $post['name'] . ' has sent you a Query mail. The details are:</p>'
                        . '<hr/>' . '<br/>'
                        . '<table style="width: 90%; border: 1px solid black; text-align: center;border-collapse:collapse" cellpadding="3" border="1">'
                        . '<tr>' . '<th style="text-align:left">' . 'Name' . '</th>'
                        . '<td style="text-align:left">' . $post['name'] . '</td>' . '</tr>'
                        . '<tr>' . '<th style="text-align:left">' . 'E-mail' . '</th>'
                        . '<td style="text-align:left">' . $post['mail'] . '</td>' . '</tr>'
                        . '<tr>' . '<th style="text-align:left">' . 'Contact Number' . '</th>'
                        . '<td style="text-align:left">' . $post['phn'] . '</td>' . '</tr>'          
                        . '<tr>' . '<th style="text-align:left">' . 'Message' . '</th>'
                        . '<td style="text-align:left">' . $post['msg'] . '</td>'
                        . '</tr>'
                        . '</table>'
                        . '<hr/>';
                $mailed = sendBookingMail('Query Mail from ' . $_SERVER['SERVER_NAME'] . ' ', $post['name'], $post['mail'], $contents);
                if ($mailed !== false) {
                    $contents = '<p>Dear ' . $post['name'] . ',</p>'
                            . '<p>We have received your query.<br/>'
                            . 'We will contact you very soon. You may also contact us for any enquiry.</p>'
                            . '<br/>Thank you<br/>Regards<br/>Brightlife Matrimony<br/>';
                    $amailed = sendBookingAcknowledgement('Contact Query', $post['name'], $post['mail'], $contents);
                    $_SESSION['STATUS'] = 'success';
                    $_SESSION['MSG'] = 'We got it. Thank you. We will talk with you shortly.';
                    header('Location:' . $_SERVER['HTTP_REFERER']);
                    exit;
                } else {
                    $_SESSION['STATUS'] = 'error';
                    $_SESSION['MSG'] = 'Sorry, can&#039;t send mail right now due to some technical errors. Please retry.';
                    header('Location:' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
            } catch (Exception $ex) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = $ex->getMessage();
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            break;
        case 'faq':
            $replaceData = [
                'PageTitle' => 'FAQ - Brigthtlife Matrimony',
                'MetaKeys' => 'Frequently Asked Question Brigthtlife Matrimony',
                'MetaDesc' => 'Frequently Asked Question Brigthtlife Matrimony',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'faq.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'terms-conditions':
            $replaceData = [
                'PageTitle' => 'Terms &amp; Conditions - Brigthtlife Matrimony',
                'MetaKeys' => 'Terms & Conditions Brigthtlife Matrimony',
                'MetaDesc' => 'Terms & Conditions Brigthtlife Matrimony',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'terms-conditions.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'save-sign-up':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'profFunction.php';
            saveSignUp();
            break;
        case 'personal-detail':
            $replaceData = [
                'PageTitle' => 'Personal Detail - Brigthtlife Matrimony',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . ''),
                'Contents' => file_get_contents(PGS_DIR . DS . 'personal-detail.html'),
                'buttonHead' => '',
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng,
                'custId' => $req[1],
                'rashi' => $rashi,
                'star' => $star,
                'state' => $state,
                'education' => $education,
                'occupation' => $occupation,
                'income' => $income
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'save-personal-detail':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'profFunction.php';
            savePerDet($req[1]);
            break;
        case 'partner-preference':
            $replaceData = [
                'PageTitle' => 'Partner Preference - Brigthtlife Matrimony',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . ''),
                'Contents' => file_get_contents(PGS_DIR . DS . 'partner-preference.html'),
                'buttonHead' => '',
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng,
                'custId' => $req[1],
                'rashi' => $rashi,
                'star' => $star,
                'state' => $state,
                'education' => $education,
                'occupation' => $occupation,
                'income' => $income
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'save-partner-preference':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'profFunction.php';
            savePartPref($req[1]);
            break;
        case 'thank-you':
            $sql = 'select pp_cust_id from bl_partner_preference where signup_id = ?';
            $data = DbOperations::getObject()->fetchData($sql, [$req[1]]);
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Thank You',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . ''),
                'Contents' => file_get_contents(PGS_DIR . DS . 'thank-you.html'),
                'blId' => $data[0]['pp_cust_id'],
                'buttonHead' => ''
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'search-by-id':
            $replaceData = [
                'PageTitle' => 'Search by ID - Brigthtlife Matrimony',
                'MetaKeys' => 'Search life partner',
                'MetaDesc' => 'Search life partner',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . ''),
                'Contents' => file_get_contents(PGS_DIR . DS . 'search-by-id.html'),
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'search':
            $replaceData = [
                'PageTitle' => 'Search - Brigthtlife Matrimony',
                'MetaKeys' => 'Search life partner',
                'MetaDesc' => 'Search life partner',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . ''),
                'Contents' => file_get_contents(PGS_DIR . DS . 'search.html'),
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng,
                'State' => $state,
                'buttonHead' => $_SESSION['btn'],
                'Caste' => $caste,
                'religion' => $rel,
                'mthrTng' => $mthrTng
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
        case 'profile':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'profFunction.php';
            viewProfile();
            break;
        case 'search-profile':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'profFunction.php';
            searchProf();
            break;
        case 'login':
            if (isLogged() === true) {
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'You are already logged in';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'loginFunction.php';
            loginUser();
            break;
        case 'user-index':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'userFunction.php';
            userIndex($req[1]);
            break;
        case 'add-image':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'userFunction.php';
            addImage($req[1]);
            $post = DataFilter::getObject()->cleanData($_POST);
            break;
        case 'set-dp':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'userFunction.php';
            setDp();
            break;
        case 'edit-profile':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'userFunction.php';
            editProf($req[1]);
            break;
        case 'save-edited-personal-detail':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'userFunction.php';
            saveEditedPerData($req[1]);
            break;
        case 'edit-partner-preference':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'userFunction.php';
            editPatPref($req[1]);
            break;
        case 'save-edit-partner-preference':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'userFunction.php';
            saveEditedPatPref($req[1]);
            break;
        case 'view-profile':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'profFunction.php';
            if (!isset($req[1]) or empty($req[1])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'No Customer ID found';
                header('Location:' . ACCESS_URL);
                exit;
            }
            viewProfSearch($req[1]);
            break;
        case 'send-message':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'profFunction.php';
            if (!isset($req[1]) or empty($req[1])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'No Customer ID found';
                header('Location:' . ACCESS_URL);
                exit;
            }
            if (!isset($req[2]) or empty($req[2])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please Login to send message';
                header('Location:' . ACCESS_URL);
                exit;
            }
            sendMsg($req[1], $req[2]);
            break;
        case 'notification':
            if (isLogged() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
                header('Location:' . ACCESS_URL);
                exit;
            }
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'notifications.php';
            getNotification();
            break;
        case 'show-notifications':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'notifications.php';
            showNotification($req[1]);
            break;
        case 'get-notification-data':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'notifications.php';
            getNotificationData($req[1]);
            break;
        case 'logout':
            session_destroy();
            @session_start();
            $_SESSION['STATUS'] = 'info';
            $_SESSION['MSG'] = 'You logged out successfully';
            session_write_close();
            header('Location:' . ACCESS_URL);
            break;
        case 'admin':
            if (isLogged() !== false) {
                session_destroy();
                @session_start();
                session_write_close();
                header('Location:' . ACCESS_URL . 'admin/');
                exit;
            }
            $replaceData = [
                'PageTitle' => 'Login to Bright Life Admin Panel - Manage Bright Life',
                'KeyWords' => 'Bright Life, Bright Life login panel, manage Bright Life website, Designed and Developed by Lokaprakash Behera <lokaprakash.behera@gmail.com>',
                'Descript' => 'Bright Life login panel to manage website, Designed and Developed by Lokaprakash Behera <lokaprakash.behera@gmail.com>',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => []
            ];
            assignTemplate($replaceData, 'loginTpl.html');
            break;
        case 'login-action':
            $post = DataFilter::getObject()->cleanData($_POST);

            if (!isset($post['uname']) or empty($post['uname'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please enter your Username';
                header('Location:' . ACCESS_URL . 'admin/');
                exit;
            }

            if (!isset($post['pass']) or empty($post['pass'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please enter your password';
                header('Location:' . ACCESS_URL . 'admin/');
                exit;
            }

            // fetch by identifier only, password is checked in PHP below so that both
            // legacy (pwdHash) and native (password_hash) su_pass values can be verified
            $sql = 'select su_id, su_name, su_email, su_pass from bl_sign_up where su_email = ?';
            $aData = DbOperations::getObject()->fetchData($sql, [$post['uname']]);
            if (count($aData) < 1 or ! isset($aData) or ! PasswordService::getObject()->verify($post['pass'], $aData[0]['su_pass'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Username or Password may be incorrect, Please try again';
                header('Location:' . ACCESS_URL . 'admin/');
                exit;
            } else {
                // password verified above: transparently upgrade legacy or stale hashes
                if (PasswordService::getObject()->isLegacyHash($aData[0]['su_pass'])
                        or PasswordService::getObject()->needsRehash($aData[0]['su_pass'])) {
                    $newHash = PasswordService::getObject()->upgradeLegacyHash($post['pass']);
                    DbOperations::getObject()->transaction('start');
                    DbOperations::getObject()->buildUpdateQuery('bl_sign_up', ['su_pass'], ['su_id']);
                    $suc = DbOperations::getObject()->runQuery([$newHash, $aData[0]['su_id']]);
                    if ($suc !== false) {
                        DbOperations::getObject()->transaction('on');
                    } else {
                        DbOperations::getObject()->transaction('off');
                    }
                }
                $_SESSION['AUID'] = $aData[0]['su_name'];
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'Welcome Administrator';
                header('Location:' . ACCESS_URL . 'admin-home/');
            }
            break;
        case 'admin-home':
            if (isLoggedAdmin() === false) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please Enter Administrator Username and Password to Continue';
                header('Location:' . ACCESS_URL . 'admin/');
                exit;
            }
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Admin Page',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css', 'brightlife-admin.css'],
                'JSHelpers' => ['script.js'],
                'Contents' => file_get_contents(PGS_DIR . DS . 'view-admin-data.html')
            ];
            assignTemplate($replaceData, 'adminTpl.html');
            break;
        case 'get-admin-data':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'adminFunctions.php';
            getAdminData();
            break;
        case 'edit-signup-admin':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'adminFunctions.php';
            editSignupAdmin($req[1]);
            break;
        case 'save-signup-admin':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'adminFunctions.php';
            saveSignUpAdmin($req[1]);
            break;
        case 'edit-profile-admin':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'adminFunctions.php';
            editProfAdmin($req[1]);
            break;
        case 'save-prof-admin':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'adminFunctions.php';
            saveProfAdmin($req[1]);
            break;
        case 'edit-part-pref-admin':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'adminFunctions.php';
            editPartPrefAdmin($req[1]);
            break;
        case 'save-part-pref-admin':
            require_once DIRPATH . DS . 'helpers' . DS . 'functions' . DS . 'adminFunctions.php';
            savePartPrefAdmin($req[1]);
            break;
        case 'log-out':
            session_destroy();
            @session_start();
            $_SESSION['STATUS'] = 'info';
            $_SESSION['MSG'] = 'You logged out successfully';
            session_write_close();
            header('Location:' . ACCESS_URL . 'admin/');
            break;
        default :
            $replaceData = [
                'PageTitle' => '404 Not Found, Brightlife Matrimony',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . '404.html'),
                'buttonHead' => $_SESSION['btn']
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
            break;
    }
} else {
    $replaceData = [
        'PageTitle' => 'Home - Brightlife Matrimony',
        'MetaKeys' => 'Matrimony website, Marriage in Odisha, Brightlife Matrimony',
        'MetaDesc' => 'Brightlife Matrimony: One of the best matrimony Website in Odisha. Trusted by many Brides & grooms. Register Now.',
        'CSSHelpers' => ['style.min.css', 'jquery.jConveyorTicker.min.css'],
        'JSHelpers' => ['script.js', 'jquery.jConveyorTicker.min.js'],
        'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-home.html'),
        'Contents' => file_get_contents(PGS_DIR . DS . 'home.html'),
        'buttonHead' => $_SESSION['btn'],
        'Caste' => $caste,
        'religion' => $rel,
        'mthrTng' => $mthrTng,
        'State' => $state
    ];
    if (isLogged() === FALSE) {
        assignTemplate($replaceData, '');
    } else {
        assignTemplate($replaceData, 'logTemplate.html');
    }
}