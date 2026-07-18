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
// common include file required
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'include.php';
// isLoggedAdmin() returns the admin's name (string) or false, never boolean true —
// compare against false so the else-branch (deny + exit) actually triggers.
if (isLoggedAdmin() !== false) {
    if (!function_exists('getAdminData')) {

        function getAdminData() {
            $sql = 'select pp_cust_id, signup_id, email_id, pp_dttm from bl_partner_preference '
                    . ' order by pp_dttm desc';
            $pData = DbOperations::getObject()->fetchData($sql);
            $suSql = 'select su_name, su_dob, su_mobile, su_email from bl_sign_up where su_id = ?';
            $suRes = DbOperations::getObject()->prepareQuery($suSql);
            $aaData = [];
            if (count($pData) > 0) {
                foreach ($pData as $dat) {
                    $suData = DbOperations::getObject()->fetchData('', [$dat['signup_id']], FALSE, $suRes);
                    $aaData[] = [
                        $dat['pp_cust_id'],
                        $suData[0]['su_name'],
                        $suData[0]['su_email'],
                        $suData[0]['su_mobile'],
                        date('jS M Y', strtotime($suData[0]['su_dob'])),
                        date('d/m/Y H:i:s', strtotime($dat['pp_dttm'])),
                        '<div class="dropdown show">
                            <a class="ba-action-btn dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Click here for edit options">
                              <i class="fas fa-user-cog"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="' . ACCESS_URL . 'edit-signup-admin/' . $dat['signup_id'] . '/" title="Edit Signup Data"><i class="fas fa-user-edit"></i> Edit Signup Data</a>
                                <a class="dropdown-item" href="' . ACCESS_URL . 'edit-profile-admin/' . $dat['signup_id'] . '/" title="Edit Profile"><i class="fas fa-user-edit"></i> Edit Profile</a>
                                <a class="dropdown-item" href="' . ACCESS_URL . 'edit-part-pref-admin/' . $dat['signup_id'] . '/" title="Edit Partner Preference"><i class="fas fa-user-edit"></i> Edit Partner Preference</a>
                              </div>
                            </div>'
                    ];
                }
            }
            die(json_encode($aaData));
        }

    }
    if (!function_exists('editSignupAdmin')) {

        function editSignupAdmin($cId) {
            $sql = 'select su_name, su_dob, su_gender, su_religion, su_caste, '
                    . ' su_mobile, su_email from bl_sign_up where su_id = ?';
            $suData = DbOperations::getObject()->fetchData($sql, [$cId]);
            $gender = '';
            switch ($suData[0]['su_gender']) {
                case '1':
                    $gender .= '<label class="col-12 text-danger">Gender</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="gender" id="female" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="female">Female</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="gender" id="male" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="male">Male</label></div>';
                    break;
                case '2':
                    $gender .= '<label class="col-12 text-danger">Gender</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="gender" id="female" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="female">Female</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="gender" id="male" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="male">Male</label></div>';
                    break;
                default :
                    $gender .= '';
                    break;
            }
            $religion = '';
            $sql = 'select rel_id, rel_name from religion';
            $rData = DbOperations::getObject()->fetchData($sql);
            foreach ($rData as $dat) {
                $religion .= '<option' . ((isset($suData[0]['su_religion']) and ( $suData[0]['su_religion'] === $dat['rel_id'])) ? ' selected="selected" ' : '') . ' value="' . $dat['rel_id'] . '">' . $dat['rel_name'];
            }
            $caste = '';
            $sql = 'select caste_id, caste_name from bl_caste';
            $cData = DbOperations::getObject()->fetchData($sql);
            foreach ($cData as $dat) {
                $caste .= '<option' . ((isset($suData[0]['su_caste']) and ( $suData[0]['su_caste'] === $dat['caste_id'])) ? ' selected="selected" ' : '') . ' value="' . $dat['caste_id'] . '">' . $dat['caste_name'];
            }
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Edit Signup Data - Admin',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css', 'brightlife-admin.css'],
                'JSHelpers' => ['script.js'],
                'Contents' => file_get_contents(PGS_DIR . DS . 'edit-signup-admin.html'),
                'cId' => $cId,
                'Name' => $suData[0]['su_name'],
                'DOB' => date('d-m-Y', strtotime($suData[0]['su_dob'])),
                'Gender' => $gender,
                'religion' => $religion,
                'caste' => $caste,
                'mobNo' => $suData[0]['su_mobile'],
                'email' => $suData[0]['su_email']
            ];
            assignTemplate($replaceData, 'adminTpl.html');
        }

    }
    if (!function_exists('saveSignUpAdmin')) {

        function saveSignUpAdmin($cId) {
            $post = DataFilter::getObject()->cleanData($_POST);
            //var_dump($post);exit;
            if (!isset($post['name']) or empty($post['name'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Name cann\'t be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['dob']) or empty($post['dob'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Date of Birth cann\'t be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['mob']) or empty($post['mob'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Mobile Number cann\'t be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            $sql = 'select count(su_email) as unmcount from bl_sign_up where su_email';
            $counted = DbOperations::getObject()->fetchData($sql, [$post['mail']]);
            if (intval($counted[0]['unmcount']) > 1) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'This Username or E-mail id is not available, someone has already taken this username';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['rpass']) or ( $post['pass'] !== $post['rpass'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please Re-enter Password Correctly';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildUpdateQuery(
                    'bl_sign_up', ['su_name', 'su_dob', 'su_gender', 'su_religion', 'su_caste', 'su_mobile', 'su_email', 'su_pass', 'su_dttm'], ['su_id']
            );
            $ins = [
                $post['name'],
                date('Y-m-d', strtotime($post['dob'])),
                $post['gender'],
                $post['rel'],
                $post['caste'],
                $post['mob'],
                $post['mail'],
                PasswordService::getObject()->hash($post['rpass']),
                DBTIMESTAMP,
                $cId
            ];
            $suc = DbOperations::getObject()->runQuery($ins);
            if ($suc !== false) {
                DbOperations::getObject()->transaction('on');
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'Signup Data updated successfully';
                header('Location:' . ACCESS_URL . 'admin-home/');
                exit;
            } else {
                DbOperations::getObject()->transaction('off');
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Some error occured while updating data, Please retry';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }

    }
    if (!function_exists('editProfAdmin')) {

        function editProfAdmin($cId) {
            $sql = 'select pd_profCreated, pd_maritalStatus, pd_rashi, pd_star, '
                    . ' pd_gothra, pd_residingState, pd_residingCity, pd_height, '
                    . ' pd_weight, pd_bodyType, pd_complexion, pd_phyisicalStatus, '
                    . ' pd_education, pd_eduDetails, pd_occupation, pd_occDetails, '
                    . ' pd_employedIn, pd_income, pd_food, pd_smoking, pd_drinking, '
                    . ' pd_familyStatus, pd_familyType, pd_familyValues, pd_occFather, '
                    . ' pd_occMother, pd_desc from bl_personal_detail where sign_up_id = ?';
            $perData = DbOperations::getObject()->fetchData($sql, [$cId]);
            $profCrtdFor = '';
            switch ($perData[0]['pd_profCreated']) {
                case '1':
                    $profCrtdFor .= '<label class="col-12 text-info">Profile Created For</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="self" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="self">Self</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="son" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="son">Son</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="daughter" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="daughter">Daughter</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="brother" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="brother">Brother</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="relative" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="relative">Relative</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="sister" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="sister">Sister</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="friend" value="7" class="form-check-input">'
                            . '<label class="form-check-label" for="friend">Friend</label></div>';
                    break;
                case '2':
                    $profCrtdFor .= '<label class="col-12 text-info">Profile Created For</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="self" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="self">Self</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="son" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="son">Son</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="daughter" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="daughter">Daughter</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="brother" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="brother">Brother</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="relative" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="relative">Relative</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="sister" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="sister">Sister</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="friend" value="7" class="form-check-input">'
                            . '<label class="form-check-label" for="friend">Friend</label></div>';
                    break;
                case '3':
                    $profCrtdFor .= '<label class="col-12 text-info">Profile Created For</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="self" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="self">Self</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="son" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="son">Son</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="daughter" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="daughter">Daughter</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="brother" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="brother">Brother</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="relative" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="relative">Relative</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="sister" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="sister">Sister</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="friend" value="7" class="form-check-input">'
                            . '<label class="form-check-label" for="friend">Friend</label></div>';
                    break;
                case '4':
                    $profCrtdFor .= '<label class="col-12 text-info">Profile Created For</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="self" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="self">Self</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="son" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="son">Son</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="daughter" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="daughter">Daughter</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="brother" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="brother">Brother</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="relative" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="relative">Relative</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="sister" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="sister">Sister</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="friend" value="7" class="form-check-input">'
                            . '<label class="form-check-label" for="friend">Friend</label></div>';
                    break;
                case '5':
                    $profCrtdFor .= '<label class="col-12 text-info">Profile Created For</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="self" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="self">Self</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="son" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="son">Son</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="daughter" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="daughter">Daughter</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="brother" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="brother">Brother</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="relative" value="5" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="relative">Relative</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="sister" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="sister">Sister</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="friend" value="7" class="form-check-input">'
                            . '<label class="form-check-label" for="friend">Friend</label></div>';
                    break;
                case '6':
                    $profCrtdFor .= '<label class="col-12 text-info">Profile Created For</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="self" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="self">Self</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="son" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="son">Son</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="daughter" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="daughter">Daughter</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="brother" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="brother">Brother</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="relative" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="relative">Relative</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="sister" value="6" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="sister">Sister</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="friend" value="7" class="form-check-input">'
                            . '<label class="form-check-label" for="friend">Friend</label></div>';
                    break;
                case '7':
                    $profCrtdFor .= '<label class="col-12 text-info">Profile Created For</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="self" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="self">Self</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="son" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="son">Son</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="daughter" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="daughter">Daughter</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="brother" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="brother">Brother</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="relative" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="relative">Relative</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="sister" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="sister">Sister</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="prCrt" id="friend" value="7" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="friend">Friend</label></div>';
                    break;
                default :
                    $profCrtdFor .= '';
                    break;
            }
            $martStat = '';
            switch ($perData[0]['pd_maritalStatus']) {
                case '1':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                case '2':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                case '3':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                case '4':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                default :
                    $martStat .= '';
                    break;
            }
            $rashi = '';
            $sql = 'select ras_id, ras_name from bl_rashi';
            $rData = DbOperations::getObject()->fetchData($sql);
            foreach ($rData as $dat) {
                $rashi .= '<option' . ((isset($perData[0]['pd_rashi']) and $perData[0]['pd_rashi'] === $dat['ras_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['ras_id'] . '">' . $dat['ras_name'] . '</option>';
            }
            $star = '';
            $sql = 'select star_id, star_name from bl_star';
            $sData = DbOperations::getObject()->fetchData($sql);
            foreach ($sData as $dat) {
                $star .= '<option' . ((isset($perData[0]['pd_star']) and $perData[0]['pd_star'] === $dat['star_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['star_id'] . '">' . $dat['star_name'] . '</option>';
            }
            $state = '';
            $sql = 'select state_id, state_name from bl_states';
            $stData = DbOperations::getObject()->fetchData($sql);
            foreach ($stData as $dat) {
                $state .= '<option' . ((isset($perData[0]['pd_residingState']) and $perData[0]['pd_residingState'] === $dat['state_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['state_id'] . '">' . $dat['state_name'] . '</option>';
            }
            $bodyType = '';
            switch ($perData[0]['pd_bodyType']) {
                case '1':
                    $bodyType .= '<label class="col-12 text-info">Body Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="average" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="average">Average</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="athletic" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="athletic">Athletic</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="slim" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="slim">Slim</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="heavy" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="heavy">Heavy</label></div>';
                    break;
                case '2':
                    $bodyType .= '<label class="col-12 text-info">Body Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="average" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="average">Average</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="athletic" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="athletic">Athletic</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="slim" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="slim">Slim</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="heavy" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="heavy">Heavy</label></div>';
                    break;
                case '3':
                    $bodyType .= '<label class="col-12 text-info">Body Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="average" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="average">Average</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="athletic" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="athletic">Athletic</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="slim" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="slim">Slim</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="heavy" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="heavy">Heavy</label></div>';
                    break;
                case '4':
                    $bodyType .= '<label class="col-12 text-info">Body Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="average" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="average">Average</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="athletic" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="athletic">Athletic</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="slim" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="slim">Slim</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="bodyType" id="heavy" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="heavy">Heavy</label></div>';
                    break;
                default :
                    $bodyType .= '';
                    break;
            }
            $complexion = '';
            switch ($perData[0]['pd_complexion']) {
                case '1':
                    $complexion .= '<label class="col-12 text-info">Complexion</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="vryFair" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="vryFair">Very Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="fair" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="fair">Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatish" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatish">Wheatish</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatishBrown" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatishBrown">Wheatish Brown</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="dark" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="dark">Dark</label></div>';
                    break;
                case '2':
                    $complexion .= '<label class="col-12 text-info">Complexion</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="vryFair">Very Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="fair" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="fair">Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatish" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatish">Wheatish</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatishBrown" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatishBrown">Wheatish Brown</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="dark" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="dark">Dark</label></div>';
                    break;
                case '3':
                    $complexion .= '<label class="col-12 text-info">Complexion</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="vryFair">Very Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="fair" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="fair">Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatish" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatish">Wheatish</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatishBrown" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatishBrown">Wheatish Brown</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="dark" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="dark">Dark</label></div>';
                    break;
                case '4':
                    $complexion .= '<label class="col-12 text-info">Complexion</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="vryFair">Very Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="fair" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="fair">Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatish" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatish">Wheatish</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatishBrown" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatishBrown">Wheatish Brown</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="dark" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="dark">Dark</label></div>';
                    break;
                case '5':
                    $complexion .= '<label class="col-12 text-info">Complexion</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="vryFair">Very Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="fair" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="fair">Fair</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatish" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatish">Wheatish</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="wheatishBrown" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="wheatishBrown">Wheatish Brown</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="complexion" id="dark" value="5" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="dark">Dark</label></div>';
                    break;
                default :
                    $complexion .= '';
                    break;
            }
            $phyStat = '';
            switch ($perData[0]['pd_phyisicalStatus']) {
                case '1':
                    $phyStat .= '<label class="col-12 text-info">Phyisical Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="phyisicalStatus" id="normal" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="normal">Normal</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="phyisicalStatus" id="phyChallenged" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="phyChallenged">Physically Challenged</label></div>';
                    break;
                case '2':
                    $phyStat .= '<label class="col-12 text-info">Phyisical Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="phyisicalStatus" id="normal" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="normal">Normal</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="phyisicalStatus" id="phyChallenged" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="phyChallenged">Physically Challenged</label></div>';
                    break;
                default :
                    $phyStat .= '';
                    break;
            }
            $education = '';
            $sql = 'select edu_id, edu_name from bl_education';
            $eData = DbOperations::getObject()->fetchData($sql);
            foreach ($eData as $dat) {
                $education .= '<option' . ((isset($perData[0]['pd_education']) and $perData[0]['pd_education'] === $dat['edu_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['edu_id'] . '">' . $dat['edu_name'] . '</option>';
            }
            $occupation = '';
            $sql = 'select occ_id, occ_name from bl_occupation';
            $oData = DbOperations::getObject()->fetchData($sql);
            foreach ($oData as $dat) {
                $occupation .= '<option' . ((isset($perData[0]['pd_occupation']) and $perData[0]['pd_occupation'] === $dat['occ_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['occ_id'] . '">' . $dat['occ_name'] . '</option>';
            }
            $emplIn = '';
            switch ($perData[0]['pd_employedIn']) {
                case '1':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '2':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '3':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '4':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '5':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '6':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                default :
                    $emplIn .= '';
                    break;
            }
            $income = '';
            $sql = 'select in_id, in_name from bl_income';
            $iData = DbOperations::getObject()->fetchData($sql);
            foreach ($iData as $dat) {
                $income .= '<option' . ((isset($perData[0]['pd_income']) and $perData[0]['pd_income'] === $dat['in_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['in_id'] . '">' . $dat['in_name'] . '</option>';
            }
            $food = '';
            switch ($perData[0]['pd_food']) {
                case '1':
                    $food .= '<label class="col-12 text-info">Food</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="vegetarian" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="vegetarian">Vegetarian</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="nonVegitarian" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="nonVegitarian">Non Vegitarian</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="eggetarian" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="eggetarian">Eggetarian</label></div>';
                    break;
                case '2':
                    $food .= '<label class="col-12 text-info">Food</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="vegetarian" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="vegetarian">Vegetarian</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="nonVegitarian" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="nonVegitarian">Non Vegitarian</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="eggetarian" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="eggetarian">Eggetarian</label></div>';
                    break;
                case '3':
                    $food .= '<label class="col-12 text-info">Food</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="vegetarian" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="vegetarian">Vegetarian</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="nonVegitarian" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="nonVegitarian">Non Vegitarian</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="food" id="eggetarian" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="eggetarian">Eggetarian</label></div>';
                    break;
                default :
                    $food .= '';
                    break;
            }
            $smoke = '';
            switch ($perData[0]['pd_smoking']) {
                case '1':
                    $smoke .= '<label class="col-12 text-info">Smoking</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="no" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="no">No</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="occasionally" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="occasionally">Occasionally</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="yes" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="yes">Yes</label></div>';
                    break;
                case '2':
                    $smoke .= '<label class="col-12 text-info">Smoking</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="no" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="no">No</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="occasionally" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="occasionally">Occasionally</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="yes" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="yes">Yes</label></div>';
                    break;
                case '3':
                    $smoke .= '<label class="col-12 text-info">Smoking</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="no" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="no">No</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="occasionally" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="occasionally">Occasionally</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="smoking" id="yes" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="yes">Yes</label></div>';
                    break;
                default :
                    $smoke .= '';
                    break;
            }
            $drink = '';
            switch ($perData[0]['pd_drinking']) {
                case '1':
                    $drink .= '<label class="col-12 text-info">Drinking</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="no" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="no">No</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="occasionally" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="occasionally">Occasionally</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="yes" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="yes">Yes</label></div>';
                    break;
                case '2':
                    $drink .= '<label class="col-12 text-info">Drinking</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="no" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="no">No</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="occasionally" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="occasionally">Occasionally</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="yes" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="yes">Yes</label></div>';
                    break;
                case '3':
                    $drink .= '<label class="col-12 text-info">Drinking</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="no" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="no">No</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="occasionally" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="occasionally">Occasionally</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="drinking" id="yes" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="yes">Yes</label></div>';
                    break;
                default :
                    $drink .= '';
                    break;
            }
            $famStat = '';
            switch ($perData[0]['pd_familyStatus']) {
                case '1':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                case '2':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                case '3':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                case '4':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                default :
                    $famStat .= '';
                    break;
            }
            $famTyp = '';
            switch ($perData[0]['pd_familyType']) {
                case '1':
                    $famTyp .= '<label class="col-12 text-info">Family Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="joint" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="joint">Joint</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="Nuclear" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="Nuclear">Nuclear</label></div>';
                    break;
                case '2':
                    $famTyp .= '<label class="col-12 text-info">Family Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="joint" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="joint">Joint</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="Nuclear" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="Nuclear">Nuclear</label></div>';
                    break;
                default :
                    $famTyp .= '';
                    break;
            }
            $famVal = '';
            switch ($perData[0]['pd_familyValues']) {
                case '1':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                case '2':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                case '3':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                case '4':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                default :
                    $famVal .= '';
                    break;
            }
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Edit Profile - Admin',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css', 'brightlife-admin.css'],
                'JSHelpers' => ['script.js'],
                'Contents' => file_get_contents(PGS_DIR . DS . 'edit-prof-admin.html'),
                'cId' => $cId,
                'profCrtdFor' => $profCrtdFor,
                'martStat' => $martStat,
                'rashi' => $rashi,
                'star' => $star,
                'gothra' => $perData[0]['pd_gothra'],
                'state' => $state,
                'city' => $perData[0]['pd_residingCity'],
                'height' => $perData[0]['pd_height'],
                'weight' => $perData[0]['pd_weight'],
                'bodyType' => $bodyType,
                'complexion' => $complexion,
                'phyStat' => $phyStat,
                'education' => $education,
                'eduDet' => $perData[0]['pd_eduDetails'],
                'occupation' => $occupation,
                'occDet' => $perData[0]['pd_occDetails'],
                'emplIn' => $emplIn,
                'income' => $income,
                'food' => $food,
                'smoke' => $smoke,
                'drink' => $drink,
                'famStat' => $famStat,
                'famTyp' => $famTyp,
                'famVal' => $famVal,
                'occFat' => $perData[0]['pd_occFather'],
                'occMot' => $perData[0]['pd_occMother'],
                'desc' => $perData[0]['pd_desc']
            ];
            assignTemplate($replaceData, 'adminTpl.html');
        }

    }
    if (!function_exists('saveProfAdmin')) {

        function saveProfAdmin($cuId) {
            $post = DataFilter::getObject()->cleanData($_POST);
            if (!isset($post['prCrt']) or empty($post['prCrt'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = '&#39;Profile Created for&#39; can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['marStat']) or empty($post['marStat'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = '&#39;Marital Status&#39; can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['gothra']) or empty($post['gothra'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Gothra can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['city']) or empty($post['city'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'City can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['height']) or empty($post['height'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Height can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['weight']) or empty($post['weight'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Weight can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['bodyType']) or empty($post['bodyType'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Body Type can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['complexion']) or empty($post['complexion'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'complexion can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['phyisicalStatus']) or empty($post['phyisicalStatus'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Physical Status can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['eduDet']) or empty($post['eduDet'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Education details can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['occDet']) or empty($post['occDet'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Occupation details can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['desc']) or empty($post['desc']) or ( strlen($post['desc']) >= 300)) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Occupation details can not be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            $sql = 'select su_name from bl_sign_up where su_id = ?';
            $sData = DbOperations::getObject()->fetchData($sql, [$cuId]);
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildUpdateQuery(
                    'bl_personal_detail', ['pd_profCreated', 'pd_maritalStatus', 'pd_rashi', 'pd_star', 'pd_gothra', 'pd_residingState', 'pd_residingCity', 'pd_height', 'pd_weight', 'pd_bodyType', 'pd_complexion', 'pd_phyisicalStatus', 'pd_education', 'pd_eduDetails', 'pd_occupation', 'pd_occDetails', 'pd_employedIn', 'pd_income', 'pd_food', 'pd_smoking', 'pd_drinking', 'pd_familyStatus', 'pd_familyType', 'pd_familyValues', 'pd_occFather', 'pd_occMother', 'pd_desc', 'pd_dttm'], ['sign_up_id']
            );
            $ins = [
                $post['prCrt'],
                $post['marStat'],
                $post['rashi'],
                $post['star'],
                $post['gothra'],
                $post['state'],
                $post['city'],
                $post['height'],
                $post['weight'],
                $post['bodyType'],
                $post['complexion'],
                $post['phyisicalStatus'],
                $post['edu'],
                $post['eduDet'],
                $post['ocu'],
                $post['occDet'],
                $post['employedIn'],
                $post['income'],
                $post['food'],
                $post['smoking'],
                $post['drinking'],
                $post['familyStatus'],
                $post['familyType'],
                $post['familyValues'],
                $post['occFat'],
                $post['occMot'],
                $post['desc'],
                DBTIMESTAMP,
                $cuId
            ];
            $suc = DbOperations::getObject()->runQuery($ins);
            if ($suc !== false) {
                DbOperations::getObject()->transaction('on');
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'Personal Detail Updated Successfully of ' . $sData[0]['su_name'];
                header('Location:' . ACCESS_URL . 'admin-home/');
                exit;
            } else {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Some error occured, Please retry';
                header('Location:' . ACCESS_URL . 'admin-home/');
                exit;
            }
        }

    }
    if (!function_exists('editPartPrefAdmin')) {

        function editPartPrefAdmin($cuId) {
            $sql = 'select pp_maritial_stat, pp_religion, pp_caste, pp_residing_state, '
                    . ' pp_residing_city, pp_ageFrom, pp_ageTo, pp_education, '
                    . ' pp_occupation, pp_employedIn, pp_income, pp_familyStatus, '
                    . ' pp_familyType, pp_familyValues, pp_partnerDesc from '
                    . ' bl_partner_preference where signup_id = ?';
            $ppData = DbOperations::getObject()->fetchData($sql, [$cuId]);
            $martStat = '';
            switch ($ppData[0]['pp_maritial_stat']) {
                case '1':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                case '2':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                case '3':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                case '4':
                    $martStat .= '<label class="col-12 text-info">Marital Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="unmarried" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="unmarried">Unmarried</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="widower" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="widower">Widower</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="divorced" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="divorced">Divorced</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="marStat" id="awtDivorce" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="awtDivorce">Awaiting Divorce</label></div>';
                    break;
                default :
                    $martStat .= '';
                    break;
            }
            $religion = '';
            $sql = 'select rel_id, rel_name from religion order by rel_name asc';
            $rData = DbOperations::getObject()->fetchData($sql);
            foreach ($rData as $dat) {
                $religion .= '<option' . ((isset($ppData[0]['pp_religion']) and $ppData[0]['pp_religion'] === $dat['rel_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['rel_id'] . '">' . $dat['rel_name'] . '</option>';
            }
            $caste = '';
            $sql = 'select caste_id, caste_name from bl_caste';
            $cData = DbOperations::getObject()->fetchData($sql);
            foreach ($cData as $dat) {
                $caste .= '<option' . ((isset($ppData[0]['pp_caste']) and $ppData[0]['pp_caste'] === $dat['caste_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['caste_id'] . '">' . $dat['caste_name'] . '</option>';
            }
            $state = '';
            $sql = 'select state_id, state_name from bl_states order by state_name asc';
            $sData = DbOperations::getObject()->fetchData($sql);
            foreach ($sData as $dat) {
                $state .= '<option' . ((isset($ppData[0]['pp_residing_state']) and $ppData[0]['pp_residing_state'] === $dat['state_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['state_id'] . '">' . $dat['state_name'] . '</option>';
            }
            $education = '';
            $sql = 'select edu_id, edu_name from bl_education order by edu_name asc';
            $eData = DbOperations::getObject()->fetchData($sql);
            foreach ($eData as $dat) {
                $education .= '<option' . ((isset($ppData[0]['pp_education']) and $ppData[0]['pp_education'] === $dat['edu_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['edu_id'] . '">' . $dat['edu_name'] . '</option>';
            }
            $occupation = '';
            $sql = 'select occ_id, occ_name from bl_occupation order by occ_name asc';
            $oData = DbOperations::getObject()->fetchData($sql);
            foreach ($oData as $dat) {
                $occupation .= '<option' . ((isset($ppData[0]['pp_occupation']) and $ppData[0]['pp_occupation'] === $dat['occ_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['occ_id'] . '">' . $dat['occ_name'] . '</option>';
            }
            $emplIn = '';
            switch ($ppData[0]['pp_employedIn']) {
                case '1':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '2':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '3':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '4':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '5':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                case '6':
                    $emplIn .= '<label class="col-12 text-info">Employed In</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="government" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="government">Government</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="private" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="private">Private</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="business" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="business">Business</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="Defence" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="Defence">Defence</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="SelfEmployed" value="5" class="form-check-input">'
                            . '<label class="form-check-label" for="SelfEmployed">Self Employed</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="employedIn" id="notWorking" value="6" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="notWorking">Not Working</label></div>';
                    break;
                default :
                    $emplIn .= '';
                    break;
            }
            $income = '';
            $sql = 'select in_id, in_name from bl_income';
            $iData = DbOperations::getObject()->fetchData($sql);
            foreach ($iData as $dat) {
                $income .= '<option' . ((isset($ppData[0]['pp_income']) and $ppData[0]['pp_income'] === $dat['in_id']) ? ' selected="selected" ' : '') . ' value="' . $dat['in_id'] . '">' . $dat['in_name'] . '</option>';
            }
            $famStat = '';
            switch ($ppData[0]['pp_familyStatus']) {
                case '1':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                case '2':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                case '3':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                case '4':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="middleClass">Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="upMiddleClass" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="upMiddleClass">Upper Middle Class</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="rich" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="rich">Rich</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyStatus" id="affluent" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="affluent">Affluent</label></div>';
                    break;
                default :
                    $famStat .= '';
                    break;
            }
            $famTyp = '';
            switch ($ppData[0]['pp_familyType']) {
                case '1':
                    $famTyp .= '<label class="col-12 text-info">Family Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="joint" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="joint">Joint</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="Nuclear" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="Nuclear">Nuclear</label></div>';
                    break;
                case '2':
                    $famTyp .= '<label class="col-12 text-info">Family Type</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="joint" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="joint">Joint</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyType" id="Nuclear" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="Nuclear">Nuclear</label></div>';
                    break;
                default :
                    $famTyp .= '';
                    break;
            }
            $famVal = '';
            switch ($ppData[0]['pp_familyValues']) {
                case '1':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                case '2':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                case '3':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                case '4':
                    $famVal .= '<label class="col-12 text-info">Family Values</label>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">'
                            . '<label class="form-check-label" for="orthodox">Orthodox</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="traditional" value="2" class="form-check-input">'
                            . '<label class="form-check-label" for="traditional">Traditional</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="moderate" value="3" class="form-check-input">'
                            . '<label class="form-check-label" for="moderate">Moderate</label></div>'
                            . '<div class="form-check form-check-inline ml-3">'
                            . '<input type="radio" name="familyValues" id="liberal" value="4" checked="checked" class="form-check-input">'
                            . '<label class="form-check-label" for="liberal">Liberal</label></div>';
                    break;
                default :
                    $famVal .= '';
                    break;
            }
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Edit Profile - Admin',
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css', 'brightlife-admin.css'],
                'JSHelpers' => ['script.js'],
                'Contents' => file_get_contents(PGS_DIR . DS . 'edit-part-pref-admin.html'),
                'cId' => $cuId,
                'marStat' => $martStat,
                'religion' => $religion,
                'Caste' => $caste,
                'state' => $state,
                'residingCity' => $ppData[0]['pp_residing_city'],
                'ageFrm' => $ppData[0]['pp_ageFrom'],
                'ageTo' => $ppData[0]['pp_ageTo'],
                'education' => $education,
                'occupation' => $occupation,
                'empIn' => $emplIn,
                'income' => $income,
                'famStat' => $famStat,
                'famType' => $famTyp,
                'famVal' => $famVal,
                'desc' => $ppData[0]['pp_partnerDesc']
            ];
            assignTemplate($replaceData, 'adminTpl.html');
        }

    }
    if (!function_exists('savePartPrefAdmin')) {

        function savePartPrefAdmin($suId) {
            $post = DataFilter::getObject()->cleanData($_POST);
            if (!isset($post['resdingState']) or empty($post['resdingState'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Residing State Cann\'t be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['residingCity']) or empty($post['residingCity'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Residing City Cann\'t be empty';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['ageFrom']) or empty($post['ageFrom'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Define Age From';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['ageTo']) or empty($post['ageTo'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Define Age To';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if (!isset($post['desc']) or empty($post['desc'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Define Partner Description';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            $sql = 'select su_name from bl_sign_up where su_id = ?';
            $sData = DbOperations::getObject()->fetchData($sql, [$suId]);
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildUpdateQuery(
                    'bl_partner_preference', ['pp_maritial_stat', 'pp_religion', 'pp_caste', 'pp_residing_state', 'pp_residing_city', 'pp_ageFrom', 'pp_ageTo', 'pp_education', 'pp_occupation', 'pp_employedIn', 'pp_income', 'pp_familyStatus', 'pp_familyType', 'pp_familyValues', 'pp_partnerDesc'], ['signup_id']
            );
            $ins = [
                $post['marStat'],
                $post['religion'],
                $post['caste'],
                $post['resdingState'],
                $post['residingCity'],
                $post['ageFrom'],
                $post['ageTo'],
                $post['education'],
                $post['occupation'],
                $post['employedIn'],
                $post['income'],
                $post['familyStatus'],
                $post['familyType'],
                $post['familyValues'],
                $post['desc'],
                $suId
            ];
            $suc = DbOperations::getObject()->runQuery($ins);
            if ($suc !== false) {
                DbOperations::getObject()->transaction('on');
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'Partner Preference Detail Updated Successfully of ' . $sData[0]['su_name'];
                header('Location:' . ACCESS_URL . 'admin-home/');
                exit;
            } else {
                DbOperations::getObject()->transaction('off');
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Some Error occured, Please retry';
                header('Location:' . ACCESS_URL . 'admin-home/');
                exit;
            }
        }

    }
} else {
    $_SESSION['STATUS'] = 'error';
    $_SESSION['MSG'] = 'Your Session has Expired, Kindly Log In again';
    header('Location:' . $_SERVER['HTTP_REFERER']);
    exit;
}