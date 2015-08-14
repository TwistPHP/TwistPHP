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

namespace Twist\Core\Models\User;

class SessionHandler{

	protected $strSecretKey = 'mySecretKey';
	protected $intSessionLife = 604800;
	protected $intDeviceLife = 31536000;

	/**
	 * Set the device cookie life in seconds (Default: 31536000 == 1 year)
	 * @param $intDeviceLife
	 */
	public function setDeviceLife($intDeviceLife = 31536000){
		$this->intDeviceLife = $intDeviceLife;
	}

	/**
	 * Set the device cookie life in seconds (Default: 604800 == 7 Days)
	 * @param $intSessionLife
	 */
	public function setSessionLife($intSessionLife = 604800){
		$this->intSessionLife = $intSessionLife;
	}

	/**
	 * Create a device ID, this for now is unique to a browser / computer, multiple users can use one device key
	 * @param  $intUserID
	 * @return null|string
	 */
	protected function createDeviceID($intUserID){

		$strDeviceID = null;
		$objDB = \Twist::Database();

		if(count($_COOKIE) && !array_key_exists('device',$_COOKIE)){

			//Generate a unique device ID (Length 72)
			$strDeviceID = md5(uniqid(rand(), true)).sha1($_SERVER['HTTP_USER_AGENT'].$this->strSecretKey);

			//setcookie('device', $strDeviceID, (\Twist::DateTime()->time()+$this->intDeviceLife), '/', $_SERVER["HTTP_HOST"], isset($_SERVER["HTTPS"]), true);
			setcookie('device', $strDeviceID, (\Twist::DateTime()->time()+$this->intDeviceLife), '/');
			//echo "Create Cookie";
			//die();
			$_COOKIE['device'] = $strDeviceID;

			$strSQL = sprintf("INSERT INTO `%s`.`%suser_sessions`
									SET `user_id` = %d,
										`device` = '%s',
										`os` = '%s',
										`browser` = '%s'",
				TWIST_DATABASE_NAME,
				TWIST_DATABASE_TABLE_PREFIX,
				$objDB->escapeString($intUserID),
				$objDB->escapeString($strDeviceID),
				\Twist::Device()->getOSVersion(),
				\Twist::Device()->getBrowser()
			);

			$objDB->query($strSQL);
		}else{

			//Get the device ID
			$strDeviceID = $_COOKIE['device'];

			if(!count($this->getCurrentDevice($intUserID))){

				$strSQL = sprintf("INSERT INTO `%s`.`%suser_sessions`
										SET `user_id` = %d,
											`device` = '%s',
											`os` = '%s',
											`browser` = '%s'",
					TWIST_DATABASE_NAME,
					TWIST_DATABASE_TABLE_PREFIX,
					$objDB->escapeString($intUserID),
					$objDB->escapeString($strDeviceID),
					\Twist::Device()->getOSVersion(),
					\Twist::Device()->getBrowser()
				);

				$objDB->query($strSQL);
			}
		}

		return $strDeviceID;
	}

	public function getDeviceID(){

		$strDeviceID = null;

		//Get the device ID if set
		if(count($_COOKIE) && array_key_exists('device',$_COOKIE)){
			$strDeviceID = $_COOKIE['device'];
		}

		return $strDeviceID;
	}

	public function deleteDeviceID($strDeviceID){

		$objDB = \Twist::Database();

		$strSQL = sprintf("DELETE FROM `%s`.`%suser_sessions`
								WHERE `device` = '%s'",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($strDeviceID)
		);

		$objDB->query($strSQL);
	}

	public function getDeviceList($intUserID){

		$arrOut = array();
		$objDB = \Twist::Database();

		$strSQL = sprintf("SELECT `id`,`device`,`device_name`,`os`,`browser`,`last_login`
								FROM `%s`.`%suser_sessions`
								WHERE `user_id` = %d
								ORDER BY `last_login` DESC",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($intUserID)
		);

		if($objDB->query($strSQL) && $objDB->getNumberRows()){
			$arrOut = $objDB->getFullArray();
		}

		return $arrOut;
	}

