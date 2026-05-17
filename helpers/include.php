<?php

//die('SITE IS UNDER CONSTRUCTION');
/**
 * This is the common configuration file to be included all over the project
 * and it contains the required constants and variables that will be commonly used
 * New functionality and classes can be added via class files but this file should
 * remain intact if possible.
 * 
 * @version Build 1.0
 * @outputBuffering disabled
 */
session_start();
/*
 * set the error handler in ALL, DEPRICATED & STRICT mode
 * so that no errors (not even syntactical) can be tolerated
 */
error_reporting(E_ALL | E_STRICT);

/*
 * set default time-zone to confirm the timezone
 * else it will show an error that system time is not reliable
 * Change it as per your timezone
 */
date_default_timezone_set('Asia/Calcutta');

/*
 * set maximum script execution time to overcome
 * timeout situations
 * I have set it for 5 minutes, i.e. 5 mins * 60 seconds,
 * But dont use unlimited or too much time as it may cause
 * too much server load and even breakdown
 */
set_time_limit(5 * 60);


/**
 * Define commonly used Constants so that using them will be easier
 * Here I've tried to name the constants in capitals so that they can be
 * distinguished clearly without any confusion, which is a standard also
 * Hence I'm going to name all GLOBALS & CONSTANTS in CAPITALS
 * Variables in camelCase,
 * private, protected properties and methods with _underscrore
 * and all pear2 standards of coding PHP
 * @link http://pear.php.net/manual/en/coding-standards.php for more info
 */
// define the server or host
define('HOST', $_SERVER['HTTP_HOST']);
/*
 * define the installed directory name
 * as this file is inside 'classes' directory, we must go one level
 * up to get the base directory name
 */
//define('INSTALL_DIR', basename(dirname(dirname(__FILE__))));
define('INSTALL_DIR', 'brightlife');
// get http protocol
define(
        'PROTOCOL', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://"
);

// define complete host url by adding two slashes at start and end
define('ACCESS_URL', PROTOCOL . HOST . (INSTALL_DIR === '' ? '/' : '/' . INSTALL_DIR . '/'));

// define directory separator
define('DS', DIRECTORY_SEPARATOR);

// define complete directory path in order to get the actual directory path relative to the system
define('DIRPATH', dirname(dirname(__FILE__)));

// define current date
define('CURDATE', date('d-m-Y'));

// define current time
define('CURTIME', date('h:i:s A'));

// define database format date
define('DBDATE', date('Y-m-d'));

// define database format time
define('DBTIME', date('H:i:s'));

// define database timestamp
define('DBTIMESTAMP', date('Y-m-d H:i:s'));

// define a temporary directory to store attachments till message being sent
define('UPLOAD_DIR', DIRPATH . DS . 'helpers' . DS . 'images' . DS . 'uploads');

// define max attachment files size could be sent
define('MAX_ATTACHMENT_SIZE', 10 * 1024 * 1024);

// define mail template location
define('TPL_DIR', DIRPATH . DS . 'helpers' . DS . 'tpls');

// define page contents location
define('PGS_DIR', DIRPATH . DS . 'helpers' . DS . 'pages');

/* * **********************************************************************************************************
 * Database details start here, we have to define all the database details so that
 * those can be connected via those credentials.
 */

// define database host
define('DBHOST', 'localhost');

// define database driver
define('DRIVER', 'mysql');

// define database name
define('DBNAME', 'matrimony');

// define database username
define('DBUSER', 'pma');

// define database password
define('DBPASS', 'password');

/*define('DBNAME', 'brightli_matrimony');
define('DBUSER', 'brightli_matri');
define('DBPASS', 'brightlife@mat');*/

/**
 * define a security salt to encrypt password
 * This is used for password encryption which is
 * irreversible and don't ever change after you
 * have logged out of admin panel, else you may
 * never reset your password.
 * CAUTION
 * If you want to change the salt, contact the author
 */
