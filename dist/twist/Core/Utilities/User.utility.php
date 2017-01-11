<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html GPL License
 * @link       https://twistphp.com
 */

namespace Twist\Core\Utilities;

use \Twist\Core\Models\User\User as UserObject;
use \Twist\Core\Models\User\Auth;
use \Twist\Core\Models\User\SessionHandler;

/**
 * User management and control allowing users to register, login and be updated
 * Functionality to edit, reset passwords, send welcome emails with session management for multi and single devices
 */
class User extends Base{

    public $strLoginUrl = null;
    public $strViewLocation;
    public $resView = null;
    protected $resCurrentUser = null;

    public function __construct(){

        //Set the main user settings from the database
        $this->strLoginUrl = '/login';

        $this->resView = \Twist::View();
        $this->setCustomTemplateLocation(sprintf('%suser/',TWIST_FRAMEWORK_VIEWS));
    }

    /**
     * Check to see if the user is logged into the system
     * @return bool
     */
    public function loggedIn(){
        $arrAuthData = Auth::current();
        return $arrAuthData['status'];
    }

    /**
     * Return data about the logged in user
     * @param null $strKey
     * @return array|mixed
     */
    public function loggedInData($strKey = null){
        return (is_object($this->current())) ? $this->current()->get($strKey) : null;
    }

    /**
     * @alias currentID
     * @return null
     */
    public function loggedInID(){
        return $this->currentID();
    }

    /**
     * @alias currentLevel
     * @return null
     */
    public function loggedInLevel(){
        return $this->currentLevel();
    }

    /**
     * Get an object of the current user, if the user is not logged in null will be returned
     * @return \Twist\Core\Models\User\User
     */
    public function current(){

        $arrAuthData = Auth::current();
        if(is_null($this->resCurrentUser) && $arrAuthData['status']){
            $this->resCurrentUser = $this->get($arrAuthData['user_id']);
        }

        return $this->resCurrentUser;
    }

    /**
     * Get the current users user ID
     * @return null|int
     */
    public function currentID(){
        $arrAuthData = Auth::current();
        return $arrAuthData['user_id'];
    }

    /**
     * Get the current users user level
     * @return null|int
     */
    public function currentLevel(){
        $arrAuthData = Auth::current();
        return ($arrAuthData['status']) ? $arrAuthData['user_data']['level'] : null;
    }

    /**
     * Manual authentication if you are not using BaseControllerUser to handle the login and authentication for you
     * @param null $strEmailAddress
     * @param null $strPassword
     * @param null $blRememberMe
     * @return array
     */
    public function authenticate($strEmailAddress = null,$strPassword = null,$blRememberMe = null){

        if(is_null($strEmailAddress) && is_null($strPassword)){
            $strEmailAddress = (array_key_exists('email',$_POST) && !is_null($_POST['email'])) ? $_POST['email'] : null;
            $strPassword = (array_key_exists('password',$_POST) && !is_null($_POST['password'])) ? $_POST['password'] : null;
        }

        $blRememberMe = (is_null($blRememberMe)) ? ((array_key_exists('remember',$_POST) && $_POST['remember'] === '1') ? true : false) : $blRememberMe;

        return Auth::login($strEmailAddress,$strPassword,$blRememberMe);
    }

    /**
     * Log the user out of the system and destroy the users session
     */
    public function logout(){
        return Auth::logout();
    }

	/**
	 * Get the pre-built, unbranded HTML registration form and return it as a string.
	 * @param string $strLoginPage
	 * @return string
	 */
    public function getRegistrationForm($strLoginPage = ''){
        return $this->viewExtension(($strLoginPage === '') ? 'registration_form' : sprintf('%s,%s','registration_form',$strLoginPage));
    }

    /**
     * Get the pre-built, unbranded HTML login form and return it as a string.
     * @param string $strLoginPage
     * @return mixed
     */
    public function getLoginForm($strLoginPage = ''){
        return $this->viewExtension(($strLoginPage === '') ? 'login_form' : sprintf('%s,%s','login_form',$strLoginPage));
    }

