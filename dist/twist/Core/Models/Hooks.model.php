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

		protected $arrPermanentHooks = array();
		protected $arrHooks = array();

		public function __construct(){
			$this->loadHooks();
		}

		/**
		 * Register a hook to extend framework or package functionality
		 * @param $strHook
		 * @param $mxdUniqueKey
		 * @param $mxdData
		 * @param $blPermanent
		 */
		public function register($strHook,$mxdUniqueKey,$mxdData,$blPermanent = false){

			if(!array_key_exists($strHook,$this->arrHooks)){
				$this->arrHooks[$strHook] = array();
			}

			$this->arrHooks[$strHook][$mxdUniqueKey] = $mxdData;

			if($blPermanent){
				$this->storeHook($strHook,$mxdUniqueKey,$mxdData);
			}
		}

		/**
		 * Cancel a hook from being active in the system, this will cancel the hook form the current page load only
		 * @param $strHook
		 * @param $mxdUniqueKey
		 * @param $blPermanent
		 */
		public function cancel($strHook,$mxdUniqueKey,$blPermanent = false){

			unset($this->arrHooks[$strHook][$mxdUniqueKey]);

			if($blPermanent){
				$this->removeHook($strHook,$mxdUniqueKey);
			}
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

		/**
		 * Load the hooks form storage area
		 * @todo Make this database driven rather than cache files
		 */
		protected function loadHooks(){

			$arrCachedHooks = \Twist::Cache('twist/hooks')->read('permanent');
			$this->arrPermanentHooks = ($arrCachedHooks) ? $arrCachedHooks : array();

			if(count($this->arrHooks) == 0){
				$this->arrHooks = $this->arrPermanentHooks;
			}else{
				$this->arrHooks = \Twist::framework()->tools()->arrayMergeRecursive($this->arrHooks,$this->arrPermanentHooks);
			}
		}

		/**
		 * Permanently store a new hook
		 * @param $strHook
		 * @param $mxdUniqueKey
		 * @param $mxdData
		 * @todo Make this database driven rather than cache files
		 */
		protected function storeHook($strHook,$mxdUniqueKey,$mxdData){

			if(!array_key_exists($strHook,$this->arrPermanentHooks)){
				$this->arrPermanentHooks[$strHook] = array();
			}

			$this->arrPermanentHooks[$strHook][$mxdUniqueKey] = $mxdData;

			\Twist::Cache('twist/hooks')->write('permanent',$this->arrPermanentHooks,(86400*100));
		}

		/**
		 * Remove a permanently stored hook
		 * @param $strHook
		 * @param $mxdUniqueKey
		 * @todo Make this database driven rather than cache files
		 */
		protected function removeHook($strHook,$mxdUniqueKey){

			//Remove the hook from the permanent array
			unset($this->arrHooks[$strHook][$mxdUniqueKey]);

			\Twist::Cache('twist/hooks')->write('permanent',$this->arrPermanentHooks,(86400*100));
		}
	}