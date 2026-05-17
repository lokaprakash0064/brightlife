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
if (isLogged() !== true) {
    if (!function_exists('userIndex')) {

        function userIndex($uId) {
            $sql = 'select pd_maritalStatus, ras_name, star_name, '
                    . ' pd_gothra, state_name, pd_height, pd_weight, edu_name, '
                    . ' pd_eduDetails, occ_name, in_name, pd_food, pd_drinking, pd_smoking, '
                    . ' pd_employedIn, pd_familyStatus, pd_occFather, '
                    . ' pd_occMother, pd_desc from bl_personal_detail '
                    . ' left join bl_rashi on pd_rashi = ras_id left join bl_star '
                    . ' on pd_star = star_id left join bl_states '
                    . ' on pd_residingState = state_id left join bl_education on '
                    . ' pd_education = edu_id left join bl_occupation on '
                    . ' pd_occupation = occ_id left join bl_income on '
                    . ' pd_income = in_id where sign_up_id = ?';
            $perData = DbOperations::getObject()->fetchData($sql, [$uId]);
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
            $sql = 'select pp_maritial_stat, rel_name, pp_caste, caste_name, '
                    . 'pp_ageFrom, pp_ageTo, state_name, edu_name, '
                    . ' occ_name, in_name, pp_familyStatus, pp_familyType, pp_familyValues, '
                    . ' pp_partnerDesc from bl_partner_preference left join bl_occupation on '
                    . ' pp_occupation = occ_id left join bl_income on pp_income = in_id '
                    . ' left join religion on pp_religion = rel_id '
                    . ' left join bl_caste on pp_caste = caste_id left join bl_education '
                    . ' on pp_education = edu_id '
                    . ' left join bl_states on pp_residing_state = state_id where signup_id = ?';
            $patData = DbOperations::getObject()->fetchData($sql, [$uId]);
            $sql = 'select img_name from bl_images where img_dp = 1 and img_su_id = ?';
            $imgData = DbOperations::getObject()->fetchData($sql, [$uId]);
            $gender = '';
            if ($_SESSION['GENDER'] == '2') {
                $gender = '1';
            } else {
                $gender = '2';
            }
            $fnDate1 = date('Y-m-d', strtotime(-($patData[0]['pp_ageTo']) . ' years'));
            $fnDate2 = date('Y-m-d', strtotime(-($patData[0]['pp_ageFrom']) . ' years'));
            $sql = 'select su_id, su_name, su_dob from bl_sign_up where su_gender = ? and '
                    . ' su_dob between ? and ? and su_caste = ? order by su_dttm desc';
            $piData = DbOperations::getObject()->fetchData($sql, [$gender, $fnDate1, $fnDate2, $patData[0]['pp_caste']]);
            $profIntrst = '';
            if (count($piData) < 1) {
                $profIntrst = '<li class="list-inline-item"><p class="text-justify text-center">No data to show</p></li>'
                        . '<li class="list-inline-item"><p class="text-justify text-center">No data to show</p></li>'
                        . '<li class="list-inline-item"><p class="text-justify text-center">No data to show</p></li>';
            } else {
                foreach ($piData as $pDat) {
                    $sql = 'select img_name from bl_images where img_su_id = ? and img_dp = 1';
                    $ppData = DbOperations::getObject()->fetchData($sql, [$pDat['su_id']]);
                    if (count($ppData) < 1) {
                        continue;
                    }
                    $sql = 'select pp_cust_id from bl_partner_preference where signup_id = ?';
                    $idData = DbOperations::getObject()->fetchData($sql, [$pDat['su_id']]);
                    if (count($idData) < 1) {
                        continue;
                    }
                    $sql = 'select pd_residingCity, state_name, edu_name, occ_name '
                            . ' from bl_personal_detail left join bl_states on '
                            . ' pd_residingState = state_id left join bl_education on '
                            . ' pd_education = edu_id left join bl_occupation on '
                            . ' pd_occupation = occ_id where sign_up_id = ?';
                    $perDetData = DbOperations::getObject()->fetchData($sql, [$pDat['su_id']]);
                    if (count($perDetData) < 1) {
                        continue;
                    }
                    $img = (count($ppData) < 1 ? '<img src="' . ACCESS_URL . 'helpers/images/no-img.jpg" alt=" " class="card-img-top">' : '<img src="' . ACCESS_URL . 'helpers/images/uploads/' . $ppData[0]['img_name'] . '" alt=" " class="card-img-top">');
                    $profIntrst .= '<li class="list-inline-item">'
                            . '<div class="card border-dark box-shadow bg-dark-grad" style="width: 250px">'
                            . $img
                            . '<div class="card-body text-light">'
                            . '<h6 class="text-info heading text-capitalize">'
                            . $pDat['su_name'] . '</h6>'
                            . '<p class="text-justify my-1">'
                            . round(((((time() - intval(strtotime($pDat['su_dob']))) / 3600) / 24) / 365))
                            . ', ' . $perDetData[0]['pd_residingCity'] 
                            . ', ' . $perDetData[0]['state_name']
                            . '</p>'
                            . '<p class="text-justify my-1">'
                            . $perDetData[0]['edu_name'] . ', <br>'
                            . $perDetData[0]['occ_name']
                            . '</p>'
                            . '<div class="text-center">'
                            . '<a class="btn btn-danger" href="' . ACCESS_URL . 'view-profile/' . $idData[0]['pp_cust_id'] . '/">Full Profile</a>'
                            . '</div>'
                            . '</div>'
                            . '</div>'
                            . '</li>';
                }
            }
            switch ($patData[0]['pp_familyValues']) {
                case '1':
                    $patFamVal = 'Orthodox';
                    break;
                case '2':
                    $patFamVal = 'Traditional';
                    break;
                case '3':
                    $patFamVal = 'Moderate';
                    break;
                default :
                    $patFamVal = 'Liberal';
                    break;
            }
            switch ($patData[0]['pp_familyType']) {
                case '1':
                    $patFamType = 'Joint';
                    break;
                default :
                    $patFamType = 'Nuclear';
                    break;
            }
            switch ($patData[0]['pp_familyStatus']) {
                case '1':
                    $patFamStat = 'Middle Class';
                    break;
                case '2':
                    $patFamStat = 'Upper Middle Class';
                    break;
                case '3':
                    $patFamStat = 'Rich';
                    break;
                default :
                    $patFamStat = 'Affluent';
                    break;
            }
            switch ($patData[0]['pp_maritial_stat']) {
                case '1':
                    $patMarStat = 'Unmarried';
                    break;
                case '2':
                    $patMarStat = 'Widow';
                    break;
                case '3':
                    $patMarStat = 'Divorced';
                    break;
                default :
                    $patMarStat = 'Awaiting Divorce';
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
            switch ($perData[0]['pd_maritalStatus']) {
                case '1':
                    $marStat = 'Unmarried';
                    break;
                case '2':
                    $marStat = 'Widow';
                    break;
                case '3':
                    $marStat = 'Divorced';
                    break;
                default :
                    $marStat = 'Awaiting Divorce';
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
            $imageData = '';
            $sql = 'select img_id, img_name, img_dp from bl_images where '
                    . ' img_su_id = ? order by img_dttm desc';
            $iData = DbOperations::getObject()->fetchData($sql, [$uId]);
            foreach ($iData as $img) {
                $imageData .= '<div class="col-md-4 py-3">'
                        . '<input type="radio" name="img" id="' . 'bl_' . $uId . '_' . $img['img_id'] . '" class="input-hidden" value="' . $img['img_id'] . '"' . ($img['img_dp'] === '1' ? ' checked="checked"' : '') . '>'
                        . '<label for="' . 'bl_' . $uId . '_' . $img['img_id'] . '">'
                        . '<img src="' . ACCESS_URL . 'helpers/images/uploads/'
                        . $img['img_name'] . '" alt=" " class="img-thumbnail">'
                        . '</label>'
                        . '</div>';
            }
            //var_dump($imageData);exit;
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Home ' . $_SESSION['USERNAME'],
                'MetaKeys' => '',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css', 'jquery.jConveyorTicker.min.css'],
                'JSHelpers' => ['script.js', 'jquery.jConveyorTicker.min.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner-user.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'user-index.html'),
                'buttonHead' => $_SESSION['btn'],
                'Img' => $imgData[0]['img_name'],
                'custId' => $uId,
                'custID' => $_SESSION['CID'],
                'custName' => $_SESSION['USERNAME'],
                'custAge' => round(((($_SESSION['AGE'] / 3600) / 24) / 365)),
                'custCaste' => $_SESSION['CASTE'],
                'marStat' => $marStat,
                'rashi' => $perData[0]['ras_name'],
                'star' => $perData[0]['star_name'],
                'gothra' => $perData[0]['pd_gothra'],
                'state' => $perData[0]['state_name'],
                'height' => $perData[0]['pd_height'],
                'weight' => $perData[0]['pd_weight'],
                'education' => $perData[0]['edu_name'] . ', ' . $perData[0]['pd_eduDetails'],
                'occupation' => $perData[0]['occ_name'],
                'empIn' => $empIn,
                'income' => $perData[0]['in_name'],
                'food' => $food,
                'smoking' => $smoking,
                'drinking' => $drinking,
                'famStat' => $famStat,
                'occFat' => $perData[0]['pd_occFather'],
                'occMot' => $perData[0]['pd_occMother'],
                'desc' => $perData[0]['pd_desc'],
                'patMarStat' => $patMarStat,
                'rel' => $patData[0]['rel_name'],
                'patCaste' => $patData[0]['caste_name'],
                'patState' => $patData[0]['state_name'],
                'patEdu' => $patData[0]['edu_name'],
                'patOcc' => $patData[0]['occ_name'],
                'patInc' => $patData[0]['in_name'],
                'patFamStat' => $patFamStat,
                'patFamType' => $patFamType,
                'patFamVal' => $patFamVal,
                'patDesc' => $patData[0]['pp_partnerDesc'],
                'profIntrst' => $profIntrst,
                'imageData' => $imageData
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
        }

    }
    if (!function_exists('editProf')) {

        function editProf($uId) {
            $sql = 'select pd_residingState, pd_residingCity, pd_height, pd_weight, pd_bodyType, '
                    . 'pd_complexion, pd_phyisicalStatus, pd_education, pd_eduDetails, '
                    . ' pd_occupation,  pd_occDetails, pd_employedIn, pd_income, pd_food, '
                    . ' pd_smoking, pd_drinking, pd_familyStatus, pd_familyType, pd_familyValues, '
                    . ' pd_occFather, pd_occMother, pd_desc '
                    . ' from bl_personal_detail where sign_up_id = ?';
            $pdData = DbOperations::getObject()->fetchData($sql, [$uId]);
            $sql = 'select su_name from bl_sign_up where su_id = ?';
            $suData = DbOperations::getObject()->fetchData($sql, [$uId]);
            $sql = 'select state_id, state_name from bl_states';
            $stateData = DbOperations::getObject()->fetchData($sql);
            $state = '';
            foreach ($stateData as $val) {
                $state .= '<option' . ((isset($pdData[0]['pd_residingState']) and ( $pdData[0]['pd_residingState'] === $val['state_id'])) ? ' selected="selected"' : '') . ' value="' . $val['state_id'] . '">' . $val['state_name'] . '</option>';
            }
            $bodyType = '<label class="col-12 text-info">Body Type</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="bodyType" id="average" value="1" checked="checked" class="form-check-input">
                                <label class="form-check-label" for="average">Average</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="athletic" value="2">
                                <label class="form-check-label" for="athletic">Athletic</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="slim" value="3">
                                <label class="form-check-label" for="slim">Slim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="heavy" value="4">
                                <label class="form-check-label" for="heavy">Athletic</label>
                            </div>';
            switch ($pdData[0]['pd_bodyType']) {
                case '1':
                    $bodyType = '<label class="col-12 text-info">Body Type</label>
                        <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="bodyType" id="average" value="1" checked="checked" class="form-check-input">
                                <label class="form-check-label" for="average">Average</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="athletic" value="2">
                                <label class="form-check-label" for="athletic">Athletic</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="slim" value="3">
                                <label class="form-check-label" for="slim">Slim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="heavy" value="4">
                                <label class="form-check-label" for="heavy">Athletic</label>
                            </div>';
                    break;
                case '2':
                    $bodyType = '<label class="col-12 text-info">Body Type</label>
                        <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="bodyType" id="average" value="1" class="form-check-input">
                                <label class="form-check-label" for="average">Average</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="athletic" value="2" checked="checked">
                                <label class="form-check-label" for="athletic">Athletic</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="slim" value="3">
                                <label class="form-check-label" for="slim">Slim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="heavy" value="4">
                                <label class="form-check-label" for="heavy">Athletic</label>
                            </div>';
                    break;
                case '3':
                    $bodyType = '<label class="col-12 text-info">Body Type</label>
                        <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="bodyType" id="average" value="1" class="form-check-input">
                                <label class="form-check-label" for="average">Average</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="athletic" value="2">
                                <label class="form-check-label" for="athletic">Athletic</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="slim" value="3" checked="checked">
                                <label class="form-check-label" for="slim">Slim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="heavy" value="4">
                                <label class="form-check-label" for="heavy">Athletic</label>
                            </div>';
                    break;

                default :
                    $bodyType = '<label class="col-12 text-info">Body Type</label>
                        <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="bodyType" id="average" value="1" class="form-check-input">
                                <label class="form-check-label" for="average">Average</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="athletic" value="2">
                                <label class="form-check-label" for="athletic">Athletic</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="slim" value="3">
                                <label class="form-check-label" for="slim">Slim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bodyType" id="heavy" value="4" checked="checked">
                                <label class="form-check-label" for="heavy">Athletic</label>
                            </div>';
                    break;
            }
            $complexion = '<label class="col-12 text-info">Complexion</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">
                                <label class="form-check-label" for="vryFair">Very Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="fair" value="2">
                                <label class="form-check-label" for="fair">Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatish" value="3">
                                <label class="form-check-label" for="wheatish">Wheatish</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatishBrown" value="4">
                                <label class="form-check-label" for="wheatishBrown">Wheatish Brown</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="dark" value="5">
                                <label class="form-check-label" for="dark">Dark</label>
                            </div>';
            switch ($pdData[0]['pd_complexion']) {
                case '1' :
                    $complexion = '<label class="col-12 text-info">Complexion</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="vryFair">Very Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="fair" value="2">
                                <label class="form-check-label" for="fair">Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatish" value="3">
                                <label class="form-check-label" for="wheatish">Wheatish</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatishBrown" value="4">
                                <label class="form-check-label" for="wheatishBrown">Wheatish Brown</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="dark" value="5">
                                <label class="form-check-label" for="dark">Dark</label>
                            </div>';
                    break;
                case '2' :
                    $complexion = '<label class="col-12 text-info">Complexion</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">
                                <label class="form-check-label" for="vryFair">Very Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="fair" value="2" checked="checked">
                                <label class="form-check-label" for="fair">Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatish" value="3">
                                <label class="form-check-label" for="wheatish">Wheatish</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatishBrown" value="4">
                                <label class="form-check-label" for="wheatishBrown">Wheatish Brown</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="dark" value="5">
                                <label class="form-check-label" for="dark">Dark</label>
                            </div>';
                    break;
                case '3' :
                    $complexion = '<label class="col-12 text-info">Complexion</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">
                                <label class="form-check-label" for="vryFair">Very Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="fair" value="2">
                                <label class="form-check-label" for="fair">Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatish" value="3" checked="checked">
                                <label class="form-check-label" for="wheatish">Wheatish</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatishBrown" value="4">
                                <label class="form-check-label" for="wheatishBrown">Wheatish Brown</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="dark" value="5">
                                <label class="form-check-label" for="dark">Dark</label>
                            </div>';
                    break;
                case '4' :
                    $complexion = '<label class="col-12 text-info">Complexion</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">
                                <label class="form-check-label" for="vryFair">Very Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="fair" value="2">
                                <label class="form-check-label" for="fair">Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatish" value="3">
                                <label class="form-check-label" for="wheatish">Wheatish</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatishBrown" value="4" checked="checked">
                                <label class="form-check-label" for="wheatishBrown">Wheatish Brown</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="dark" value="5">
                                <label class="form-check-label" for="dark">Dark</label>
                            </div>';
                    break;
                default :
                    $complexion = '<label class="col-12 text-info">Complexion</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="complexion" id="vryFair" value="1" class="form-check-input">
                                <label class="form-check-label" for="vryFair">Very Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="fair" value="2">
                                <label class="form-check-label" for="fair">Fair</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatish" value="3">
                                <label class="form-check-label" for="wheatish">Wheatish</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="wheatishBrown" value="4">
                                <label class="form-check-label" for="wheatishBrown">Wheatish Brown</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="complexion" id="dark" value="5" checked="checked">
                                <label class="form-check-label" for="dark">Dark</label>
                            </div>';
                    break;
            }
            $phyStat = '<label class="col-12 text-info">Physical Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="phyisicalStatus" id="normal" value="1" class="form-check-input">
                                <label class="form-check-label" for="normal">Normal</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="phyisicalStatus" id="phyChallenged" value="2">
                                <label class="form-check-label" for="phyChallenged">Phyisically Challenged</label>
                            </div>';
            switch ($pdData[0]['pd_phyisicalStatus']) {
                case '1':
                    $phyStat = '<label class="col-12 text-info">Physical Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="phyisicalStatus" id="normal" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="normal">Normal</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="phyisicalStatus" id="phyChallenged" value="2">
                                <label class="form-check-label" for="phyChallenged">Phyisically Challenged</label>
                            </div>';
                    break;
                default :
                    $phyStat = '<label class="col-12 text-info">Physical Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="phyisicalStatus" id="normal" value="1" class="form-check-input">
                                <label class="form-check-label" for="normal">Normal</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="phyisicalStatus" id="phyChallenged" value="2" checked="checked">
                                <label class="form-check-label" for="phyChallenged">Phyisically Challenged</label>
                            </div>';
                    break;
            }
            $education = '';
            $sql = 'select edu_id, edu_name from bl_education order by edu_name asc';
            $eduData = DbOperations::getObject()->fetchData($sql);
            foreach ($eduData as $value) {
                $education .= '<option' . ((isset($pdData[0]['pd_education']) and ( $pdData[0]['pd_education'] === $value['edu_id'])) ? ' selected="selected"' : '') . ' value="' . $value['edu_id'] . '">' . $value['edu_name'] . '</option>';
            }
            $occupation = '';
            $sql = 'select occ_id, occ_name from bl_occupation order by occ_name asc';
            $occData = DbOperations::getObject()->fetchData($sql);
            foreach ($occData as $data) {
                $occupation .= '<option' . ((isset($pdData[0]['pd_occupation']) and ( $pdData[0]['pd_occupation'] === $data['occ_id'])) ? ' selected="selected"' : '') . ' value="' . $data['occ_id'] . '">' . $data['occ_name'] . '</option>';
            }
            $employedIn = '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
            switch ($pdData[0]['pd_employedIn']) {
                case '1':
                    $employedIn = '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '2':
                    $employedIn = '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2" checked="checked">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '3':
                    $employedIn = '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3" checked="checked">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '4':
                    $employedIn = '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4" checked="checked">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '5':
                    $employedIn = '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5" checked="checked">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                default :
                    $employedIn = '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6" checked="checked">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
            }
            $income = '';
            $sql = 'select in_id, in_name from bl_income order by in_name asc';
            $inData = DbOperations::getObject()->fetchData($sql);
            foreach ($inData as $data) {
                $income .= '<option' . ((isset($pdData[0]['pd_income']) and ( $pdData[0]['pd_income'] === $data['in_id'])) ? ' selected="selected"' : '') . ' value="' . $data['in_id'] . '">' . $data['in_name'] . '</option>';
            }
            $food = '<label class="col-12 text-info">Food</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="food" id="vegetarian" value="1" class="form-check-input">
                                <label class="form-check-label" for="vegetarian">Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="nonVegitarian" value="2">
                                <label class="form-check-label" for="nonVegitarian">Non Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="eggetarian" value="3">
                                <label class="form-check-label" for="eggetarian">Eggetarian</label>
                            </div>';
            switch ($pdData[0]['pd_food']) {
                case '1':
                    $food = '<label class="col-12 text-info">Food</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="food" id="vegetarian" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="vegetarian">Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="nonVegitarian" value="2">
                                <label class="form-check-label" for="nonVegitarian">Non Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="eggetarian" value="3">
                                <label class="form-check-label" for="eggetarian">Eggetarian</label>
                            </div>';
                    break;
                case '2':
                    $food = '<label class="col-12 text-info">Food</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="food" id="vegetarian" value="1" class="form-check-input">
                                <label class="form-check-label" for="vegetarian">Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="nonVegitarian" value="2" checked="checked">
                                <label class="form-check-label" for="nonVegitarian">Non Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="eggetarian" value="3">
                                <label class="form-check-label" for="eggetarian">Eggetarian</label>
                            </div>';
                    break;
                default :
                    $food = '<label class="col-12 text-info">Food</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="food" id="vegetarian" value="1" class="form-check-input">
                                <label class="form-check-label" for="vegetarian">Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="nonVegitarian" value="2">
                                <label class="form-check-label" for="nonVegitarian">Non Vegetarian</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food" id="eggetarian" value="3" checked="checked">
                                <label class="form-check-label" for="eggetarian">Eggetarian</label>
                            </div>';
                    break;
            }
            $smoking = '<label class="col-12 text-info">Smoking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="smoking" id="no" value="1" class="form-check-input">
                                <label class="form-check-label" for="no">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="occasionally" value="2">
                                <label class="form-check-label" for="occasionally">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="yes" value="3">
                                <label class="form-check-label" for="yes">Yes</label>
                            </div>';
            switch ($pdData[0]['pd_smoking']) {
                case '1':
                    $smoking = '<label class="col-12 text-info">Smoking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="smoking" id="no" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="no">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="occasionally" value="2">
                                <label class="form-check-label" for="occasionally">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="yes" value="3">
                                <label class="form-check-label" for="yes">Yes</label>
                            </div>';
                    break;
                case '2':
                    $smoking = '<label class="col-12 text-info">Smoking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="smoking" id="no" value="1" class="form-check-input">
                                <label class="form-check-label" for="no">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="occasionally" value="2" checked="checked">
                                <label class="form-check-label" for="occasionally">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="yes" value="3">
                                <label class="form-check-label" for="yes">Yes</label>
                            </div>';
                    break;
                default :
                    $smoking = '<label class="col-12 text-info">Smoking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="smoking" id="no" value="1" class="form-check-input">
                                <label class="form-check-label" for="no">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="occasionally" value="2">
                                <label class="form-check-label" for="occasionally">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="smoking" id="yes" value="3" checked="checked">
                                <label class="form-check-label" for="yes">Yes</label>
                            </div>';
                    break;
            }
            $drinking = '<label class="col-12 text-info">Drinking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="drinking" id="noDrnk" value="1" class="form-check-input">
                                <label class="form-check-label" for="noDrnk">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="occasionallyDrnk" value="2">
                                <label class="form-check-label" for="occasionallyDrnk">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="yesDrnk" value="3">
                                <label class="form-check-label" for="yesDrnk">Yes</label>
                            </div>';
            switch ($pdData[0]['pd_drinking']) {
                case '1':
                    $drinking = '<label class="col-12 text-info">Drinking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="drinking" id="noDrnk" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="noDrnk">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="occasionallyDrnk" value="2">
                                <label class="form-check-label" for="occasionallyDrnk">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="yesDrnk" value="3">
                                <label class="form-check-label" for="yesDrnk">Yes</label>
                            </div>';
                    break;
                case '2':
                    $drinking = '<label class="col-12 text-info">Drinking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="drinking" id="noDrnk" value="1" class="form-check-input">
                                <label class="form-check-label" for="noDrnk">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="occasionallyDrnk" value="2" checked="checked">
                                <label class="form-check-label" for="occasionallyDrnk">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="yesDrnk" value="3">
                                <label class="form-check-label" for="yesDrnk">Yes</label>
                            </div>';
                    break;
                default :
                    $drinking = '<label class="col-12 text-info">Drinking</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="drinking" id="noDrnk" value="1" class="form-check-input">
                                <label class="form-check-label" for="noDrnk">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="occasionallyDrnk" value="2">
                                <label class="form-check-label" for="occasionallyDrnk">Occasionally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="drinking" id="yesDrnk" value="3" checked="checked">
                                <label class="form-check-label" for="yesDrnk">Yes</label>
                            </div>';
                    break;
            }
            $familyStat = '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
            switch ($pdData[0]['pd_familyStatus']) {
                case '1':
                    $familyStat = '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
                case '2':
                    $familyStat = '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2" checked="checked">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
                case '3':
                    $familyStat = '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3" checked="checked">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
                default :
                    $familyStat = '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4" checked="checked">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
            }
            $familyType = '<label class="col-12 text-info">Family Type</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyType" id="joint" value="1" class="form-check-input">
                                <label class="form-check-label" for="joint">Joint</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyType" id="Nuclear" value="2">
                                <label class="form-check-label" for="Nuclear">Nuclear</label>
                            </div>';
            switch ($pdData[0]['pd_familyType']) {
                case '1':
                    $familyType = '<label class="col-12 text-info">Family Type</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyType" id="joint" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="joint">Joint</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyType" id="Nuclear" value="2">
                                <label class="form-check-label" for="Nuclear">Nuclear</label>
                            </div>';
                    break;
                default :
                    $familyType = '<label class="col-12 text-info">Family Type</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyType" id="joint" value="1" class="form-check-input">
                                <label class="form-check-label" for="joint">Joint</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyType" id="Nuclear" value="2" checked="checked">
                                <label class="form-check-label" for="Nuclear">Nuclear</label>
                            </div>';
                    break;
            }
            $familyValues = '<label class="col-12 text-info">Family Values</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="2">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="2">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
            switch ($pdData[0]['pd_familyValues']) {
                case '1':
                    $familyValues = '<label class="col-12 text-info">Family Values</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
                case '2':
                    $familyValues = '<label class="col-12 text-info">Family Values</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2" checked="checked">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
                case '3':
                    $familyValues = '<label class="col-12 text-info">Family Values</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3"  checked="checked">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
                default :
                    $familyValues = '<label class="col-12 text-info">Family Values</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4" checked="checked">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
            }
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Edit Profile ' . $_SESSION['USERNAME'],
                'MetaKeys' => 'Personal Details, brightlife Matrimony, Describe Yourself Brightlife Matrimony',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'edit-profile.html'),
                'buttonHead' => $_SESSION['btn'],
                'State' => $state,
                'resCity' => $pdData[0]['pd_residingCity'],
                'height' => $pdData[0]['pd_height'],
                'weight' => $pdData[0]['pd_weight'],
                'bodyType' => $bodyType,
                'complexion' => $complexion,
                'physicalStat' => $phyStat,
                'Education' => $education,
                'educationDetails' => $pdData[0]['pd_eduDetails'],
                'Occupation' => $occupation,
                'occupationDetails' => $pdData[0]['pd_occDetails'],
                'employedIn' => $employedIn,
                'income' => $income,
                'food' => $food,
                'smoking' => $smoking,
                'drinking' => $drinking,
                'familyStat' => $familyStat,
                'familyType' => $familyType,
                'familyValues' => $familyValues,
                'fatherOcc' => $pdData[0]['pd_occFather'],
                'motherOcc' => $pdData[0]['pd_occMother'],
                'desc' => $pdData[0]['pd_desc'],
                'custName' => $_SESSION['USERNAME'],
                'custId' => $uId
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
        }

    }
    if (!function_exists('saveEditedPerData')) {

        function saveEditedPerData($uId) {
            $post = DataFilter::getObject()->cleanData($_POST);
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
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildUpdateQuery(
                    'bl_personal_detail', ['pd_residingState', 'pd_residingCity', 'pd_height', 'pd_weight', 'pd_bodyType', 'pd_complexion', 'pd_phyisicalStatus', 'pd_education', 'pd_eduDetails', 'pd_occupation', 'pd_occDetails', 'pd_employedIn', 'pd_income', 'pd_food', 'pd_smoking', 'pd_drinking', 'pd_familyStatus', 'pd_familyType', 'pd_familyValues', 'pd_occFather', 'pd_occMother', 'pd_desc'], ['sign_up_id']
            );
            $ins = [
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
                $uId
            ];
            $suc = DbOperations::getObject()->runQuery($ins);
            if ($suc !== false) {
                DbOperations::getObject()->transaction('on');
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'Personal Detail Saved Successfully';
                header('Location:' . ACCESS_URL . 'user-index/' . $uId . '/');
                exit(0);
            } else {
                DbOperations::getObject()->transaction('off');
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Some error occured, please retry';
                header('Location:' . ACCESS_URL . 'user-index/' . $uId . '/');
                exit(0);
            }
        }

    }
    if (!function_exists('editPatPref')) {

        function editPatPref($custId) {
            $sql = 'select pp_maritial_stat, pp_religion, pp_caste, pp_residing_state, '
                    . ' pp_residing_city, pp_ageFrom, pp_ageTo, pp_education, pp_occupation, '
                    . ' pp_income, pp_employedIn, pp_familyStatus, pp_familyType, pp_familyValues, '
                    . ' pp_partnerDesc from bl_partner_preference where signup_id = ?';
            $pData = DbOperations::getObject()->fetchData($sql, [$custId]);
            $oSql = 'select occ_id, occ_name from bl_occupation order by occ_name asc';
            $oData = DbOperations::getObject()->fetchData($oSql);
            $occupation = '';
            foreach ($oData as $val1) {
                $occupation .= '<option ' . ((isset($pData[0]['pp_occupation']) and ( $pData[0]['pp_occupation'] === $val1['occ_id'])) ? 'selected="selected"' : '') . ' value="' . $val1['occ_id'] . '">' . $val1['occ_name'] . '</option>';
            }
            $sql = 'select in_id, in_name from bl_income order by in_name asc';
            $inData = DbOperations::getObject()->fetchData($sql);
            $income = '';
            foreach ($inData as $val2) {
                $income .= '<option ' . ((isset($pData[0]['pp_income']) and ( $pData[0]['pp_income'] === $val2['in_id'])) ? 'selected="selected"' : '') . ' value="' . $val2['in_id'] . '">' . $val2['in_name'] . '</option>';
            }
            $relSql = 'select rel_id, rel_name from religion order by rel_name asc';
            $relData = DbOperations::getObject()->fetchData($relSql);
            $religion = '';
            foreach ($relData as $data) {
                $religion .= '<option ' . ((isset($pData[0]['pp_religion']) and ( $pData[0]['pp_religion'] === $data['rel_id'])) ? 'selected="selected"' : '') . ' value="' . $data['rel_id'] . '">' . $data['rel_name'] . '</option>';
            }
            $cSql = 'select caste_id, caste_name from bl_caste order by caste_name asc';
            $cData = DbOperations::getObject()->fetchData($cSql);
            $caste = '';
            foreach ($cData as $val) {
                $caste .= '<option ' . ((isset($pData[0]['pp_caste']) and ( $pData[0]['pp_caste'] === $val['caste_id'])) ? 'selected="selected"' : '') . ' value="' . $val['caste_id'] . '">' . $val['caste_name'] . '</option>';
            }
            $sSql = 'select state_id, state_name from bl_states order by state_name asc';
            $sData = DbOperations::getObject()->fetchData($sSql);
            $state = '';
            foreach ($sData as $dat) {
                $state .= '<option ' . ((isset($pData[0]['pp_residing_state']) and ( $pData[0]['pp_residing_state'] === $dat['state_id'])) ? 'selected="selected"' : '') . ' value="' . $dat['state_id'] . '">' . $dat['state_name'] . '</option>';
            }
            $eSql = 'select edu_id, edu_name from bl_education order by edu_name asc';
            $eData = DbOperations::getObject()->fetchData($eSql);
            $education = '';
            foreach ($eData as $dat1) {
                $education .= '<option ' . ((isset($pData[0]['pp_education']) and ( $pData[0]['pp_education'] === $dat1['edu_id'])) ? 'selected="selected"' : '') . ' value="' . $dat1['edu_id'] . '">' . $dat1['edu_name'] . '</option>';
            }
            $marStat = '<label class="col-12 text-info">Marital Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="maritalStat" id="unmarried" value="1" class="form-check-input">
                                <label class="form-check-label" for="unmarried">Unmarried</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="widower" value="2">
                                <label class="form-check-label" for="widower">Widower</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="divorced" value="3">
                                <label class="form-check-label" for="divorced">Divorced</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="awtDivorce" value="4">
                                <label class="form-check-label" for="awtDivorce">Awaiting Divorce</label>
                            </div>';
            switch ($pData[0]['pp_maritial_stat']) {
                case '1':
                    $marStat = '<label class="col-12 text-info">Marital Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="maritalStat" id="unmarried" value="1" checked="checked" class="form-check-input">
                                <label class="form-check-label" for="unmarried">Unmarried</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="widower" value="2">
                                <label class="form-check-label" for="widower">Widower</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="divorced" value="3">
                                <label class="form-check-label" for="divorced">Divorced</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="awtDivorce" value="4">
                                <label class="form-check-label" for="awtDivorce">Awaiting Divorce</label>
                            </div>';
                    break;
                case '2':
                    $marStat = '<label class="col-12 text-info">Marital Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="maritalStat" id="unmarried" value="1" checked="checked" class="form-check-input">
                                <label class="form-check-label" for="unmarried">Unmarried</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="widower" value="2" checked="checked">
                                <label class="form-check-label" for="widower">Widower</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="divorced" value="3">
                                <label class="form-check-label" for="divorced">Divorced</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="awtDivorce" value="4">
                                <label class="form-check-label" for="awtDivorce">Awaiting Divorce</label>
                            </div>';
                    break;
                case '3':
                    $marStat = '<label class="col-12 text-info">Marital Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="maritalStat" id="unmarried" value="1" checked="checked" class="form-check-input">
                                <label class="form-check-label" for="unmarried">Unmarried</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="widower" value="2">
                                <label class="form-check-label" for="widower">Widower</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="divorced" value="3" checked="checked">
                                <label class="form-check-label" for="divorced">Divorced</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="awtDivorce" value="4">
                                <label class="form-check-label" for="awtDivorce">Awaiting Divorce</label>
                            </div>';
                    break;
                default :
                    $marStat = '<label class="col-12 text-info">Marital Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="maritalStat" id="unmarried" value="1" checked="checked" class="form-check-input">
                                <label class="form-check-label" for="unmarried">Unmarried</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="widower" value="2">
                                <label class="form-check-label" for="widower">Widower</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="divorced" value="3">
                                <label class="form-check-label" for="divorced">Divorced</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="maritalStat" id="awtDivorce" value="4" checked="checked">
                                <label class="form-check-label" for="awtDivorce">Awaiting Divorce</label>
                            </div>';
                    break;
            }
            $empIn = '';
            switch ($pData[0]['pp_employedIn']) {
                case '1':
                    $empIn .= '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '2':
                    $empIn .= '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2" checked="checked">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '3':
                    $empIn .= '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3" checked="checked">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '4':
                    $empIn .= '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4" checked="checked">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                case '5':
                    $empIn .= '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5" checked="checked">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
                default :
                    $empIn .= '<label class="col-12 text-info">Employed In</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="employedIn" id="government" value="1" class="form-check-input">
                                <label class="form-check-label" for="government">Government</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="private" value="2">
                                <label class="form-check-label" for="private">Private</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="business" value="3">
                                <label class="form-check-label" for="business">Business</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="Defence" value="4">
                                <label class="form-check-label" for="Defence">Defence</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="SelfEmployed" value="5">
                                <label class="form-check-label" for="SelfEmployed">Self Employed</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="employedIn" id="notWorking" value="6" checked="checked">
                                <label class="form-check-label" for="notWorking">Not Working</label>
                            </div>';
                    break;
            }
            $famStat = '';
            switch ($pData[0]['pp_familyStatus']) {
                case '1':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
                case '2':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2" checked="checked">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
                case '3':
                    $famStat .= '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3" checked="checked">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
                default :
                    $famStat .= '<label class="col-12 text-info">Family Status</label>
                <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyStatus" id="middleClass" value="1" class="form-check-input">
                                <label class="form-check-label" for="middleClass">Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="upMiddleClass" value="2">
                                <label class="form-check-label" for="upMiddleClass">Upper Middle Class</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="rich" value="3">
                                <label class="form-check-label" for="rich">Rich</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyStatus" id="affluent" value="4" checked="checked">
                                <label class="form-check-label" for="affluent">Affluent</label>
                            </div>';
                    break;
            }
            $famType = '';
            switch ($pData[0]['pp_familyType']) {
                case '1':
                    $famType .= '<label class="col-12 text-info">Family Type</label>
                            <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyType" id="joint" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="joint">Joint</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyType" id="Nuclear" value="2">
                                <label class="form-check-label" for="Nuclear">Nuclear</label>
                            </div>';
                    break;
                default :
                    $famType .= '<label class="col-12 text-info">Family Type</label>
                            <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyType" id="joint" value="1" class="form-check-input">
                                <label class="form-check-label" for="joint">Joint</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyType" id="Nuclear" value="2" checked="checked">
                                <label class="form-check-label" for="Nuclear">Nuclear</label>
                            </div>';
                    break;
            }
            $famValues = '';
            switch ($pData[0]['pp_familyValues']) {
                case '1':
                    $famValues .= '<label class="col-12 text-info">Family Values</label>
                            <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input" checked="checked">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
                case '2':
                    $famValues .= '<label class="col-12 text-info">Family Values</label>
                            <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2" checked="checked">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
                case '3':
                    $famValues .= '<label class="col-12 text-info">Family Values</label>
                            <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3" checked="checked">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
                default :
                    $famValues .= '<label class="col-12 text-info">Family Values</label>
                            <div class="form-check form-check-inline ml-3">
                                <input type="radio" name="familyValues" id="orthodox" value="1" class="form-check-input">
                                <label class="form-check-label" for="orthodox">Orthodox</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="traditional" value="2">
                                <label class="form-check-label" for="traditional">Traditional</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="moderate" value="3">
                                <label class="form-check-label" for="moderate">Moderate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="familyValues" id="liberal" value="4" checked="checked">
                                <label class="form-check-label" for="liberal">Liberal</label>
                            </div>';
                    break;
            }
            $replaceData = [
                'PageTitle' => 'Brightlife Matrimony - Edit Profile ' . $_SESSION['USERNAME'],
                'MetaKeys' => 'Personal Details, brightlife Matrimony, Describe Yourself Brightlife Matrimony',
                'MetaDesc' => '',
                'CSSHelpers' => ['style.min.css'],
                'JSHelpers' => ['script.js'],
                'exJs' => file_get_contents(PGS_DIR . DS . 'ex-js-inner-banner.html'),
                'Contents' => file_get_contents(PGS_DIR . DS . 'edit-partner-preference.html'),
                'buttonHead' => $_SESSION['btn'],
                'marStat' => $marStat,
                'religion' => $religion,
                'caste' => $caste,
                'state' => $state,
                'resCity' => $pData[0]['pp_residing_city'],
                'ageFrom' => $pData[0]['pp_ageFrom'],
                'ageTo' => $pData[0]['pp_ageTo'],
                'Education' => $education,
                'Occupation' => $occupation,
                'employedIn' => $empIn,
                'famStat' => $famStat,
                'famType' => $famType,
                'income' => $income,
                'famValues' => $famValues,
                'patDesc' => $pData[0]['pp_partnerDesc'],
                'custName' => $_SESSION['USERNAME'],
                'custId' => $custId
            ];
            if (isLogged() === FALSE) {
                assignTemplate($replaceData, '');
            } else {
                assignTemplate($replaceData, 'logTemplate.html');
            }
        }

    }
    if (!function_exists('saveEditedPatPref')) {

        function saveEditedPatPref($custId) {
            $post = DataFilter::getObject()->cleanData($_POST);
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildUpdateQuery(
                    'bl_partner_preference', ['pp_maritial_stat', 'pp_religion', 'pp_caste', 'pp_residing_state', 'pp_residing_city', 'pp_ageFrom', 'pp_ageTo', 'pp_education', 'pp_occupation', 'pp_employedIn', 'pp_income', 'pp_familyStatus', 'pp_familyType', 'pp_familyValues', 'pp_partnerDesc', 'pp_dttm'], ['signup_id']
            );
            $ins = [
                $post['maritalStat'],
                $post['religion'],
                $post['caste'],
                $post['residingState'],
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
                DBTIMESTAMP,
                $custId
            ];
            $suc = DbOperations::getObject()->runQuery($ins);
            if ($suc !== false) {
                DbOperations::getObject()->transaction('on');
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'Partner Preference Updated Successfully';
                header('Location:' . ACCESS_URL . 'user-index/' . $custId . '/');
                exit;
            } else {
                DbOperations::getObject()->transaction('off');
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Some error Occured, Please retry';
                header('Location:' . ACCESS_URL . 'user-index/' . $custId . '/');
                exit;
            }
        }

    }
    if (!function_exists('addImage')) {

        function addImage($cusId) {
            if (!isset($_FILES['uploadPhoto']['name']) or empty($_FILES['uploadPhoto']['name'])) {
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Please attach one or multiple image';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildInsertQuery('bl_images');
            Uploader::getObject()->allowedFileSize = 10 * 1024 * 1024;
            Uploader::getObject()->allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $img = Uploader::getObject()->uploadMultipleFiles($_FILES['uploadPhoto']);
            foreach ($img['fileData'] as $value) {
                if ($value['status'] === 'success') {
                    ResizeImage::getObject()->setImage(UPLOAD_DIR . DS . $value['newFileName']);
                    ResizeImage::getObject()->resizeTo(480, 640);
                    ResizeImage::getObject()->saveImage(UPLOAD_DIR . DS . $value['newFileName'], 65);
                    $ins = [
                        null,
                        $cusId,
                        $value['newFileName'],
                        0,
                        DBTIMESTAMP
                    ];
                    DbOperations::getObject()->runQuery($ins);
                } else {
                    DbOperations::getObject()->transaction('off');
                    $_SESSION['STATUS'] = 'error';
                    $_SESSION['MSG'] = 'Some error occured while saving data into Database, please retry';
                    header('Location:' . ACCESS_URL);
                    exit;
                }
            }
            DbOperations::getObject()->transaction('on');
            $_SESSION['STATUS'] = 'success';
            $_SESSION['MSG'] = 'Images Saved Successfully';
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }

    }
    if (!function_exists('setDp')) {

        function setDp() {
            $post = DataFilter::getObject()->cleanData($_POST);
            $sql = 'select img_id from bl_images where img_dp = 1 and img_su_id = ?';
            $imData = DbOperations::getObject()->fetchData($sql, [$_SESSION['UID']]);
            DbOperations::getObject()->transaction('start');
            DbOperations::getObject()->buildUpdateQuery(
                    'bl_images', ['img_dp'], ['img_su_id']
            );
            $ins = [
                0,
                $_SESSION['UID']
            ];
            $suc = DbOperations::getObject()->runQuery($ins);
            DbOperations::getObject()->buildUpdateQuery(
                    'bl_images', ['img_dp'], ['img_id']
                    );
            $ins1 = [
                1,
                $post['img']
            ];
            $suc1 = DbOperations::getObject()->runQuery($ins1);
            if ($suc !== false and $suc1 !== false) {
                DbOperations::getObject()->transaction('on');
                $_SESSION['STATUS'] = 'success';
                $_SESSION['MSG'] = 'Profile picture set successfuly';
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                DbOperations::getObject()->transaction('off');
                $_SESSION['STATUS'] = 'error';
                $_SESSION['MSG'] = 'Some error occured, Please retry';
                header('Location:' . $_SERVER['HTTP_REFERER']);
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