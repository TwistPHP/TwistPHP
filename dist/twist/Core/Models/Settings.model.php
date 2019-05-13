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
	
	namespace Twist\Core\Models;
	
	/**
	* Get, Set and update all the framework settings. Manage the update of all default tables created by packages and modules.
	*/
	class Settings{
	
	    public $arrSettings = array();
	    public $arrSettingsInfo = array();
	    public $intStatus = 0;

	    protected $blFileConfig = true;
	    protected $blShowInstallWizard = false;
	    protected $blLoaded = false;

	    public function __construct(){

	        //Check if the framework is setup or not setup
	        $this->blShowInstallWizard = !(defined('TWIST_APP_CONFIG') && file_exists(sprintf("%sconfig.php",TWIST_APP_CONFIG)));
	        $this->load();
	    }

		/**
		 * Return a boolean status, if true the framework has not been setup.
		 * @return bool
		 */
		public function showInstallWizard(){
			return $this->blShowInstallWizard;
		}

		/**
		 * Set to true will ensure that the framework runs using a file configuration rather than the Database settings table.
		 * @param bool $blFileConfig
		 */
		public function fileConfigOverride($blFileConfig = true){
			$this->blFileConfig = $blFileConfig;
		}

		/**
		 * Load all the framework settings into the system, these could either be stored in a Database or a file depending on how TwistPHP has been configured.
		 * @throws \Exception
		 */
	    protected function load(){

	        if(!$this->blLoaded){

				$this->arrSettings = $this->arrSettingsInfo = array();

				if($this->blShowInstallWizard){
					$this->loadTempSettings();
				}else{

					if(TWIST_DATABASE_PROTOCOL === 'none'){

						$this->blFileConfig = true;

						//Get the settings from a json settings file
						$strSettingsFile = defined('TWIST_APP_CONFIG') ? sprintf('%ssettings.json',TWIST_APP_CONFIG) : null;

						if(file_exists($strSettingsFile)){
							$jsonData = file_get_contents($strSettingsFile);
							$this->arrSettingsInfo = json_decode($jsonData,true);
						}
					}else{
						$this->blFileConfig = false;
						\Twist::Database()->connect();

						$this->arrSettingsInfo = \Twist::Database()->records(sprintf('%ssettings',TWIST_DATABASE_TABLE_PREFIX))->find();
					}
	            }

	            if(count($this->arrSettingsInfo)){

	                foreach($this->arrSettingsInfo as $arrEachSetting){

	                    //Set the output types of boolean,integer and floats to their correct data types
	                    if($arrEachSetting['type'] === 'boolean'){
	                        $arrEachSetting['value'] = ($arrEachSetting['value'] == '1' || $arrEachSetting['value'] == 1);
	                    }elseif($arrEachSetting['type'] === 'float' && preg_match('#^[0-9]+\.[0-9]+$#',$arrEachSetting['value'])){
	                        settype( $arrEachSetting['value'] , 'float' );
	                    }elseif($arrEachSetting['type'] === 'integer' && is_numeric($arrEachSetting['value'])){
	                        settype( $arrEachSetting['value'] , 'integer' );
	                    }elseif($arrEachSetting['type'] === 'json' && is_array($arrEachSetting['value'])){
							$arrEachSetting['value'] = json_encode($arrEachSetting['value']);
						}

	                    $this->arrSettings[$arrEachSetting['key']] = $arrEachSetting['value'];
	                }

		            $this->arrSettingsInfo = \Twist::framework()->tools()->arrayReindex($this->arrSettingsInfo,'key');
	            }

	            $this->blLoaded = true;
	        }
	    }

		/**
		 * Before the framework has been setup it will be running on its default settings that are defined in the frameworks install folder.
		 */
		protected function loadTempSettings(){

			//Process the core settings of the framework
			$strCoreJSON = sprintf('%sData/settings.json',TWIST_PACKAGE_INSTALL);

			$jsonData = file_get_contents($strCoreJSON);
			$this->arrSettingsInfo = json_decode($jsonData,true);

			foreach($this->arrSettingsInfo as $strKey => $arrSettings){
				$arrSettings['key'] = $strKey;

				//Fix JSON encoded data
				if($arrSettings['type'] === 'json' && is_array($arrSettings['default'])){
					$arrSettings['default'] = json_encode($arrSettings['default']);
				}

				$arrSettings['value'] = $arrSettings['default'];
				$arrSettings['package'] = 'core';

				$this->arrSettingsInfo[$strKey] = $arrSettings;
			}
		}

		/**
		 * Get the setting value that is associated with the provided key, is the key does not exists NULL will be returned.
		 * @param string $strKey
		 * @return null|mixed
		 */
	    public function get($strKey){
	        return (array_key_exists($strKey,$this->arrSettings)) ? $this->arrSettings[$strKey] : null;
	    }

		/**
		 * Get all information that is associated with the provided key, is the key does not exists NULL will be returned.
		 * @param string $strKey
		 * @return null
		 */
	    public function getInfo($strKey){
	        return (array_key_exists($strKey,$this->arrSettingsInfo)) ? $this->arrSettingsInfo[$strKey] : null;
	    }

		/**
		 * Set a value against a particular setting Key, the value will be stored in the Database or File depending on how TwistPHP has been configured.
		 * @param string $strKey
		 * @param mixed $mxdData
		 * @return bool
		 */
	    public function set($strKey,$mxdData){

	        $blOut = false;

	        if($this->blFileConfig){

	            if(array_key_exists($strKey,$this->arrSettings)){
	                $this->arrSettingsInfo[$strKey]['value'] = $mxdData;
	                $this->arrSettings[$strKey] = $mxdData;

	                //Export the settings back to the setting file
	                file_put_contents(sprintf('%ssettings.json',TWIST_APP_CONFIG),json_encode($this->arrSettingsInfo));
	                $blOut = true;
	            }
	        }else{

		        //Only update the setting in the table if the key exists
	            $resResult = \Twist::Database()->query("UPDATE `%s`.`%ssettings`
	                                SET `value` = '%s'
	                                WHERE `key` = '%s'
	                                LIMIT 1",
		            TWIST_DATABASE_NAME,
		            TWIST_DATABASE_TABLE_PREFIX,
		            $mxdData,
		            strtoupper($strKey)
	            );

	            if($resResult->status() && $resResult->affectedRows()){
	                $this->arrSettings[$strKey] = $mxdData;
					$this->arrSettingsInfo[$strKey]['value'] = $mxdData;
	                $blOut = true;
	            }
	        }

	        return $blOut;
	    }

		/**
		 * Remove/Uninstall a particular setting or group of settings form the Database or File depending on how TwistPHP has been configured.
		 * @param string $strPackage
		 * @param null $strKey
		 */
		public function uninstall($strPackage,$strKey = null){

			if($this->blFileConfig){

				if(!is_null($strKey)){
					if(array_key_exists($strKey,$this->arrSettings)){
						unset($this->arrSettingsInfo[$strKey]);
						unset($this->arrSettings[$strKey]);

						//Export the settings back to the setting file
						file_put_contents(sprintf('%ssettings.json', TWIST_APP_CONFIG), json_encode($this->arrSettingsInfo));
					}
				}else{
					foreach($this->arrSettingsInfo as $strKey => $arrInfo){

						if($arrInfo['package'] == $strPackage){
							unset($this->arrSettingsInfo[$strKey]);
							unset($this->arrSettings[$strKey]);
						}
					}

					//Export the settings back to the setting file
					file_put_contents(sprintf('%ssettings.json', TWIST_APP_CONFIG), json_encode($this->arrSettingsInfo));
				}

			}else{
				if(is_null($strKey)){
					\Twist::Database()->query("DELETE FROM `%s`.`%ssettings` WHERE `package` = '%s'",
						TWIST_DATABASE_NAME,
						TWIST_DATABASE_TABLE_PREFIX,
						$strPackage
					);
				}else{
					\Twist::Database()->query("DELETE FROM `%s`.`%ssettings` WHERE `package` = '%s' AND `key` = '%s'",
						TWIST_DATABASE_NAME,
						TWIST_DATABASE_TABLE_PREFIX,
						$strPackage,
						$strKey
					);
				}
			}
		}

		/**
		 * Install/Add a new setting into the Database or File depending on how TwistPHP has been configured.
		 * @param string $strPackage
		 * @param string $strGroup
		 * @param string $strKey
		 * @param mixed $mxdValue
		 * @param string $strTitle
		 * @param string $strDescription
		 * @param string $strDefault
		 * @param string $strType
		 * @param string $strOptions
		 * @param bool $blNull
		 * @return bool|null
		 * @throws \Exception
		 */
		public function install($strPackage,$strGroup,$strKey,$mxdValue,$strTitle,$strDescription,$strDefault,$strType,$strOptions,$blNull = false){

			if($strType === 'json' && is_array($strDefault)){
				$strDefault = json_encode($strDefault);
			}

			if($strType === 'json' && is_array($mxdValue)){
				$mxdValue = json_encode($mxdValue);
			}

			if(TWIST_DATABASE_PROTOCOL === 'none'){

				$strSettingsJSON = sprintf('%ssettings.json',TWIST_APP_CONFIG);

				if(is_writable(TWIST_APP_CONFIG)){
					if(!file_exists($strSettingsJSON)){
						file_put_contents($strSettingsJSON,'{}');
					}
				}else{
					throw new \Exception("Error, Twist Framework could not setup its core settings file. Please ensure that the twist/config folder is writable");
				}

				$rawJSON = file_get_contents($strSettingsJSON);
				$arrSettings = json_decode($rawJSON,true);

				if(array_key_exists($strKey,$arrSettings)){

					$arrSettings[$strKey] = array(
						'group' => $strGroup,
						'title' => $strTitle,
						'description' => $strDescription,
						'default' => $strDefault,
						'type' => $strType,
						'options' => $strOptions,
						'null' => ($blNull) ? 1 : 0,
						'deprecated' => 0
					);
				}else{

					$arrSettings[$strKey] = array(
						'package' => $strPackage,
						'group' => $strGroup,
						'key' => $strKey,
						'value' => $mxdValue,
						'title' => $strTitle,
						'description' => $strDescription,
						'default' => $strDefault,
						'type' => $strType,
						'options' => $strOptions,
						'null' => ($blNull) ? 1 : 0,
						'deprecated' => 0
					);
				}

				file_put_contents($strSettingsJSON,json_encode($arrSettings));
				return true;
			}else{

				return \Twist::Database()->query("INSERT INTO `%s`.`%ssettings`
									SET `package` = '%s',
										`group` = '%s',
										`key` = '%s',
										`value` = '%s',
										`title` = '%s',
										`description` = '%s',
										`default` = '%s',
										`type` = '%s',
										`options` = '%s',
										`null` = '%s',
										`deprecated` = '0'
								ON DUPLICATE KEY UPDATE
										`group` = '%s',
										`title` = '%s',
										`description` = '%s',
										`default` = '%s',
										`type` = '%s',
										`options` = '%s',
										`null` = '%s',
										`deprecated` = '0'",
					TWIST_DATABASE_NAME,
					TWIST_DATABASE_TABLE_PREFIX,
					$strPackage,
					$strGroup,
					strtoupper($strKey),
					$mxdValue,
					$strTitle,
					$strDescription,
					$strDefault,
					$strType,
					$strOptions,
					($blNull) ? '1' : '0',
					$strGroup,
					$strTitle,
					$strDescription,
					$strDefault,
					$strType,
					$strOptions,
					($blNull) ? '1' : '0'
				)->status();
			}
		}
	}