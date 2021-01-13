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

	namespace Twist\Classes;

	/**
	 * Instance handler to store and retrieve resources and objects from anywhere withing the framework. Handy for storing instances of models and other data to help minimize repetition of processing and loading data.
	 * @package Twist\Classes
	 */
	class Instance{

		protected static $arrFrameworkObjects = array();

		/**
		 * Detect if and object Key has been stored.
		 * @param string $strObjectKey Unique identification key
		 * @return bool
		 */
		public static function isObject($strObjectKey){
			return (array_key_exists($strObjectKey,self::$arrFrameworkObjects));
		}

		/**
		 * Store and object/resource against an object key. An object key is a unique string that will enable you to retrieve the object later.
		 * @param string $strObjectKey Unique identification key
		 * @param object|resource $objResource
		 */
		public static function storeObject($strObjectKey,$objResource){
			self::$arrFrameworkObjects[$strObjectKey] = $objResource;
		}

		/**
		 * Returns an object/resource using its object key.
		 * @param string $strObjectKey Unique identification key
		 * @return null|object|resource
		 */
		public static function retrieveObject($strObjectKey){
			return self::$arrFrameworkObjects[$strObjectKey];
		}

		/**
		 * Removes the object from the instance holder and destroys the object.
		 * @param string $strObjectKey
		 */
		public static function removeObject($strObjectKey){
			unset(self::$arrFrameworkObjects[$strObjectKey]);
		}

		/**
		 * Returns an array of all the stored object keys.
		 * @return array
		 */
		public static function listObjects(){
			return array_keys(self::$arrFrameworkObjects);
		}
	}