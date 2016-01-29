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

	use Twist\Classes\Instance;
	use Twist\Classes\Error;
	use Twist\Core\Utilities as Utilities;

	/**
	 * Main functionality for the Framework, this main class is that gateway to makes it all possible to use the framework in a simple yet powerful way.
	 */
	class Twist{

		protected static $blLaunched = false;
		protected static $blRecordEvents = false;

		public function __construct(){
			throw new Exception("Twist Framework can only be called statically, please refer to documentation for more details");
		}

		/**
		 * Define PHP Defines but automatically checks to see if has already been defined, if so the new define is ignored but no error is thrown.
		 * @param $strKey
		 * @param $mxdValue
		 */
		public static function define($strKey,$mxdValue){
			if(!defined($strKey)){
				define($strKey,$mxdValue);
			}
		}

		/**
		 * Return the version number of the framework, optionally you can return a shorter version number by specifying the level of detail you want (major, minor, patch, pre-release).
		 * TwistPHP adheres to the Semantic Versioning 2.0.0 standards (http://semver.org/)
		 * @param null|string $strVersionPart Pass in major, minor, patch or pre-release. Null for the full output
		 * @return string Version number of the framework
		 */
		public static function version($strVersionPart = null){

			$arrVersion = array(
				'major' => 3,
				'minor' => 0,
				'patch' => 1,
				'pre-release' => ''//pre-release can be set to 'dev'
			);

			switch($strVersionPart){
				case'major':
					$strVersion = $arrVersion['major'];
					break;
				case'minor':
					$strVersion = sprintf('%d.%d',$arrVersion['major'],$arrVersion['minor']);
					break;
				case'patch':
					$strVersion = sprintf('%d.%d.%d',$arrVersion['major'],$arrVersion['minor'],$arrVersion['patch']);
					break;
				default:

					if($arrVersion['build'] == ''){
						$strVersion = sprintf('%d.%d.%d',$arrVersion['major'],$arrVersion['minor'],$arrVersion['patch']);
					}else{
						$strVersion = sprintf('%d.%d.%d-%s',$arrVersion['major'],$arrVersion['minor'],$arrVersion['patch'],$arrVersion['pre-release']);
					}
					break;
			}

			return $strVersion;
		}

		/**
		 * Main function called by the boot.php file, this function will boot the framework setting all the variables and initialising required functionality to ensure that TwistPHP runs as expected.
		 */
		public static function launch(){

			if(self::$blLaunched === false){
				self::$blLaunched = true;

				//Get the base location of the site, based on apaches report fo the document root minus a trailing slash
				self::define('TWIST_DOCUMENT_ROOT',rtrim($_SERVER['DOCUMENT_ROOT'],'/'));

				$blAboveDocumentRoot = false;
				$strInstallationFolder = realpath(sprintf('%s/../',TWIST_FRAMEWORK));

				if(!($strInstallationFolder === TWIST_DOCUMENT_ROOT || (defined("TWIST_DOCUMENT_ROOT") && TWIST_DOCUMENT_ROOT != '' && strstr($strInstallationFolder,TWIST_DOCUMENT_ROOT)))){
					$blAboveDocumentRoot = true;
				}

				$strBaseURI = str_replace('//','','/'.trim(str_replace(TWIST_DOCUMENT_ROOT,"",dirname($_SERVER['SCRIPT_FILENAME'])),'/').'/');

				if(defined("TWIST_DOCUMENT_ROOT") && TWIST_DOCUMENT_ROOT != '' && strstr(TWIST_FRAMEWORK,TWIST_DOCUMENT_ROOT)){
					$strFrameworkURI = '/'.ltrim(str_replace(TWIST_DOCUMENT_ROOT,"",TWIST_FRAMEWORK),'/');
				}else{
					$strFrameworkURI = sprintf('%stwist/',$strBaseURI);
				}

				self::define('TWIST_FRAMEWORK_URI',$strFrameworkURI);
				self::define('TWIST_ABOVE_DOCUMENT_ROOT',$blAboveDocumentRoot);
				self::define('TWIST_BASE_PATH',dirname($_SERVER['SCRIPT_FILENAME']));
				self::define('TWIST_BASE_URI',$strBaseURI);

				date_default_timezone_set( !is_null( self::framework() -> setting('TIMEZONE') ) ? self::framework() -> setting('TIMEZONE') : 'Europe/London' );

				self::$blRecordEvents = (self::framework() -> setting('DEVELOPMENT_MODE') && self::framework() -> setting('DEVELOPMENT_EVENT_RECORDER'));

				//Log the framework boot time, this is the point in which the framework code was required
				if(self::$blRecordEvents){
					self::Timer('TwistEventRecorder')->start($_SERVER['TWIST_BOOT']);
				}

				self::define('E_TWIST_NOTICE',E_USER_NOTICE);
				self::define('E_TWIST_WARNING',E_USER_WARNING);
				self::define('E_TWIST_ERROR',E_USER_ERROR);
				self::define('E_TWIST_DEPRECATED',E_USER_DEPRECATED);
				
				//Register the PHP handlers
				self::errorHandlers();
				self::recordEvent('Handlers prepared');

				//Initialise the resource handler
				Instance::storeObject('twistCoreResources',new \Twist\Core\Models\Resources());
				self::recordEvent('Resources prepared');

				/**
				 * Override the error handlers and exception handlers and turn on AJAX debugging
				 * Note: In the future we could use this to enable the log handler instead
				 */
				self::define('TWIST_AJAX_REQUEST',array_key_exists('HTTP_X_REQUESTED_WITH',$_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

				self::showInstallWizard();
				self::phpSettings();

				self::recordEvent('Framework ready');
				self::define('TWIST_LAUNCHED',1);
			}
		}

		/**
		 * Show the install wizard, if the wizard is required to be output all existing routes will be cleared and the wizard will be served.
		 */
		protected static function showInstallWizard(){

			if(self::framework() -> settings() -> showInstallWizard()){

				if(defined("TWIST_QUICK_INSTALL")){

					//Turn off all error handlers for quick/inline installation
					self::framework()->register()->cancelHandler('error');
					self::framework()->register()->cancelHandler('fatal');
					self::framework()->register()->cancelHandler('exception');

					\Twist\Core\Models\Install::framework(json_decode(TWIST_QUICK_INSTALL,true));
					echo "200 OK - Installation Complete";
					die();
				}else{
					self::Route()->purge();
					self::Route()->setDirectory(sprintf('%sinstall-wizard/',TWIST_FRAMEWORK_VIEWS));
					self::Route()->baseView('_base.tpl');
					self::Route()->baseURI(TWIST_BASE_URI);
					self::Route()->controller('/%','\Twist\Core\Controllers\InstallWizard');
					self::Route()->serve();
				}
			}
		}

		/**
		 * Set PHP settings that will allow your site to work the way you need it too
		 */
		protected static function phpSettings(){

			if(!is_null(self::framework()->setting('PHP_MEMORY_LIMIT'))){
				ini_set('memory_limit',self::framework()->setting('PHP_MEMORY_LIMIT'));
			}

			if(!is_null(self::framework()->setting('PHP_MAX_EXECUTION'))){
				ini_set('max_execution_time',self::framework()->setting('PHP_MAX_EXECUTION'));
			}
		}

		/**
		 * Register the PHP handlers for errors, exceptions and log outputs
		 */
		protected static function errorHandlers(){

			if(self::framework()->setting('ERROR_HANDLING')){
				self::framework() -> register() -> handler('error','Twist\Classes\Error','handleError');
			}

			if(self::framework()->setting('ERROR_FATAL_HANDLING')){
				self::framework() -> register() -> handler('fatal','Twist\Classes\Error','handleFatal');
			}

			if(self::framework()->setting('ERROR_EXCEPTION_HANDLING')){
				self::framework() -> register() -> handler('exception','Twist\Classes\Error','handleException');
			}

			if(self::framework()->setting('ERROR_LOG')){
				self::framework() -> register() -> shutdownEvent('errorLog','Twist\Classes\Error','outputLog');
			}
		}

		/**
		 * Log an error message that can be output using the {messages:} template tag
		 * @param $strMessage
		 * @param null $strKey
		 */
		public static function errorMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'error');
		}

		/**
		 * Log an warning message that can be output using the {messages:} template tag
		 * @param $strMessage
		 * @param null $strKey
		 */
		public static function warningMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'warning');
		}

		/**
		 * Log an notice message that can be output using the {messages:} template tag
		 * @param $strMessage
		 * @param null $strKey
		 */
		public static function noticeMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'notice');
		}

		/**
		 * Log an success message that can be output using the {messages:} template tag
		 * @param $strMessage
		 * @param null $strKey
		 */
		public static function successMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'success');
		}

		/**
		 * Redirect the user to a new page or site by URL, optionally you can make the redirect permanent.
		 * URL redirects can be passed in as full path/URL or relative to your current URI. For example you can pass in '../../' or './test'
		 * @param $urlRedirectURL URL that the user will be redirected too
		 * @param $blPermanent Set the redirect type to be a Permanent 301 redirect
		 */
		public static function redirect($urlRedirect,$blPermanent = false){

			$urlRedirect = self::framework()->tools()->traverseURI($urlRedirect);

			header(sprintf('Location: %s',$urlRedirect),true,($blPermanent) ? 301 : 302);
			die();
		}

		/**
		 * Respond with a HTTP status page, pass in the status code that you require
		 * @param $intResponseCode Code of the required response i.e. 404
		 */
		public static function respond($intResponseCode,$strCustomDescription = null){
			Error::errorPage($intResponseCode,$strCustomDescription);
		}

		/**
		 * Dump data to the screen in a nice format with other key debug information
		 * @param null $mxdData
		 * @throws Exception
		 */
		public static function dump($mxdData = null){
			throw new \Exception(json_encode($mxdData),1200);
		}

		/**
		 * Record events on for the current page load can be logged and a time-line produced, helps with debugging.
		 * The TwistPHP event recorder only records and outputs events if DEVELOPMENT_MODE and DEVELOPMENT_EVENT_RECORDER settings are set to true|1.
		 * @param $strEventName
		 */
		public static function recordEvent($strEventName){
			if(self::$blRecordEvents){
				self::Timer('TwistEventRecorder')->log($strEventName);
			}
		}

		/**
		 * Get an array of all the recorded events from withing the TwistPHP even recorder, this will provide times and memory usage data on allow of key processing done by TwistPHP.
		 *  The TwistPHP event recorder only records and outputs events if DEVELOPMENT_MODE and DEVELOPMENT_EVENT_RECORDER settings are set to true|1.
		 * @param bool $blStopTimer
		 * @return array|mixed
		 */
		public static function getEvents($blStopTimer = false){
			return (self::$blRecordEvents) ? (($blStopTimer) ? self::Timer('TwistEventRecorder')->stop() : self::Timer('TwistEventRecorder')->results()) : array();
		}

		/**
		 * Process each message as they are added and store them for the current PHP session only
		 * @param $strMessage
		 * @param $strKey
		 * @param $strType
		 */
		protected static function messageProcess($strMessage,$strKey,$strType){

			$arrMessages = self::Cache()->read('twistUserMessages');
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

			self::Cache()->write('twistUserMessages',$arrMessages,0);
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
			$arrMessages = self::Cache()->read('twistUserMessages');

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
		 * Returns the core framework classes, these are not packages but contain some useful tools such as settings.
		 * @return \Twist\Core\Utilities\Framework
		 */
		public static function framework(){

			$resTwistUtility = (!Instance::isObject('CoreFramework')) ? new Utilities\Framework() : Instance::retrieveObject('CoreFramework');
			Instance::storeObject('CoreFramework',$resTwistUtility);
			return $resTwistUtility;
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
		 * Return an instance of the Archive utility.
		 * @return \Twist\Core\Utilities\Archive
		 */
		public static function Archive(){

			$resTwistUtility = (!Instance::isObject('pkgArchive')) ? new Utilities\Archive() : Instance::retrieveObject('pkgArchive');
			Instance::storeObject('pkgArchive',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Asset utility.
		 * @return \Twist\Core\Utilities\Asset
		 */
		public static function Asset(){

			$resTwistUtility = (!Instance::isObject('pkgAsset')) ? new Utilities\Asset() : Instance::retrieveObject('pkgAsset');
			Instance::storeObject('pkgAsset',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Cookie utility.
		 * @return \Twist\Core\Utilities\Cookie
		 */
		public static function Cookie(){

			$resTwistUtility = (!Instance::isObject('pkgCookie')) ? new Utilities\Cookie() : Instance::retrieveObject('pkgCookie');
			Instance::storeObject('pkgCookie',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the CSV utility.
		 * @return \Twist\Core\Utilities\CSV
		 */
		public static function CSV(){

			$resTwistUtility = (!Instance::isObject('pkgCSV')) ? new Utilities\CSV() : Instance::retrieveObject('pkgCSV');
			Instance::storeObject('pkgCSV',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Cache utility.
		 * @return \Twist\Core\Utilities\Cache
		 */
		public static function Cache(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('pkgCache-%s',$strObjectKey);
				$resTwistUtility = (!Instance::isObject($strInstanceKey)) ? new Utilities\Cache($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistUtility);
			}else{
				$resTwistUtility = (!Instance::isObject('pkgCache')) ? new Utilities\Cache($strObjectKey) : Instance::retrieveObject('pkgCache');
				Instance::storeObject('pkgCache',$resTwistUtility);
			}

			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Command utility.
		 * @return \Twist\Core\Utilities\Command
		 */
		public static function Command(){

			$resTwistUtility = (!Instance::isObject('pkgCommand')) ? new Utilities\Command() : Instance::retrieveObject('pkgCommand');
			Instance::storeObject('pkgCommand',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Curl utility.
		 * @return \Twist\Core\Utilities\Curl
		 */
		public static function Curl(){

			$resTwistUtility = (!Instance::isObject('pkgCurl')) ? new Utilities\Curl() : Instance::retrieveObject('pkgCurl');
			Instance::storeObject('pkgCurl',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Database utility.
		 * @return \Twist\Core\Utilities\Database
		 */
		public static function Database(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('pkgDatabase-%s',$strObjectKey);
				$resTwistUtility = (!Instance::isObject($strInstanceKey)) ? new Utilities\Database($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistUtility);
			}else{
				$resTwistUtility = (!Instance::isObject('pkgDatabase')) ? new Utilities\Database($strObjectKey) : Instance::retrieveObject('pkgDatabase');
				Instance::storeObject('pkgDatabase',$resTwistUtility);
			}

			return $resTwistUtility;
		}

		/**
		 * Return an instance of the DateTime utility.
		 * @return \Twist\Core\Utilities\DateTime
		 */
		public static function DateTime(){

			$resTwistUtility = (!Instance::isObject('pkgDateTime')) ? new Utilities\DateTime() : Instance::retrieveObject('pkgDateTime');
			Instance::storeObject('pkgDateTime',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Device utility.
		 * @return \Twist\Core\Utilities\Device
		 */
		public static function Device(){

			$resTwistUtility = (!Instance::isObject('pkgDevice')) ? new Utilities\Device() : Instance::retrieveObject('pkgDevice');
			Instance::storeObject('pkgDevice',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Email utility.
		 * @return \Twist\Core\Utilities\Email
		 */
		public static function Email(){

			$resTwistUtility = (!Instance::isObject('pkgEmail')) ? new Utilities\Email() : Instance::retrieveObject('pkgEmail');
			Instance::storeObject('pkgEmail',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the File utility.
		 * @return \Twist\Core\Utilities\File
		 */
		public static function File(){

			$resTwistUtility = (!Instance::isObject('pkgFile')) ? new Utilities\File() : Instance::retrieveObject('pkgFile');
			Instance::storeObject('pkgFile',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the FTP utility.
		 * @return \Twist\Core\Utilities\FTP
		 */
		public static function FTP(){

			$resTwistUtility = (!Instance::isObject('pkgFTP')) ? new Utilities\FTP() : Instance::retrieveObject('pkgFTP');
			Instance::storeObject('pkgFTP',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the ICS utility.
		 * @return \Twist\Core\Utilities\ICS
		 */
		public static function ICS(){

			$resTwistUtility = (!Instance::isObject('pkgICS')) ? new Utilities\ICS() : Instance::retrieveObject('pkgICS');
			Instance::storeObject('pkgICS',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Image utility.
		 * @return \Twist\Core\Utilities\Image
		 */
		public static function Image(){

			$resTwistUtility = (!Instance::isObject('pkgImage')) ? new Utilities\Image() : Instance::retrieveObject('pkgImage');
			Instance::storeObject('pkgImage',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Localisation utility.
		 * @return \Twist\Core\Utilities\Localisation
		 */
		public static function Localisation(){

			$resTwistUtility = (!Instance::isObject('pkgLocalisation')) ? new Utilities\Localisation() : Instance::retrieveObject('pkgLocalisation');
			Instance::storeObject('pkgLocalisation',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Route utility.
		 * @return \Twist\Core\Utilities\Route
		 */
		public static function Route(){

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('pkgRoute-%s',func_get_arg(0));
				$resTwistUtility = (!Instance::isObject($strInstanceKey)) ? new Utilities\Route(func_get_arg(0)) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistUtility);
			}else{
				$resTwistUtility = (!Instance::isObject('pkgRoute')) ? new Utilities\Route() : Instance::retrieveObject('pkgRoute');
				Instance::storeObject('pkgRoute',$resTwistUtility);
			}

			return $resTwistUtility;
		}

		/**
		 * Run through all registered instances of the route object, check for domain name matches and serve the correct route accordingly.
		 * @param bool $blExitOnComplete
		 */
		public static function ServeRoutes($blExitOnComplete = true){

			$blHTTPS = (!empty($_SERVER['HTTPS'])) ? true : false;
			$strHost = $_SERVER['HTTP_HOST'];
			$strViewerIP = $_SERVER['REMOTE_ADDR'];

			$resFallback = null;
			$blFoundMatch = false;
			$arrInstances = Instance::listObjects();

			foreach($arrInstances as $strInstanceKey){

				if(substr($strInstanceKey,0,8) == 'pkgRoute'){

					//Get the Route instance
					$resInstance = Instance::retrieveObject($strInstanceKey);

					//Get all the listeners for this route
					$arrRouteListeners = $resInstance->listeners();

					if(is_null($arrRouteListeners['domain'])){

						//Store as a fallback, if no domain/alias match has been found run the fallback
						$resFallback = $resInstance;
					}elseif($arrRouteListeners['enabled'] && (strtolower($arrRouteListeners['domain']) == $strHost || in_array($strHost,$arrRouteListeners['aliases']))){

						//Check to see if the domain matches the string
						$blFoundMatch = true;
						$resInstance->serve($blExitOnComplete);
						break;
					}
				}
			}

			//If no match was found and a fallback is set, serve the fallback
			if($blFoundMatch == false && !is_null($resFallback)){
				$resFallback->serve($blExitOnComplete);
			}

			//Nothing was found and we need to exit, serve a 404 page
			if($blExitOnComplete){
				self::respond(404);
			}
		}

		/**
		 * Return an instance of the Session utility.
		 * @return \Twist\Core\Utilities\Session
		 */
		public static function Session(){

			$resTwistUtility = (!Instance::isObject('pkgSession')) ? new Utilities\Session() : Instance::retrieveObject('pkgSession');
			Instance::storeObject('pkgSession',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Timer utility.
		 * @return \Twist\Core\Utilities\Timer
		 */
		public static function Timer(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('pkgTimer-%s',$strObjectKey);
				$resTwistUtility = (!Instance::isObject($strInstanceKey)) ? new Utilities\Timer($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistUtility);
			}else{
				$resTwistUtility = (!Instance::isObject('pkgTimer')) ? new Utilities\Timer($strObjectKey) : Instance::retrieveObject('pkgTimer');
				Instance::storeObject('pkgTimer',$resTwistUtility);
			}

			return $resTwistUtility;
		}

		/**
		 * Return an instance of the User utility.
		 * @return \Twist\Core\Utilities\User
		 */
		public static function User(){

			$resTwistUtility = (!Instance::isObject('pkgUser')) ? new Utilities\User() : Instance::retrieveObject('pkgUser');
			Instance::storeObject('pkgUser',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the Validate utility.
		 * @return \Twist\Core\Utilities\Validate
		 */
		public static function Validate(){

			$resTwistUtility = (!Instance::isObject('pkgValidate')) ? new Utilities\Validate() : Instance::retrieveObject('pkgValidate');
			Instance::storeObject('pkgValidate',$resTwistUtility);
			return $resTwistUtility;
		}

		/**
		 * Return an instance of the View utility.
		 * @return \Twist\Core\Utilities\View
		 */
		public static function View(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('pkgView-%s',$strObjectKey);
				$resTwistUtility = (!Instance::isObject($strInstanceKey)) ? new Utilities\View($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistUtility);
			}else{
				$resTwistUtility = (!Instance::isObject('pkgView')) ? new Utilities\View($strObjectKey) : Instance::retrieveObject('pkgView');
				Instance::storeObject('pkgView',$resTwistUtility);
			}

			return $resTwistUtility;
		}

		/**
		 * Return an instance of the XML utility.
		 * @return \Twist\Core\Utilities\XML
		 */
		public static function XML(){

			$resTwistUtility = (!Instance::isObject('pkgXML')) ? new Utilities\XML() : Instance::retrieveObject('pkgXML');
			Instance::storeObject('pkgXML',$resTwistUtility);
			return $resTwistUtility;
		}
	}