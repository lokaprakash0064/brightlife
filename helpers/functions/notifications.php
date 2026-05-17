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

if (!function_exists('getNotification')) {

    function getNotification() {
        $totNotification = 0;
        $sql = 'select count(msg_id) idcount from bl_message '
                . ' where cust_id = ? and msg_sts = 1';
        $ntData = DbOperations::getObject()->fetchData($sql, [$_SESSION['CID']]);
        $totNotification .= intval($ntData[0]['idcount']);
        //var_dump($totNotification);exit;
        die($totNotification);
    }

}
if (!function_exists('showNotification')) {

    function showNotification($cusId) {
        if (isLogged() === false) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $sql = 'select pp_cust_id from bl_partner_preference where signup_id = ?';
        $cusData = DbOperations::getObject()->fetchData($sql, [$cusId]);
        DbOperations::getObject()->transaction('start');
        DbOperations::getObject()->buildUpdateQuery(
                'bl_message', ['msg_sts'], ['msg_sts', 'cust_id']
        );
        $ins = [
            0,
            1,
            $cusData[0]['pp_cust_id']
        ];
        $suc = DbOperations::getObject()->runQuery($ins);
        if ($suc !== false) {
            DbOperations::getObject()->transaction('on');
        } else {
            DbOperations::getObject()->transaction('off');
        }
        $replaceData = [
            'PageTitle' => 'Brightlife Matrimony - Notifications ' . $_SESSION['USERNAME'],
            'MetaKeys' => '',
            'MetaDesc' => '',
            'CSSHelpers' => ['style.min.css'],
            'JSHelpers' => ['script.js'],
            'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner-user.html'),
            'Contents' => file_get_contents(PGS_DIR . DS . 'show-notifications.html'),
            'buttonHead' => $_SESSION['btn'],
            'CusId' => $_SESSION['CID']
        ];
        if (isLogged() === FALSE) {
            assignTemplate($replaceData, '');
        } else {
            assignTemplate($replaceData, 'logTemplate.html');
        }
    }

}
if (!function_exists('getNotificationData')) {

    function getNotificationData($blId) {
        if (isLogged() === false) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $sql = 'select sender_cus_id, msg_message, msg_dttm from bl_message '
                . ' where cust_id = ? order by msg_dttm desc';
        $msgData = DbOperations::getObject()->fetchData($sql, [$blId]);
        $aaData = [];
        if (count($msgData) > 0) {
            foreach ($msgData as $data) {
                $aaData[] = [
                    $data['sender_cus_id'],
                    $data['msg_message'],
                    date('d/m/Y H:i:s', strtotime($data['msg_dttm'])),
                    '<div class="text-center"><form method="post" action="' . ACCESS_URL . 'send-message/' . $data['sender_cus_id'] . '/' . $_SESSION['CID'] . '/" class="text-light"><div class="form-group"><textarea class="form-control" name="msg" id="msg" placeholder="Write Your Message"></textarea></div><div class="text-center"><button class="btn btn-danger" type="submit">Reply</button></div></form></div>'
                ];
            }
        }
        die(json_encode($aaData));
    }

}