define('SECURITY_SALT', 'lk,;h``h_+/-lkL"\'Llk*&%67445_7!~lLKJHkjhkut');

/**
 * Function to autoload class files needed dynamically when new instance of the class is created
 * This autoload function need not be called but automatically fired
 * 
 * @package RegistrationSystem
 * @param string $className The name of the class which is called
 * @author Kirti Kumar Nayak <admin@thebestfreelancer.in>
 * @access public
 * @category CommonFunction
 * @link http://www.php.net/manual/en/function.autoload.php The autoload function documentation
 */
spl_autoload_register(function($className) {
    /**
     * variable to store the filename of the class
     * The common style is to make the string into lowercase
     * and append .class.php in order to make the class file name
     * Same is also followed to name a class file
     * 
     * @var string The class file name
     * @access private
     */
    $fileName = strtolower($className) . '.class.php';
    // check if the file exists
    if (file_exists(DIRPATH . DS . 'helpers' . DS . 'classes' . DS . $fileName)) {
        // if exists, require it
        require_once DIRPATH . DS . 'helpers' . DS . 'classes' . DS . $fileName;
    } else {
        die('The required class file not found. Path:' . DIRPATH . DS . 'helpers' . DS . 'classes' . DS . $fileName);
    }
});

/**
 * Function to get messages from session and format
 * the message as per Bootstrap design standard.
 * If you want any other type of message you can customize accordingly
 * 
 * @package RegistrationSyatem
 * @author Kirti Kumar Nayak <admin@thebestfreelancer.in>
 * @access public
 * @category CommonFunction
 * @return string The HTML formatted notification string
 */
if (!function_exists('getAlertMsg')) {

    function getAlertMsg() {
        // check if session has been set
        if (isset($_SESSION['STATUS']) and isset($_SESSION['MSG'])) {
            /**
             * if set initialize a variable to store the html formatted string
             * @access private
             * @var string The HTML formatted string
             */
            $formattedMsg = '';
            switch ($_SESSION['STATUS']) {
                case 'error':
                    // if status is error then format with related style and so on
                    $formattedMsg = '<div class="alert alert-danger alert-dismissable">'
                            . $_SESSION['MSG']
                            . '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                    break;
                case 'warning':
                    $formattedMsg = '<div class="alert alert-danger alert-dismissable">'
                            . $_SESSION['MSG']
                            . '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                    break;
                case 'success':
                    $formattedMsg = '<div class="alert alert-success alert-dismissable">'
                            . $_SESSION['MSG']
                            . '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                    break;

                default:
                    $formattedMsg = '<div class="alert alert-info alert-dismissable">'
                            . $_SESSION['MSG']
                            . '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                    break;
            }
            // at last unset the seeions set
            unset($_SESSION['STATUS']);
            unset($_SESSION['MSG']);
            // return the formatted string
            return $formattedMsg;
        }
        return false;
    }

}

