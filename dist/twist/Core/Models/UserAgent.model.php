<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
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

	namespace Twist\Core\Models;

	/**
	 * Detection based on the User Agent string to work out the type of a device be it Desktop, Mac, Tablet or Mobile. OS and Browser details are also detected.
	 * @package Twist\Core\Models\UserAgent
	 */
	class UserAgent{

		protected static $blLoaded = false;
		protected static $arrDevices = array();
		protected static $arrBrowsers = array();
		protected static $arrUnknown = array(
			'type' => 'Unknown',
			'os' => array(
				'key' => 'unknown',
				'title' => 'Unknown',
				'version' => '',
				'fa-icon' => 'fa-question-circle',
			),
			'browser' => array(
				'key' => 'unknown',
				'title' => 'Unknown',
				'version' => '',
				'fa-icon' => 'fa-question-circle',
			)
		);

		/**
		 * Load in the detection data from the data store. Ensure that all generic matches are at the top, as the matches are found the last correct match is the one that is chosen.
		 */
		protected static function loadData(){

			if(self::$blLoaded == false){

				self::$arrDevices = json_decode(file_get_contents(sprintf('%sCore/Data/user-agents/devices.json',TWIST_FRAMEWORK)),true);
				self::$arrBrowsers = json_decode(file_get_contents(sprintf('%sCore/Data/user-agents/browsers.json',TWIST_FRAMEWORK)),true);

				self::$blLoaded = true;
			}
		}

		/**
		 * Get the User Agent string form the PHP Server array
		 * @return string User Agent Header
		 */
		public static function get(){
			return $_SERVER['HTTP_USER_AGENT'];
		}

		/**
		 * Detect the Device Type, OS Name, Name/Version and the Browser Name based on a user agent string that is either passed in or detected from the server headers
		 * @param null $strUserAgent The user agent header to analise, null will detect the users current user-agent
		 * @return array Information about the device device,os,version,browser
		 */
		public static function detect($strUserAgent = null){

			self::loadData();

			$arrOut = self::$arrUnknown;
			$strUserAgent = (is_null($strUserAgent)) ? self::get() : $strUserAgent;

			//Loop through all the detections to find the OS
			foreach(self::$arrDevices as $strDeviceKey => $arrDevice){

				foreach($arrDevice['regx'] as $strRegX){
					if(preg_match($strRegX, $strUserAgent)){

						$arrOut['type'] = $arrDevice['device'];

						$arrOut['os']['key'] = $strDeviceKey;
						$arrOut['os']['title'] = $arrDevice['os'];
						$arrOut['os']['version'] = $arrDevice['version'];
						$arrOut['os']['fa-icon'] = $arrDevice['fa-icon'];
						break;
					}
				}
				//Don't break out of this loop as a more precise match may be found
			}

			foreach(self::$arrBrowsers as $strBrowserKey => $arrBrowserInfo){

				foreach($arrBrowserInfo['regx'] as $strRegX){
					if(preg_match($strRegX, $strUserAgent)){

						$arrOut['browser']['key'] = $strBrowserKey;
						$arrOut['browser']['title'] = $arrBrowserInfo['browser'];
						$arrOut['browser']['fa-icon'] = $arrBrowserInfo['fa-icon'];
						break;
					}
				}
				//Don't break out of this loop as a more precise match may be found
			}

			return $arrOut;
		}

		/**
		 * Get device type by device key, these are the keys found in the devices.json file
		 * @param string $strDeviceKey
		 * @return string
		 */
		public static function getDeviceType($strDeviceKey){

			self::loadData();

			if(array_key_exists($strDeviceKey,self::$arrDevices)){
				return self::$arrDevices[$strDeviceKey]['device'];
			}else{
				return self::$arrUnknown['device'];
			}
		}

		/**
		 * Get OS details from a device key, these are the keys found in the devices.json file
		 * @param string $strDeviceKey
		 * @return array
		 */
		public static function getOS($strDeviceKey){

			self::loadData();

			if(array_key_exists($strDeviceKey,self::$arrDevices)){
				$arrOut = array();
				$arrOut['key'] = $strDeviceKey;
				$arrOut['title'] = self::$arrDevices[$strDeviceKey]['os'];
				$arrOut['version'] = self::$arrDevices[$strDeviceKey]['version'];
				$arrOut['fa-icon'] = self::$arrDevices[$strDeviceKey]['fa-icon'];

				return $arrOut;
			}else{
				return self::$arrUnknown['os'];
			}
		}

		/**
		 * Get Browser details from a browser key, these are the keys found in the browsers.json file
		 * @param string $strBrowserKey
		 * @return array
		 */
		public static function getBrowser($strBrowserKey){

			self::loadData();

			if(array_key_exists($strBrowserKey,self::$arrBrowsers)){
				$arrOut = array();
				$arrOut['key'] = $strBrowserKey;
				$arrOut['title'] = self::$arrBrowsers[$strBrowserKey]['browser'];
				$arrOut['fa-icon'] = self::$arrBrowsers[$strBrowserKey]['fa-icon'];

				return $arrOut;
			}else{
				return self::$arrUnknown['browser'];
			}
		}
	}