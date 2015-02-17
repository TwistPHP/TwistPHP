<?php
/**
 * This file is part of TwistPHP.
 *
 * TwistPHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TwistPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
 * @link       https://twistphp.com
 *
 */

namespace TwistPHP\Packages;
use TwistPHP\ModuleBase;

/**
 * User management and control allowing users to register, login and be updated
 * Functionality to edit, reset passwords, send welcome emails with session management for multi and single devices
 */
class User extends ModuleBase{

	public $strLoginUrl = null;
	public $strOverrideUrl = null;
	public $strLogoutPage = null;

	public $strEncryptionType = null;
	public $strPasswordHash = null;

	public $strTemplateLocation;
	public $resTemplate = null;

	protected $objUserSession = null;
	protected $resCurrentUser = null;

	protected $blUserValidatedSession = false;
	protected $intUserID = 0;

	public function __construct(){

		//Start the session handler as it is required
		\Twist::Session()->start();

		//Set the main user settings from the database
		$this->strLoginUrl = $this->framework()->setting('USER_DEFAULT_LOGIN_URI');
		$this->strLogoutPage = $this->framework()->setting('USER_DEFAULT_LOGOUT_URI');
		$this->strEncryptionType = $this->framework()->setting('USER_PASSWORD_ENCRYPTION');
		$this->strPasswordHash = $this->framework()->setting('USER_PASSWORD_HASH');

		$this->resTemplate = \Twist::Template('pkgUser');

		$strCustomTemplateLocation = $this->framework()->setting('USER_TEMPLATE_LOCATION');
		if(is_null($strCustomTemplateLocation) || $strCustomTemplateLocation == ''){
			$this->setCustomTemplateLocation(sprintf('%s/templates/User/',DIR_FRAMEWORK_PACKAGES));
		}else{
			$this->setCustomTemplateLocation($strCustomTemplateLocation);
		}

		require_once sprintf('%s/libraries/User/Session.lib.php',DIR_FRAMEWORK_PACKAGES);
		$this->objUserSession = new UserSession();

		//Set the remember me life span in seconds
		if($this->framework()->setting('USER_REMEMBER_LENGTH') > 0){
			$this->objUserSession->setSessionLife( (($this->framework()->setting('USER_REMEMBER_LENGTH') * 60) * 60) );
		}
	}

	/**
	 * By default don't update the session key every time the function is called
	 * @param bool $blUpdateKey
	 * @return bool
	 */
	public function loggedIn($blUpdateKey = false){

		if($this->blUserValidatedSession){

			if(!is_null($this->resCurrentUser)){
				$this->intUserID = $this->resCurrentUser->id();
			}

		}else{

			//Validate the session if available else validate the cookie if remembered
			if(!is_null(\Twist::Session()->data('user-session_key'))){
				$this->intUserID = $this->objUserSession->validateCode( \Twist::Session()->data('user-session_key'), $blUpdateKey );
			}elseif($this->objUserSession->remembered()){
				$this->intUserID = $this->objUserSession->validateCookie($blUpdateKey);
			}

			if($this->intUserID > 0){
				$this->processUserSession($this->intUserID);
				$this->blUserValidatedSession = true;
			}
		}

		return $this->intUserID > 0;
	}

	/**
	 * Return data about the logged in user
	 * @param null $strKey
	 * @return array|mixed
	 */
	public function loggedInData($strKey = null){

		if($this->blUserValidatedSession && !is_null($this->resCurrentUser)){
			return $this->resCurrentUser->get($strKey);
		} else {
			return null;
		}
	}

	/**
	 * @alias currentID
	 * @return null
	 */
	public function loggedInID(){
		return $this->currentID();
	}

	/**
	 * @alias currentID
	 * @return null
	 */
	public function loggedInLevel(){
		return $this->currentLevel();
	}