if (!function_exists('isLogged')) {

    /**
     * function to check if user is logged in or not
     * and to return usertype/privilage
     * 
     * @package JKDiag
     * @author Kirti Kumar Nayak <admin@thebestfreelancer.in>
     * @access public
     * @category CommonFunction
     * @return boolean If user is logged in, returns true else false
     */
    function isLogged() {
        // check if user credentials has been set and the file to be accessed is correct
        if (isset($_SESSION['UID']) and ! empty($_SESSION['USERNAME'])) {
            return $_SESSION['USERNAME'];
        } else {
            return false;
        }
    }

}
if (!function_exists('isLoggedAdmin')) {

    /**
     * function to check if user is logged in or not
     * and to return usertype/privilage
     * 
     * @package Brightlife Matrimony
     * @author Lokaprakash Behera <lokaprakash.behera@gmail.com>
     * @access public
     * @category CommonFunction
     * @return a string If user is logged in, returns else false
     */
    function isLoggedAdmin() {
        // check if user credentials has been set and the file to be accessed is correct
        if (isset($_SESSION['AUID']) and ! empty($_SESSION['AUID'])) {
            return $_SESSION['AUID'];
        } else {
            return false;
        }
    }

}
if (!function_exists('assignTemplate')) {

    /**
     * function to check if user is logged in or not
     * and to redirect to the specific page if so
     * 
     * @param mixed $replacementArray The associative array to be replaced against defined keys
     * 
     * @package JKDiag
     * @author Kirti Kumar Nayak <admin@thebestfreelancer.in>
     * @access public
     * @category CommonFunction
     * @return void prints the data as per passed
     */
    function assignTemplate($replacementArray, $templateFileName = '', $outputAs = 'html') {
        // require the template for respective user to show the design
        if ($templateFileName === '') {
            $template = TPL_DIR . DS . 'template.html';
        } else {
            $template = TPL_DIR . DS . $templateFileName;
        }
        // check if the template exists else give out an error
        if (!file_exists($template)) {
            die('The required template : ' . $template . ' could not be found.');
        }
        // get the template contents
        $templateContents = file_get_contents($template);
        // assign the path of favicon icon
        $replacementArray['ImgPath'] = ACCESS_URL . 'helpers/images/';
        $replacementArray['FaviconPath'] = ACCESS_URL . 'helpers/images/ico/';
        $replacementArray['AbsUrl'] = ACCESS_URL;
        $replacementArray['TimeStamp'] = time();
        $replacementArray['CurYr'] = date('Y');
        // Set Home URL
        if (!isLogged()) {
            $replacementArray['HomeUrl'] = ACCESS_URL;
        } else {
            $replacementArray['HomeUrl'] = ACCESS_URL . 'user-index/' . $_SESSION['UID'] . '/';
        }
        // convert the css files array into a css link string
        if (array_key_exists('CSSHelpers', $replacementArray)) {
            $links = '';
            if (count($replacementArray['CSSHelpers']) > 0) {
                foreach ($replacementArray['CSSHelpers'] as $key => $value) {
                    $links .= '<link href="' . ACCESS_URL . 'helpers/css/' . $value . '" rel="stylesheet" type="text/css" media="all">';
                }
            }
            $replacementArray['CSSHelpers'] = $links;
        }
        // convert the js files array into a css link string
        if (array_key_exists('JSHelpers', $replacementArray)) {
            $links = '';
            if (count($replacementArray['JSHelpers']) > 0) {
                foreach ($replacementArray['JSHelpers'] as $key => $value) {
                    $links .= '<script src="' . ACCESS_URL . 'helpers/js/' . $value . '" type="text/javascript"></script>';
                }
            }
            $replacementArray['JSHelpers'] = $links;
        }
        $replacementArray['ErrMsgs'] = getAlertMsg();
        // assign the template contents into final contents
        $finalContents = $templateContents;
        foreach ($replacementArray as $key => $value) {
            $finalContents = preg_replace('/{' . $key . '}/', $value, $finalContents);
        }
        //$finalContents         = str_replace($rep, $links, $templateContents)
        // remove whitespaces to compress the contents
        $finalContents = str_replace(array('   ', '    ', "\r", "\n", "\r\n", "\n\r"), '', $finalContents);
        if ($outputAs === 'pdf') {
            try {
                require_once DIRPATH . DS . 'helpers' . DS . 'classes' . DS . 'html2pdf' . DS . 'Html2Pdf.php';
                //use Spipu\Html2Pdf\Html2Pdf;
                spl_autoload_unregister(array('HTML2PDF', '__autoload'));
                $pdf = new HTML2PDF('P', 'A4', 'en');
                $pdf->AddPage();
                $pdf->WriteHTML($finalContents);
                $pdf->Output('voucher.pdf', 'F');
            } catch (Exception $ex) {
                die('Error : ' . $ex->getMessage());
            }
        } else {
            echo $finalContents;
        }
    }

}
if (!function_exists('sendBookingMail')) {

    function sendBookingMail($subject, $name, $email, $contents) {
        require_once DIRPATH . DS . 'helpers' . DS . 'classes' . DS . 'phpmailer' . DS . 'PHPMailerAutoload.php';
        $mail = new PHPMailer();
        //$mail->isSendmail();
        $mail->isHTML(true);
        // tell phpmailer to send the mail via smtp
        $mail->isSMTP(true);
        $mail->SMTPDebug = 0;
        //Ask for HTML-friendly debug output
        //$mail->Debugoutput = 'html';
        //Set the hostname of the mail server
        $mail->Host = 'mail.brightlifematrimony.com';
        // use
        // $mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = '';
        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = 'tls';
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = 'support@brightlifematrimony.com';
        //Password to use for SMTP authentication
        $mail->Password = 'BZ@~?Dn@q9du';

        // Set who the message is to be sent from
        $mail->setFrom('support@brightlifematrimony.com', 'Brightlife Booking Query');
        $mail->addReplyTo($email, $name);
        // Set who the message is to be sent to
        //$mail->addAddress('lokaprakash.behera@gmail.com', 'Info Account - Toshali');
        $mail->addAddress('bjmohanty86@gmail.com', 'Admin - Brightlife Matrimony');
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:lokaprakash.behera@gmail.com?subject=UnsubscribeBrightlife>');

        $finalMailContents = $contents;
        $finalAltMailContents = strip_tags($contents);

        // Set the subject line
        $mail->Subject = $subject;
        //$mail->Body = $finalMailContents;
        $mail->msgHTML($finalMailContents, DIRPATH);
        // Replace the plain text body with one created manually
        $mail->AltBody = $finalAltMailContents;
        /**
         * send and store the response of the mail
         *
         * @var boolean True if success else failure message
         * @access private
         */
        return $mail->send();
    }

}

