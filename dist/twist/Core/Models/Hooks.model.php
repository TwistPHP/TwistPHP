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
	 * The default framework hooks are loaded in the construct
	 */
	class Hooks{

		protected $arrPermanentHooks = array();
		protected $arrHooks = array();

		public function __construct(){

			$this->loadHooks();

			//Register the default Twist utility extensions
			$this->arrHooks['TWIST_VIEW_TAG']['asset'] = array('module' => 'Asset','function' => 'viewExtension');
			$this->arrHooks['TWIST_VIEW_TAG']['file'] = array('module' => 'File','function' => 'viewExtension');
			$this->arrHooks['TWIST_VIEW_TAG']['image'] = array('module' => 'Image','function' => 'viewExtension');
			$this->arrHooks['TWIST_VIEW_TAG']['session'] = array('module' => 'Session','function' => 'viewExtension');
			$this->arrHooks['TWIST_VIEW_TAG']['user'] = array('module' => 'User','function' => 'viewExtension');

			//Register the framework message handler into the template system
			$this->arrHooks['TWIST_VIEW_TAG']['messages'] = array('core' => 'messageHandler');

			//Register the framework resources handler into the template system
			$this->arrHooks['TWIST_VIEW_TAG']['resource'] = array('instance' => 'twistCoreResources','function' => 'viewResource');
			$this->arrHooks['TWIST_VIEW_TAG']['css'] = array('instance' => 'twistCoreResources','function' => 'viewCSS');
			$this->arrHooks['TWIST_VIEW_TAG']['js'] = array('instance' => 'twistCoreResources','function' => 'viewJS');
			$this->arrHooks['TWIST_VIEW_TAG']['img'] = array('instance' => 'twistCoreResources','function' => 'viewImage');

			//Integrate the basic core href tag support
			$strResourcesURI = sprintf('%s/%sCore/Resources/',rtrim(SITE_URI_REWRITE,'/'),ltrim(TWIST_FRAMEWORK_URI,'/'));

			$this->arrHooks['TWIST_VIEW_TAG']['core'] = array(
				'logo' => sprintf('%stwist/logos/logo.png',$strResourcesURI),
				'logo-favicon' => sprintf('%stwist/logos/favicon.ico',$strResourcesURI),
				'logo-32' => sprintf('%stwist/logos/logo-32.png',$strResourcesURI),
				'logo-48' => sprintf('%stwist/logos/logo-48.png',$strResourcesURI),
				'logo-57' => sprintf('%stwist/logos/logo-57.png',$strResourcesURI),
				'logo-64' => sprintf('%stwist/logos/logo-64.png',$strResourcesURI),
				'logo-72' => sprintf('%stwist/logos/logo-72.png',$strResourcesURI),
				'logo-96' => sprintf('%stwist/logos/logo-96.png',$strResourcesURI),
				'logo-114' => sprintf('%stwist/logos/logo-114.png',$strResourcesURI),
				'logo-128' => sprintf('%stwist/logos/logo-128.png',$strResourcesURI),
				'logo-144' => sprintf('%stwist/logos/logo-144.png',$strResourcesURI),
				'logo-192' => sprintf('%stwist/logos/logo-192.png',$strResourcesURI),
				'logo-256' => sprintf('%stwist/logos/logo-256.png',$strResourcesURI),
				'logo-512' => sprintf('%stwist/logos/logo-512.png',$strResourcesURI),
				'logo-640' => sprintf('%stwist/logos/logo-640.png',$strResourcesURI),
				'logo-800' => sprintf('%stwist/logos/logo-800.png',$strResourcesURI),
				'logo-1024' => sprintf('%stwist/logos/logo-1024.png',$strResourcesURI),
				'logo-large' => sprintf('%stwist/logos/logo-512.png',$strResourcesURI),
				'logo-small' => sprintf('%stwist/logos/logo-32.png',$strResourcesURI),
				'resources_uri' => $strResourcesURI,
				'uri' => ltrim(sprintf('%s/%s',rtrim(SITE_URI_REWRITE,'/'),ltrim(TWIST_FRAMEWORK_URI,'/')),'/')
			);
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