	public function authenticate($strEmailAddress = null,$strPassword = null,$strLoginUrl = null,$blIgnoreProcess = false){

		$this->strOverrideUrl = $strLoginUrl;

		//First of all log the user out if required
		if(array_key_exists('logout',$_GET)){
			$this->processLogout();
		}

		//Process any additional requests that may have been made
		if($blIgnoreProcess == false){
			$this->processRequests();
		}

		//Get the users current status
		if($this->loggedIn(true)){

			//Log the user out if the get param logout is set
			if(array_key_exists('logout',$_GET)){
				$this->processLogout();
			}else{
				//Redirect if necessary, in the else to protect from login into a logout request
				$this->afterLoginRedirect();
			}

		}else{
			if(is_null($strEmailAddress) && is_null($strPassword)){
				$strEmailAddress = (array_key_exists('email',$_POST) && !is_null($_POST['email'])) ? $_POST['email'] : null;
				$strPassword = (array_key_exists('password',$_POST) && !is_null($_POST['password'])) ? $_POST['password'] : null;
			}

			//If the user is still not valid then check email and password
			if(!is_null($strEmailAddress) && !is_null($strPassword)){

				$objDB = \Twist::Database();

				$strSQL = sprintf("SELECT `id`,`password`
									FROM `%s`.`%susers`
									WHERE `email` = '%s'
									LIMIT 1",
					DATABASE_NAME,
					DATABASE_TABLE_PREFIX,
					$objDB->escapeString($strEmailAddress)
				);

				if($objDB->query($strSQL) && $objDB->getNumberRows()){
					$arrUserData = $objDB->getArray();

					if($arrUserData['password'] == sha1($strPassword)){
						$intUserID = $arrUserData['id'];

						//Create the session key or session cookie
						if(array_key_exists('remember',$_POST) && $_POST['remember'] == '1'){
							$strSessionKey = $this->objUserSession->createCookie($intUserID);
						}else{
							$strSessionKey = $this->objUserSession->createCode($intUserID);
						}

						//Blank out the post params
						if(count($_POST) && array_key_exists('password',$_POST)){
							unset($_POST['password']);
						}

						if(count($_POST) && array_key_exists('email',$_POST)){
							unset($_POST['email']);
						}

						$this->processUserLoginSession($intUserID,$strSessionKey);
					}else{
						\Twist::Session()->data('site-login_error_message','Invalid login credentials, please try again.');
					}
				}else{
					\Twist::Session()->data('site-login_error_message','Invalid login credentials, please try again.');
				}
			}
		}
	}

	protected function processUserLoginSession($intUserID,$strSessionKey){

		$arrUserData = $this->processUserSession($intUserID);

		$objSession = \Twist::Session();

		$objSession->data('user-session_key',$strSessionKey);
		$objSession->data('user-logged_in',\Twist::DateTime()->time());

		$this->resCurrentUser->lastLogin($_SERVER['REMOTE_ADDR']);
		$this->resCurrentUser->commit();

		$this->intUserID = $intUserID;

		if($this->framework()->setting('USER_PASSWORD_CHANGE') == true){

			if($arrUserData['temp_password'] == '1'){
				$objSession->data('user-temp_password','1');
				$objSession->remove('site-login_redirect');

				$strLoginURLFull = sprintf('%s?change',(is_null( $this->strOverrideUrl ) ? $this->strLoginUrl : $this->strOverrideUrl));
				$this->goToPage( $strLoginURLFull, false );
			}

		}elseif($arrUserData['temp_password'] == '1'){
			$this->resCurrentUser->tempPassword(0);
		}

		//Destroy the var
		unset($objSession);

		$this->afterLoginRedirect();
	}

	protected function processUserSession($intUserID){

		$objSession = \Twist::Session();
		$arrUserData = $this->getData($intUserID);

		//Check that the account is enabled
		if($arrUserData['enabled'] == '1'){

			//Check that the account is verified if required
			if($this->framework()->setting('USER_EMAIL_VERIFICATION') == false || ($this->framework()->setting('USER_EMAIL_VERIFICATION') && $arrUserData['verified'] == '1')){

				$objSession->data('user-id',$arrUserData['id']);
				$objSession->data('user-level',$arrUserData['level']);
				$objSession->data('user-email',$arrUserData['email']);;
				$objSession->data('user-name',sprintf('%s %s',$arrUserData['firstname'],$arrUserData['surname']));
				$objSession->data('user-firstname',$arrUserData['firstname']);
				$objSession->data('user-surname',$arrUserData['surname']);
				$objSession->data('user-temp_password','0');

				$this->loadCurrentUser();

			}else{
				//If the user is not verified and password is correct show verification message
				$objSession->data('user-email',$arrUserData['email']);

				$strLoginURLFull = sprintf('%s?verification',(is_null( $this->strOverrideUrl ) ? $this->strLoginUrl : $this->strOverrideUrl));
				$this->goToPage( $strLoginURLFull, false );
			}
		}else{
			$objSession->remove();
			$objSession->data('site-login_error_message','Your account has been disabled.');
		}

		//Destroy the var
		unset($objSession);

		return $arrUserData;
	}

