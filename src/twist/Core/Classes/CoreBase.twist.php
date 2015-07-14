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

	/**
	 * Class CoreBase
	 * This class has all the core packages defined, this is mainly because when developing in most IDEs with code completion you will get auto-complete functionality when calling "Twist::"
	 * @package Twist\Core\Classes
	 */
	class CoreBase{

		/**
		 * Process each message as they are added and store them for the current PHP session only
		 * @param $strMessage
		 * @param $strKey
		 * @param $strType
		 */
		protected static function messageProcess($strMessage,$strKey,$strType){

			$arrMessages = \Twist::Cache()->read('twistUserMessages');
			$arrMessages = (is_null($arrMessages)) ? array() : $arrMessages;

			$strMessageKey = sprintf('%s-%s',$strKey,$strType);

			if(array_key_exists($strMessageKey,$arrMessages)){
				$arrMessages[$strMessageKey]['messages'][] = $strMessage;
			}else{

				$arrMessages[$strMessageKey] = array(
					'type' => $strType,
					'key' => $strKey,
					'messages' => array($strMessage)
				);
			}

			\Twist::Cache()->write('twistUserMessages',$arrMessages,0);
		}

		/**
		 * Process the user messages to be output into the view
		 *
		 * Tag: Tag can be any one of the below but not multiple.
		 * {messages:all|error|notice|warning|success}
		 *
		 * Tag Parameters:
		 * combine - default is true (on)
		 * key - pass in the required messages by key can be pipe (|) separated
		 * style - determine th output styling, currently can be plain, rich or HTML
		 *
		 * Example Tag:
		 * {messages:error,combine=true,key=andi|dan,style=html}
		 *
		 * @param $strReference
		 * @param array $arrParameters
		 * @return string
		 */
		public static function messageHandler($strReference,$arrParameters = array()){

			$strOut = '';
			$arrCombine = array();
			$arrMessages = \Twist::Cache()->read('twistUserMessages');

			//Combine is enabled by default it not passed in (combines all messages by type)
			$blCombine = (!array_key_exists('combine',$arrParameters) || $arrParameters['combine']);

			$strStyle = array_key_exists('style',$arrParameters) ? $arrParameters['style'] : null;
			$mxdFilterByKey = array_key_exists('key',$arrParameters) ? $arrParameters['key'] : null;

			if(is_array($arrMessages)){
				foreach($arrMessages as $strUniqueKey => $arrData){

					if($strReference == 'all' || $strReference == $arrData['type']){

						if(is_null($mxdFilterByKey) || (is_array($mxdFilterByKey) && in_array($arrData['key'],$mxdFilterByKey)) || $mxdFilterByKey == $arrData['key']){

							switch($strStyle){

								case'plain':
									$strOut .= implode("\n",$arrData['messages']);
									break;

								case'rich':
									$strOut .= implode("<br>",$arrData['messages']);
									break;

								case'html':
								default:

									if($blCombine){
										if(!array_key_exists($arrData['type'],$arrCombine)){
											$arrCombine[$arrData['type']] = implode("<br>",$arrData['messages']);
										}else{
											$arrCombine[$arrData['type']] .= '<br>'.implode("<br>",$arrData['messages']);
										}
									}else{
										$strOut .= self::View()->build(sprintf('%smessages/%s.tpl',TWIST_FRAMEWORK_VIEWS,$arrData['type']),array('key' => $arrData['key'],'type' => $arrData['type'],'message' => implode("<br>",$arrData['messages'])));
									}
									break;
							}
						}
					}
				}

				//If we are looking at a combined output, we need to run a final process on the combined array
				if($strOut === '' && count($arrCombine)){
					foreach($arrCombine as $strType => $strMessage){
						$strOut .= self::View()->build(sprintf('%smessages/%s.tpl',TWIST_FRAMEWORK_VIEWS,$strType),array('key' => '','type' => $strType,'message' => $strMessage));
					}
				}
			}

			return $strOut;
		}

		/**
		 * Call 3rd parky packages in the framework located in your packages folder
		 * Alternatively packages can be called '$resMyPackage = new Package\MyPackage();'
		 * @param $strPackageName
		 * @return mixed
		 */
		public static function package($strPackageName){

			$strObjectRef = sprintf('userPackage_%s',$strPackageName);
			$strPackage = sprintf('\Packages\%s\Models\%s',$strPackageName,$strPackageName);

			$resPackage = (!Instance::isObject($strObjectRef)) ? new $strPackage() : Instance::retrieveObject($strObjectRef);
			Instance::storeObject($strObjectRef,$resPackage);
			return $resPackage;
		}

		/**
		 * Returns the core framework classes, these are not packages but contain some useful tools such as settings.
		 * @return \Twist\Core\Classes\Framework
		 */
		public static function framework(){

			$resTwistModule = (!Instance::isObject('CoreFramework')) ? new Framework() : Instance::retrieveObject('CoreFramework');
			Instance::storeObject('CoreFramework',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Archive package.
		 * @return \Twist\Core\Packages\Archive
		 */
		public static function Archive(){

			$resTwistModule = (!Instance::isObject('pkgArchive')) ? new Packages\Archive() : Instance::retrieveObject('pkgArchive');
			Instance::storeObject('pkgArchive',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Asset package.
		 * @return \Twist\Core\Packages\Asset
		 */
		public static function Asset(){

			$resTwistModule = (!Instance::isObject('pkgAsset')) ? new Packages\Asset() : Instance::retrieveObject('pkgAsset');
			Instance::storeObject('pkgAsset',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Cookie package.
		 * @return \Twist\Core\Packages\Cookie
		 */
		public static function Cookie(){

			$resTwistModule = (!Instance::isObject('pkgCookie')) ? new Packages\Cookie() : Instance::retrieveObject('pkgCookie');
			Instance::storeObject('pkgCookie',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the CSV package.
		 * @return \Twist\Core\Packages\CSV
		 */
		public static function CSV(){

			$resTwistModule = (!Instance::isObject('pkgCSV')) ? new Packages\CSV() : Instance::retrieveObject('pkgCSV');
			Instance::storeObject('pkgCSV',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Cache package.
		 * @return \Twist\Core\Packages\Cache
		 */
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

		/**
		 * Return an instance of the Command package.
		 * @return \Twist\Core\Packages\Command
		 */
		public static function Command(){

			$resTwistModule = (!Instance::isObject('pkgCommand')) ? new Packages\Command() : Instance::retrieveObject('pkgCommand');
			Instance::storeObject('pkgCommand',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Curl package.
		 * @return \Twist\Core\Packages\Curl
		 */
		public static function Curl(){

			$resTwistModule = (!Instance::isObject('pkgCurl')) ? new Packages\Curl() : Instance::retrieveObject('pkgCurl');
			Instance::storeObject('pkgCurl',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Database package.
		 * @return \Twist\Core\Packages\Database
		 */
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

		/**
		 * Return an instance of the DateTime package.
		 * @return \Twist\Core\Packages\DateTime
		 */
		public static function DateTime(){

			$resTwistModule = (!Instance::isObject('pkgDateTime')) ? new Packages\DateTime() : Instance::retrieveObject('pkgDateTime');
			Instance::storeObject('pkgDateTime',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Email package.
		 * @return \Twist\Core\Packages\Email
		 */
		public static function Email(){

			$resTwistModule = (!Instance::isObject('pkgEmail')) ? new Packages\Email() : Instance::retrieveObject('pkgEmail');
			Instance::storeObject('pkgEmail',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the File package.
		 * @return \Twist\Core\Packages\File
		 */
		public static function File(){

			$resTwistModule = (!Instance::isObject('pkgFile')) ? new Packages\File() : Instance::retrieveObject('pkgFile');
			Instance::storeObject('pkgFile',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the FTP package.
		 * @return \Twist\Core\Packages\FTP
		 */
		public static function FTP(){

			$resTwistModule = (!Instance::isObject('pkgFTP')) ? new Packages\FTP() : Instance::retrieveObject('pkgFTP');
			Instance::storeObject('pkgFTP',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the ICS package.
		 * @return \Twist\Core\Packages\ICS
		 */
		public static function ICS(){

			$resTwistModule = (!Instance::isObject('pkgICS')) ? new Packages\ICS() : Instance::retrieveObject('pkgICS');
			Instance::storeObject('pkgICS',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Image package.
		 * @return \Twist\Core\Packages\Image
		 */
		public static function Image(){

			$resTwistModule = (!Instance::isObject('pkgImage')) ? new Packages\Image() : Instance::retrieveObject('pkgImage');
			Instance::storeObject('pkgImage',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Localisation package.
		 * @return \Twist\Core\Packages\Localisation
		 */
		public static function Localisation(){

			$resTwistModule = (!Instance::isObject('pkgLocalisation')) ? new Packages\Localisation() : Instance::retrieveObject('pkgLocalisation');
			Instance::storeObject('pkgLocalisation',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Route package.
		 * @return \Twist\Core\Packages\Route
		 */
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

		/**
		 * Return an instance of the Session package.
		 * @return \Twist\Core\Packages\Session
		 */
		public static function Session(){

			$resTwistModule = (!Instance::isObject('pkgSession')) ? new Packages\Session() : Instance::retrieveObject('pkgSession');
			Instance::storeObject('pkgSession',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Timer package.
		 * @return \Twist\Core\Packages\Timer
		 */
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

		/**
		 * Return an instance of the User package.
		 * @return \Twist\Core\Packages\User
		 */
		public static function User(){

			$resTwistModule = (!Instance::isObject('pkgUser')) ? new Packages\User() : Instance::retrieveObject('pkgUser');
			Instance::storeObject('pkgUser',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the Validate package.
		 * @return \Twist\Core\Packages\Validate
		 */
		public static function Validate(){

			$resTwistModule = (!Instance::isObject('pkgValidate')) ? new Packages\Validate() : Instance::retrieveObject('pkgValidate');
			Instance::storeObject('pkgValidate',$resTwistModule);
			return $resTwistModule;
		}

		/**
		 * Return an instance of the View package.
		 * @return \Twist\Core\Packages\View
		 */
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

		/**
		 * Return an instance of the XML package.
		 * @return \Twist\Core\Packages\XML
		 */
		public static function XML(){

			$resTwistModule = (!Instance::isObject('pkgXML')) ? new Packages\XML() : Instance::retrieveObject('pkgXML');
			Instance::storeObject('pkgXML',$resTwistModule);
			return $resTwistModule;
		}
	}