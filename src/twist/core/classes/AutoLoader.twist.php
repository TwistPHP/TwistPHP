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
	 * @link       http://twistphp.com
	 *
	 */

	namespace TwistPHP;

	class AutoLoader{

		public $arrRegisteredLoaders = array();
		public static $resAutoLoader;

		public static function init(){

			if(self::$resAutoLoader == NULL){
				self::$resAutoLoader = new self();
			}

			return self::$resAutoLoader;
		}

		public static function registerPath($strMatch,$dirPath,$strExtension = '.php'){

			if(self::$resAutoLoader != NULL){
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

			$blMatchFound = false;

			foreach($this->arrRegisteredLoaders as $strMatch => $arrLoader){

				//Find a match for the file to be auto loaded
				if(preg_match(sprintf("#^%s(.*)#",str_replace(array('*','\\'),array('.+','\\\\'),$strMatch)),$strRequest,$arrMatches)){

					$blMatchFound = true;

					if($arrLoader['type'] == 'path'){

						try{
							set_include_path($arrLoader['path']);
							spl_autoload_extensions($arrLoader['extension']);
							spl_autoload($arrMatches[1]);
						}catch(\Exception $resException){
							$blMatchFound = false;
						}
					}else{
						//Only send the handle
						call_user_func($arrLoader['class'],$strRequest);
					}

					break;
				}
			}

			if($blMatchFound == false){
				throw new \Exception(sprintf("TwistPHP AutoLoader: Unable to load the requested class '%s', please check to see if the file exists",$strRequest));
			}
		}
	}