	protected function processRequests(){

		//Process the forgotten password request
		if(array_key_exists('forgotten_email',$_POST) && $_POST['forgotten_email'] != ''){
			$arrUserData = $this->getByEmail($_POST['forgotten_email']);

			//Now if the email exists send out the reset password email.
			if(is_array($arrUserData) && count($arrUserData) > 0){

				$resUser = $this->get($arrUserData['id']);
				$resUser->resetPassword();
				$resUser->commit();

				\Twist::Session()->data('site-login_message','A temporary password has been emailed to you.');
			}
		}

		//Process the register user request
		if(array_key_exists('register',$_POST) && $_POST['register'] != ''){

			$resUser = $this->create();
			$resUser->email($_POST['email']);
			$resUser->firstname($_POST['firstname']);
			$resUser->surname($_POST['lastname']);
			$resUser->level(10);
			$resUser->resetPassword();
			$intUserID = $resUser->commit();

			if($intUserID > 0){
				\Twist::Session()->data('site-login_message','Thank you for your registration, your password has been emailed to you');
			}else{
				\Twist::Session()->data('site-login_error_message','Failed to register user');
			}
		}

		//Resend a new verification code
		if(array_key_exists('verification_email',$_POST) && $_POST['verification_email'] != ''){
			$arrUserData = $this->getByEmail($_POST['verification_email']);

			//Now if the email exists send out the reset password email.
			if(is_array($arrUserData) && count($arrUserData) > 0){

				$resUser = $this->get($arrUserData['id']);
				$resUser->requireVerification();
				$resUser->commit();
			}
		}

		if(array_key_exists('password',$_POST) && array_key_exists('confirm_password',$_POST)){

			if($this->loggedIn()){

				if($_POST['password'] == $_POST['confirm_password']){

					if(\Twist::Session()->data('user-temp_password') == '0'){

						if(array_key_exists('current_password',$_POST)){

							$strNewPassword = $_POST['password'];

							//Change the users password and re-log them in (Only for none-temp password users)
							$this->changePassword(\Twist::Session()->data('user-id'),$strNewPassword,$_POST['current_password']);

							//Remove the two posted password vars
							unset($_POST['password']);
							unset($_POST['current_password']);

							$this->authenticate(\Twist::Session()->data('user-email'),$strNewPassword,$this->strLoginUrl,true);
						}
					}else{

						$strNewPassword = $_POST['password'];

						//Change the users password and re-log them in
						$this->updatePassword(\Twist::Session()->data('user-id'),$strNewPassword);

						//Remove the posted password and reset the session var
						unset($_POST['password']);
						\Twist::Session()->data('user-temp_password','0');

						$this->authenticate(\Twist::Session()->data('user-email'),$strNewPassword,$this->strLoginUrl,true);
					}

				}else{
					\Twist::Session()->data('site-error_message','The passwords you entered do not match');
					$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
				}
			}
		}

		if(array_key_exists('verify',$_GET) && array_key_exists('verify',$_GET) && $_GET['verify'] != ''){
			$this->verifyEmail($_GET['verify']);
		}
	}

	/**
	 * Restrict access to the PHP file this function was called form, if the user is not logged in they
	 * will be redirected to the login page.
	 * @param null $strLoginUrl
	 * @param boolean $blAuthenticate
	 * @return bool|void
	 */
	public function restrict( $strLoginUrl = null,$blAuthenticate = false ){

		//Authentication has been requested
		if($blAuthenticate){
			\Twist::Session()->start();
			$this->authenticate(null,null,$strLoginUrl);
		}

		//See if the user is able to view the restricted page
		//If User is not logged in direct ot the login page
		$blOut = ($this->loggedIn()) ? true : $this->goToPage( (is_null( $strLoginUrl ) ? $this->strLoginUrl : $strLoginUrl), true );

		return $blOut;
	}

	/**
	 * Access log, this is option bit of system to log selected activities.
	 * Mainly of use if you are running a secure system where the users activities
	 * need to be monitored.
	 * @param null $intUserID
	 * @param $intType
	 * @param $strIPAddress
	 */
	public function accessLog($intUserID = null,$intType,$strIPAddress){

		$strSQL = sprintf("INSERT INTO `%s`.`%saccess_logs`
										SET `user_id` = %s,
											`type_id` = %d,
											`ip` = '%s',
											`logged` = NOW()",
			DATABASE_NAME,
			DATABASE_TABLE_PREFIX,
			(is_null($intUserID)) ? 'NULL' : \Twist::Database()->escapeString($intUserID),
			\Twist::Database()->escapeString($intType),
			\Twist::Database()->escapeString($strIPAddress)
		);

		\Twist::Database()->query($strSQL);
	}

