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
		public $arrClassLookup = array();
		public $arrClassLoaded = array();
		public $strAutoloadCache = null;

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
			$this->strAutoloadCache = sprintf('%s/../../config/autoload.cache',dirname(__FILE__));
			$this->arrClassLookup = (file_exists($this->strAutoloadCache)) ? json_decode(file_get_contents($this->strAutoloadCache),true) : array();
			spl_autoload_register(array($this,'load'));
		}

		public function load($strRequest){

			$blMatchFound = false;

			//Fix for matches that are in 'Twist\Core\Classes' namespace
			if(!strstr($strRequest,'\\')){
				$strRequest = sprintf('%s\\%s',__NAMESPACE__,$strRequest);
			}

			if(!array_key_exists($strRequest,$this->arrClassLoaded) && array_key_exists($strRequest,$this->arrClassLookup) && file_exists($this->arrClassLookup[$strRequest])){
				require_once $this->arrClassLookup[$strRequest];
			}elseif(!array_key_exists($strRequest,$this->arrClassLoaded)){

				//If in lookup and not exists then kill
				if(array_key_exists($strRequest,$this->arrClassLookup) && !file_exists($this->arrClassLookup[$strRequest])){
					$this->arrClassLookup = array();
					unlink($this->strAutoloadCache);
				}

				foreach($this->arrRegisteredLoaders as $strMatch => $arrLoader){

					$regxMatchURI = null;
					if(strstr($strMatch,'{') && strstr($strMatch,'}')){
						$regxMatchNamespace = sprintf("#^(?<autoload_reg>%s)#i",str_replace(array('*',"\\","{","}"),array(".+","\\\\","(?<al_",">[^\\\\]+)",""),$strMatch));
					}else{
						$regxMatchNamespace = sprintf("#^%s(.*)#",str_replace(array('*','\\'),array('.+','\\\\'),$strMatch));
					}

					//Find a match for the file to be auto loaded
					if(preg_match($regxMatchNamespace,$strRequest,$arrMatches)){

						$blMatchFound = true;

						if($arrLoader['type'] == 'path'){

							//This is abit messy but allow {brackets} in auto loaders as replacements from the match to the string
							//Using this method the file name must be caught at the end in a tag and place at the end of the path
							if(array_key_exists('autoload_reg',$arrMatches)){

								foreach($arrMatches as $strKey => $strValue){
									if(substr($strKey,0,3) == 'al_'){
										$strFind = "{".trim(substr($strKey,3))."}";
										$arrLoader['path'] = str_replace($strFind,$strValue,$arrLoader['path']);
									}
								}

								$strRequireFile = sprintf('%s%s',$arrLoader['path'],$arrLoader['extension']);
							}else{
								$strRequireFile = sprintf('%s/%s%s',$arrLoader['path'],str_replace('\\','/',$arrMatches[1]),$arrLoader['extension']);
							}

							if(file_exists($strRequireFile)){
								require_once $strRequireFile;
								$this->arrClassLookup[$strRequest] = $strRequireFile;
								$this->arrClassLoaded[$strRequest] = $strRequireFile;

								//@todo register a shutdown function to store this file after page complete and served
								file_put_contents($this->strAutoloadCache,json_encode($this->arrClassLookup));
							}else{
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
	}