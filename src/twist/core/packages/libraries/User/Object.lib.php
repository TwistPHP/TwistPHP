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
 * @link       http://twistphp.com
 *
 */

namespace TwistPHP\Packages;

class UserObject{

	protected $resDatabaseRecord = null;
	protected $resDatabaseRecordData = null;

	protected $arrOriginalData = array();
	protected $arrOriginalUserData = array();

	protected $arrCustomData = array();
	protected $resParentClass = null;
	protected $blNewAccount = false;

	private $strTempPassword = null;

	public function __construct(DatabaseRecord $resDatabaseRecord,$resParentClass){
		$this->resParentClass = $resParentClass;
		$this->resDatabaseRecord = $resDatabaseRecord;
		$this->arrOriginalData = $this->resDatabaseRecord->values();

		$intUserID = ($this->resDatabaseRecord->get('id') == 0) ? null : $this->resDatabaseRecord->get('id');

		//Get the user data record to allow for this ti be edited
		$this->blNewAccount = is_null($intUserID);
		$this->resDatabaseRecordData = \Twist::Database()->getRecord(sprintf('%suser_data',DATABASE_TABLE_PREFIX),$intUserID,'user_id');
		$this->resDatabaseRecordData = (is_object($this->resDatabaseRecordData)) ? $this->resDatabaseRecordData : \Twist::Database()->createRecord(sprintf('%suser_data',DATABASE_TABLE_PREFIX));

		$this->arrOriginalUserData = $this->resDatabaseRecordData->values();
	}

	public function get($strField = null){
		return (is_null($strField)) ? $this->resDatabaseRecord->values() : $this->resDatabaseRecord->get($strField);
	}

	public function custom($strField,$strValue=null){

		if(!is_null($strValue)){
			$this->arrCustomData[$strField] = $strValue;
		}else{
			return (array_key_exists($strField,$this->arrCustomData)) ? $this->arrCustomData[$strField] : null;
		}
	}

	public function commit(){

		$blSendVerification = ($this->resDatabaseRecord->get('email') != $this->arrOriginalData['email'] || $this->resDatabaseRecord->get('verification_code') != $this->arrOriginalData['verification_code']);
		$blSendPassword = ($this->resDatabaseRecord->get('password') != $this->arrOriginalData['password']);

		//Commit and grab the standard user data
		$mxdOut = $this->resDatabaseRecord->commit();
		$this->arrOriginalData = $this->resDatabaseRecord->values();

		//Set the new users ID into the user data record
		if($this->blNewAccount){
			$this->resDatabaseRecordData->set('user_id',$mxdOut);
		}

		//Commit and grab the additional user data
		$this->resDatabaseRecordData->commit();
		$this->arrOriginalUserData = $this->resDatabaseRecordData->values();

		//@todo - add in custom data commit

		if($this->blNewAccount){
			$this->sendWelcomeEmail();
			$this->blNewAccount = false;
		}else{

			if($blSendVerification){
				$this->sendVerificationEmail();
			}

			if($blSendPassword){
				$this->sendPasswordEmail();
			}
		}

		//Just to ensure the temp password is defiantly removed
		$this->strTempPassword = null;

		return $mxdOut;
	}

	public function id(){
		return $this->resDatabaseRecord->get('id');
	}

	public function name(){
		return trim(sprintf("%s %s",$this->firstname(),$this->surname()));
	}

	public function firstname($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecord->get('firstname') : $this->resDatabaseRecord->set('firstname',$strValue);
	}

	public function surname($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecord->get('surname') : $this->resDatabaseRecord->set('surname',$strValue);
	}

	public function email($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecord->get('email') : $this->resDatabaseRecord->set('email',$strValue);
	}

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

	public function addressLine1($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('address_line1') : $this->resDatabaseRecordData->set('address_line1',$strValue);
	}

	public function addressLine2($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('address_line2') : $this->resDatabaseRecordData->set('address_line2',$strValue);
	}

	public function city($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('city') : $this->resDatabaseRecordData->set('city',$strValue);
	}

	public function region($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('region') : $this->resDatabaseRecordData->set('region',$strValue);
	}

	public function postcode($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('postcode') : $this->resDatabaseRecordData->set('postcode',$strValue);
	}

	public function country($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('country_iso') : $this->resDatabaseRecordData->set('country_iso',$strValue);
	}

	public function phone($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('phone') : $this->resDatabaseRecordData->set('phone',$strValue);
	}

	public function mobile($strValue = null){
		return (is_null($strValue)) ? $this->resDatabaseRecordData->get('mobile') : $this->resDatabaseRecordData->set('mobile',$strValue);
	}

	public function emailOptIn($blValue = null){
		return (is_null($blValue)) ? $this->resDatabaseRecordData->get('email_optin') : $this->resDatabaseRecordData->set('email_optin',($blValue == '1' || $blValue == true)  ? '1' : '0');
	}

	public function delete(){
		$blOut = \Twist::Database()->delete('users',$this->resDatabaseRecord->get('id'));
		\Twist::Database()->delete('user_details',$this->resDatabaseRecord->get('id'),'user_id');
		return $blOut;
	}