    /**
     * Get the pre-built, unbranded HTML forgotten password form and return it as a string.
     * @param string $strLoginPage
     * @return mixed
     */
    public function getForgottenPasswordForm($strLoginPage = ''){
        return $this->viewExtension(($strLoginPage === '') ? 'forgotten_password_form' : sprintf('%s,%s','forgotten_password_form',$strLoginPage));
    }

    /**
     * Get the pre-built, unbranded HTML change password form and return it as a string.
     * @param string $strLoginPage
     * @return mixed
     */
    public function getChangePasswordForm($strLoginPage = ''){
        return $this->viewExtension(($strLoginPage === '') ? 'change_password_form' : sprintf('%s,%s','change_password_form',$strLoginPage));
    }

    public function afterLoginRedirect(){

        $objSession = \Twist::Session();

        //Get the URL and then unset so only happens once
        $strUrl = $this->getAfterLoginRedirect();

        //If the login redirect is set then proceed to redirect
        if(!is_null($strUrl)){

            $this->clearAfterLoginRedirect();

            //Just in case, remove the logout comment otherwise the redirect could log you out again
            $strUrl = str_replace(array("?logout=1","?logout"),"",$strUrl);

            if($strUrl != $_SERVER['REQUEST_URI']
                && !in_array(substr($strUrl, -3), array('.js'))
                && !in_array(substr($strUrl, -4), array('.css','.jpg','.png','.gif','.ico'))){
                $this->goToPage($strUrl);
            } else {
                //$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
                $this->goToPage( sprintf('%s%s?change',\Twist::Route()->current('registered_uri'),$this->strLoginUrl), false );
            }
        }elseif($objSession->data('user-temp_password') === '1' && !strstr($_SERVER['REQUEST_URI'],'?change')){
            //$this->goToPage( '?change', false );
            $this->goToPage( sprintf('%s%s?change',\Twist::Route()->current('registered_uri'),$this->strLoginUrl), false );
        }
    }

	/**
	 * @param null $strRedirectURL
	 * @return mixed
	 */
    public function setAfterLoginRedirect($strRedirectURL = null){
        $strRedirectURL = (is_null($strRedirectURL)) ? $_SERVER['REQUEST_URI'] : str_replace('//','/',$strRedirectURL);
        return \Twist::Session()->data('site-login_redirect',$strRedirectURL);
    }

	/**
	 * @return mixed
	 */
    public function getAfterLoginRedirect(){
        return \Twist::Session()->data('site-login_redirect');
    }

    public function clearAfterLoginRedirect(){
        \Twist::Session()->remove('site-login_redirect');
    }

    /**
     * Redirect the user to a relevant page when required
     * @param string $strPageURI
     * @param bool $blReturnUserAfterLogin
     */
    protected function goToPage($strPageURI, $blReturnUserAfterLogin = false){

        if($strPageURI != ''){

            if($blReturnUserAfterLogin){
                $this->setAfterLoginRedirect();
            }

            \Twist::redirect($strPageURI);
        }
    }

