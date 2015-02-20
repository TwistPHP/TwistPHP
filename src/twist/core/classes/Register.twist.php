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
	 * Register Shutdown and Event handlers, also handlers can be canceled if required
	 */
	final class Register{

		public function autoloaderPath($strMatch,$dirPath,$strExtension = '.php'){
			Autoloader::registerPath($strMatch,$dirPath,$strExtension);
		}

		public function autoloaderClass($strMatch,$strClass,$strFunction){
			Autoloader::registerClass($strMatch,$strClass,$strFunction);
		}

		public function handler($strType,$strClass,$strFunction){

			switch($strType){

				case'error':
					set_error_handler(array($strClass, $strFunction));
					break;

				case'fatal':
					$this->shutdownEvent('TwistFatalError',$strClass,$strFunction);
					break;

				case'exception':
					set_exception_handler(array($strClass, $strFunction));
					break;
			}
		}

		public function cancelHandler($strType){

			switch($strType){

				case'error':
					restore_error_handler();
					break;

				case'fatal':
					$this->cancelShutdownEvent('TwistFatalError');
					break;

				case'exception':
					restore_exception_handler();
					break;
			}
		}

		public function shutdownEvent($strEventKey,$strClass,$strFunction){
			Shutdown::registerEvent(array($strEventKey,$strClass,$strFunction));
		}

		public function cancelShutdownEvent($strEventKey){
			Shutdown::cancelEvent($strEventKey);
		}

		public function modules(){

			//Get a list of all the installed modules
			$arrModuleFolders = scandir(DIR_FRAMEWORK_MODULES);

			//Go through each module one by one and setup each ready to be used
			foreach($arrModuleFolders as $strEachModule){
				if(!in_array($strEachModule,array('.','..'))){
					include_once sprintf('%s/%s/register.php',DIR_FRAMEWORK_MODULES,$strEachModule);
				}
			}
		}

        public function interfaces(){

            //Get a list of all the installed modules
            $arrInterfaceFolders = scandir(DIR_FRAMEWORK_INTERFACES);

            //Go through each module one by one and setup each ready to be used
            foreach($arrInterfaceFolders as $strEachInterface){
                if(!in_array($strEachInterface,array('.','..'))){
                    include_once sprintf('%s/%s/register.php',DIR_FRAMEWORK_INTERFACES,$strEachInterface);
                }
            }
        }
	}