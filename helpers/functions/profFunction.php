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

if (!function_exists('saveSignUp')) {

    function saveSignUp() {
        $post = DataFilter::getObject()->cleanData($_POST);
        // trim() is redundant after cleanData() (which already trims), kept only
        // for clarity that whitespace-only names are rejected below
        if (empty(trim($post['name'] ?? '')) or ! isset($post['name'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Name';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (mb_strlen($post['name']) > 100) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Name must not exceed 100 characters';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (empty($post['dob']) or ! isset($post['dob'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Date of Birth';
            header('Location:' . ACCESS_URL);
            exit;
        }
        // strict parse against the dd-mm-yyyy format advertised by the signup form
        // (see helpers/tpls/template.html placeholder="dd-mm-yyyy"); strtotime()
        // is deliberately not used here as it silently accepts/reinterprets
        // malformed dates instead of rejecting them
        $dobObj = DateTime::createFromFormat('d-m-Y', $post['dob']);
        $dobErrors = DateTime::getLastErrors();
        // PHP 8.3+ returns false (not an array) from getLastErrors() when there
        // were no warnings/errors, so only inspect the counts when it's an array
        if ($dobObj === false or ( is_array($dobErrors) and ( $dobErrors['warning_count'] > 0 or $dobErrors['error_count'] > 0))) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter a Valid Date of Birth in dd-mm-yyyy format';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $dobObj->setTime(0, 0, 0);
        $today = new DateTime('today');
        if ($dobObj > $today) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Date of Birth cannot be in the future';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if ($today->diff($dobObj)->y < 18) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'You must be at least 18 years old to register';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (!isset($post['gender']) or !in_array($post['gender'], ['1', '2'], true)) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Select a Valid Gender';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (empty($post['religion']) or ! isset($post['religion'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Select Your Religion';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $relData = DbOperations::getObject()->fetchData('select rel_id from religion where rel_id = ?', [$post['religion']]);
        if (count($relData) < 1) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Select a Valid Religion';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (empty($post['motherTounge']) or ! isset($post['motherTounge'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Select Your Mother Tongue';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $mtData = DbOperations::getObject()->fetchData('select mt_id from mother_tounge where mt_id = ?', [$post['motherTounge']]);
        if (count($mtData) < 1) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Select a Valid Mother Tongue';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (empty($post['caste']) or ! isset($post['caste'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Caste / Division';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $casteData = DbOperations::getObject()->fetchData('select caste_id from bl_caste where caste_id = ?', [$post['caste']]);
        if (count($casteData) < 1) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Select a Valid Caste / Division';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (empty($post['mobile']) or ! isset($post['mobile'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Mobile Number';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (!preg_match('/^[0-9]{10}$/', $post['mobile'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter a Valid 10 Digit Mobile Number';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $moData = DbOperations::getObject()->fetchData('select count(su_mobile) as mocount from bl_sign_up where su_mobile = ?', [$post['mobile']]);
        if (intval($moData[0]['mocount']) > 0) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'This mobile number is already registered';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (empty($post['email']) or ! isset($post['email'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your E-mail Id';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL) or ( strlen($post['email']) > 100)) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter a Valid E-mail Id';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $sql = 'select count(su_email) as unmcount from bl_sign_up where su_email = ?';
        $emData = DbOperations::getObject()->fetchData($sql, [$post['email']]);
        if (intval($emData[0]['unmcount']) > 0) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'This email id isn\'t available, This email id registered before.';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (!isset($post['pass']) or empty($post['pass'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please enter password';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (( strlen($post['pass']) < 6) or ( strlen($post['pass']) > 50)) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Password must be between 6 and 50 characters';
            header('Location:' . ACCESS_URL);
            exit;
        }
        if (!isset($post['rpass']) or ( $post['pass'] !== $post['rpass'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please re-enter password correctly';
            header('Location:' . ACCESS_URL);
            exit;
        }
        DbOperations::getObject()->transaction('start');
        DbOperations::getObject()->buildInsertQuery('bl_sign_up');
        $ins = [
            null,
            $post['name'],
            $dobObj->format('Y-m-d'),
            $post['gender'],
            $post['religion'],
            $post['motherTounge'],
            $post['caste'],
            $post['mobile'],
            $post['email'],
            PasswordService::getObject()->hash($post['pass']),
            DBTIMESTAMP
        ];
        $suc = DbOperations::getObject()->runQuery($ins);
        if ($suc !== false) {
            DbOperations::getObject()->transaction('on');
            $_SESSION['STATUS'] = 'success';
            $_SESSION['MSG'] = 'Data saved successfully, kindly fill up your personal detail';
            header('Location:' . ACCESS_URL . 'personal-detail/' . $suc . '/');
        } else {
            DbOperations::getObject()->transaction('off');
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Some Error Occured while saving data, Please retry';
            header('Location:' . ACCESS_URL);
        }
    }

}
if (!function_exists('viewProfile')) {

    function viewProfile() {
        $post = DataFilter::getObject()->cleanData($_POST);
        if (!isset($post['blId']) or empty($post['blId'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please enter the ID you want to view';
            header('Location:' . ACCESS_URL . 'search-by-id');
            exit;
        }
        $sql = 'select signup_id from bl_partner_preference where pp_cust_id = ?';
        $eData = DbOperations::getObject()->fetchData($sql, [$post['blId']]);
        if (count($eData) < 1) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'No ID found';
            header('Location:' . ACCESS_URL . 'search-by-id');
            exit;
        }
        $sql = 'select img_name, pd_height, pd_maritalStatus, state_name, pd_residingCity, '
                . ' pd_employedIn, pd_profCreated, edu_name, pd_eduDetails, occ_name, pd_occDetails, '
                . ' ras_name, star_name, pd_gothra, pd_weight, in_name, pd_food, pd_smoking, '
                . ' pd_drinking, pd_familyStatus, pd_desc, pd_occFather, pd_occMother '
                . ' from bl_personal_detail left join bl_income on pd_income = in_id '
                . ' left join bl_images on sign_up_id = img_su_id'
                . ' left join bl_star on pd_star = star_id '
                . ' left join bl_rashi on pd_rashi = ras_id '
                . ' left join bl_states on pd_residingState = state_id '
                . ' left join bl_occupation on pd_occupation = occ_id '
                . ' left join bl_education on pd_education = edu_id where sign_up_id = ?'
                . ' and img_dp = 1';
        $perData = DbOperations::getObject()->fetchData($sql, [$eData[0]['signup_id']]);
        $empIn = '';
        switch ($perData[0]['pd_employedIn']) {
            case '1':
                $empIn .= 'Government';
                break;
            case '2':
                $empIn .= 'Private';
                break;
            case '3':
                $empIn .= 'Business';
                break;
            case '4':
                $empIn .= 'Defence';
                break;
            case '5':
                $empIn .= 'Self Employed';
                break;
            default :
                $empIn .= 'Not Working';
                break;
        }
        $suSql = 'select su_name, su_dob, caste_name, rel_name from bl_sign_up '
                . 'left join religion on su_religion = rel_id left join bl_caste '
                . ' on su_caste = caste_id where su_id = ?';
        $suData = DbOperations::getObject()->fetchData($suSql, [$eData[0]['signup_id']]);
        switch ($perData[0]['pd_familyStatus']) {
            case '1':
                $famStat = 'Middle Class';
                break;
            case '2':
                $famStat = 'Upper Middle Class';
                break;
            case '3':
                $famStat = 'Rich';
                break;
            default :
                $famStat = 'Affluent';
                break;
        }
        switch ($perData[0]['pd_drinking']) {
            case '1':
                $drinking = 'No';
                break;
            case '2':
                $drinking = 'Occasionally';
                break;
            default :
                $drinking = 'Yes';
                break;
        }
        switch ($perData[0]['pd_smoking']) {
            case '1':
                $smoking = 'No';
                break;
            case '2':
                $smoking = 'Occasionally';
                break;
            default :
                $smoking = 'Yes';
                break;
        }
        switch ($perData[0]['pd_food']) {
            case '1':
                $food = 'Vegetarian';
                break;
            case '2':
                $food = 'Non Vegetarian';
                break;
            default:
                $food = 'Eggetarian';
                break;
        }
        $age = time() - intval(strtotime($suData[0]['su_dob']));
        $maritalStat = '';
        switch ($perData[0]['pd_maritalStatus']) {
            case '1':
                $maritalStat .= 'Unmarried';
                break;
            case '2':
                $maritalStat .= 'Widower';
                break;
            case '3':
                $maritalStat .= 'Divorced';
                break;
            default:
                $maritalStat .= 'Awaiting divorce';
                break;
        }
        $profCreatedBy = '';
        switch ($perData[0]['pd_profCreated']) {
            case '1':
                $profCreatedBy .= 'Self';
                break;
            case '2':
                $profCreatedBy .= 'Son';
                break;
            case '3':
                $profCreatedBy .= 'Daughter';
                break;
            case '4':
                $profCreatedBy .= 'Brother';
                break;
            case '5':
                $profCreatedBy .= 'Relative';
                break;
            case '6':
                $profCreatedBy .= 'Sister';
                break;
            default:
                $profCreatedBy .= 'Friend';
                break;
        }
        $sql = 'select img_name from bl_images where img_su_id = ? '
                . ' order by img_dttm desc';
        $imgData = DbOperations::getObject()->fetchData($sql, [$eData[0]['signup_id']]);
        $imageDataProf = '';
        foreach ($imgData as $iDat) {
            $imageDataProf .= '<div class="col-md-4 my-2">'
                    . '<img src="' . ACCESS_URL . 'helpers/images/uploads/'
                    . $iDat['img_name'] . '" alt=" " class="img-fluid">'
                    . '</div>';
        }
        $replaceData = [
            'PageTitle' => 'Brightlife Matrimony - Profile',
            'MetaKeys' => '',
            'MetaDesc' => '',
            'CSSHelpers' => ['style.min.css'],
            'JSHelpers' => ['script.js'],
            'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
            'Contents' => file_get_contents(PGS_DIR . DS . 'profile.html'),
            'buttonHead' => $_SESSION['btn'],
            'profID' => $post['blId'],
            'Img' => $perData[0]['img_name'],
            'custName' => $suData[0]['su_name'],
            'marStat' => $maritalStat,
            'rashi' => $perData[0]['ras_name'],
            'star' => $perData[0]['star_name'],
            'gothra' => $perData[0]['pd_gothra'],
            'age' => round(((($age / 3600) / 24) / 365)),
            'height' => $perData[0]['pd_height'],
            'weight' => $perData[0]['pd_weight'],
            'religion' => $suData[0]['rel_name'],
            'caste' => $suData[0]['caste_name'],
            'Name' => $suData[0]['su_name'],
            'martStat' => $maritalStat,
            'state' => $perData[0]['state_name'],
            'city' => $perData[0]['pd_residingCity'],
            'profCreatedBy' => $profCreatedBy,
            'education' => $perData[0]['edu_name'],
            'occupation' => $perData[0]['occ_name'],
            'empIn' => $empIn,
            'fatherOccupation' => $perData[0]['pd_occFather'],
            'motherOccupation' => $perData[0]['pd_occMother'],
            'food' => $food,
            'smoking' => $smoking,
            'income' => $perData[0]['in_name'],
            'food' => $food,
            'smoking' => $smoking,
            'drinking' => $drinking,
            'famStat' => $famStat,
            'desc' => $perData[0]['pd_desc'],
            'imgBtn' => $_SESSION['imgBtn'],
            'imageDataProf' => $imageDataProf,
            'sendMsg' => $_SESSION['msgUrl']
        ];
        if (isLogged() === FALSE) {
            assignTemplate($replaceData, '');
        } else {
            assignTemplate($replaceData, 'logTemplate.html');
        }
    }

}
if (!function_exists('searchProf')) {

    function searchProf() {
        $post = DataFilter::getObject()->cleanData($_POST);
        switch ($post['age']) {
            case '1':
                $date1 = 18;
                $date2 = 25;
                break;
            case '2':
                $date1 = 25;
                $date2 = 30;
                break;
            case '3':
                $date1 = 30;
                $date2 = 35;
                break;
            case '4':
                $date1 = 35;
                $date2 = 40;
                break;
            case '5':
                $date1 = 18;
                $date2 = 60;
                break;
            default :
                $date1 = 40;
                $date2 = 60;
                break;
        }
        $fnDate1 = date('Y-m-d', strtotime(-$date2 . ' years'));
        $fnDate2 = date('Y-m-d', strtotime(-$date1 . ' years'));
        $profData = '';
        $suSql = 'select su_id, su_name, su_dob, su_email, rel_name, su_caste from bl_sign_up '
                . 'left join religion on su_religion = rel_id where su_caste = ? and '
                . ' su_religion = ? and su_dob between ? and ? and su_gender = ? '
                . ' order by su_dttm desc';
        $suData = DbOperations::getObject()->fetchData($suSql, [$post['caste'], $post['rel'], $fnDate1, $fnDate2, $post['gender']]);
        if ((count($suData) < 1) or ! isset($suData)) {
            $profData .= '<div class="col-12"><p class="bl-search-no-results">No profiles found matching your search criteria.</p></div>';
        } else {
            foreach ($suData as $data) {
                $sql = 'select img_name, pd_height, pd_maritalStatus, state_name, pd_residingCity, '
                        . ' pd_profCreated, pd_residingCity, edu_name, occ_name '
                        . ' from bl_personal_detail left join bl_images on sign_up_id = img_su_id'
                        . ' left join bl_states on pd_residingState = state_id '
                        . ' left join bl_occupation on pd_occupation = occ_id '
                        . ' left join bl_education on pd_education = edu_id '
                        . ' where sign_up_id = ? and img_dp = 1'
                        . ' order by pd_dttm desc';
                $perData = DbOperations::getObject()->fetchData($sql, [$data['su_id']]);
                if ((count($perData) < 1) or ! isset($perData)) {
                    continue;
                }
                $sql = 'select pp_cust_id from bl_partner_preference where signup_id = ?';
                $cusData = DbOperations::getObject()->fetchData($sql, [$data['su_id']]);
                if ((count($cusData) < 1) or ! isset($cusData)) {
                    continue;
                }
                $age = time() - intval(strtotime($data['su_dob']));
                $maritalStat = '';
                switch (((count($perData) > 0) ? $perData[0]['pd_maritalStatus'] : '')) {
                    case '1':
                        $maritalStat .= 'Unmarried';
                        break;
                    case '2':
                        $maritalStat .= 'Widower';
                        break;
                    case '3':
                        $maritalStat .= 'Divorced';
                        break;
                    default:
                        $maritalStat .= 'Awaiting divorce';
                        break;
                }
                $profCreatedBy = '';
                switch (((count($perData) > 0) ? $perData[0]['pd_profCreated'] : '')) {
                    case '1':
                        $profCreatedBy .= 'Self';
                        break;
                    case '2':
                        $profCreatedBy .= 'Son';
                        break;
                    case '3':
                        $profCreatedBy .= 'Daughter';
                        break;
                    case '4':
                        $profCreatedBy .= 'Brother';
                        break;
                    case '5':
                        $profCreatedBy .= 'Relative';
                        break;
                    case '6':
                        $profCreatedBy .= 'Sister';
                        break;
                    default:
                        $profCreatedBy .= 'Friend';
                        break;
                }
                $profData .= '<div class="col-lg-6 mb-4">
                                <div class="bl-search-card">
                                    <div class="bl-search-card-photo">
                                        <img src="' . ACCESS_URL . 'helpers/images/uploads/' . ((count($perData) > 0) ? $perData[0]['img_name'] : '') . '" alt=" ">
                                    </div>
                                    <div class="bl-search-card-info">
                                        <div class="bl-search-card-id">' . ((count($cusData) > 0) ? $cusData[0]['pp_cust_id'] : '') . '</div>
                                        <div class="bl-search-card-summary">' . $perData[0]['pd_residingCity'] . ', ' . round(((($age / 3600) / 24) / 365)) . ' Yrs, ' . ((count($perData) > 0) ? $perData[0]['pd_height'] : '') . ', ' . $data['rel_name'] . '</div>
                                        <div class="bl-search-card-summary">' . $perData[0]['edu_name'] . ', ' . $perData[0]['occ_name'] . '</div>
                                        <a href="' . ACCESS_URL . 'view-profile/' . ((count($cusData) > 0) ? $cusData[0]['pp_cust_id'] . '/' : '') . '" class="btn btn-outline-danger btn-sm bl-search-card-btn">View Profile</a>
                                    </div>
                                </div>
                            </div>';
            }
        }
        $replaceData = [
            'PageTitle' => 'Brightlife Matrimony - Profile',
            'MetaKeys' => '',
            'MetaDesc' => '',
            'CSSHelpers' => ['style.min.css'],
            'JSHelpers' => ['script.js'],
            'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
            'Contents' => file_get_contents(PGS_DIR . DS . 'search-profile.html'),
            'buttonHead' => $_SESSION['btn'],
            'profileData' => $profData
        ];
        if (isLogged() === FALSE) {
            assignTemplate($replaceData, '');
        } else {
            assignTemplate($replaceData, 'logTemplate.html');
        }
    }

}
if (!function_exists('savePerDet')) {

    function savePerDet($cusId) {
        $post = DataFilter::getObject()->cleanData($_POST);
        //var_dump($cusId);exit;
        if (!isset($post['rashi']) or empty($post['rashi'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Zodiac Sign';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['star']) or empty($post['star'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Star';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['gothra']) or empty($post['gothra'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Gothra';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['residingCity']) or empty($post['residingCity'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter the City where you stay';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['height']) or empty($post['height'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Height';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['weight']) or empty($post['weight'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Weight';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['eduDetails']) or empty($post['eduDetails'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Education Details';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['occDetails']) or empty($post['occDetails'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your Occupation Details';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['occFather']) or empty($post['occFather'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your father&#039;s Occupation';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['occMother']) or empty($post['occMother'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Your mother&#039;s Occupation';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['desc']) or empty($post['desc']) or ( strlen($post['desc']) >= 300)) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Describe yourself within 300 characters';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($_FILES['uploadPhoto']['name']) or empty($_FILES['uploadPhoto']['name'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please attach an image';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        $sql = 'select su_email from bl_sign_up where su_id = ?';
        $eData = DbOperations::getObject()->fetchData($sql, [$cusId]);
        DbOperations::getObject()->transaction('start');
        DbOperations::getObject()->buildInsertQuery('bl_personal_detail');
        $ins = [
            null,
            $cusId,
            $post['profCreated'],
            $post['maritalStatus'],
            $post['rashi'],
            $post['star'],
            $post['gothra'],
            $post['residingState'],
            $post['residingCity'],
            $post['height'],
            $post['weight'],
            $post['bodyType'],
            $post['complexion'],
            $post['phyisicalStatus'],
            $post['education'],
            $post['eduDetails'],
            $post['occupation'],
            $post['occDetails'],
            $post['employedIn'],
            $post['income'],
            $post['food'],
            $post['smoking'],
            $post['drinking'],
            $post['familyStatus'],
            $post['familyType'],
            $post['familyValues'],
            $post['occFather'],
            $post['occMother'],
            $post['desc'],
            $eData[0]['su_email'],
            DBTIMESTAMP
        ];
        $suc = DbOperations::getObject()->runQuery($ins);
        DbOperations::getObject()->buildInsertQuery('bl_images');
        Uploader::getObject()->allowedFileSize = 10 * 1024 * 1024;
        $img = Uploader::getObject()->doUpload($_FILES['uploadPhoto']);
        if (isset($img['status']) and ( $img['status'] === 'success')) {
            ResizeImage::getObject()->setImage(UPLOAD_DIR . DS . $img['newFileName']);
            ResizeImage::getObject()->resizeTo(480, 640);
            ResizeImage::getObject()->saveImage(UPLOAD_DIR . DS . $img['newFileName'], 60);
            $ins1 = [
                null,
                $cusId,
                $img['newFileName'],
                1,
                DBTIMESTAMP
            ];
            $suc1 = DbOperations::getObject()->runQuery($ins1);
        }
        if ($suc !== false and $suc1 !== false) {
            DbOperations::getObject()->transaction('on');
            $_SESSION['STATUS'] = 'success';
            $_SESSION['MSG'] = 'Personal Detail Saved Successfully';
            header('Location:' . ACCESS_URL . 'partner-preference/' . $cusId . '/');
            exit(0);
        } else {
            DbOperations::getObject()->transaction('off');
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Some error occured, please retry';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit(0);
        }
    }

}
if (!function_exists('savePartPref')) {

    function savePartPref($cusId) {
        $post = DataFilter::getObject()->cleanData($_POST);
        if (!isset($post['maritalStat']) or empty($post['maritalStat'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Check any option for Maritial status';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['religion']) or empty($post['religion'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select Religion';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['caste']) or empty($post['caste'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Enter Caste';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['resdingState']) or empty($post['resdingState'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select Residing state';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['residingCity']) or empty($post['residingCity'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please enter Residing city';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['ageFrom']) or empty($post['ageFrom'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please enter age from';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['ageTo']) or empty($post['ageTo'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please enter age to';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['education']) or empty($post['education'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select education';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['occupation']) or empty($post['occupation'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select occupation';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['employedIn']) or empty($post['employedIn'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please check any option for Employed in';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['income']) or empty($post['income'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select income';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['familyStatus']) or empty($post['familyStatus'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select family status';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['familyType']) or empty($post['familyType'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select family type';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['familyValues']) or empty($post['familyValues'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please select family values';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        if (!isset($post['partnerDesc']) or empty($post['partnerDesc']) or ( strlen($post['partnerDesc']) >= 300)) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Please Describe about your partner within 300 characters';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        $randId = mt_rand(0, 9999);
        $custId = 'BL' . date('ymd') . $cusId;
        $sql = 'select count(pp_id) as cicount from bl_partner_preference where pp_cust_id = ?';
        $cData = DbOperations::getObject()->fetchData($sql, [$custId]);
        if (intval($cData[0]['cicount']) > 0) {
            $custId = 'BL' . $randId . $cusId;
        }
        $sql = 'select su_email from bl_sign_up where su_id = ?';
        $eData = DbOperations::getObject()->fetchData($sql, [$cusId]);
        DbOperations::getObject()->transaction('start');
        DbOperations::getObject()->buildInsertQuery('bl_partner_preference');
        $ins = [
            null,
            $custId,
            $cusId,
            $eData[0]['su_email'],
            $post['maritalStat'],
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
            $post['partnerDesc'],
            DBTIMESTAMP
        ];
        $suc = DbOperations::getObject()->runQuery($ins);
        if ($suc !== false) {
            DbOperations::getObject()->transaction('on');
            $_SESSION['STATUS'] = 'success';
            $_SESSION['MSG'] = 'Profile Created';
            header('Location:' . ACCESS_URL . 'thank-you/' . $cusId . '/');
            exit(0);
        } else {
            DbOperations::getObject()->transaction('off');
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Some error occured, please retry';
            header('Location:' . ACCESS_URL);
            exit(0);
        }
    }

}
if (!function_exists('viewProfSearch')) {

    function viewProfSearch($custId) {
        $sql = 'select signup_id from bl_partner_preference where pp_cust_id = ?';
        $eData = DbOperations::getObject()->fetchData($sql, [$custId]);
        $sql = 'select img_name, pd_height, pd_maritalStatus, state_name, pd_residingCity, '
                . ' pd_profCreated, edu_name, pd_eduDetails, occ_name, pd_occDetails, '
                . ' ras_name, star_name, pd_gothra, pd_weight, in_name, pd_food, pd_smoking, '
                . ' pd_employedIn, pd_drinking, pd_familyStatus, pd_desc, pd_occFather, pd_occMother '
                . ' from bl_personal_detail left join bl_income on pd_income = in_id '
                . ' left join bl_images on sign_up_id = img_su_id'
                . ' left join bl_star on pd_star = star_id '
                . ' left join bl_rashi on pd_rashi = ras_id '
                . ' left join bl_states on pd_residingState = state_id '
                . ' left join bl_occupation on pd_occupation = occ_id '
                . ' left join bl_education on pd_education = edu_id where sign_up_id = ?'
                . ' and img_dp = 1';
        $perData = DbOperations::getObject()->fetchData($sql, [$eData[0]['signup_id']]);
        $empIn = '';
        switch ($perData[0]['pd_employedIn']) {
            case '1':
                $empIn .= 'Government';
                break;
            case '2':
                $empIn .= 'Private';
                break;
            case '3':
                $empIn .= 'Business';
                break;
            case '4':
                $empIn .= 'Defence';
                break;
            case '5':
                $empIn .= 'Self Employed';
                break;
            default :
                $empIn .= 'Not Working';
                break;
        }
        $suSql = 'select su_name, su_dob, caste_name, rel_name from bl_sign_up '
                . 'left join religion on su_religion = rel_id left join bl_caste '
                . ' on su_caste = caste_id where su_id = ?';
        $suData = DbOperations::getObject()->fetchData($suSql, [$eData[0]['signup_id']]);
        $age = time() - intval(strtotime($suData[0]['su_dob']));
        $maritalStat = '';
        switch ($perData[0]['pd_maritalStatus']) {
            case '1':
                $maritalStat .= 'Unmarried';
                break;
            case '2':
                $maritalStat .= 'Widower';
                break;
            case '3':
                $maritalStat .= 'Divorced';
                break;
            default:
                $maritalStat .= 'Awaiting divorce';
                break;
        }
        $profCreatedBy = '';
        switch ($perData[0]['pd_profCreated']) {
            case '1':
                $profCreatedBy .= 'Self';
                break;
            case '2':
                $profCreatedBy .= 'Son';
                break;
            case '3':
                $profCreatedBy .= 'Daughter';
                break;
            case '4':
                $profCreatedBy .= 'Brother';
                break;
            case '5':
                $profCreatedBy .= 'Relative';
                break;
            case '6':
                $profCreatedBy .= 'Sister';
                break;
            default:
                $profCreatedBy .= 'Friend';
                break;
        }
        switch ($perData[0]['pd_familyStatus']) {
            case '1':
                $famStat = 'Middle Class';
                break;
            case '2':
                $famStat = 'Upper Middle Class';
                break;
            case '3':
                $famStat = 'Rich';
                break;
            default :
                $famStat = 'Affluent';
                break;
        }
        switch ($perData[0]['pd_drinking']) {
            case '1':
                $drinking = 'No';
                break;
            case '2':
                $drinking = 'Occasionally';
                break;
            default :
                $drinking = 'Yes';
                break;
        }
        switch ($perData[0]['pd_smoking']) {
            case '1':
                $smoking = 'No';
                break;
            case '2':
                $smoking = 'Occasionally';
                break;
            default :
                $smoking = 'Yes';
                break;
        }
        switch ($perData[0]['pd_food']) {
            case '1':
                $food = 'Vegetarian';
                break;
            case '2':
                $food = 'Non Vegetarian';
                break;
            default:
                $food = 'Eggetarian';
                break;
        }
        $sql = 'select img_name from bl_images where img_su_id = ? '
                . ' order by img_dttm desc';
        $imgData = DbOperations::getObject()->fetchData($sql, [$eData[0]['signup_id']]);
        $imgDataProf = '';
        foreach ($imgData as $iDat) {
            $imgDataProf .= '<div class="col-md-4 my-2">'
                    . '<img src="' . ACCESS_URL . 'helpers/images/uploads/'
                    . $iDat['img_name'] . '" alt=" " class="img-fluid">'
                    . '</div>';
        }
        $cId = '';
        if (isLogged() === false) {
            $cId .= '';
        } else {
            $cId = $_SESSION['CID'];
        }
        $replaceData = [
            'PageTitle' => 'Brightlife Matrimony - Profile',
            'MetaKeys' => '',
            'MetaDesc' => '',
            'CSSHelpers' => ['style.min.css'],
            'JSHelpers' => ['script.js'],
            'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
            'Contents' => file_get_contents(PGS_DIR . DS . 'view-profile.html'),
            'buttonHead' => $_SESSION['btn'],
            'profID' => $custId,
            'Img' => $perData[0]['img_name'],
            'custName' => $suData[0]['su_name'],
            'marStat' => $maritalStat,
            'rashi' => $perData[0]['ras_name'],
            'star' => $perData[0]['star_name'],
            'gothra' => $perData[0]['pd_gothra'],
            'age' => round(((($age / 3600) / 24) / 365)),
            'height' => $perData[0]['pd_height'],
            'weight' => $perData[0]['pd_weight'],
            'religion' => $suData[0]['rel_name'],
            'caste' => $suData[0]['caste_name'],
            'Name' => $suData[0]['su_name'],
            'martStat' => $maritalStat,
            'state' => $perData[0]['state_name'],
            'city' => $perData[0]['pd_residingCity'],
            'profCreatedBy' => $profCreatedBy,
            'education' => $perData[0]['edu_name'],
            'occupation' => $perData[0]['occ_name'] . ', ' . $perData[0]['pd_occDetails'],
            'empIn' => $empIn,
            'fatherOccupation' => $perData[0]['pd_occFather'],
            'motherOccupation' => $perData[0]['pd_occMother'],
            'food' => $food,
            'smoking' => $smoking,
            'income' => $perData[0]['in_name'],
            'food' => $food,
            'smoking' => $smoking,
            'drinking' => $drinking,
            'famStat' => $famStat,
            'desc' => $perData[0]['pd_desc'],
            'imageDataProf' => $imgDataProf,
            'imgBtn' => $_SESSION['imgBtn'],
            'sendMsg' => $_SESSION['msgUrl'],
            'ownId' => $cId
        ];
        if (isLogged() === FALSE) {
            assignTemplate($replaceData, '');
        } else {
            assignTemplate($replaceData, 'logTemplate.html');
        }
    }

}
if (!function_exists('sendMsg')) {

    function sendMsg($partId, $ownId) {
        if (isLogged() === false) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Your Session has expired, Kindly Log In again';
            header('Location:' . ACCESS_URL);
            exit;
        }
        $post = DataFilter::getObject()->cleanData($_POST);
        if (!isset($post['msg']) or empty($post['msg'])) {
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Write Your Message';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        DbOperations::getObject()->transaction('start');
        DbOperations::getObject()->buildInsertQuery('bl_message');
        $ins = [
            null,
            $partId,
            $ownId,
            $post['msg'],
            1,
            DBTIMESTAMP
        ];
        $suc = DbOperations::getObject()->runQuery($ins);
        if ($suc != false) {
            DbOperations::getObject()->transaction('on');
            $_SESSION['STATUS'] = 'success';
            $_SESSION['MSG'] = 'Message Sent';
            header('Location:' . $_SERVER['HTTP_REFERER']);
        } else {
            DbOperations::getObject()->transaction('off');
            $_SESSION['STATUS'] = 'error';
            $_SESSION['MSG'] = 'Some Errors occured, Please try again later';
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }

}