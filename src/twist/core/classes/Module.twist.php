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

	namespace TwistPHP;

	/**
	 * Handle all module/package related enquiries, for instance if you want to know if a package is installed or what version it is.
	 */
	final class Module{

		protected $arrModules = array();

		/**
		 * Check to see that a module is installed and usable, optional throw an exception of the module dosnt exist
		 * @param $strModule
		 * @param $blThrowException
		 * @return bool
		 */
		public function exists($strModule,$blThrowException = false){

			if($blThrowException && !array_key_exists($strModule,$this->arrModules)){
				throw new \Exception(sprintf("The module '%s' has not been installed or does not exist",$strModule));
			}

			return (array_key_exists($strModule,$this->arrModules));
		}

		/**
		 * Get all the current information for any installed module
		 * @param $strModule
		 * @return array
		 */
		public function information($strModule){
			$arrParts = explode('\\',$strModule);
			$strModule = array_pop($arrParts);
			return (array_key_exists($strModule,$this->arrModules)) ? $this->arrModules[$strModule] : array();
		}

		public function load($strModule){
			if($this->exists($strModule,true)){
				require_once sprintf('%s/load.php',$this->arrModules[$strModule]['path']);
			}
		}

		public function register($strClassName){

			$strPath = sprintf('%s%s',DIR_FRAMEWORK_MODULES,$strClassName);
			$strURI = '/'.ltrim(str_replace(BASE_LOCATION,"",$strPath),'/');

			$arrInformation = json_decode(file_get_contents(sprintf('%s/info.json',$strPath)),true);

			if(!array_key_exists($strClassName,$this->arrModules)){
				$this->arrModules[$strClassName] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','extensions' => array(),'installed' => 0);
			}

			//Register the module for use withing the system
			$this->arrModules[$strClassName]['type'] = 'Module';
			$this->arrModules[$strClassName]['name'] = $arrInformation['name'];
			$this->arrModules[$strClassName]['description'] = $arrInformation['description'];
			$this->arrModules[$strClassName]['version'] = $arrInformation['version'];
			$this->arrModules[$strClassName]['author'] = $arrInformation['author'];
			$this->arrModules[$strClassName]['class'] = $strClassName;
			$this->arrModules[$strClassName]['instances'] = false;//Too do later
			$this->arrModules[$strClassName]['path'] = $strPath;
			$this->arrModules[$strClassName]['uri'] = $strURI;
			$this->arrModules[$strClassName]['installed'] = 1;
		}

		/**
		 * Create the module record for use within the system
		 * @param $strClassName
		 * @param bool $blAllowInstances
		 * @param $strModuleName
		 * @param $strVersion
		 * @param $strAuthor
		 */
		public function create($strClassName,$blAllowInstances = false,$strModuleName,$strVersion,$strAuthor){

			if(!array_key_exists($strClassName,$this->arrModules)){
				$this->arrModules[$strClassName] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','extensions' => array(),'installed' => 0);
			}

			if($strAuthor == 'TwistPackage'){
				$strPath = DIR_FRAMEWORK_PACKAGES;
				$strURI = str_replace(BASE_LOCATION,"",$strPath);
			}else{
				$strPath = sprintf('%s%s',DIR_FRAMEWORK_MODULES,$strClassName);
				$strURI = str_replace(BASE_LOCATION,"",$strPath);
			}

			//Register the module for use withing the system
			$this->arrModules[$strClassName]['type'] = ($strAuthor == 'TwistPackage') ? 'Package' : 'Module';
			$this->arrModules[$strClassName]['name'] = ($strAuthor == 'TwistPackage') ? $strClassName : $strModuleName;
			$this->arrModules[$strClassName]['description'] = '';
			$this->arrModules[$strClassName]['version'] = ($strAuthor == 'TwistPackage') ? '-' : $strVersion;
			$this->arrModules[$strClassName]['author'] = ($strAuthor == 'TwistPackage') ? 'Shadow Technologies' : $strAuthor;
			$this->arrModules[$strClassName]['class'] = $strClassName;
			$this->arrModules[$strClassName]['instances'] = $blAllowInstances;
			$this->arrModules[$strClassName]['path'] = $strPath;
			$this->arrModules[$strClassName]['uri'] = $strURI;
			$this->arrModules[$strClassName]['installed'] = 1;
		}

		/**
		 * Register the module for use withing the system
		 * @param $strModule
		 * @param $mxdKey
		 * @param $mxdData
		 */
		public function extend($strModule,$mxdKey,$mxdData){

			if(!array_key_exists($strModule,$this->arrModules)){
				$this->arrModules[$strModule] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','extensions' => array(),'installed' => 0);
			}

			$this->arrModules[$strModule]['extensions'][$mxdKey] = $mxdData;
		}

		/**
		 * Get the array of extensions for the requested module
		 * @param $strModule
		 * @return array
		 */
		public function extensions($strModule){
			return (array_key_exists($strModule,$this->arrModules)) ? $this->arrModules[$strModule]['extensions'] : array();
		}

		/**
		 * Get an array of all the registered modules/packages in the system
		 * @return array
		 */
		public function getAll(){
			return $this->arrModules;
		}
	}