	/**
	 * Log the user out only if the logout get param has been set
	 */
	public function logout(){

		//First of all log the user out if required
		if(array_key_exists('logout',$_GET)){
			$this->processLogout();
		}
	}

	/**
	 * Process the users logout request and remove any session data to.
	 * @param string $strPage
	 */
	public function processLogout($strPage = ''){

		$this->objUserSession->forget();
		\Twist::Session()->remove();

		//Null the logout message
		\Twist::Session()->data('site-login_error_message',null);
		\Twist::Session()->data('site-login_message',null);

		$this->blUserValidatedSession = false;
		$this->resCurrentUser = null;

		if(!is_null($strPage)){
			$this->goToPage($strPage);
		}
	}

	/**
	 * Get the current users User ID
	 * @return null
	 */
	public function currentID(){
		return \Twist::Session()->data('user-id');
	}

	/**
	 * Get the current users User Level
	 * @return null
	 */
	public function currentLevel(){
		return \Twist::Session()->data('user-level');
	}

	protected function loadCurrentUser(){
		$intUserID = $this->currentID();
		$this->resCurrentUser = (!is_null($intUserID) && $intUserID > 0) ? $this->get($intUserID) : null;
	}

	/**
	 * Get the pre-built, unbranded HTML registration form and return it as a string.
	 */
	public function getRegistrationForm($strLoginPage = ''){
		return $this->templateExtension(($strLoginPage == '') ? 'registration_form' : sprintf('%s,%s','registration_form',$strLoginPage));
	}

	/**
	 * Get the pre-built, unbranded HTML login form and return it as a string.
	 * @param string $strLoginPage
	 * @return mixed
	 */
	public function getLoginForm($strLoginPage = ''){
		return $this->templateExtension(($strLoginPage == '') ? 'login_form' : sprintf('%s,%s','login_form',$strLoginPage));
	}

	/**
	 * Get the pre-built, unbranded HTML forgotten password form and return it as a string.
	 * @param string $strLoginPage
	 * @return mixed
	 */
	public function getForgottenPasswordForm($strLoginPage = ''){
		return $this->templateExtension(($strLoginPage == '') ? 'forgotten_password_form' : sprintf('%s,%s','forgotten_password_form',$strLoginPage));
	}

	/**
	 * Get the pre-built, unbranded HTML change password form and return it as a string.
	 * @param string $strLoginPage
	 * @return mixed
	 */
	public function getChangePasswordForm($strLoginPage = ''){
		return $this->templateExtension(($strLoginPage == '') ? 'change_password_form' : sprintf('%s,%s','change_password_form',$strLoginPage));
	}

	protected function afterLoginRedirect(){

		$objSession = \Twist::Session();

		//Get the URL and then unset so only happens once
		$strUrl = $this->getAfterLoginRedirect();

		//If the login redirect is set then proceed to redirect
		if(!is_null($strUrl)){

			$this->clearAfterLoginRedirect();

			//Just in case, remove the logout comment otherwise the redirect could log you out again
			$strUrl = str_replace(array("?logout=1","?logout"),"",$strUrl);

			if($strUrl != $_SERVER['request_uri']
					&& !in_array(substr($strUrl, -3), array('.js'))
					&& !in_array(substr($strUrl, -4), array('.css','.jpg','.png','.gif','.ico'))){
				$this->goToPage($strUrl);
			} else {
				$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
			}
		}elseif($objSession->data('user-temp_password') == '1' && !strstr($_SERVER['REQUEST_URI'],'?change')){
			$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
		}
	}

	public function setAfterLoginRedirect($strRedirectURL = null){
		$strRedirectURL = (is_null($strRedirectURL)) ? $_SERVER['REQUEST_URI'] : str_replace('//','/',$strRedirectURL);
		return \Twist::Session()->data('site-login_redirect',$strRedirectURL);
	}

	public function getAfterLoginRedirect(){
		return \Twist::Session()->data('site-login_redirect');
	}

