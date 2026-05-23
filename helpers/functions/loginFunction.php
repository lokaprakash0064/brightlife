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
// common include file required MIND THE PATH (__DIR__ INSTEAD OF __FILE__)
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'include.php';

if (!function_exists('loginUser')) {

    function loginUser() {
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
        $sql = 'select su_id, su_name, su_dob, su_gender, caste_name, su_email from bl_sign_up '
                . ' left join bl_caste on su_caste = caste_id left join bl_partner_preference '
                . ' on su_id = signup_id where (su_email = ? or pp_cust_id = ?) and su_pass = ?';
        $cusData = DbOperations::getObject()->fetchData($sql, [$post['id'], $post['id'], DataFilter::getObject()->pwdHash($post['pwd'])]);
        if ((count($cusData) < 1) or ! isset($cusData)) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Your E-mail id / BL id or Password may be incorrect, Please try again';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
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