    /**
     * Get the user as an object
     * @param integer $intUserID
     * @return \Twist\Core\Models\User\User
     */
    public function get($intUserID){
        return new UserObject(\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'users')->get($intUserID),$this);
    }

    /**
     * Create a new user and return the user object
     * @return \Twist\Core\Models\User\User
     */
    public function create(){
        return new UserObject(\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'users')->create(),$this);
    }

    /**
     * Get an array of the users default information by User ID
     * @param integer $intUserID
     * @return array
     */
    public function getData($intUserID){
        return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'users')->get($intUserID,'id',true);
    }

    /**
     * Get and array of the users default information by User Email
     * @param string $strEmail
     * @return array
     */
    public function getByEmail($strEmail){
        return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'users')->get($strEmail,'email',true);
    }

    /**
     * Get user full details by User ID
     * @param integer $intUserID
     * @return array
     */
    public function getDetailsByID($intUserID){

        $arrUserDetails = array();

        $resResult = \Twist::Database()->query("SELECT `ud`.`data`,`udf`.`slug` FROM `%suser_data` AS `ud` JOIN `%suser_data_fields` AS `udf` ON `ud`.`field_id` = `udf`.`id` WHERE `ud`.`user_id` = %d",
            TWIST_DATABASE_TABLE_PREFIX,
            TWIST_DATABASE_TABLE_PREFIX,
            $intUserID
        );

        if($resResult->status() && $resResult->numberRows()){
            foreach($resResult->rows() as $arrEachItem){
                $arrUserDetails[$arrEachItem['slug']] = $arrEachItem['data'];
            }
        }

        return $arrUserDetails;
    }

    /**
     * Get and array of the users default information by User ID
     * @param integer $intUserID
     * @return array
     */
    public function getAll($strOrderBy = 'id'){
        return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'users')->find(null,null,$strOrderBy);
    }

    /**
     * Get and array of the users default information by User ID
     * @param integer $intUserID
     * @return array
     */
    public function getAllByLevel($intLevelID){
        return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'users')->find($intLevelID,'level');
    }

    /**
     * Get information about any given user level ID
     * @param integer $intLevelID
     * @return array
     */
    public function getLevel($intLevelID){
        return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'user_levels')->get($intLevelID,'level',true);
    }

    /**
     * Get all the levels in the system
     * @return array
     */
    public function getLevels(){
        return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'user_levels')->find();
    }

	/**
	 * @param string $strVerificationCode
	 * @return bool
	 */
    public function verifyEmail($strVerificationCode){

        $blOut = false;

        if($strVerificationCode != ''){

            $strVerifyData = $this->base64url_decode($strVerificationCode);
            $arrParts = explode('|',$strVerifyData);

            //Check that the email address is semi valid and code is long enough
            if(strstr($arrParts[0],'@') && strstr($arrParts[0],'.') && strlen($arrParts[1]) === 16){

                $resResult = \Twist::Database()->query("UPDATE `%s`.`%susers`
												SET `verified` = '1',
													`verification_code` = ''
												WHERE `email` = '%s'
												AND `verification_code` = '%s'
												LIMIT 1",
                    TWIST_DATABASE_NAME,
                    TWIST_DATABASE_TABLE_PREFIX,
                    $arrParts[0],
                    $arrParts[1]
                );

                if($resResult->status() && $resResult->affectedRows()){
                    $blOut = true;
                    \Twist::Session()->data('site-login_message','Your account has been verified');
                }else{
                    \Twist::Session()->data('site-login_error_message','Failed to verify your account, invalid verification code');
                }
            }
        }

        return $blOut;
    }

	/**
	 * @param string $strData
	 * @return string
	 */
    protected function base64url_encode($strData) {
        $strBase64 = base64_encode($strData);
        $strBase64URL = strtr($strBase64, '+/=', '-_$');
        return $strBase64URL;
    }

	/**
	 * @param string $strBase64URL
	 * @return string
	 */
    protected function base64url_decode($strBase64URL) {
        $strBase64 = strtr($strBase64URL, '-_$', '+/=');
        $strData = base64_decode($strBase64);
        return $strData;
    }

    /**
     * Update the users password to a new password. THis is the non secure method for when you don't know the users original password.
     * Default use would be for a forgotten password system etc.
     * @param integer $intUserID
     * @param string $strNewPassword
     * @return bool
     */
    public function updatePassword($intUserID,$strNewPassword){

        $blPasswordChanged = false;
        $resUser = $this->get($intUserID);

        $arrOut = $resUser->password($strNewPassword);

        if($arrOut['status']){
            $resUser->commit();
            $blPasswordChanged = true;
        }else{
            \Twist::Session()->data('site-error_message',$arrOut['message']);
        }

        if(!$blPasswordChanged){
            //Send the user back to the change password page
            //$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
            $this->goToPage( sprintf('%s%s?change',\Twist::Route()->current('registered_uri'),$this->strLoginUrl), false );
        }

        return $blPasswordChanged;
    }

    /**
     * Change password, this used when you have both the users old and new password, very useful to ensure the user who is changing
     * the password is the valid account holder.
     * @param integer $intUserID
     * @param string $strNewPassword
     * @param string $strCurrentPassword
     * @param bool $blRedirectOnFail
     * @return bool
     */
    public function changePassword($intUserID,$strNewPassword,$strCurrentPassword,$blRedirectOnFail=true){

        $blPasswordChanged = false;
        $resUser = $this->get($intUserID);

        if($strNewPassword === $strCurrentPassword){
            \Twist::Session()->data('site-error_message','Your new password must be different from your current password');
        }else{
            if($resUser->comparePasswordHash(sha1($strCurrentPassword))){
                $arrOut = $resUser->password($strNewPassword);

                if($arrOut['status']){
                    $resUser->commit();
                    $blPasswordChanged = true;
                }else{
                    \Twist::Session()->data('site-error_message',$arrOut['message']);
                }
            }else{
                \Twist::Session()->data('site-error_message','Your current password is incorrect');
            }
        }


        if(!$blPasswordChanged && $blRedirectOnFail){
            //Send the user back to the change password page
            //$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
            $this->goToPage( sprintf('%s%s?change',\Twist::Route()->current('registered_uri'),$this->strLoginUrl), false );
        }

        return $blPasswordChanged;
    }

	/**
	 * @param string $strViewLocation
	 */
    public function setCustomTemplateLocation($strViewLocation){

        if(!file_exists($strViewLocation)){
            $strViewLocation = trim($strViewLocation,'/').'/';
            $strViewLocation = sprintf('%s/%s',TWIST_DOCUMENT_ROOT,$strViewLocation);
        } else {
            $strViewLocation = rtrim($strViewLocation,'/').'/';
        }

        $this->strViewLocation = $strViewLocation;
    }

	/**
	 * @param string $strReference
	 * @return string
	 */
    public function viewExtension($strReference){

        $strData = '';
        $arrParts = explode(',',$strReference);

        $strParameter = '';
        $strLoginPage = $this->strLoginUrl;

        if(count($arrParts) > 1){
            $strReference = $arrParts[0];
            $strLoginPage = $arrParts[1];
        }

        //If the user is on a temp password show the change password form
        if(\Twist::Session()->data('user-temp_password') === '1' && $strReference === 'login_form'){
            $strReference = 'change_password_form';
        }

        if(array_key_exists('forgotten',$_GET)){
            $strReference = 'forgotten_password_form';
        }elseif(array_key_exists('change',$_GET) && $this->loggedIn()){
            $strReference = 'change_password_form';
        }elseif(array_key_exists('verification',$_GET)){
            $strReference = 'account_verification';
        }elseif(array_key_exists('register',$_GET)){
            $strReference = 'registration_form';
        }elseif(array_key_exists('devices',$_GET) && $this->loggedIn()){
            $strReference = 'devices_form';
        }

        $strDefaultLogin = $this->strViewLocation.'login.tpl';

        switch($strReference){

            case'login_form':

                if($this->loggedIn()){
                    //\Twist::redirect(($strLoginPage === $_SERVER['REQUEST_URI']) ? './' : $strLoginPage);
                    \Twist::redirect('./');
                }else{

                    $arrTags = array(
                        'login_page' => $strLoginPage,
                        'login_error_message' => \Twist::Session()->data('site-login_error_message'),
                        'login_message' => \Twist::Session()->data('site-login_message')
                    );

                    //Remove the login error
                    \Twist::Session()->remove('site-login_message');
                    \Twist::Session()->remove('site-login_error_message');

                    $strData = $this->resView->build( $strDefaultLogin, $arrTags );
                }
                break;

            case'account_verification':
                $strData = $this->resView->build( $this->strViewLocation.'account-verification.tpl', array( 'login_page' => $strLoginPage ) );
                break;

            case'forgotten_password_form':
                $strData = $this->resView->build( $this->strViewLocation.'forgotten-password.tpl', array( 'login_page' => $strLoginPage ) );
                break;

            case'change_password_form':

                $arrTags = array(
                    'login_page' => $strLoginPage,
                    'error_message' => \Twist::Session()->data('site-error_message')
                );
                \Twist::Session()->data('site-error_message',null);

                if(\Twist::Session()->data('user-temp_password') === '0' || is_null(\Twist::Session()->data('user-temp_password'))){
                    $strData = $this->resView->build( $this->strViewLocation.'change-password.tpl', $arrTags );
                }else{
                    $strData = $this->resView->build( $this->strViewLocation.'change-password-initial.tpl', $arrTags );
                }

                break;

            case'registration_form':

                $arrTags = array(
                    'login_page' => $strLoginPage,
                    'register_error_message' => \Twist::Session()->data('site-register_error_message'),
                    'register_message' => \Twist::Session()->data('site-register_message')
                );

                //Remove the registration error
                \Twist::Session()->remove('site-register_message');
                \Twist::Session()->remove('site-register_error_message');

                $strData = $this->resView->build((\Twist::framework()->setting('USER_REGISTER_PASSWORD')) ? $this->strViewLocation.'register-password.tpl' : $this->strViewLocation.'register.tpl', $arrTags, true );
                break;

            case'devices_form':

                $objUserSession = new SessionHandler();

                if(array_key_exists('save-device',$_GET) && array_key_exists('device-name',$_GET)){
                    $objUserSession->editDevice($this->currentID(),$_GET['save-device'],$_GET['device-name']);
                }

                if(array_key_exists('forget-device',$_GET)){
                    $objUserSession->forgetDevice($this->currentID(),$_GET['forget-device']);
                }

                $arrCurrentDevices = $objUserSession->getCurrentDevice($this->currentID());
                $arrDevices = $objUserSession->getDeviceList($this->currentID());

                $strDeviceList = '';
                foreach($arrDevices as $arrEachDevice){

                    $arrEachDevice['current'] = ($arrCurrentDevices['id'] === $arrEachDevice['id']) ? true : false;

                    if(array_key_exists('edit-device',$_GET) && $arrEachDevice['device'] === $_GET['edit-device']){
                        $strDeviceList .= $this->resView->build($this->strViewLocation.'device-each-edit.tpl',$arrEachDevice);
                    }else{
                        $strDeviceList .= $this->resView->build($this->strViewLocation.'device-each.tpl',$arrEachDevice);
                    }
                }

                $strData = $this->resView->build( $this->strViewLocation.'devices.tpl', array( 'login_page' => $strLoginPage, 'device_list' => $strDeviceList ),true );
                break;

            case'id':
                $strData = $this->currentID();
                break;

            case'logged_in':
                $strData = $this->loggedIn();
                break;


            case'level':
                $strData = $this->loggedInData('level');
                break;

            case'level_description':
                $intUsersLevel = $this->loggedInData('level');

                if($intUsersLevel === 0){
                    $strData = 'Root';
                }else{
                    $arrLevelData = $this->getLevel($intUsersLevel);
                    $strData = $arrLevelData['description'];
                }
                break;

            case'email':
                $strData = $this->loggedInData('email');
                break;

            case'name':
                $strData = sprintf('%s %s',$this->loggedInData('firstname'),$this->loggedInData('surname'));
                break;

            case'firstname':
                $strData = $this->loggedInData('firstname');
                break;

            case'surname':
                $strData = $this->loggedInData('surname');
                break;
        }

        return $strData;
    }
}