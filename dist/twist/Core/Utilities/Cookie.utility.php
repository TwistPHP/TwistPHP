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

	namespace Twist\Core\Utilities;

	/**
	 * Easy session management allowing the use of a user and site array of data. All stored using the PHP session.
	 * Also extends the template package to allow the use of session vars in templates.
	 */
	class Cookie extends Base{

		/**
		 * Returns true if there is a cookie with this name.
		 *
		 * @param string $name
		 * @return bool
		 */
		public function exists($strName){
			return array_key_exists($strName,$_COOKIE);
		}

		/**
		 * Get the value of the given cookie. If the cookie does not exist the value
		 * of $default will be returned.
		 *
		 * @param string $name
		 * @param string $default
		 * @return mixed
		 */
		public function get($strName){
			return ($this->exists($strName)) ? $_COOKIE[$strName] : null;
		}

		/**
		 * Set a cookie. Silently does nothing if headers have already been sent.
		 *
		 * @param string $name
		 * @param string $value
		 * @param mixed $expiry
		 * @param string $path
		 * @param string $domain
		 * @return bool
		 */
		public function set($strName, $mxdValue, $intExpiry = 0, $strPath = '/', $strDomain = false){

			$blOut = false;

			if(!headers_sent()){

				if($strDomain === false){
					$strDomain = $_SERVER['HTTP_HOST'];
				}

				if($intExpiry !== 0){
					if($intExpiry === -1){
						$intExpiry = strtotime('+99 Years');
					}elseif (is_numeric($intExpiry)){
						$intExpiry += time();
					}elseif(!is_null($intExpiry)){
						$intExpiry = strtotime($intExpiry);
					}
				}

				$blOut = @setcookie($strName, $mxdValue, $intExpiry, $strPath, $strDomain);

				if($blOut){
					$_COOKIE[$strName] = $mxdValue;
				}
			}

			return $blOut;
		}

		/**
		 * Delete a cookie.
		 *
		 * @param string $name
		 * @param string $path
		 * @param string $domain
		 * @param bool $remove_from_global Set to true to remove this cookie from this request.
		 * @return bool
		 */
		public function delete($strName, $strPath = '/', $strDomain = false, $blRemoveFromGlobal = false){

			$blOut = false;

			if(!headers_sent()){

				if($strDomain === false){
					$strDomain = $_SERVER['HTTP_HOST'];
				}

				$blOut = setcookie($strName, '', time() - 3600, $strPath, $strDomain);

				if($blRemoveFromGlobal){
					unset($_COOKIE[$strName]);
				}
			}

			return $blOut;
		}
	}