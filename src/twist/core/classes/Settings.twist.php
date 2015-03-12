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
	* Get, Set and update all the framework settings. Manage the update of all default tables created by packages and modules.
	*/
	class Settings{
	
        public $arrSettings = array();
        public $arrSettingsInfo = array();
        public $intStatus = 0;

        protected $blFileConfig = true;
        protected $blShowSetup = false;
        protected $blLoaded = false;

        public function __construct(){

            //Check if the framework is setup or not setup
            $this->blShowSetup = !(defined('DIR_APP_CONFIG') && file_exists(sprintf("%sconfig.php",DIR_APP_CONFIG)));
            $this->load();
        }

		public function showSetup(){
			return $this->blShowSetup;
		}

		public function fileConfigOverride($blFileConfig = true){
			$this->blFileConfig = $blFileConfig;
		}

        protected function load(){

            if($this->blLoaded == false){

				$this->arrSettings = $this->arrSettingsInfo = array();

				if($this->blShowSetup){
					$this->loadTempSettings();
				}else{

					if(DATABASE_PROTOCOL == 'none'){

						$this->blFileConfig = true;

						//Get the settings from a json settings file
						$strSettingsFile = defined('DIR_APP_CONFIG') ? sprintf('%ssettings.json',DIR_APP_CONFIG) : null;

						if(file_exists($strSettingsFile)){
							$jsonData = file_get_contents($strSettingsFile);
							$this->arrSettingsInfo = json_decode($jsonData,true);
						}
					}else{
						$this->blFileConfig = false;
						\Twist::Database()->connect();

						$this->arrSettingsInfo = \Twist::Database()->getAll(sprintf('%ssettings',DATABASE_TABLE_PREFIX));
					}
                }

                if(count($this->arrSettingsInfo)){

                    foreach($this->arrSettingsInfo as $arrEachSetting){

                        //Set the output types of boolean,integer and floats to their correct data types
                        if($arrEachSetting['type'] == 'boolean'){
                            $arrEachSetting['value'] = ($arrEachSetting['value'] == '1' || $arrEachSetting['value'] == 1);
                        }elseif($arrEachSetting['type'] == 'float' && preg_match('#^[0-9]+\.[0-9]+$#',$arrEachSetting['value'])){
                            settype( $arrEachSetting['value'] , 'float' );
                        }elseif($arrEachSetting['type'] == 'integer' && is_numeric($arrEachSetting['value'])){
                            settype( $arrEachSetting['value'] , 'integer' );
                        }

                        $this->arrSettings[$arrEachSetting['key']] = $arrEachSetting['value'];
                    }
                }

                $this->blLoaded = true;
            }
        }

		protected function loadTempSettings(){

			//Process the core settings of the framework
			$strCoreJSON = sprintf('%ssettings.json',DIR_FRAMEWORK_INSTALL);

			$jsonData = file_get_contents($strCoreJSON);
			$this->arrSettingsInfo = json_decode($jsonData,true);

			foreach($this->arrSettingsInfo as $strKey => $arrSettings){
				$arrSettings['key'] = $strKey;
				$arrSettings['value'] = $arrSettings['default'];
				$this->arrSettingsInfo[$strKey] = $arrSettings;
			}
		}

        public function get($strKey){
            return (array_key_exists($strKey,$this->arrSettings)) ? $this->arrSettings[$strKey] : null;
        }

        public function set($strKey,$mxdData){

            $blOut = false;

            if($this->blFileConfig){

                if(array_key_exists($strKey,$this->arrSettings)){
                    $this->arrSettingsInfo[$strKey]['value'] = $mxdData;
                    $this->arrSettings[$strKey] = $mxdData;

                    //Export the settings back to the setting file
                    file_put_contents(sprintf('%ssettings.json',DIR_APP_CONFIG),json_encode($this->arrSettingsInfo));
                    $blOut = true;
                }
            }else{

                $objDB = \Twist::Database();

                //Only update the setting in the table if the key exists
                $strSQL = sprintf("UPDATE `%s`.`%ssettings`
                                    SET `value` = '%s'
                                    WHERE `key` = '%s'
                                    LIMIT 1",
                    DATABASE_NAME,
                    DATABASE_TABLE_PREFIX,
                    $objDB->escapeString($mxdData),
                    $objDB->escapeString(strtoupper($strKey))
                );

                if($objDB->query($strSQL) && $objDB->getAffectedRows()){
                    $this->arrSettings[$strKey] = $mxdData;
					$this->arrSettingsInfo[$strKey]['value'] = $mxdData;
                    $blOut = true;
                }
            }

            return $blOut;
        }
	}