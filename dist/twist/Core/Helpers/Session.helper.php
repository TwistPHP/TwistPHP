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

	namespace Twist\Core\Helpers;

	use \Twist\Core\Models\Session\Files;
	use \Twist\Core\Models\Session\Memcache;
	use \Twist\Core\Models\Session\Mysql;

	/**
	 * Easy session management allowing the use of a user and site array of data. All stored using the PHP session.
	 * Also extends the View helper to allow the use of session vars in templates.
	 */
	class Session extends Base{

		protected $intSessionLife = 86400;
		protected $blStarted = false;
		protected $strHandler = 'native';
		protected $resHandler = null;

		public function __construct(){

			$this->strHandler = \Twist::framework() -> setting('SESSION_HANDLER');
			switch($this->strHandler){

				case'file';
					$this->resHandler = new Files();
					break;

				case'memcache';
					$this->resHandler = new Memcache();
					break;

				case'mysql';
					$this->resHandler = new Mysql();
					break;

				case'native';
				default:
					//Do nothing, uses the default handler
					break;
			}

			$this->start();
		}

		/**
		 * Create the guest session for the user, this will then be used when the user becomes real
		 */
		public function start(){

			if($this->blStarted == false){

				session_start();
				$_COOKIE['PHPSESSID'] = $this->getSessionID();

				if(!array_key_exists('twist-session',$_SESSION)){
					$_SESSION['twist-session'] = array();
				}

				$this->blStarted = true;
			}
		}

		/**
		 * Get the currently assigned session ID from the session handler
		 * @return mixed The ID of the current session
		 */
		public function getSessionID(){
			return session_id();
		}

		/**
		 * Set and get the Twist session data
		 * Passing only a key will return the data stored against that key, pass in a value as well will set and return the result
		 * @note You can pass multidimensional keys separated by '/', but this will not be able to change an existing value from a non-array value to an array value
		 * @param string $strKey The key for the item to be returned
		 * @param null $mxdValue The value to be set against the provided key, passing null will not set any data
		 * @return mixed Return the data that is contained in the provided key (if any exists otherwise NULL)
		 */
		public function data($strKey,$mxdValue = null){
			
			if(!is_null($mxdValue)){
				if(strstr($strKey,'/')){
					$newValue = \Twist::framework() -> tools() -> ghostArray( $strKey, '/', $mxdValue );
					$_SESSION['twist-session'] = \Twist::framework() -> tools() -> arrayMergeRecursive( $_SESSION['twist-session'], $newValue );
				} else {
					$_SESSION['twist-session'][$strKey] = $mxdValue;
				}
			}

			return \Twist::framework()->tools()->arrayParse($strKey,$_SESSION['twist-session']);
		}

		/**
		 * Null a value in the session array
		 * @note You can pass multidimensional keys separated by '/'
		 * @param string $strKey The key for the item to be nulled
		 * @return void
		 */
		public function nullData($strKey){

			if(strstr($strKey,'/')){
				$newValue = \Twist::framework() -> tools() -> ghostArray( $strKey, '/', null );
				$_SESSION['twist-session'] = \Twist::framework() -> tools() -> arrayMergeRecursive( $_SESSION['twist-session'], $newValue );
			} else {
				$_SESSION['twist-session'][$strKey] = null;
			}
		}

		/**
		 * Remove a single session item or clear the whole session by leaving the key field null
		 * @note You can pass multidimensional keys separated by '/'
		 * @param string $strKey The key for the item to be removed, passing null removes all
		 */
		public function delete($strKey = null){

			if(!is_null($strKey) && strstr($strKey,'/')){
				$_SESSION['twist-session'] = \Twist::framework()->tools()->arrayParseUnset($strKey,$_SESSION['twist-session'],'/');
			}elseif(!is_null($strKey) && array_key_exists($strKey,$_SESSION['twist-session'])){
				unset($_SESSION['twist-session'][$strKey]);
			}elseif(is_null($strKey)){
				$_SESSION['twist-session'] = array();
			}
		}

		/**
		 * @alias delete
		 * @param string $strKey
		 */
		public function remove($strKey = null){
			$this->delete($strKey);
		}

		/**
		 * @param string $strReference
		 */
		public function viewExtension($strReference){

			if(strstr($strReference,'/')){
				$mxdTempData = \Twist::framework()->tools()->arrayParse($strReference,$_SESSION['twist-session']);
				return (is_array($mxdTempData)) ? print_r($mxdTempData,true) : $mxdTempData;
			}elseif(array_key_exists($strReference,$_SESSION['twist-session'])){
				return (is_array($_SESSION['twist-session'][$strReference])) ? print_r($_SESSION['twist-session'][$strReference],true) : $_SESSION['twist-session'][$strReference];
			}
		}
	}