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

	if(!class_exists('Base')){
		class Base{

			/**
			 * @deprecated
			 */
			protected static function Template(){
				return self::view((count(func_get_args())) ? func_get_arg(0) : 'twist');
			}

			protected static function framework(){

				$resTwistModule = (!Instance::isObject('CoreFramework')) ? new Framework() : Instance::retrieveObject('CoreFramework');
				Instance::storeObject('CoreFramework',$resTwistModule);
				return $resTwistModule;
			}

			protected static function AJAX(){

				require_once sprintf('%sAJAX.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgAJAX')) ? new Packages\AJAX() : Instance::retrieveObject('pkgAJAX');
				Instance::storeObject('pkgAJAX',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Archive(){

				require_once sprintf('%sArchive.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgArchive')) ? new Packages\Archive() : Instance::retrieveObject('pkgArchive');
				Instance::storeObject('pkgArchive',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Asset(){

				require_once sprintf('%sAsset.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgAsset')) ? new Packages\Asset() : Instance::retrieveObject('pkgAsset');
				Instance::storeObject('pkgAsset',$resTwistModule);
				return $resTwistModule;
			}

			protected static function CSV(){

				require_once sprintf('%sCSV.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgCSV')) ? new Packages\CSV() : Instance::retrieveObject('pkgCSV');
				Instance::storeObject('pkgCSV',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Cache(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';
				require_once sprintf('%sCache.twist.php',DIR_FRAMEWORK_PACKAGES);

				//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
				if(count(func_get_args())){
					$strInstanceKey = sprintf('pkgCache-%s',$strObjectKey);
					$resTwistModule = (!Instance::isObject($strInstanceKey)) ? new Packages\Cache($strObjectKey) : Instance::retrieveObject($strInstanceKey);
					Instance::storeObject($strInstanceKey,$resTwistModule);
				}else{
					$resTwistModule = (!Instance::isObject('pkgCache')) ? new Packages\Cache($strObjectKey) : Instance::retrieveObject('pkgCache');
					Instance::storeObject('pkgCache',$resTwistModule);
				}

				return $resTwistModule;
			}

			protected static function Command(){

				require_once sprintf('%sCommand.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgCommand')) ? new Packages\Command() : Instance::retrieveObject('pkgCommand');
				Instance::storeObject('pkgCommand',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Curl(){

				require_once sprintf('%sCurl.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgCurl')) ? new Packages\Curl() : Instance::retrieveObject('pkgCurl');
				Instance::storeObject('pkgCurl',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Database(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';
				require_once sprintf('%sDatabase.twist.php',DIR_FRAMEWORK_PACKAGES);

				//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
				if(count(func_get_args())){
					$strInstanceKey = sprintf('pkgDatabase-%s',$strObjectKey);
					$resTwistModule = (!Instance::isObject($strInstanceKey)) ? new Packages\Database($strObjectKey) : Instance::retrieveObject($strInstanceKey);
					Instance::storeObject($strInstanceKey,$resTwistModule);
				}else{
					$resTwistModule = (!Instance::isObject('pkgDatabase')) ? new Packages\Database($strObjectKey) : Instance::retrieveObject('pkgDatabase');
					Instance::storeObject('pkgDatabase',$resTwistModule);
				}

				return $resTwistModule;
			}

			protected static function DateTime(){

				require_once sprintf('%sDateTime.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgDateTime')) ? new Packages\DateTime() : Instance::retrieveObject('pkgDateTime');
				Instance::storeObject('pkgDateTime',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Email(){

				require_once sprintf('%sEmail.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgEmail')) ? new Packages\Email() : Instance::retrieveObject('pkgEmail');
				Instance::storeObject('pkgEmail',$resTwistModule);
				return $resTwistModule;
			}

			protected static function File(){

				require_once sprintf('%sFile.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgFile')) ? new Packages\File() : Instance::retrieveObject('pkgFile');
				Instance::storeObject('pkgFile',$resTwistModule);
				return $resTwistModule;
			}

			protected static function FTP(){

				require_once sprintf('%sFTP.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgFTP')) ? new Packages\FTP() : Instance::retrieveObject('pkgFTP');
				Instance::storeObject('pkgFTP',$resTwistModule);
				return $resTwistModule;
			}

			protected static function ICS(){

				require_once sprintf('%sICS.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgICS')) ? new Packages\ICS() : Instance::retrieveObject('pkgICS');
				Instance::storeObject('pkgICS',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Image(){

				require_once sprintf('%sImage.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgImage')) ? new Packages\Image() : Instance::retrieveObject('pkgImage');
				Instance::storeObject('pkgImage',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Localisation(){

				require_once sprintf('%sLocalisation.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgLocalisation')) ? new Packages\Localisation() : Instance::retrieveObject('pkgLocalisation');
				Instance::storeObject('pkgLocalisation',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Route(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';
				require_once sprintf('%sRoute.twist.php',DIR_FRAMEWORK_PACKAGES);

				//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
				if(count(func_get_args())){
					$strInstanceKey = sprintf('pkgRoute-%s',$strObjectKey);
					$resTwistModule = (!Instance::isObject($strInstanceKey)) ? new Packages\Route($strObjectKey) : Instance::retrieveObject($strInstanceKey);
					Instance::storeObject($strInstanceKey,$resTwistModule);
				}else{
					$resTwistModule = (!Instance::isObject('pkgRoute')) ? new Packages\Route($strObjectKey) : Instance::retrieveObject('pkgRoute');
					Instance::storeObject('pkgRoute',$resTwistModule);
				}

				return $resTwistModule;
			}

			protected static function Session(){

				require_once sprintf('%sSession.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgSession')) ? new Packages\Session() : Instance::retrieveObject('pkgSession');
				Instance::storeObject('pkgSession',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Timer(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';
				require_once sprintf('%sTimer.twist.php',DIR_FRAMEWORK_PACKAGES);

				//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
				if(count(func_get_args())){
					$strInstanceKey = sprintf('pkgTimer-%s',$strObjectKey);
					$resTwistModule = (!Instance::isObject($strInstanceKey)) ? new Packages\Timer($strObjectKey) : Instance::retrieveObject($strInstanceKey);
					Instance::storeObject($strInstanceKey,$resTwistModule);
				}else{
					$resTwistModule = (!Instance::isObject('pkgTimer')) ? new Packages\Timer($strObjectKey) : Instance::retrieveObject('pkgTimer');
					Instance::storeObject('pkgTimer',$resTwistModule);
				}

				return $resTwistModule;
			}

			protected static function User(){

				require_once sprintf('%sUser.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgUser')) ? new Packages\User() : Instance::retrieveObject('pkgUser');
				Instance::storeObject('pkgUser',$resTwistModule);
				return $resTwistModule;
			}

			protected static function Validate(){

				require_once sprintf('%sValidate.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgValidate')) ? new Packages\Validate() : Instance::retrieveObject('pkgValidate');
				Instance::storeObject('pkgValidate',$resTwistModule);
				return $resTwistModule;
			}

			protected static function View(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';
				require_once sprintf('%sView.twist.php',DIR_FRAMEWORK_PACKAGES);

				//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
				if(count(func_get_args())){
					$strInstanceKey = sprintf('pkgView-%s',$strObjectKey);
					$resTwistModule = (!Instance::isObject($strInstanceKey)) ? new Packages\View($strObjectKey) : Instance::retrieveObject($strInstanceKey);
					Instance::storeObject($strInstanceKey,$resTwistModule);
				}else{
					$resTwistModule = (!Instance::isObject('pkgView')) ? new Packages\View($strObjectKey) : Instance::retrieveObject('pkgView');
					Instance::storeObject('pkgView',$resTwistModule);
				}

				return $resTwistModule;
			}

			protected static function XML(){

				require_once sprintf('%sXML.twist.php',DIR_FRAMEWORK_PACKAGES);

				$resTwistModule = (!Instance::isObject('pkgXML')) ? new Packages\XML() : Instance::retrieveObject('pkgXML');
				Instance::storeObject('pkgXML',$resTwistModule);
				return $resTwistModule;
			}
		}
	}