function sendBookingAcknowledgement($subject, $name, $email, $contents) {
    require_once DIRPATH . DS . 'helpers' . DS . 'classes' . DS . 'phpmailer' . DS . 'PHPMailerAutoload.php';
    $mail = new PHPMailer();
    $mail->isHTML(true);
    $mail->isSMTP(true);
    $mail->SMTPDebug = 0;
    $mail->Host = 'mail.brightlifematrimony.com';
    $mail->Port = '';
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = 'support@brightlifematrimony.com';
    $mail->Password = 'BZ@~?Dn@q9du';
    $mail->setFrom('support@brightlifematrimony.com', 'Brightlife Query');
    $mail->addReplyTo('bjmohanty86@gmail.com', 'Brightlife Admin');
    $mail->addAddress($email, $name);
    $mail->addCustomHeader('List-Unsubscribe', '<mailto:lokaprakash.behera@gmail.com?subject=UnsubscribeBrightlife>');
    $finalMailContents = $contents;
    $finalAltMailContents = strip_tags($contents);
    $mail->Subject = $subject;
    $mail->msgHTML($finalMailContents, DIRPATH);
    $mail->AltBody = $finalAltMailContents;
    return $mail->send();
}

// Set Caste Name
$sql = 'select caste_id, caste_name from bl_caste order by caste_name asc';
$cData = DbOperations::getObject()->fetchData($sql);
$caste = '';
foreach ($cData as $cDat) {
    $caste .= '<option value="' . $cDat['caste_id'] . '">' . $cDat['caste_name'] . '</option>';
}
// Set Religion
$sql = 'select rel_id, rel_name from religion order by rel_name asc';
$rData = DbOperations::getObject()->fetchData($sql);
$rel = '';
foreach ($rData as $rDat) {
    $rel .= '<option value="' . $rDat['rel_id'] . '">' . $rDat['rel_name'] . '</option>';
}
// Set Mother Tongue
$sql = 'select mt_id, mt_name from mother_tounge order by mt_name asc';
$mData = DbOperations::getObject()->fetchData($sql);
$mthrTng = '';
foreach ($mData as $mDat) {
    $mthrTng .= '<option value="' . $mDat['mt_id'] . '">' . $mDat['mt_name'] . '</option>';
}
// Set States
$sql = 'select state_id, state_name from bl_states order by state_name asc';
$sData = DbOperations::getObject()->fetchData($sql);
$state = '';
foreach ($sData as $sDat) {
    $state .= '<option value="' . $sDat['state_id'] . '">' . $sDat['state_name'] . '</option>';
}
// Set Rashi
$sql = 'select ras_id, ras_name from bl_rashi order by ras_name asc';
$rsData = DbOperations::getObject()->fetchData($sql);
$rashi = '';
foreach ($rsData as $rsDat) {
    $rashi .= '<option value="' . $rsDat['ras_id'] . '">' . $rsDat['ras_name'] . '</option>';
}
// Set Star
$sql = 'select star_id, star_name from bl_star order by star_name asc';
$stData = DbOperations::getObject()->fetchData($sql);
$star = '';
foreach ($stData as $stDat) {
    $star .= '<option value="' . $stDat['star_id'] . '">' . $stDat['star_name'] . '</option>';
}
// Set Education Details
$sql = 'select edu_id, edu_name from bl_education order by edu_name';
$edData = DbOperations::getObject()->fetchData($sql);
$education = '';
foreach ($edData as $eDat) {
    $education .= '<option value="' . $eDat['edu_id'] . '">' . $eDat['edu_name'] . '</option>';
}
// Set Occupation Details
$sql = 'select occ_id, occ_name from bl_occupation order by occ_name';
$ocData = DbOperations::getObject()->fetchData($sql);
$occupation = '';
foreach ($ocData as $oDat) {
    $occupation .= '<option value="' . $oDat['occ_id'] . '">' . $oDat['occ_name'] . '</option>';
}
// Set Income Details
$sql = 'select in_id, in_name from bl_income order by in_name';
$iData = DbOperations::getObject()->fetchData($sql);
$income = '';
foreach ($iData as $iDat) {
    $income .= '<option value="' . $iDat['in_id'] . '">' . $iDat['in_name'] . '</option>';
}
// Set Button Value
$buttonHead = '';
$sendMsg = '';
$imgBtn = '';
if (isLogged() === false) {
    $buttonHead .= '<a href="#" data-toggle="modal" data-target="#loginModal" class="btn btn-danger box-shadow mr-2">
                                <i class="fas fa-sign-in-alt"></i> Log In
                            </a>
                            <a href="#" data-toggle="modal" data-target="#signupModal" class="btn btn-danger box-shadow">
                                <i class="fas fa-user"></i> Register
                            </a>';
    $sendMsg .= '';
    $imgBtn .= '';
} else {
    $buttonHead .= '<a href="' . ACCESS_URL . 'logout/" class="btn btn-danger box-shadow mr-2">
                                <i class="fas fa-sign-out-alt"></i> Log Out
                            </a>
                            <a href="' . ACCESS_URL . 'show-notifications/' . $_SESSION['UID'] . '/" class="btn btn-secondary box-shadow mr-2">
                                <i class="fas fa-bell"></i> Notifications
                                <span class="badge badge-danger">
                                <span id="notification"></span>
                                </span>
                            </a>';
    $sendMsg .= '<div class="text-center">'
            . '<a href="#"  data-toggle="modal" data-target="#msgModal" class="btn btn-danger box-shadow">Send Message</a>'
            . '</div>';
    $imgBtn .= '<div class="text-center">'
            . '<a href="#" data-toggle="modal" data-target="#viewProfModal" class="btn btn-success"> <i class="fas fa-eye"></i> View Images</a>'
            . '</div>';
}
$_SESSION['btn'] = $buttonHead;
$_SESSION['msgUrl'] = $sendMsg;
$_SESSION['imgBtn'] = $imgBtn;
