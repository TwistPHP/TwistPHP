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

	namespace Twist\Core\Models;

	/**
	 * Framework hooks that are used throughout the framework, see the docs for a full list of integrated hocks.
	 * Register a hook to add in new view tags or extend framework functionality. Custom hooks are available in some packages to all them to be extended easily.
	 */
	class Hooks{

		protected $arrHooks = array();

		/**
		 * Register a hook to extend framework or package functionality
		 * @param $strHook
		 * @param $mxdUniqueKey
		 * @param $mxdData
		 */
		public function register($strHook,$mxdUniqueKey,$mxdData){

			if(!array_key_exists($strHook,$this->arrHooks)){
				$this->arrHooks[$strHook] = array();
			}

			$this->arrHooks[$strHook][$mxdUniqueKey] = $mxdData;
		}

		/**
		 * Cancel a hook from being active in the system, this will cancel the hook form the current page load only
		 * @param $strHook
		 * @param $mxdUniqueKey
		 */
		public function cancel($strHook,$mxdUniqueKey){
			unset($this->arrHooks[$strHook][$mxdUniqueKey]);
		}

		/**
		 * Get the array of extensions for the requested hook and key
		 * @param $strHook
		 * @param $mxdUniqueKey
		 * @return array
		 */
		public function get($strHook,$mxdUniqueKey){
			return (array_key_exists($strHook,$this->arrHooks)) ? $this->arrHooks[$strHook][$mxdUniqueKey] : array();
		}

		/**
		 * Get all the hooks, you can filter by package or leave blank for everything
		 * @param $strHook
		 * @return array
		 */
		public function getAll($strHook = null){

			if(is_null($strHook)){
				return $this->arrHooks;
			}

			return (array_key_exists($strHook,$this->arrHooks)) ? $this->arrHooks[$strHook] : array();
		}
	}