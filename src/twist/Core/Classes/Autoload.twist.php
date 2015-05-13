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

	class Autoload{

		public $arrRegisteredLoaders = array();
		public $strBaseDir = null;
		public $arrClassLoaded = array();

		public static $resAutoLoader;

		public static function init($strBaseDir){

			if(self::$resAutoLoader == NULL){
				self::$resAutoLoader = new self();
				self::$resAutoLoader->strBaseDir = $strBaseDir;
			}

			return self::$resAutoLoader;
		}

		public static function registerPath($strMatch,$dirPath,$strExtension = '.php'){

			if(self::$resAutoLoader != NULL){

				if(substr($dirPath,0,1) != '/'){
					$dirPath = sprintf('%s/%s',rtrim(self::$resAutoLoader->strBaseDir,'/'),$dirPath);
				}

				self::$resAutoLoader->arrRegisteredLoaders[ltrim($strMatch,'\\')] = array('type' => 'path','path' => $dirPath,'extension' => $strExtension);
				krsort(self::$resAutoLoader->arrRegisteredLoaders);
			}
		}

		public static function registerClass($strMatch,$strClass,$strFunction){

			if(self::$resAutoLoader != NULL){
				self::$resAutoLoader->arrRegisteredLoaders[ltrim($strMatch,'\\')] = array('type' => 'class','class' => array($strClass,$strFunction));
				krsort(self::$resAutoLoader->arrRegisteredLoaders);
			}
		}

		public function __construct(){
			spl_autoload_register(array($this,'load'));
		}

		public function load($strRequest){

			//Fix for matches that are in 'Twist\Core\Classes' namespace
			if(!strstr($strRequest,'\\')){
				$strRequest = sprintf('%s\\%s',__NAMESPACE__,$strRequest);
			}

			if(!array_key_exists($strRequest,$this->arrClassLoaded)){

				$strFile = str_replace('\\','/',$strRequest);

				if(strstr($strRequest,'Twist\\Core\\Classes')){
					$strFile .= '.twist.php';
				}elseif(strstr($strRequest,'Twist\\Core\\Packages')){
					$strFile .= '.package.php';
				}elseif(strstr($strRequest,'Twist\\Core\\Models')){
					$strFile .= '.model.php';
				}elseif(strstr($strRequest,'Route')){
					$strFile .= '.route.php';
				}elseif(strstr($strRequest,'Controller')){
					$strFile .= '.controller.php';
				}elseif(strstr($strRequest,'Model')){
					$strFile .= '.model.php';
				}elseif(strstr($strRequest,'Package')){
					$strFile .= '.package.php';
				}else{
					$strFile .= '.php';
				}

				$arrPrats = explode('/',$strFile);
				$arrPrats[0] = strtolower($arrPrats[0]);
				$strFile = implode('/',$arrPrats);

				if(substr($strRequest,0,6) == 'Twist\\'){
					$dirRequire = sprintf('%s/%s',rtrim($this->strBaseDir,'/'),ltrim($strFile));
				}else{
					$dirRequire = sprintf('%s/%s',rtrim(DIR_SITE_ROOT,'/'),ltrim($strFile));
				}

				if(file_exists($dirRequire)){
					require_once $dirRequire;
					$this->arrClassLoaded[$strRequest] = $dirRequire;
				}else{
					throw new \Exception(sprintf("TwistPHP AutoLoader: Unable to load the requested class '%s', please check to see if the file exists",$strRequest));
				}
			}
		}
	}