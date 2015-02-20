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

	namespace Twist\Core;

    /**
     * Handle all interfaces related enquiries, for instance if you want to know if a package is installed or what version it is.
     */
    final class Interfaces{

        protected $arrInterfaces = array();

        /**
         * Check to see that a interface is installed and usable, optional throw an exception of the module dosnt exist
         * @param $strInterface
         * @param $blThrowException
         * @return bool
         */
        public function exists($strInterface,$blThrowException = false){

            if($blThrowException && !array_key_exists($strInterface,$this->arrInterfaces)){
                throw new \Exception(sprintf("The interface '%s' has not been installed or does not exist",$strInterface));
            }

            return (array_key_exists($strInterface,$this->arrInterfaces));
        }

        /**
         * Get all the current information for any installed module
         * @param $strInterface
         * @return array
         */
        public function information($strInterface){
            $arrParts = explode('\\',$strInterface);
            $strInterface = array_pop($arrParts);
            return (array_key_exists($strInterface,$this->arrInterfaces)) ? $this->arrInterfaces[$strInterface] : array();
        }

        /**
         * Load interface into the system to be used
         * @param $strInterface
         */
        public function load($strInterface,$strURI,$mxdBaseTemplate=true){
            if($this->exists($strInterface,true)){

				//Get the contents of the load file
                include_once sprintf('%s/load.php',$this->arrInterfaces[$strInterface]['path']);

				$strInterfaceClass = sprintf('\Twist\Interfaces\%s',$strInterface);

				//Call the interface
				$objInterface = new $strInterfaceClass($strInterface);
				$objInterface->baseURI($strURI);
				$objInterface->baseTemplate($mxdBaseTemplate);
				$objInterface->load();
				$objInterface->serve();
            }
        }

        /**
         * Register the interface for use in the framework
         * @param $strClassName
         * @param $dirInterfacePath
         */
        public function register($strClassName,$dirInterfacePath=null){

		    $strPath = sprintf('%s%s',(is_null($dirInterfacePath)) ? DIR_FRAMEWORK_INTERFACES : $dirInterfacePath,$strClassName);
            $strURI = str_replace(BASE_LOCATION,"",$strPath);

            $arrInformation = json_decode(file_get_contents(sprintf('%s/info.json',$strPath)),true);

            if(!array_key_exists($strClassName,$this->arrInterfaces)){
                $this->arrInterfaces[$strClassName] = array('type' => null,'name' => null,'description' => null,'version' => null,'author' => null,'class' => null,'instances' => null,'path' => '','uri' => '','extensions' => array(),'installed' => 0);
            }

            //Register the module for use withing the system
            $this->arrInterfaces[$strClassName]['type'] = 'Module';
            $this->arrInterfaces[$strClassName]['name'] = $arrInformation['name'];
            $this->arrInterfaces[$strClassName]['description'] = $arrInformation['description'];
            $this->arrInterfaces[$strClassName]['version'] = $arrInformation['version'];
            $this->arrInterfaces[$strClassName]['author'] = $arrInformation['author'];
            $this->arrInterfaces[$strClassName]['class'] = $strClassName;
            $this->arrInterfaces[$strClassName]['instances'] = false;//Too do later
            $this->arrInterfaces[$strClassName]['path'] = $strPath;
            $this->arrInterfaces[$strClassName]['uri'] = $strURI;
            $this->arrInterfaces[$strClassName]['installed'] = 1;
        }

        /**
         * Get an array of all the registered modules/packages in the system
         * @return array
         */
        public function getAll(){
            return $this->arrInterfaces;
        }
    }
