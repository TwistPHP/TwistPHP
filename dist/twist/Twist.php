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

	use Twist\Classes\Instance;
	use Twist\Classes\Error;
	use Twist\Core\Helpers as Helpers;

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
		 * @param string $strKey
		 * @param mixed $mxdValue
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
				'major' => 4,
				'minor' => 0,
				'patch' => 0,
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

					if($arrVersion['pre-release'] == ''){
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
				self::define('TWIST_BASE_URL',\Twist::framework()->setting('SITE_PROTOCOL').'://'.\Twist::framework()->setting('SITE_HOST').'/'.TWIST_BASE_URI);

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

					\Packages\install\Models\Install::framework(json_decode(TWIST_QUICK_INSTALL,true));
					echo "200 OK - Installation Complete";
					die();
				}else{
					self::Route()->purge();
					self::Route()->setDirectory(TWIST_PACKAGE_INSTALL.'Views');
					self::Route()->baseView('_base.tpl');
					self::Route()->baseURI(TWIST_BASE_URI);
					self::Route()->controller('/%','\Packages\install\Controllers\InstallWizard');
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
		 * @param string $strMessage
		 * @param null $strKey
		 */
		public static function errorMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'error');
		}

		/**
		 * Log an warning message that can be output using the {messages:} template tag
		 * @param string $strMessage
		 * @param null $strKey
		 */
		public static function warningMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'warning');
		}

		/**
		 * Log an notice message that can be output using the {messages:} template tag
		 * @param string $strMessage
		 * @param null $strKey
		 */
		public static function noticeMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'notice');
		}

		/**
		 * Log an success message that can be output using the {messages:} template tag
		 * @param string $strMessage
		 * @param null $strKey
		 */
		public static function successMessage($strMessage,$strKey = null){
			self::messageProcess($strMessage,$strKey,'success');
		}

		/**
		 * Redirect the user to a new page or site by URL, optionally you can make the redirect permanent.
		 * URL redirects can be passed in as full path/URL or relative to your current URI. For example you can pass in '../../' or './test'
		 * @param string $urlRedirect URL that the user will be redirected too
		 * @param bool $blPermanent Set the redirect type to be a Permanent 301 redirect
		 */
		public static function redirect($urlRedirect,$blPermanent = false){

			$urlRedirect = self::framework()->tools()->traverseURI($urlRedirect);

			header(sprintf('Location: %s',$urlRedirect),true,($blPermanent) ? 301 : 302);
			die();
		}

		/**
		 * Respond with a HTTP status page, pass in the status code that you require
		 * @param int $intResponseCode Code of the required response i.e. 404
		 * @param null|string $strCustomDescription
		 * @param boolean $blExitOnComplete Set false will output error and continue (Used for testing)
		 */
		public static function respond($intResponseCode,$strCustomDescription = null,$blExitOnComplete = true){
			Error::response($intResponseCode,$strCustomDescription,$blExitOnComplete);
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
		 * @param string $strEventName
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
		 * @param string $strMessage
		 * @param string $strKey
		 * @param string $strType
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
		 * @param string $strReference
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
         * Auto runs the corn jobs, example commands below:
         * twist_cron=on php -t "/doc/root/if/required" ./index.php
         * twist_cron=off php -t "/doc/root/if/required" ./index.php
         *
         */
		public static function sheduledtasks(){

			//If the code has been run by commandline and has the URI of index.php/cron
			if(php_sapi_name() == "cli" && getenv('twist_cron') == 'on'){

                echo "== Starting TwistCron Manager ==\n";

				$arrTasks = Twist\Core\Models\ScheduledTasks::activeTasks();

				foreach($arrTasks as $arrEachTask){
					Twist\Core\Models\ScheduledTasks::run($arrEachTask['id']);
				}

				echo "== Finished TwistCron Manager ==\n";
				die();
			}
		}

		/**
		 * Returns the core framework classes, these are not packages but contain some useful tools such as settings.
		 * @return \Twist\Core\Helpers\Framework
		 */
		public static function framework(){

			$resTwistHelper = (!Instance::isObject('CoreFramework')) ? new Helpers\Framework() : Instance::retrieveObject('CoreFramework');
			Instance::storeObject('CoreFramework',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Call 3rd parky packages in the framework located in your packages folder
		 * Alternatively packages can be called '$resMyPackage = new Package\MyPackage();'
		 * @param string $strPackageName
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
		 * Return an instance of the Archive helper.
		 * @return \Twist\Core\Helpers\Archive
		 */
		public static function Archive(){

			$resTwistHelper = (!Instance::isObject('helperArchive')) ? new Helpers\Archive() : Instance::retrieveObject('helperArchive');
			Instance::storeObject('helperArchive',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Asset helper.
		 * @return \Twist\Core\Helpers\Asset
		 */
		public static function Asset(){

			$resTwistHelper = (!Instance::isObject('helperAsset')) ? new Helpers\Asset() : Instance::retrieveObject('helperAsset');
			Instance::storeObject('helperAsset',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Cookie helper.
		 * @return \Twist\Core\Helpers\Cookie
		 */
		public static function Cookie(){

			$resTwistHelper = (!Instance::isObject('helperCookie')) ? new Helpers\Cookie() : Instance::retrieveObject('helperCookie');
			Instance::storeObject('helperCookie',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the CSV helper.
		 * @return \Twist\Core\Helpers\CSV
		 */
		public static function CSV(){

			$resTwistHelper = (!Instance::isObject('helperCSV')) ? new Helpers\CSV() : Instance::retrieveObject('helperCSV');
			Instance::storeObject('helperCSV',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Cache helper.
		 * @return \Twist\Core\Helpers\Cache
		 */
		public static function Cache(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('helperCache-%s',$strObjectKey);
				$resTwistHelper = (!Instance::isObject($strInstanceKey)) ? new Helpers\Cache($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistHelper);
			}else{
				$resTwistHelper = (!Instance::isObject('helperCache')) ? new Helpers\Cache($strObjectKey) : Instance::retrieveObject('helperCache');
				Instance::storeObject('helperCache',$resTwistHelper);
			}

			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Command helper.
		 * @return \Twist\Core\Helpers\Command
		 */
		public static function Command(){

			$resTwistHelper = (!Instance::isObject('helperCommand')) ? new Helpers\Command() : Instance::retrieveObject('helperCommand');
			Instance::storeObject('helperCommand',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Curl helper.
		 * @return \Twist\Core\Helpers\Curl
		 */
		public static function Curl(){

			$resTwistHelper = (!Instance::isObject('helperCurl')) ? new Helpers\Curl() : Instance::retrieveObject('helperCurl');
			Instance::storeObject('helperCurl',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Database helper.
		 * @return \Twist\Core\Helpers\Database
		 */
		public static function Database(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('helperDatabase-%s',$strObjectKey);
				$resTwistHelper = (!Instance::isObject($strInstanceKey)) ? new Helpers\Database($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistHelper);
			}else{
				$resTwistHelper = (!Instance::isObject('helperDatabase')) ? new Helpers\Database($strObjectKey) : Instance::retrieveObject('helperDatabase');
				Instance::storeObject('helperDatabase',$resTwistHelper);
			}

			return $resTwistHelper;
		}

		/**
		 * Return an instance of the DateTime helper.
		 * @return \Twist\Core\Helpers\DateTime
		 */
		public static function DateTime(){

			$resTwistHelper = (!Instance::isObject('helperDateTime')) ? new Helpers\DateTime() : Instance::retrieveObject('helperDateTime');
			Instance::storeObject('helperDateTime',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Device helper.
		 * @return \Twist\Core\Helpers\Device
		 */
		public static function Device(){

			$resTwistHelper = (!Instance::isObject('helperDevice')) ? new Helpers\Device() : Instance::retrieveObject('helperDevice');
			Instance::storeObject('helperDevice',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Email helper.
		 * @return \Twist\Core\Helpers\Email
		 */
		public static function Email(){

			$resTwistHelper = (!Instance::isObject('helperEmail')) ? new Helpers\Email() : Instance::retrieveObject('helperEmail');
			Instance::storeObject('helperEmail',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the File helper.
		 * @return \Twist\Core\Helpers\File
		 */
		public static function File(){

			$resTwistHelper = (!Instance::isObject('helperFile')) ? new Helpers\File() : Instance::retrieveObject('helperFile');
			Instance::storeObject('helperFile',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the FTP helper.
		 * @return \Twist\Core\Helpers\FTP
		 */
		public static function FTP(){

			$resTwistHelper = (!Instance::isObject('helperFTP')) ? new Helpers\FTP() : Instance::retrieveObject('helperFTP');
			Instance::storeObject('helperFTP',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the ICS helper.
		 * @return \Twist\Core\Helpers\ICS
		 */
		public static function ICS(){

			$resTwistHelper = (!Instance::isObject('helperICS')) ? new Helpers\ICS() : Instance::retrieveObject('helperICS');
			Instance::storeObject('helperICS',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Image helper.
		 * @return \Twist\Core\Helpers\Image
		 */
		public static function Image(){

			$resTwistHelper = (!Instance::isObject('helperImage')) ? new Helpers\Image() : Instance::retrieveObject('helperImage');
			Instance::storeObject('helperImage',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Localisation helper.
		 * @return \Twist\Core\Helpers\Localisation
		 */
		public static function Localisation(){

			$resTwistHelper = (!Instance::isObject('helperLocalisation')) ? new Helpers\Localisation() : Instance::retrieveObject('helperLocalisation');
			Instance::storeObject('helperLocalisation',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Route helper.
		 * @return \Twist\Core\Helpers\Route
		 */
		public static function Route(){

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('helperRoute-%s',func_get_arg(0));
				$resTwistHelper = (!Instance::isObject($strInstanceKey)) ? new Helpers\Route(func_get_arg(0)) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistHelper);
			}else{
				$resTwistHelper = (!Instance::isObject('helperRoute')) ? new Helpers\Route() : Instance::retrieveObject('helperRoute');
				Instance::storeObject('helperRoute',$resTwistHelper);
			}

			return $resTwistHelper;
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

				if(substr($strInstanceKey,0,11) == 'helperRoute'){

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
		 * Return an instance of the Session helper.
		 * @return \Twist\Core\Helpers\Session
		 */
		public static function Session(){

			$resTwistHelper = (!Instance::isObject('helperSession')) ? new Helpers\Session() : Instance::retrieveObject('helperSession');
			Instance::storeObject('helperSession',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Timer helper.
		 * @return \Twist\Core\Helpers\Timer
		 */
		public static function Timer(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('helperTimer-%s',$strObjectKey);
				$resTwistHelper = (!Instance::isObject($strInstanceKey)) ? new Helpers\Timer($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistHelper);
			}else{
				$resTwistHelper = (!Instance::isObject('helperTimer')) ? new Helpers\Timer($strObjectKey) : Instance::retrieveObject('helperTimer');
				Instance::storeObject('helperTimer',$resTwistHelper);
			}

			return $resTwistHelper;
		}

		/**
		 * Return an instance of the User helper.
		 * @return \Twist\Core\Helpers\User
		 */
		public static function User(){

			$resTwistHelper = (!Instance::isObject('helperUser')) ? new Helpers\User() : Instance::retrieveObject('helperUser');
			Instance::storeObject('helperUser',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the Validate helper.
		 * @return \Twist\Core\Helpers\Validate
		 */
		public static function Validate(){

			$resTwistHelper = (!Instance::isObject('helperValidate')) ? new Helpers\Validate() : Instance::retrieveObject('helperValidate');
			Instance::storeObject('helperValidate',$resTwistHelper);
			return $resTwistHelper;
		}

		/**
		 * Return an instance of the View helper.
		 * @return \Twist\Core\Helpers\View
		 */
		public static function View(){

			$strObjectKey = (count(func_get_args())) ? func_get_arg(0) : 'twist';

			//Could be done in 2 lines of code but PHP editors are not smart enough to auto-complete
			if(count(func_get_args())){
				$strInstanceKey = sprintf('helperView-%s',$strObjectKey);
				$resTwistHelper = (!Instance::isObject($strInstanceKey)) ? new Helpers\View($strObjectKey) : Instance::retrieveObject($strInstanceKey);
				Instance::storeObject($strInstanceKey,$resTwistHelper);
			}else{
				$resTwistHelper = (!Instance::isObject('helperView')) ? new Helpers\View($strObjectKey) : Instance::retrieveObject('helperView');
				Instance::storeObject('helperView',$resTwistHelper);
			}

			return $resTwistHelper;
		}

		/**
		 * Return an instance of the XML helper.
		 * @return \Twist\Core\Helpers\XML
		 */
		public static function XML(){

			$resTwistHelper = (!Instance::isObject('helperXML')) ? new Helpers\XML() : Instance::retrieveObject('helperXML');
			Instance::storeObject('helperXML',$resTwistHelper);
			return $resTwistHelper;
		}
	}
