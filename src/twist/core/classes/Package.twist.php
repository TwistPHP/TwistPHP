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
		protected $arrInstalledPackages = array();

		/**
		 * Get an array of all the packages that are in the packages folder but have not been installed
		 * @return array
		 */
		public function getUninstalled(){

			$arrOut = array();
			$arrPackages = $this->getInstalled();

			//Find Packages
			foreach(scandir(DIR_PACKAGES) as $strFile){

				$dirPackage = sprintf('%s/%s',DIR_PACKAGES,$strFile);

				if(!in_array($strFile,array('.','..')) && is_dir($dirPackage)){

					$strPackageSlug = strtolower(basename($dirPackage));

					if(!array_key_exists($strPackageSlug,$arrPackages)){
						if(is_file(sprintf('%s/info.json',$dirPackage)) &&
							is_file(sprintf('%s/install.php',$dirPackage)) &&
							is_file(sprintf('%s/uninstall.php',$dirPackage))){

							$rawJson = file_get_contents(sprintf('%s/info.json',$dirPackage));
							$arrDetails = json_decode($rawJson,true);

							$arrOut[] = array(
								'slug' => $strPackageSlug,
								'path' => $dirPackage,
								'install' => sprintf('%s/install.php',$dirPackage),
								'uninstall' => sprintf('%s/uninstall.php',$dirPackage),
								'package' => $arrDetails
							);
						}
					}
				}
			}

			return $arrOut;
		}

		/**
		 * Get an array of all the installed packages on the system
		 * @return array|bool
		 */
		public function getInstalled(){

			$arrPackages = \Twist::Database()->getAll(DATABASE_PREFIX.'packages');
			$this->arrInstalledPackages = \Twist::framework()->tools()->arrayReindex($arrPackages,'slug');

			return $this->arrInstalledPackages;
		}

		/**
		 * Install the package into the framework
		 */
		public function install(){

			$arrBacktrace = debug_backtrace();

			if(count($arrBacktrace)){

				$dirInstallFile = $arrBacktrace[0]['file'];
				$dirPackage = dirname($dirInstallFile);

				$rawJson = file_get_contents(sprintf('%s/info.json',$dirPackage));
				$arrDetails = json_decode($rawJson,true);

				$resPackage = \Twist::Database()->createRecord(DATABASE_PREFIX.'packages');

				$resPackage->set('slug',strtolower(basename($dirPackage)));
				$resPackage->set('path',$dirPackage);
				$resPackage->set('name',$arrDetails['name']);
				$resPackage->set('version',$arrDetails['version']);
				$resPackage->set('resources',(is_file(sprintf('%s/resources.json',$dirPackage)) && count(scandir(sprintf('%s/resources',$dirPackage))) > 2) ? '1' : '0');
				$resPackage->set('routes',(is_dir(sprintf('%s/routes',$dirPackage)) && count(scandir(sprintf('%s/routes',$dirPackage))) > 2) ? '1' : '0');

				return $resPackage->commit();
			}

			return false;
		}

		/**
		 * Uninstall the package from the framework
		 */
		public function uninstall(){

			$arrBacktrace = debug_backtrace();

			if(count($arrBacktrace)){

				$dirInstallFile = $arrBacktrace[0]['file'];
				$dirPackage = dirname($dirInstallFile);

				$rawJson = file_get_contents(sprintf('%s/info.json',$dirPackage));
				$arrDetails = json_decode($rawJson,true);

				$arrUninstall = array(
					'slug' => strtolower(basename($dirPackage)),
					'path' => $dirPackage,
					'name' => $arrDetails['name'],
					'version' => $arrDetails['version'],
					'resources' => (is_file(sprintf('%s/resources.json',$dirPackage)) && count(scandir(sprintf('%s/resources',$dirPackage))) > 2) ? '1' : '0',
					'routes' => (is_dir(sprintf('%s/routes',$dirPackage)) && count(scandir(sprintf('%s/routes',$dirPackage))) > 2) ? '1' : '0'
				);

				//Write the code to un-install the package
			}
		}

		/**
		 * Check to see if a package is installed on the framework by its package slug (lowercase package folder name)
		 * @param $strPackageSlug
		 * @return bool
		 */
		public function isInstalled($strPackageSlug){
			return (count(\Twist::Database()->get(DATABASE_PREFIX.'packages',$strPackageSlug,'slug'))) ? true : false;
		}

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
		public function route($strPackageRoute,$strRegisteredURI,$mxdBaseView){

			if(array_key_exists($strPackageRoute,$this->arrRoutes)){
				$strPackage = $this->arrRoutes[$strPackageRoute];

				if($this->exists($strPackage,true)){
					require_once sprintf('%s/route.php',$this->arrPackages[$strPackage]['path']);

					$strRouteClass = sprintf('\Twist\Packages\Routes\%s',$strPackageRoute);

					if(class_exists($strRouteClass)){

						//Call the interface
						$objInterface = new $strRouteClass($strPackage);
						$objInterface->baseURI($strRegisteredURI);

						if($mxdBaseView === false || is_null($mxdBaseView)){
							$objInterface->baseViewIgnore();
						}elseif($mxdBaseView !== true){
							$objInterface->baseView($mxdBaseView);
						}

						$objInterface->load();
						$objInterface->serve();
					}else{
						throw new \Exception(sprintf("TwistPHP: The route '%s' for the package '%s' cannot be found",$strPackageRoute,$strPackage));
					}
				}
			}else{
				throw new \Exception(sprintf("TwistPHP: There is no registered package route '%s'",$strPackageRoute));
			}
		}

		public function register($strPackage){

			$strPath = sprintf('%s/%s',DIR_PACKAGES,$strPackage);
			$strURI = '/'.ltrim(str_replace(BASE_LOCATION,"",$strPath),'/');

			$arrInformation = json_decode(file_get_contents(sprintf('%s/info.json',$strPath)),true);

			if(!array_key_exists($strPackage,$this->arrPackages)){
				$this->arrPackages[$strPackage] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','routes' => array(),'extensions' => array(),'installed' => 0);
			}

			//Register the package for use withing the system
			$this->arrPackages[$strPackage]['type'] = 'Package';
			$this->arrPackages[$strPackage]['name'] = $arrInformation['name'];
			$this->arrPackages[$strPackage]['description'] = $arrInformation['description'];
			$this->arrPackages[$strPackage]['version'] = $arrInformation['version'];
			$this->arrPackages[$strPackage]['author'] = $arrInformation['author'];
			$this->arrPackages[$strPackage]['class'] = $strPackage;
			$this->arrPackages[$strPackage]['instances'] = false;//Too do later
			$this->arrPackages[$strPackage]['path'] = $strPath;
			$this->arrPackages[$strPackage]['uri'] = $strURI;
			$this->arrPackages[$strPackage]['installed'] = 1;
		}

		public function registerRoute($strPackage,$strRouteName){

			if(!array_key_exists($strPackage,$this->arrPackages)){
				$this->arrPackages[$strPackage] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','routes' => array(),'extensions' => array(),'installed' => 0);
			}

			$this->arrPackages[$strPackage]['routes'][$strRouteName] = $strRouteName;
			$this->arrRoutes[$strRouteName] = $strPackage;
		}

		/**
		 * Create the package record for use within the system
		 * @param $strPackage
		 * @param bool $blAllowInstances
		 * @param $strPackageName
		 * @param $strVersion
		 * @param $strAuthor
		 */
		public function create($strPackage,$blAllowInstances = false,$strPackageName,$strVersion,$strAuthor){

			if(!array_key_exists($strPackage,$this->arrPackages)){
				$this->arrPackages[$strPackage] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','routes' => array(),'extensions' => array(),'installed' => 0);
			}

			if($strAuthor == 'TwistPackage'){
				$strPath = DIR_FRAMEWORK_PACKAGES;
				$strURI = str_replace(BASE_LOCATION,"",$strPath);
			}else{
				$strPath = sprintf('%s/%s',DIR_PACKAGES,$strPackage);
				$strURI = str_replace(BASE_LOCATION,"",$strPath);
			}

			//Register the package for use withing the system
			$this->arrPackages[$strPackage]['type'] = ($strAuthor == 'TwistPackage') ? 'CorePackage' : 'Package';
			$this->arrPackages[$strPackage]['name'] = ($strAuthor == 'TwistPackage') ? $strPackage : $strPackageName;
			$this->arrPackages[$strPackage]['description'] = '';
			$this->arrPackages[$strPackage]['version'] = ($strAuthor == 'TwistPackage') ? '-' : $strVersion;
			$this->arrPackages[$strPackage]['author'] = ($strAuthor == 'TwistPackage') ? 'Shadow Technologies' : $strAuthor;
			$this->arrPackages[$strPackage]['class'] = $strPackage;
			$this->arrPackages[$strPackage]['instances'] = $blAllowInstances;
			$this->arrPackages[$strPackage]['path'] = $strPath;
			$this->arrPackages[$strPackage]['uri'] = $strURI;
			$this->arrPackages[$strPackage]['installed'] = 1;
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
				$this->arrPackages[$strPackage] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','routes' => array(),'extensions' => array(),'installed' => 0);
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