	public function requireVerification(){

		$strVerificationCode = null;

		//Send out the email verification
		if(\Twist::framework()->setting('USER_EMAIL_VERIFICATION')){

			//Generate a verification code
			$strVerificationCode = $this->generatePassword(16,3);

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
	 * @return bool
	 */
	public function resetPassword(){

		//Generate a new random password and send email
		$strPassword = $this->generatePassword(16,4);

		//Store the new temp password untill the reset email is sent upon commit
		$this->strTempPassword = $strPassword;
		$this->resDatabaseRecord->set('password',sha1($strPassword));
		$this->resDatabaseRecord->set('temp_password',1);

		return $strPassword;
	}

	protected function sendPasswordEmail(){

		$arrTags = array();
		$strSiteName = \Twist::framework()->setting('SITE_NAME');
		$strSiteHost = \Twist::framework()->setting('SITE_HOST');
		$strLoginURL = $this->resParentClass->loginURL();

		$strEmailSubject = (is_null($this->strTempPassword)) ? sprintf('%s: Password Changed',$strSiteName) : sprintf('%s: Password Reset',$strSiteName);

		$resEmail = \Twist::Email();
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

		$strData = $this->resParentClass->resTemplate->build($strTemplate, $arrTags);

		//Reset the temp password holder
		$this->strTempPassword = null;

		$resEmail->setBodyHTML($strData);
		$resEmail->send();
	}

	protected function sendWelcomeEmail(){

		$strLoginURL = $this->resParentClass->loginURL();
		$strTempPass = (is_null($this->strTempPassword)) ? '[specified on registration]' : $this->strTempPassword;

		$strSiteName = \Twist::framework()->setting('SITE_NAME');
		$strSiteHost = \Twist::framework()->setting('SITE_HOST');

		$resEmail = \Twist::Email();

		$strEmailSubject = sprintf('%s: Welcome',$strSiteName);

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

			$strVerificationCode = $this->requireVerification();
			$strVerificationString = $this->base64url_encode(sprintf("%s|%s",$this->arrOriginalData['email'],$strVerificationCode));

			$strVerificationLink = sprintf('http://%s/%s?verify=%s',$strSiteHost,ltrim($strLoginURL,'/'),$strVerificationString);
			$arrTags['verification_link'] = $strVerificationLink;

			$arrTags['verification'] = sprintf('<p><strong>Your account must be verified before you can login.</strong><br />To verify your account, <a href="%s">click here</a>.</p><p>If you have a problem with this link, please copy and paste the below link into your browser and proceed to login:<br /><a href="%s">%s</a></p>',
				$strVerificationLink,
				$strVerificationLink,
				$strVerificationLink
			);
		}

		$strHTML = $this->resParentClass->resTemplate->build('welcome-email.tpl',$arrTags);

		//Reset the temp password holder
		$this->strTempPassword = null;

		$resEmail->setBodyHTML($strHTML);
		$resEmail->send();
	}

	protected function sendVerificationEmail(){

		$blOut = false;

		//Send out the email verification
		if(\Twist::framework()->setting('USER_EMAIL_VERIFICATION')){

			$strLoginURL = $this->resParentClass->loginURL();
			$strSiteName = \Twist::framework()->setting('SITE_NAME');
			$strSiteHost = \Twist::framework()->setting('SITE_HOST');

			$strVerificationCode = $this->requireVerification();
			$strVerificationString = $this->base64url_encode(sprintf("%s|%s",$this->arrOriginalData['email'],$strVerificationCode));
			$strVerificationLink = sprintf('http://%s/%s?verify=%s',$strSiteHost,ltrim($strLoginURL,'/'),$strVerificationString);

			$resEmail = \Twist::Email();

			$strEmailSubject = sprintf('%s: Email Verification',$strSiteName);

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

			$strHTML = $this->resParentClass->resTemplate->build('account-verification-email.tpl',$arrTags);

			$resEmail->setBodyHTML($strHTML);
			$resEmail->send();
			$blOut = true;
		}

		return $blOut;
	}

	/**
	 * Generate a secure password, default length is 9 characters and standard strength.
	 * @param int $intLength
	 * @param int $intStrength
	 * @return string
	 */
	protected function generatePassword($intLength = 9, $intStrength = 0){

		$strVowels = 'aeuy';
		$strConsonants = 'bdghjmnpqrstvz';

		$strConsonants .=  ($intStrength > 0) ? 'BDGHJLMNPQRSTVWXZ' : '';
		$strVowels .=  ($intStrength > 1) ? 'AEUY' : '';
		$strConsonants .=  ($intStrength > 2) ? '23456789' : '';
		$strConsonants .=  ($intStrength > 3) ? '@#$%' : '';

		$strPassword = '';
		$intAlt = \Twist::DateTime()->time() % 2;

		for($intCount = 0; $intCount < $intLength; $intCount++){
			if($intAlt == 1){
				$strPassword .= $strConsonants[(rand() % strlen($strConsonants))];
				$intAlt = 0;
			} else{
				$strPassword .= $strVowels[(rand() % strlen($strVowels))];
				$intAlt = 1;
			}
		}

		return $strPassword;
	}

	protected function allowPassword($strPassword){

		$strPasswordFile = sprintf('%score/packages/resources/Localisation/common-passwords.json',DIR_FRAMEWORK);
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