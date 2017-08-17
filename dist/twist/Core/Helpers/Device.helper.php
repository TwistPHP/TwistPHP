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
	use \Twist\Core\Models\UserAgent;

	/**
	 * Detect device related information from a user agent string, also can return logos and icons where applicable.
	 */
	class Device extends Base{

		/**
		 * Get the Type, OS Name, Name/Version and the Browser Name of a device by it's User Agent string.
		 * @param null $strUserAgent
		 * @return mixed
		 */
		public function get($strUserAgent = null){
			return UserAgent::detect($strUserAgent);
		}

		/**
		 * Get the OS Name of a device by it's User Agent string.
		 * @related get
		 * @param null $strUserAgent The user agent header to analise, null will detect the users current user-agent
		 * @return string Returns the OS Name
		 */
		public function getOS($strUserAgent = null){

			$arrInfo = $this->get($strUserAgent);
			return $arrInfo['os']['title'];
		}

		/**
		 * Get the OS Name/Version of a device by it's User Agent string.
		 * @related get
		 * @param null $strUserAgent The user agent header to analise, null will detect the users current user-agent
		 * @return string Returns the OS version
		 */
		public function getOSVersion($strUserAgent = null){

			$arrInfo = $this->get($strUserAgent);
			return $arrInfo['os']['version'];
		}

		/**
		 * Get the Type of a device by it's User Agent string.
		 * @related get
		 * @param null $strUserAgent The user agent header to analise, null will detect the users current user-agent
		 * @return string Returns the device type
		 */
		public function getDevice($strUserAgent = null){

			$arrInfo = $this->get($strUserAgent);
			return $arrInfo['type'];
		}

		/**
		 * Get the Browser Name of a device by it's User Agent string.
		 * @related get
		 * @param null $strUserAgent The user agent header to analise, null will detect the users current user-agent
		 * @return string Returns the browser name
		 */
		public function getBrowser($strUserAgent = null){

			$arrInfo = $this->get($strUserAgent);
			return $arrInfo['browser']['title'];
		}

	}