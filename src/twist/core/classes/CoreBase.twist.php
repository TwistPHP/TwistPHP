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
	use Twist\Core\Packages as Packages;

	if(!class_exists('CoreBase')){
		class CoreBase{

			/**
			 * Used when calling 3rd Party framework packages
			 * @param $strModuleName
			 * @param $arrArguments
			 * @return mixed
			 */
			public static function __callStatic($strModuleName, $arrArguments){

				$strObjectRef = sprintf('pkg%s',$strModuleName);
				$strModule = sprintf('\Package\%s',$strModuleName);

				$resTwistModule = (!Instance::isObject($strObjectRef)) ? new $strModule() : Instance::retrieveObject($strObjectRef);
				Instance::storeObject($strObjectRef,$resTwistModule);
				return $resTwistModule;
			}

			/**
			 * @deprecated
			 */
			public static function Template(){
				return self::view((count(func_get_args())) ? func_get_arg(0) : 'twist');
			}

			public static function framework(){

				$resTwistModule = (!Instance::isObject('CoreFramework')) ? new Framework() : Instance::retrieveObject('CoreFramework');
				Instance::storeObject('CoreFramework',$resTwistModule);
				return $resTwistModule;
			}

			public static function AJAX(){

				$resTwistModule = (!Instance::isObject('pkgAJAX')) ? new Packages\AJAX() : Instance::retrieveObject('pkgAJAX');
				Instance::storeObject('pkgAJAX',$resTwistModule);
				return $resTwistModule;
			}

			public static function Archive(){

				$resTwistModule = (!Instance::isObject('pkgArchive')) ? new Packages\Archive() : Instance::retrieveObject('pkgArchive');
				Instance::storeObject('pkgArchive',$resTwistModule);
				return $resTwistModule;
			}

			public static function Asset(){

				$resTwistModule = (!Instance::isObject('pkgAsset')) ? new Packages\Asset() : Instance::retrieveObject('pkgAsset');
				Instance::storeObject('pkgAsset',$resTwistModule);
				return $resTwistModule;
			}

			public static function CSV(){

				$resTwistModule = (!Instance::isObject('pkgCSV')) ? new Packages\CSV() : Instance::retrieveObject('pkgCSV');
				Instance::storeObject('pkgCSV',$resTwistModule);
				return $resTwistModule;
			}

			public static function Cache(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

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

			public static function Command(){

				$resTwistModule = (!Instance::isObject('pkgCommand')) ? new Packages\Command() : Instance::retrieveObject('pkgCommand');
				Instance::storeObject('pkgCommand',$resTwistModule);
				return $resTwistModule;
			}

			public static function Curl(){

				$resTwistModule = (!Instance::isObject('pkgCurl')) ? new Packages\Curl() : Instance::retrieveObject('pkgCurl');
				Instance::storeObject('pkgCurl',$resTwistModule);
				return $resTwistModule;
			}

			public static function Database(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

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

			public static function DateTime(){

				$resTwistModule = (!Instance::isObject('pkgDateTime')) ? new Packages\DateTime() : Instance::retrieveObject('pkgDateTime');
				Instance::storeObject('pkgDateTime',$resTwistModule);
				return $resTwistModule;
			}

			public static function Email(){

				$resTwistModule = (!Instance::isObject('pkgEmail')) ? new Packages\Email() : Instance::retrieveObject('pkgEmail');
				Instance::storeObject('pkgEmail',$resTwistModule);
				return $resTwistModule;
			}

			public static function File(){

				$resTwistModule = (!Instance::isObject('pkgFile')) ? new Packages\File() : Instance::retrieveObject('pkgFile');
				Instance::storeObject('pkgFile',$resTwistModule);
				return $resTwistModule;
			}

			public static function FTP(){

				$resTwistModule = (!Instance::isObject('pkgFTP')) ? new Packages\FTP() : Instance::retrieveObject('pkgFTP');
				Instance::storeObject('pkgFTP',$resTwistModule);
				return $resTwistModule;
			}

			public static function ICS(){

				$resTwistModule = (!Instance::isObject('pkgICS')) ? new Packages\ICS() : Instance::retrieveObject('pkgICS');
				Instance::storeObject('pkgICS',$resTwistModule);
				return $resTwistModule;
			}

			public static function Image(){

				$resTwistModule = (!Instance::isObject('pkgImage')) ? new Packages\Image() : Instance::retrieveObject('pkgImage');
				Instance::storeObject('pkgImage',$resTwistModule);
				return $resTwistModule;
			}

			public static function Localisation(){

				$resTwistModule = (!Instance::isObject('pkgLocalisation')) ? new Packages\Localisation() : Instance::retrieveObject('pkgLocalisation');
				Instance::storeObject('pkgLocalisation',$resTwistModule);
				return $resTwistModule;
			}

			public static function Route(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

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

			public static function Session(){

				$resTwistModule = (!Instance::isObject('pkgSession')) ? new Packages\Session() : Instance::retrieveObject('pkgSession');
				Instance::storeObject('pkgSession',$resTwistModule);
				return $resTwistModule;
			}

			public static function Timer(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

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

			public static function User(){

				$resTwistModule = (!Instance::isObject('pkgUser')) ? new Packages\User() : Instance::retrieveObject('pkgUser');
				Instance::storeObject('pkgUser',$resTwistModule);
				return $resTwistModule;
			}

			public static function Validate(){

				$resTwistModule = (!Instance::isObject('pkgValidate')) ? new Packages\Validate() : Instance::retrieveObject('pkgValidate');
				Instance::storeObject('pkgValidate',$resTwistModule);
				return $resTwistModule;
			}

			public static function View(){

				$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

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

			public static function XML(){

				$resTwistModule = (!Instance::isObject('pkgXML')) ? new Packages\XML() : Instance::retrieveObject('pkgXML');
				Instance::storeObject('pkgXML',$resTwistModule);
				return $resTwistModule;
			}
		}
	}