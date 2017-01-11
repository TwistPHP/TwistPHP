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

namespace Twist\Core\Models\User;
use Twist\Core\Models\Database\Record;

class User{

	protected $resDatabaseRecord = null;

	protected $arrOriginalData = array();
	protected $arrOriginalUserData = array();
	protected $arrUserData = array();
	protected $arrUserDataFields = array();

	protected $arrCustomData = array();
	protected $resParentClass = null;
	protected $blNewAccount = false;
	protected $blOverrideSendPasswordEmail = false;

	private $strTempPassword = null;

	public function __construct(Record $resDatabaseRecord,$resParentClass){
		$this->resParentClass = $resParentClass;
		$this->resDatabaseRecord = $resDatabaseRecord;
		$this->arrOriginalData = $this->resDatabaseRecord->values();

		$intUserID = ($this->resDatabaseRecord->get('id') == 0) ? null : $this->resDatabaseRecord->get('id');

		//Get the user data record to allow for this ti be edited
		$this->blNewAccount = is_null($intUserID);

		//Get the array of user fields
		$this->arrUserDataFields = \Twist::framework()->tools()->arrayReindex(\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'user_data_fields')->find(),'slug');

		//Check the user id before trying to collect userdata
		if(!is_null($intUserID) && $intUserID > 0) {

			$resResult = \Twist::Database()->query("SELECT `ud`.`data`,`udf`.`slug` FROM `%suser_data` AS `ud` JOIN `%suser_data_fields` AS `udf` ON `ud`.`field_id` = `udf`.`id` WHERE `ud`.`user_id` = %d",
				TWIST_DATABASE_TABLE_PREFIX,
				TWIST_DATABASE_TABLE_PREFIX,
				$intUserID
			);

			if ($resResult->status() && $resResult->numberRows()) {
				foreach ($resResult->rows() as $arrEachItem) {
					$this->arrUserData[$arrEachItem['slug']] = $this->arrOriginalUserData[$arrEachItem['slug']] = $arrEachItem['data'];
				}
			}
		}
	}

	/**
	 * Get one or all of the values from the users main account record
	 * @param null $strField Pass in a field name for the associated value, null returns an array
	 * @return array|null Associated user value or and array of all values
	 */
	public function get($strField = null){
		return (is_null($strField)) ? $this->resDatabaseRecord->values() : $this->resDatabaseRecord->get($strField);
	}

	/**
	 * Get one or all of the values from the users data records, by passing in a value you can also set the value
	 * @param null $strField Pass in a field name for the associated value, null returns an array
	 * @param null $mxdValue Value to be set against the field that has been passed in
	 * @return array|mixed|null
	 */
	public function data($strField = null,$mxdValue = null){
		if(is_null($mxdValue)){
			return $this -> getData($strField);
		} else {
			return $this -> setData($strField,$mxdValue);
		}
	}

	private function getData($strField = null){
		return (is_null($strField)) ? $this->arrUserData : (array_key_exists($strField,$this->arrUserData) ? $this->arrUserData[$strField] : null);
	}

	private function setData($strField,$mxdValue){
		$this->arrUserData[$strField] = $mxdValue;
		return $mxdValue;
	}

	public function nullData($strField = null){
		$this->arrUserData[$strField] = null;
	}

	public function deleteData($strField = null){
		$this->nullData($strField);
	}

	/**
	 * Commit all changes and updates made to the users account and data, these changes will be processed and stored to the database. Emails may be sent out suh as welcome or password update emails if enabled.
	 * @return bool|int
	 * @throws \Exception
	 */
	public function commit(){

		$blSendVerification = ($this->resDatabaseRecord->get('email') != $this->arrOriginalData['email'] || $this->resDatabaseRecord->get('verification_code') != $this->arrOriginalData['verification_code']);
		$blSendPassword = (\Twist::framework()->setting('USER_PASSWORD_CHANGE_EMAIL') && $this->resDatabaseRecord->get('password') != $this->arrOriginalData['password']) || $this->blOverrideSendPasswordEmail;

		if(is_null($this->resDatabaseRecord->get('password'))){
			$this->resetPassword();
			$blSendPassword = true;
		}

		//Commit and grab the standard user data
		$mxdOut = $this->resDatabaseRecord->commit();

		//Only store user data if something has changed
		if(json_encode($this->arrUserData) !== json_encode($this->arrOriginalUserData)){
			foreach($this->arrUserData as $strKey => $mxdData){

				if(!array_key_exists($strKey,$this->arrUserDataFields)){

					//The field is a new filed, insert into the database
					$resUserDataField = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'user_data_fields')->create();
					$resUserDataField->set('slug',$strKey);
					$resUserDataField->commit();
					$this->arrUserDataFields[$strKey] = $resUserDataField->values();
				}

				if(is_null($mxdData)){

					//If the item is null it can be removed
					\Twist::Database()->query("DELETE FROM `%suser_data` WHERE `user_id` = %d AND `field_id` = %d LIMIT 1",
						TWIST_DATABASE_TABLE_PREFIX,
						$this->resDatabaseRecord->get('id'),
						$this->arrUserDataFields[$strKey]['id']
					);

					unset($this->arrUserData[$strKey]);

				}elseif(array_key_exists($strKey,$this->arrOriginalUserData) && $mxdData !== $this->arrOriginalUserData[$strKey]){

					//If the key was in the original array and the value is different from the original it can be removed
					\Twist::Database()->query( "UPDATE `%suser_data` SET `data` = '%s' WHERE `user_id` = %d AND `field_id` = %d LIMIT 1",
						TWIST_DATABASE_TABLE_PREFIX,
						$mxdData,
						$this->resDatabaseRecord->get( 'id' ),
						$this->arrUserDataFields[$strKey]['id']
					);

				}elseif(!array_key_exists($strKey,$this->arrOriginalUserData)){

					//If the key is not in the original array we need to insert the value
					$resUserData = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'user_data')->create();
					$resUserData->set('user_id',$this->resDatabaseRecord->get('id'));
					$resUserData->set('field_id',$this->arrUserDataFields[$strKey]['id']);
					$resUserData->set('data',$mxdData);
					$resUserData->commit();
				}
			}

			//Reset original data to be the same as user data, ready to continue
			$this->arrOriginalUserData = $this->arrUserData;
		}

		if($mxdOut){

			$this->arrOriginalData = $this->resDatabaseRecord->values();

			if($this->blNewAccount){
				$this->resDatabaseRecord->set('joined',\Twist::DateTime()->date('Y-m-d H:i:s'));
				$this->resDatabaseRecord->commit();
				$this->sendWelcomeEmail();
				$this->blNewAccount = false;
			}else{

				if($blSendVerification){
					$this->sendVerificationEmail();
				}

				if($blSendPassword){
					$this->blOverrideSendPasswordEmail = false;
					$this->sendPasswordEmail();
				}
			}

			//Just to ensure the temp password is defiantly removed
			$this->strTempPassword = null;
		}

		return $mxdOut;
	}

	/**
	 * Get/Returns the ID of the user account
	 * @return null User ID i.e 6
	 */
	public function id(){
		return $this->resDatabaseRecord->get('id');
	}

	/**
	 * Get/Returns the users full name (Firstname Surname) from the users account
	 * @return string full name (Firstname Surname)
	 */
	public function name(){
		return trim(sprintf("%s %s",$this->firstname(),$this->surname()));
	}

	/**
	 * Get/Returns the users firstname from the users account, passing in a value will set the users firstname
	 * @param null|string $strValue Value to be set, leave null to return only
	 * @return bool|null Firstname i.e Joe
	 * @throws \Exception
	 */
	public function firstname($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecord->get('firstname') : $this->resDatabaseRecord->set('firstname',$strValue);
	}

	/**
	 * Get/Returns the users surname from the users account, passing in a value will set the users surname
	 * @param null|string $strValue Value to be set, leave null to return only
	 * @return bool|null Surname i.e Bloggs
	 * @throws \Exception
	 */
	public function surname($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecord->get('surname') : $this->resDatabaseRecord->set('surname',$strValue);
	}

	/**
	 * Get/Returns the users email address from the users account, passing in a value will set the users email address
	 * @param null $strValue Value to be set, leave null to return only
	 * @return bool|null Email i.e joe.bloggs@twistphp.com
	 * @throws \Exception
	 */
	public function email($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecord->get('email') : $this->resDatabaseRecord->set('email',$strValue);
	}

	/**
	 * Get/Returns the users level ID from the users account, passing in a value will set the users level
	 * @param null $intLevel Value to be set, leave null to return only
	 * @return bool|null Users Level ID i.e 10
	 * @throws \Exception
	 */
	public function level($intLevel = null){
		return (is_null($intLevel)) ? $this->resDatabaseRecord->get('level') : $this->resDatabaseRecord->set('level',$intLevel);
	}

	public function enabled(){
		return (bool) $this->resDatabaseRecord->get('enabled');
	}

	public function enable(){
		return $this->resDatabaseRecord->set('enabled','1');
	}

	public function disable(){
		return $this->resDatabaseRecord->set('enabled','0');
	}

	public function lastLogin($strUserIP = null){
		$this->resDatabaseRecord->set('last_login',\Twist::DateTime()->date('Y-m-d H:i:s'));
		return $this->resDatabaseRecord->set('last_login_ip',($strUserIP == null) ? $_SERVER['REMOTE_ADDR'] : $strUserIP);
	}

	public function lastActive(){
		return $this->resDatabaseRecord->set('last_active',\Twist::DateTime()->date('Y-m-d H:i:s'));
	}

	public function delete(){

		\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'user_data')->delete($this->resDatabaseRecord->get('id'),'user_id',null);

		//TODO: remove sessions and devices

		return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'users')->delete($this->resDatabaseRecord->get('id'),'id');
	}

	public function requireVerification(){

		$strVerificationCode = null;

		//Send out the email verification
		if(\Twist::framework()->setting('USER_EMAIL_VERIFICATION')){

			//Generate a verification code
			$strVerificationCode = \Twist::framework()->Tools()->randomString(16);

			$this->resDatabaseRecord->set('verified','0');
			$this->resDatabaseRecord->set('verification_code',$strVerificationCode);
		}

		return $strVerificationCode;
	}

	public function tempPassword($blEnable = null){
		return (is_null($blEnable)) ? (bool) $this->resDatabaseRecord->get('temp_password') : $this->resDatabaseRecord->set('temp_password',($blEnable == '1' || $blEnable) ? '1' : '0');
	}

	public function comparePasswordHash($strPasswordHash){
		return $this->resDatabaseRecord->get('password') == $strPasswordHash;
	}

	public function password($strPassword){

		$arrAllowPassword = $this->allowPassword($strPassword);

		if($arrAllowPassword['status']){
			$this->resDatabaseRecord->set('password',sha1($strPassword));
			$this->resDatabaseRecord->set('temp_password','0');
		}

		return $arrAllowPassword;
	}

	/**
	 * When the user has forgotten their password this function will easily allow a new password to be generated and and email with the users new temp password to be sent to their address.
	 * @param bool $blOverrideSendEmail
	 * @return string
	 * @throws \Exception
	 */
	public function resetPassword($blOverrideSendEmail = false){

		//Generate a new random password and send email
		$strPassword = \Twist::framework()->Tools()->randomString(16);

		//Store the new temp password until the reset email is sent upon commit
		$this->strTempPassword = $strPassword;
		$this->resDatabaseRecord->set('password',sha1($strPassword));
		$this->resDatabaseRecord->set('temp_password',1);

		//Set this var so that when a functionality like forgotten password is used as password email will be sent regardless of the USER_PASSWORD_CHANGE_EMAIL setting.
		$this->blOverrideSendPasswordEmail = $blOverrideSendEmail;

		return $strPassword;
	}

	protected function sendPasswordEmail(){

		$arrTags = array();
		$strSiteName = \Twist::framework()->setting('SITE_NAME');
		$strSiteHost = \Twist::framework()->setting('SITE_HOST');
		$strLoginURL = $this->resParentClass->strLoginUrl;

		$strEmailSubject = (is_null($this->strTempPassword)) ? sprintf('%s: Password Updated',$strSiteName) : sprintf('%s: Password Reset',$strSiteName);

		$resEmail = \Twist::Email()->create();
		$resEmail->setSubject($strEmailSubject);
		$resEmail->setFrom(sprintf('no-reply@%s',str_replace('www.','',$strSiteHost)));
		$resEmail->setReplyTo(sprintf('no-reply@%s',str_replace('www.','',$strSiteHost)));
		$resEmail->addTo($this->arrOriginalData['email']);

		$arrTags['subject'] = $strEmailSubject;
		$arrTags['firstname'] = $this->arrOriginalData['firstname'];
		$arrTags['surname'] = $this->arrOriginalData['surname'];
		$arrTags['email'] = $this->arrOriginalData['email'];
		$arrTags['url'] = sprintf('http://%s/%s',$strSiteHost,ltrim($strLoginURL,'/'));
		$arrTags['host'] = $strSiteHost;
		$arrTags['password'] = $this->strTempPassword;
		$arrTags['site_name'] = $strSiteName;

		$strTemplate = (is_null($this->strTempPassword)) ? 'change-password-email.tpl' : 'forgotten-password-email.tpl';

		$strData = \Twist::View()->build(sprintf('%suser/%s',TWIST_FRAMEWORK_VIEWS,$strTemplate),$arrTags);

		//Reset the temp password holder
		$this->strTempPassword = null;

		$resEmail->setBodyHTML($strData);
		$resEmail->send();
	}

	protected function sendWelcomeEmail(){

		$strLoginURL = $this->resParentClass->strLoginUrl;

		$strTempPass = (is_null($this->strTempPassword)) ? '[specified on registration]' : $this->strTempPassword;

		$strSiteName = \Twist::framework()->setting('SITE_NAME');
		$strSiteHost = \Twist::framework()->setting('SITE_HOST');

		$resEmail = \Twist::Email()->create();

		$strEmailSubject = sprintf('Welcome to %s',$strSiteName);

		$resEmail->setSubject($strEmailSubject);
		$resEmail->setFrom(sprintf('no-reply@%s',str_replace('www.','',$strSiteHost)));
		$resEmail->setReplyTo(sprintf('no-reply@%s',str_replace('www.','',$strSiteHost)));
		$resEmail->addTo($this->arrOriginalData['email']);

		$arrTags = array();
		$arrTags['subject'] = $strEmailSubject;
		$arrTags['firstname'] = $this->arrOriginalData['firstname'];
		$arrTags['surname'] = $this->arrOriginalData['surname'];
		$arrTags['email'] = $this->arrOriginalData['email'];
		$arrTags['url'] = sprintf('http://%s/%s',$strSiteHost,ltrim($strLoginURL,'/'));
		$arrTags['host'] = $strSiteHost;
		$arrTags['password'] = $strTempPass;
		$arrTags['site_name'] = $strSiteName;

		$arrTags['verification'] = '';

		if(\Twist::framework()->setting('USER_EMAIL_VERIFICATION')){

			if($this->resDatabaseRecord->get('verification_code') == ''){
				$strVerificationCode = $this->requireVerification();
				$this->resDatabaseRecord->commit();
			}else{
				$strVerificationCode = $this->resDatabaseRecord->get('verification_code');
			}

			$strVerificationString = $this->base64url_encode(sprintf("%s|%s",$this->arrOriginalData['email'],$strVerificationCode));
			$strVerificationLink = sprintf('http://%s/%s?verify=%s',$strSiteHost,ltrim($strLoginURL,'/'),$strVerificationString);
			$arrTags['verification_link'] = $strVerificationLink;
			$arrTags['verification_code'] = $strVerificationCode;
			$arrTags['verification_string'] = $strVerificationString;

			$arrTags['verification'] = sprintf('<p><strong>Your account must be verified before you can login.</strong><br />To verify your account, <a href="%s">click here</a>.</p><p>If you have a problem with this link, please copy and paste the below link into your browser and proceed to login:<br /><a href="%s">%s</a></p>',
				$strVerificationLink,
				$strVerificationLink,
				$strVerificationLink
			);
		}

		$strHTML = \Twist::View()->build(sprintf('%suser/welcome-email.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags);

		//Reset the temp password holder
		$this->strTempPassword = null;

		$resEmail->setBodyHTML($strHTML);
		$resEmail->send();
	}

	protected function sendVerificationEmail(){

		$blOut = false;

		//Send out the email verification
		if(\Twist::framework()->setting('USER_EMAIL_VERIFICATION')){

			$strLoginURL = $this->resParentClass->strLoginUrl;
			$strSiteName = \Twist::framework()->setting('SITE_NAME');
			$strSiteHost = \Twist::framework()->setting('SITE_HOST');

			if($this->resDatabaseRecord->get('verification_code') == ''){
				$strVerificationCode = $this->requireVerification();
				$this->resDatabaseRecord->commit();
			}else{
				$strVerificationCode = $this->resDatabaseRecord->get('verification_code');
			}

			$strVerificationString = $this->base64url_encode(sprintf("%s|%s",$this->arrOriginalData['email'],$strVerificationCode));
			$strVerificationLink = sprintf('http://%s/%s?verify=%s',$strSiteHost,ltrim($strLoginURL,'/'),$strVerificationString);

			$resEmail = \Twist::Email()->create();

			$strEmailSubject = sprintf('%s: Verify your Account',$strSiteName);

			$resEmail->setSubject($strEmailSubject);
			$resEmail->setFrom(sprintf('no-reply@%s',str_replace('www.','',$strSiteHost)));
			$resEmail->setReplyTo(sprintf('no-reply@%s',str_replace('www.','',$strSiteHost)));
			$resEmail->addTo($this->arrOriginalData['email']);

			$arrTags = array();
			$arrTags['subject'] = $strEmailSubject;
			$arrTags['firstname'] = $this->arrOriginalData['firstname'];
			$arrTags['surname'] = $this->arrOriginalData['surname'];
			$arrTags['email'] = $this->arrOriginalData['email'];
			$arrTags['url'] = sprintf('http://%s/%s',$strSiteHost,ltrim($strLoginURL,'/'));
			$arrTags['host'] = $strSiteHost;
			$arrTags['site_name'] = $strSiteName;
			$arrTags['verification_link'] = $strVerificationLink;
			$arrTags['verification_code'] = $strVerificationCode;
			$arrTags['verification_string'] = $strVerificationString;

			$strHTML = \Twist::View()->build(sprintf('%suser/account-verification-email.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags);

			$resEmail->setBodyHTML($strHTML);
			$resEmail->send();
			$blOut = true;
		}

		return $blOut;
	}

	protected function allowPassword($strPassword){

		$strPasswordFile = sprintf('%sCore/Data/user/common-passwords.json',TWIST_FRAMEWORK);
		$arrOut = array(
			'status' => true,
			'message' => 'Password is allowed'
		);

		if(\Twist::framework()->setting('USER_MIN_PASSWORD_LENGTH') > 0 && strlen($strPassword) < \Twist::framework()->setting('USER_MIN_PASSWORD_LENGTH')){
			$arrOut['status'] = false;
			$arrOut['message'] = sprintf('Your new password is to short and must be at least %s characters.',\Twist::framework()->setting('USER_MIN_PASSWORD_LENGTH'));
		}

		if($arrOut['status'] && \Twist::framework()->setting('USER_COMMON_PASSWORD_FILTER') && file_exists($strPasswordFile)){
			$jsonData = file_get_contents($strPasswordFile);
			$arrCommonPasswords = json_decode($jsonData,true);

			$intPasswordIndex = array_search(trim(strtolower($strPassword)),$arrCommonPasswords);

			//If the index is not false, a common password has been entered
			if($intPasswordIndex !== false){

				$intPasswordIndex++;
				$intLevel = (ceil($intPasswordIndex/10)*10);

				$arrOut['status'] = false;
				$arrOut['message'] = sprintf('Your password is one of the top %s most common - please try again',$intLevel);
			}
		}

		return $arrOut;
	}

	public function isMember(){
		return ($this->level() >= \Twist::framework()->setting('USER_LEVEL_MEMBER') && $this->level() < \Twist::framework()->setting('USER_LEVEL_ADVANCED'));
	}

	public function isAtLeastMember(){
		return ($this->level() >= \Twist::framework()->setting('USER_LEVEL_MEMBER') || $this->level() == '0');
	}

	public function isAdvanced(){
		return ($this->level() >= \Twist::framework()->setting('USER_LEVEL_ADVANCED') && $this->level() < \Twist::framework()->setting('USER_LEVEL_ADMIN'));
	}

	public function isAtLeastAdvanced(){
		return ($this->level() >= \Twist::framework()->setting('USER_LEVEL_ADVANCED') || $this->level() == '0');
	}

	public function isAdmin(){
		return ($this->level() >= \Twist::framework()->setting('USER_LEVEL_ADMIN') && $this->level() < \Twist::framework()->setting('USER_LEVEL_SUPERADMIN'));
	}

	public function isAtLeastSuperAdmin(){
		return ($this->level() >= \Twist::framework()->setting('USER_LEVEL_SUPERADMIN') || $this->level() == '0');
	}

	public function isSuperAdmin(){
		return ($this->level() >= \Twist::framework()->setting('USER_LEVEL_SUPERADMIN'));
	}

	public function isRootUser(){
		return ($this->level() == '0');
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
}