	public function getCurrentDevice($intUserID){

		$arrOut = array();
		$objDB = \Twist::Database();

		if(count($_COOKIE) && array_key_exists('device',$_COOKIE)){

			$strSQL = sprintf("SELECT `id`,`device`,`device_name`,`os`,`browser`,`last_login`
									FROM `%s`.`%suser_sessions`
									WHERE `user_id` = %d
									AND `device` = '%s'",
				TWIST_DATABASE_NAME,
				TWIST_DATABASE_TABLE_PREFIX,
				$objDB->escapeString($intUserID),
				$_COOKIE['device']
			);

			if($objDB->query($strSQL) && $objDB->getNumberRows()){
				$arrOut = $objDB->getArray();
			}
		}

		return $arrOut;
	}

	/**
	 * Edit the name of a given device
	 */
	public function editDevice($intUserID,$mxdDevice,$strDeviceName){

		$objDB = \Twist::Database();

		$strSQL = sprintf("UPDATE `%s`.`%suser_sessions`
							SET `device_name` = '%s'
							WHERE `device` = '%s'
							AND `user_id` = %d",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($strDeviceName),
			$objDB->escapeString($mxdDevice),
			$objDB->escapeString($intUserID)
		);

		$objDB->query($strSQL);
	}

	/**
	 * Forget a device and if it is current device log the user out.
	 */
	public function forgetDevice($intUserID,$mxdDevice){

		$arrCurrent = $this->getCurrentDevice($intUserID);
		$objDB = \Twist::Database();

		$strSQL = sprintf("DELETE FROM `%s`.`%suser_sessions`
								WHERE `device` = '%s'
								AND `user_id` = %d",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($mxdDevice),
			$objDB->escapeString($intUserID)
		);

		if($objDB->query($strSQL) && $objDB->getAffectedRows() && $arrCurrent['device'] == $mxdDevice){
			$this->forget();
		}
	}

	/**
	 * Forget the user, nolonger remeber the user
	 */
	public function forget(){

		if($this->remembered()){
			//Update the remember me cookie if required
			//setcookie('remember', '', 0, '/', $_SERVER["HTTP_HOST"], isset($_SERVER["HTTPS"]), true);
			setcookie('remember', '', 0, '/');
			$_COOKIE['remember'] = '';
		}
	}

	/**
	 * See if the use has been remembered or not
	 * @return bool
	 */
	public function remembered(){
		return (count($_COOKIE) && array_key_exists('remember',$_COOKIE));
	}

	/**
	 * Create a remember me cookie session
	 * @param  $intUserID
	 * @return string
	 */
	public function createCookie($intUserID){
		return $this->createSessionKey($intUserID,true);
	}

	/**
	 * Create a session key for use PHP session
	 * @param  $intUserID
	 * @return string
	 */
	public function createCode($intUserID){
		return $this->createSessionKey($intUserID,false);
	}

	/**
	 * Validate a remember me cookie session
	 * @return int
	 */
	public function validateCookie($blUpdateKey = true){
		$this->debug("\n\n=======================\nValidate Cookie Code");
		$this->debug("URI: {$_SERVER['REQUEST_URI']}");
		return (count($_COOKIE) && array_key_exists('remember',$_COOKIE)) ? $this->validateSessionKey($_COOKIE['remember'],true,$blUpdateKey) : 0;
	}

	/**
	 * Validate a remember me session code
	 * @param  $strSessionKey
	 * @return int
	 */
	public function validateCode($strSessionKey,$blUpdateKey = true){
		$this->debug("\n\n=======================\nValidate Code: {$strSessionKey}");
		$this->debug("URI: {$_SERVER['REQUEST_URI']}");
		return $this->validateSessionKey($strSessionKey,false,$blUpdateKey);
	}

	public function debug($strMessage){
		//file_put_contents(sprintf('%s/user.log',TWIST_DOCUMENT_ROOT),$strMessage."\n",FILE_APPEND);
	}