	public function clearAfterLoginRedirect(){
		\Twist::Session()->remove('site-login_redirect');
	}

	/**
	 * Redirect the user to a relevant page when required
	 * @param $strPageURI
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

	public function loginURL($strURL = null){
		return (is_null($strURL)) ? $this->strLoginUrl : $this->strLoginUrl = $strURL;
	}


	/**** NEW CLASS BEGINS HERE *****/

	/**
	 * Get the user as an object
	 * @param $intUserID
	 * @return UserObject
	 */
	public function get($intUserID){
		require_once sprintf('%s/libraries/User/Object.lib.php',dirname(__FILE__));
		return new UserObject(\Twist::Database()->getRecord(sprintf('%susers',DATABASE_TABLE_PREFIX),$intUserID),$this);
	}

	public function create(){
		require_once sprintf('%s/libraries/User/Object.lib.php',dirname(__FILE__));
		return new UserObject(\Twist::Database()->createRecord(sprintf('%susers',DATABASE_TABLE_PREFIX)),$this);
	}

	/**
	 * Get an array of the users default information by User ID
	 * @param $intUserID
	 * @return array
	 */
	public function getData($intUserID){
		return \Twist::Database()->get(sprintf('%susers',DATABASE_TABLE_PREFIX),$intUserID);
	}

	/**
	 * Get and array of the users default information by User Email
	 * @param $strEmail
	 * @return array
	 */
	public function getByEmail($strEmail){
		return \Twist::Database()->get(sprintf('%susers',DATABASE_TABLE_PREFIX),$strEmail,'email');
	}

	/**
	 * Get user full details by User ID
	 * @param $intUserID
	 * @return array
	 */
	public function getDetailsByID($intUserID){
		return \Twist::Database()->get(sprintf('%suser_data',DATABASE_TABLE_PREFIX),$intUserID,'user_id');
	}

	/**
	 * Get and array of the users default information by User ID
	 * @param $intUserID
	 * @return array
	 */
	public function getAll($strOrderBy = 'id'){
		return \Twist::Database()->getAll(sprintf('%susers',DATABASE_TABLE_PREFIX),$strOrderBy);
	}

	/**
	 * Get and array of the users default information by User ID
	 * @param $intUserID
	 * @return array
	 */
	public function getAllByLevel($intLevelID){
		return \Twist::Database()->find(sprintf('%susers',DATABASE_TABLE_PREFIX),$intLevelID,'level');
	}

	/**
	 * Get information about any given user level ID
	 * @param $intLevelID
	 * @return array
	 */
	public function getLevel($intLevelID){
		return \Twist::Database()->get(sprintf('%suser_levels',DATABASE_TABLE_PREFIX),$intLevelID);
	}

	/**
	 * Get all the levels in the system
	 * @return int
	 */
	public function getLevels(){
		return \Twist::Database()->getAll(sprintf('%suser_levels',DATABASE_TABLE_PREFIX));
	}

	public function verifyEmail($strVerificationCode){

		$blOut = false;

		if($strVerificationCode != ''){

			$strVerifyData = $this->base64url_decode($strVerificationCode);
			$arrParts = explode('|',$strVerifyData);

			//Check that the email address is semi valid and code is long enough
			if(strstr($arrParts[0],'@') && strstr($arrParts[0],'.') && strlen($arrParts[1]) == 16){

				$strSQL = sprintf("UPDATE `%s`.`%susers`
												SET `verified` = '1',
													`verification_code` = ''
												WHERE `email` = '%s'
												AND `verification_code` = '%s'
												LIMIT 1",
					DATABASE_NAME,
					DATABASE_TABLE_PREFIX,
					\Twist::Database()->escapeString($arrParts[0]),
					\Twist::Database()->escapeString($arrParts[1])
				);

				if(\Twist::Database()->query($strSQL) && \Twist::Database()->getAffectedRows()){
					$blOut = true;
					\Twist::Session()->data('site-login_message','Your account has been verified.');
				}
			}
		}

		return $blOut;
	}

	protected function base64url_encode($strData) {
		$strBase64 = base64_encode($strData);
		$strBase64URL = strtr($strBase64, '+/=', '-_$');
		return $strBase64URL;
	}

	protected function base64url_decode($strBase64URL) {
		$strBase64 = strtr($strBase64URL, '-_$', '+/=');
		$strData = base64_decode($strBase64);
		return $strData;
	}

