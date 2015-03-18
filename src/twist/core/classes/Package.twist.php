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

	namespace Twist\Core\Classes;

	/**
	 * Handle all package/package related enquiries, for instance if you want to know if a package is installed or what version it is.
	 */
	final class Package{

		protected $arrPackages = array();

		/**
		 * Check to see that a package is installed and usable, optional throw an exception of the package dosnt exist
		 * @param $strPackage
		 * @param $blThrowException
		 * @return bool
		 */
		public function exists($strPackage,$blThrowException = false){

			if($blThrowException && !array_key_exists($strPackage,$this->arrPackages)){
				throw new \Exception(sprintf("The package '%s' has not been installed or does not exist",$strPackage));
			}

			return (array_key_exists($strPackage,$this->arrPackages));
		}

		/**
		 * Get all the current information for any installed package
		 * @param $strPackage
		 * @return array
		 */
		public function information($strPackage){
			$arrParts = explode('\\',$strPackage);
			$strPackage = array_pop($arrParts);
			return (array_key_exists($strPackage,$this->arrPackages)) ? $this->arrPackages[$strPackage] : array();
		}

		/**
		 * Load the controller the package class that extends the framework
		 * @param $strPackage
		 * @throws \Exception
		 */
		public function load($strPackage){
			if($this->exists($strPackage,true)){
				require_once sprintf('%s/load.php',$this->arrPackages[$strPackage]['path']);
			}
		}

		/**
		 * Load the interface that comes as part of a package
		 * @param $strPackage
		 * @throws \Exception
		 */
		public function route($strPackage,$strRegisteredURI,$mxdBaseView){
			if($this->exists($strPackage,true)){
				require_once sprintf('%s/route.php',$this->arrPackages[$strPackage]['path']);

				//@todo - work out how to pass in the URI and Base View
			}
		}

		public function register($strClassName){

			$strPath = sprintf('%s/%s',DIR_PACKAGES,$strClassName);
			$strURI = '/'.ltrim(str_replace(BASE_LOCATION,"",$strPath),'/');

			$arrInformation = json_decode(file_get_contents(sprintf('%s/info.json',$strPath)),true);

			if(!array_key_exists($strClassName,$this->arrPackages)){
				$this->arrPackages[$strClassName] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','extensions' => array(),'installed' => 0);
			}

			//Register the package for use withing the system
			$this->arrPackages[$strClassName]['type'] = 'Package';
			$this->arrPackages[$strClassName]['name'] = $arrInformation['name'];
			$this->arrPackages[$strClassName]['description'] = $arrInformation['description'];
			$this->arrPackages[$strClassName]['version'] = $arrInformation['version'];
			$this->arrPackages[$strClassName]['author'] = $arrInformation['author'];
			$this->arrPackages[$strClassName]['class'] = $strClassName;
			$this->arrPackages[$strClassName]['instances'] = false;//Too do later
			$this->arrPackages[$strClassName]['path'] = $strPath;
			$this->arrPackages[$strClassName]['uri'] = $strURI;
			$this->arrPackages[$strClassName]['installed'] = 1;
		}

		/**
		 * Create the package record for use within the system
		 * @param $strClassName
		 * @param bool $blAllowInstances
		 * @param $strPackageName
		 * @param $strVersion
		 * @param $strAuthor
		 */
		public function create($strClassName,$blAllowInstances = false,$strPackageName,$strVersion,$strAuthor){

			if(!array_key_exists($strClassName,$this->arrPackages)){
				$this->arrPackages[$strClassName] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','extensions' => array(),'installed' => 0);
			}

			if($strAuthor == 'TwistPackage'){
				$strPath = DIR_FRAMEWORK_PACKAGES;
				$strURI = str_replace(BASE_LOCATION,"",$strPath);
			}else{
				$strPath = sprintf('%s/%s',DIR_PACKAGES,$strClassName);
				$strURI = str_replace(BASE_LOCATION,"",$strPath);
			}

			//Register the package for use withing the system
			$this->arrPackages[$strClassName]['type'] = ($strAuthor == 'TwistPackage') ? 'CorePackage' : 'Package';
			$this->arrPackages[$strClassName]['name'] = ($strAuthor == 'TwistPackage') ? $strClassName : $strPackageName;
			$this->arrPackages[$strClassName]['description'] = '';
			$this->arrPackages[$strClassName]['version'] = ($strAuthor == 'TwistPackage') ? '-' : $strVersion;
			$this->arrPackages[$strClassName]['author'] = ($strAuthor == 'TwistPackage') ? 'Shadow Technologies' : $strAuthor;
			$this->arrPackages[$strClassName]['class'] = $strClassName;
			$this->arrPackages[$strClassName]['instances'] = $blAllowInstances;
			$this->arrPackages[$strClassName]['path'] = $strPath;
			$this->arrPackages[$strClassName]['uri'] = $strURI;
			$this->arrPackages[$strClassName]['installed'] = 1;
		}

		/**
		 * Register the package for use withing the system
		 * @param $strPackage
		 * @param $mxdKey
		 * @param $mxdData
		 */
		public function extend($strPackage,$mxdKey,$mxdData){

			//@deprecate when remove template all traces of templates
			$strPackage = ($strPackage == 'Template') ? 'View' : $strPackage;

			if(!array_key_exists($strPackage,$this->arrPackages)){
				$this->arrPackages[$strPackage] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','extensions' => array(),'installed' => 0);
			}

			$this->arrPackages[$strPackage]['extensions'][$mxdKey] = $mxdData;
		}

		/**
		 * Get the array of extensions for the requested package
		 * @param $strPackage
		 * @return array
		 */
		public function extensions($strPackage){
			return (array_key_exists($strPackage,$this->arrPackages)) ? $this->arrPackages[$strPackage]['extensions'] : array();
		}

		/**
		 * Get an array of all the registered packages/packages in the system
		 * @return array
		 */
		public function getAll(){
			return $this->arrPackages;
		}
	}