	protected function validateSessionKey($strSessionKey,$blRemember = false,$blUpdateKey = true){

		$intOut = 0;
		$objDB = \Twist::Database();

		//Session Length is 104 chars
		preg_match("/([0-9a-z]{40})([0-9a-z]{32})([0-9a-z]{32})/i",$strSessionKey,$arrMatches);

		$this->debug("Key Parts: ".print_r($arrMatches,true));

		$strSQL = sprintf("SELECT `user_sessions`.`user_id`
								FROM `%s`.`%suser_sessions` AS `user_sessions`
								JOIN `%s`.`%susers` AS `users` ON `user_sessions`.`user_id` = `users`.`id`
								WHERE sha1(`user_sessions`.`device`) = '%s'
								AND `user_sessions`.`token` = '%s'
								AND md5(concat(`user_sessions`.`user_id`,'%s')) = '%s'
								AND `users`.`enabled` = '1'",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($arrMatches[1]),
			$objDB->escapeString($arrMatches[2]),
			$objDB->escapeString($this->strSecretKey),
			$objDB->escapeString($arrMatches[3])
		);

		$this->debug("SQL: {$strSQL}");

		$strAttackSQL = sprintf("SELECT `user_id`
									FROM `%s`.`%suser_sessions`
									WHERE sha1(`device`) = '%s'
									AND md5(concat(`user_id`,'%s')) = '%s'",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($arrMatches[1]),
			$objDB->escapeString($this->strSecretKey),
			$objDB->escapeString($arrMatches[2])
		);

		$this->debug("SQL Attack: {$strAttackSQL}");

		//If their is only one session key
		if($objDB->query($strSQL) && $objDB->getNumberRows() == 1){
			$arrUserData = $objDB->getArray();

			$this->debug("Query OK");

			//Update the session Key
			if($blUpdateKey == true){
				$this->updateSession($arrMatches[1],$arrMatches[3],$blRemember);
			}

			$intOut = $arrUserData['user_id'];

		}elseif($objDB->query($strAttackSQL) && $objDB->getNumberRows() == 1){

			$this->debug("Query Attack");

			//Remove the session key as this is an invalid session
			$this->wipeSession($arrMatches[1],$arrMatches[3]);
		}else{
			$this->debug("Query Fail Both");
		}

		return $intOut;
	}

	protected function createSessionKey($intUserID,$blRemember = false){

		//Get the device ID and if null create a new device ID
		$strDeviceID = $this->createDeviceID($intUserID);

		$strDeviceKey = sha1($strDeviceID);
		$strHash = md5(sprintf('%s%s',$intUserID,$this->strSecretKey));

		$strTokenKey = $this->updateSession($strDeviceKey,$strHash,$blRemember);
		return $strTokenKey;
	}

	protected function updateSession($strDevice,$strHash,$blRemember = false){

		$objDB = \Twist::Database();

		$strNewToken = md5(uniqid(rand(), true));

		//Delete all session data for this key
		$strSQL = sprintf("UPDATE `%s`.`%suser_sessions`
								SET `token` = '%s',
									`remote_addr` = '%s',
									`last_login` = NOW()
								WHERE sha1(`device`) = '%s'
								AND md5(concat(`user_id`,'%s')) = '%s'",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($strNewToken),
			$objDB->escapeString($_SERVER['REMOTE_ADDR']),
			$objDB->escapeString($strDevice),
			$objDB->escapeString($this->strSecretKey),
			$objDB->escapeString($strHash)
		);

		if($objDB->query($strSQL)){

			$strTokenKey = sprintf("%s%s%s",$strDevice,$strNewToken,$strHash);

			if($blRemember == true || $this->remembered()){
				//if($this->remembered()){
				//Update the remember me cookie if required
				//setcookie('remember', $strTokenKey, (\Twist::DateTime()->time()+$this->intSessionLife), '/', $_SERVER["HTTP_HOST"], isset($_SERVER["HTTPS"]), true);
				setcookie('remember', $strTokenKey, (\Twist::DateTime()->time()+$this->intSessionLife), '/');
				$_COOKIE['remember'] = $strTokenKey;
			}

			//Check if the session key is set, if so update it
			if(!is_null(\Twist::Session()->data('user-session_key'))){
				\Twist::Session()->data('user-session_key',$strTokenKey);
			}
		}else{
			$strTokenKey = \Twist::Session()->data('user-session_key');
		}

		return $strTokenKey;
	}

	protected function wipeSession($strDevice,$strHash){

		$objDB = \Twist::Database();

		//Delete all session data for this key
		$strSQL = sprintf("UPDATE `%s`.`%suser_sessions`
								SET `token` = ''
								WHERE md5(`device`) = '%s'
								AND md5(concat(`user_id`,'%s')) = '%s'",
			TWIST_DATABASE_NAME,
			TWIST_DATABASE_TABLE_PREFIX,
			$objDB->escapeString($strDevice),
			$objDB->escapeString($this->strSecretKey),
			$objDB->escapeString($strHash)
		);

		$objDB->query($strSQL);
	}
}