	/**
	 * Update the users password to a new password. THis is the non secure method for when you don't know the users original password.
	 * Default use would be for a forgotten password system etc.
	 * @param $intUserID
	 * @param $strNewPassword
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
			$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
		}

		return $blPasswordChanged;
	}

	/**
	 * Change password, this used when you have both the users old and new password, very useful to ensure the user who is changing
	 * the password is the valid account holder.
	 * @param $intUserID
	 * @param $strNewPassword
	 * @param $strCurrentPassword
	 * @return bool
	 */
	public function changePassword($intUserID,$strNewPassword,$strCurrentPassword,$blRedirectOnFail=true){

		$blPasswordChanged = false;
		$resUser = $this->get($intUserID);

		if($strNewPassword == $strCurrentPassword){
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
			$this->goToPage( sprintf('%s?change',$this->strLoginUrl), false );
		}

		return $blPasswordChanged;
	}

	public function setCustomTemplateLocation($strTemplateLocation){

		if(!file_exists($strTemplateLocation)){
			$strTemplateLocation = trim($strTemplateLocation,'/').'/';
			$strTemplateLocation = sprintf('%s/%s',BASE_LOCATION,$strTemplateLocation);
		} else {
			$strTemplateLocation = rtrim($strTemplateLocation,'/').'/';
		}

		$this->strTemplateLocation = $strTemplateLocation;
		$this->resTemplate->setTemplatesDirectory($strTemplateLocation);
	}

	public function templateExtension($strReference){

		$strData = '';
		$arrParts = explode(',',$strReference);

		$strParameter = '';
		$strLoginPage = $this->strLoginUrl;

		if(count($arrParts) > 1){
			$strReference = $arrParts[0];
			$strLoginPage = $arrParts[1];
		}

		//If the user is on a temp password show the change password form
		if(\Twist::Session()->data('user-temp_password') == '1' && $strReference == 'login_form'){
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

		$strDefaultLogin = 'login.tpl';

		switch($strReference){

			case'login_register_form':
				$strDefaultLogin = 'login-register.tpl';
			case'login_form':

				if($this->loggedIn()){
					\Twist::redirect(($strLoginPage == $_SERVER['REQUEST_URI']) ? './' : $strLoginPage);
				}else{
					$this->processRequests();

					$arrTags = array(
						'login_page' => $strLoginPage,
						'login_error_message' => \Twist::Session()->data('site-login_error_message'),
						'login_message' => \Twist::Session()->data('site-login_message'),
					);

					//Remove the login error
					\Twist::Session()->data('site-login_message',null);
					\Twist::Session()->data('site-login_error_message',null);

					$strData = $this->resTemplate->build( $strDefaultLogin, $arrTags );
				}
				break;

			case'account_verification':
				$strData = $this->resTemplate->build( 'account-verification.tpl', array( 'login_page' => $strLoginPage ) );
				break;

			case'forgotten_password_form':
				$strData = $this->resTemplate->build( 'forgotten-password.tpl', array( 'login_page' => $strLoginPage ) );
				break;

			case'change_password_form':

				$arrTags = array(
					'login_page' => $strLoginPage,
					'error_message' => \Twist::Session()->data('site-error_message')
				);
				\Twist::Session()->data('site-error_message',null);

				if(\Twist::Session()->data('user-temp_password') == '0'){
					$strData = $this->resTemplate->build( 'change-password.tpl', $arrTags );
				}else{
					$strData = $this->resTemplate->build( 'change-password-initial.tpl', $arrTags );
				}

				break;

			case'registration_form':
				$strData = $this->resTemplate->build( 'register.tpl', array( 'login_page' => $strLoginPage ),true );
				break;

			case'devices_form':

				$arrDevices = $this->objUserSession->getDeviceList($this->currentID());
				$strDeviceList = '';
				foreach($arrDevices as $arrEachDevice){
					$strDeviceList .= sprintf('<dt>%s</dt><dd>%s</dd><dd><a href="">forget</a></dd>',
						($arrEachDevice['device_name'] == '') ? 'Untitled' : $arrEachDevice['device_name'],
						\Twist::DateTime()->prettyAge($arrEachDevice['last_login'])
					);
				}

				$strData = $this->resTemplate->build( 'devices.tpl', array( 'login_page' => $strLoginPage, 'device_list' => $strDeviceList ),true );
				break;

			case'id':
				$strData = $this->currentID();
				break;

			case'level':
				$strData = $this->loggedInData('level');
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