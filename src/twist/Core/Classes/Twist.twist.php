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

	use Twist\Core\Classes\CoreBase;

	class Twist extends CoreBase{

		protected static $blLaunched = false;
		protected static $blRecordEvents = false;

		public function __construct(){
			throw new Exception("Twist Framework can only be called statically, please refer to documentation for more details");
		}

		public static function define($strKey,$mxdValue){
			if(!defined($strKey)){
				define($strKey,$mxdValue);
			}
		}

		public static function launch(){

			if(self::$blLaunched === false){
				self::$blLaunched = true;

				//Get the base location of the site, based on apaches report fo the document root minus a trailing slash
				self::define('BASE_LOCATION',rtrim($_SERVER['DOCUMENT_ROOT'],'/'));

				$blAboveDocumentRoot = false;

				if(rtrim(DIR_BASE,'/') == dirname($_SERVER['SCRIPT_FILENAME'])){
					$strSiteRoot = '/';
				}elseif(strstr(rtrim(DIR_BASE,'/'),dirname($_SERVER['SCRIPT_FILENAME']))){
					$strSiteRoot = '/'.ltrim(str_replace(dirname($_SERVER['SCRIPT_FILENAME']),"",rtrim(DIR_BASE,'/')),'/');
				}else{
					$strSiteRoot = '/'.ltrim(str_replace(rtrim(DIR_BASE,'/'),"",dirname($_SERVER['SCRIPT_FILENAME'])),'/');
					$blAboveDocumentRoot = true;
				}

				$strBaseURI = str_replace('//','','/'.trim(str_replace(BASE_LOCATION,"",dirname($_SERVER['SCRIPT_FILENAME'])),'/').'/');

				if(strstr(DIR_FRAMEWORK,BASE_LOCATION)){
					$strFrameworkURI = '/'.ltrim(str_replace(BASE_LOCATION,"",DIR_FRAMEWORK),'/');
				}else{
					$strFrameworkURI = sprintf('%stwist/',$strBaseURI);
				}

				self::define('FRAMEWORK_URI',$strFrameworkURI);
				self::define('TWIST_ABOVE_DOCUMENT_ROOT',$blAboveDocumentRoot);
				self::define('BASE_PATH',$strSiteRoot);
				self::define('BASE_URI',$strBaseURI);

				date_default_timezone_set( Twist::framework() -> setting('TIMEZONE') );
				$strLocation = rtrim(Twist::framework() -> setting('SITE_BASE'),'/');

				self::$blRecordEvents = (self::framework() -> setting('DEVELOPMENT_MODE') && self::framework() -> setting('DEVELOPMENT_EVENT_RECORDER'));

				require_once sprintf('%sError.twist.php',DIR_FRAMEWORK_CLASSES);

				self::define('E_TWIST_NOTICE',E_USER_NOTICE);
				self::define('E_TWIST_WARNING',E_USER_WARNING);
				self::define('E_TWIST_ERROR',E_USER_ERROR);
				self::define('E_TWIST_DEPRECATED',E_USER_DEPRECATED);

				self::define('ERROR_LOG',Twist::framework() -> setting('ERROR_LOG'));
				self::define('ERROR_SCREEN',Twist::framework() -> setting('ERROR_SCREEN'));

				/**
				 * Override the error handlers and exception handlers and turn on AJAX debugging
				 * Note: In the future we could use this to enable the log handler instead
				 */
				self::define('TWIST_AJAX_REQUEST',array_key_exists('HTTP_X_REQUESTED_WITH',$_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

				//Register the PHP handlers
				self::errorHandlers();

				//Initalise the resource handler
				\Twist\Core\Classes\Instance::storeObject('twistCoreResources',new \Twist\Core\Classes\Resources());

				//Register all the installed packages
				Twist::framework() -> package() -> getInstalled();

				//Log the framework boot time, this is the point in which the framework code was required
				Twist::Timer('TwistEventRecorder')->start($_SERVER['TWIST_BOOT']);

				//Register the default PHP package extensions
				Twist::framework() -> package() -> extend('View','asset',array('module' => 'Asset','function' => 'viewExtension'));
				Twist::framework() -> package() -> extend('View','file',array('module' => 'File','function' => 'viewExtension'));
				Twist::framework() -> package() -> extend('View','image',array('module' => 'Image','function' => 'viewExtension'));
				Twist::framework() -> package() -> extend('View','session',array('module' => 'Session','function' => 'viewExtension'));
				Twist::framework() -> package() -> extend('View','user',array('module' => 'User','function' => 'viewExtension'));

				//Register the framework resources handler into the template system
				Twist::framework() -> package() -> extend('View','resource',array('instance' => 'twistCoreResources','function' => 'viewExtension'));

				self::coreResources();
				self::showSetup();
				self::phpSettings();
				self::maintenanceMode();

				self::recordEvent('Framework ready');
				self::define('TWIST_LAUNCHED',1);
			}
		}

        protected static function coreResources(){

            $strResourcesURI = sprintf('%sCore/Resources/',FRAMEWORK_URI);

            $arrResources = array(
                'arable' => sprintf('%sarable/arable.min.css',$strResourcesURI),
                'arable-reset' => sprintf('%sarable/arable-reset.min.css',$strResourcesURI),
                'font-awesome' => sprintf('%sfont-awesome/css/font-awesome.min.css',$strResourcesURI),
                'jquery' => sprintf('%sjquery/jquery-2.1.3.min.js',$strResourcesURI),
                'jquery-legacy' => sprintf('%sjquery/jquery-1.11.2.min.js',$strResourcesURI),
                'logo' => sprintf('%stwist/logos/logo.png',$strResourcesURI),
                'logo-favicon' => sprintf('%stwist/logos/favicon.ico',$strResourcesURI),
                'logo-32' => sprintf('%stwist/logos/logo-32.ico',$strResourcesURI),
                'logo-48' => sprintf('%stwist/logos/logo-48.ico',$strResourcesURI),
                'logo-57' => sprintf('%stwist/logos/logo-57.ico',$strResourcesURI),
                'logo-64' => sprintf('%stwist/logos/logo-64.ico',$strResourcesURI),
                'logo-72' => sprintf('%stwist/logos/logo-72.ico',$strResourcesURI),
                'logo-96' => sprintf('%stwist/logos/logo-96.ico',$strResourcesURI),
                'logo-114' => sprintf('%stwist/logos/logo-114.ico',$strResourcesURI),
                'logo-128' => sprintf('%stwist/logos/logo-128.ico',$strResourcesURI),
                'logo-144' => sprintf('%stwist/logos/logo-144.ico',$strResourcesURI),
                'logo-192' => sprintf('%stwist/logos/logo-192.ico',$strResourcesURI),
                'logo-256' => sprintf('%stwist/logos/logo-256.ico',$strResourcesURI),
                'logo-512' => sprintf('%stwist/logos/logo-512.ico',$strResourcesURI),
                'logo-640' => sprintf('%stwist/logos/logo-640.ico',$strResourcesURI),
                'logo-800' => sprintf('%stwist/logos/logo-800.ico',$strResourcesURI),
                'logo-1024' => sprintf('%stwist/logos/logo-1024.ico',$strResourcesURI),
                'logo-large' => sprintf('%stwist/logos/logo-512.png',$strResourcesURI),
                'logo-small' => sprintf('%stwist/logos/logo-32.png',$strResourcesURI),
                'modernizr' => sprintf('%smodernizr/modernizr-2.8.3.min.js',$strResourcesURI),
                'rummage' => sprintf('%srummage/rummage.min.js',$strResourcesURI),
                'shadow-js' => sprintf('%sshadow-js/shadow-js.min.js',$strResourcesURI),
                'unsemantic' => sprintf('%sunsemantic/unsemantic-grid-responsive-tablet-no-ie7.css',$strResourcesURI),
                'resources_uri' => $strResourcesURI,
                'uri' => FRAMEWORK_URI
            );

            //Integrate the basic core href tag support - legacy support
            Twist::framework() -> package() -> extend('Template','core',$arrResources);
        }

		/**
		 * Show the setup process if required
		 */
		protected static function showSetup(){

			if(Twist::framework() -> settings() -> showSetup()){

				self::Route()->purge();
				self::Route()->setDirectory(sprintf('%ssetup/',DIR_FRAMEWORK_VIEWS));
				self::Route()->baseView('_base.tpl');
				self::Route()->baseURI(BASE_URI);
				self::Route()->controller('/%','\Twist\Core\Controllers\Setup');
				self::Route()->serve();
			}
		}

		/**
		 * Set PHP settings that will allow your site to work the way you need it too
		 */
		protected static function phpSettings(){

			if(!is_null(Twist::framework()->setting('PHP_MEMORY_LIMIT'))){
				ini_set('memory_limit',Twist::framework()->setting('PHP_MEMORY_LIMIT'));
			}

			if(!is_null(Twist::framework()->setting('PHP_MAX_EXECUTION'))){
				ini_set('max_executions_time',Twist::framework()->setting('PHP_MAX_EXECUTION'));
			}
		}

		/**
		 * Decide if the maintenance page should be output, this can be used when undergoing updates/development
		 * @todo add in a level 0 override for users to browse site in maintenance mode
		 */
		protected static function maintenanceMode(){

			if(Twist::framework()->setting('MAINTENANCE_MODE')){
				\Twist\Core\Classes\Error::errorPage(503);
			}
		}

		/**
		 * Register the PHP handlers for errors, exceptions and log outputs
		 */
		protected static function errorHandlers(){

			if(Twist::framework()->setting('ERROR_HANDLING')){
				Twist::framework() -> register() -> handler('error','Twist\Core\Classes\Error','handleError');
			}

			if(Twist::framework()->setting('ERROR_FATAL_HANDLING')){
				Twist::framework() -> register() -> handler('fatal','Twist\Core\Classes\Error','handleFatal');
			}

			if(Twist::framework()->setting('ERROR_EXCEPTION_HANDLING')){
				Twist::framework() -> register() -> handler('exception','Twist\Core\Classes\Error','handleException');
			}

			if(Twist::framework()->setting('ERROR_LOG')){
				Twist::framework() -> register() -> shutdownEvent('errorLog','Twist\Core\Classes\Error','outputLog');
			}
		}

		/**
		 * Redirect the user to a new page or site by URL, optionally you can make the redirect permanent.
		 * URL redirects can be passed in as full path/URL or relative to your current URI. For example you can pass in '../../' or './test'
		 * @param $urlRedirectURL URL that the user will be redirected too
		 * @param $blPermanent Set the redirect type to be a Permanent 301 redirect
		 */
		public static function redirect($urlRedirect,$blPermanent = false){

			$urlRedirect = \Twist::framework()->tools()->traverseURI($urlRedirect);

			header(sprintf('Location: %s',$urlRedirect),true,($blPermanent) ? 301 : 302);
			die();
		}

		/**
		 * Respond with a HTTP status page, pass in the status code that you require
		 * @param $intResponseCode Code of the required response i.e. 404
		 */
		public static function respond($intResponseCode){
			\Twist\Core\Classes\Error::errorPage($intResponseCode);
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
		 * Record events on for the current page load can be logged and a timeline produced, helps with debugging
		 * @param $strEventName
		 */
		public static function recordEvent($strEventName){
			if(self::$blRecordEvents){
				Twist::Timer('TwistEventRecorder')->log($strEventName);
			}
		}

		public static function getEvents($blStopTimer = false){
			return (self::$blRecordEvents) ? (($blStopTimer) ? \Twist::Timer('TwistEventRecorder')->stop() : \Twist::Timer('TwistEventRecorder')->results()) : array();
		}
	}