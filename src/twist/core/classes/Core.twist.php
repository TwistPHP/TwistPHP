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

	if(!class_exists('Twist')){
		class Twist extends Twist\Core\BaseModules{

			protected static $blLaunched = false;

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
					self::define('FRAMEWORK_URI','/'.ltrim(str_replace(BASE_LOCATION,"",DIR_FRAMEWORK),'/'));
					self::define('BASE_URI','/'.ltrim(str_replace(BASE_LOCATION,"",DIR_BASE),'/'));

					date_default_timezone_set( Twist::framework() -> setting('TIMEZONE') );
					$strLocation = rtrim(Twist::framework() -> setting('SITE_BASE'),'/');

					Twist::define('DIR_CACHE',sprintf('%s/%scache/',rtrim(DIR_BASE,'/'),($strLocation == '') ? '' : $strLocation.'/'));
					Twist::define('DIR_VIEWS',sprintf('%s/%sviews/',rtrim(DIR_BASE,'/'),($strLocation == '') ? '' : $strLocation.'/'));

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

					//Register all the packages, this is to allow extensions
					Twist::framework() -> module() -> create('AJAX',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Archive',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Asset',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Cache',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('CSV',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Curl',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Database',true,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('DateTime',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Email',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('File',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('FTP',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Image',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Localisation',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Route',false,null,null,'TwistRoute');
					Twist::framework() -> module() -> create('Session',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('User',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('Validate',false,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('View',true,null,null,'TwistPackage');
					Twist::framework() -> module() -> create('XML',false,null,null,'TwistPackage');

					//Register the default PHP package extensions
					Twist::framework() -> module() -> extend('View','asset',array('module' => 'Asset','function' => 'viewExtension'));
					Twist::framework() -> module() -> extend('View','file',array('module' => 'File','function' => 'viewExtension'));
					Twist::framework() -> module() -> extend('View','image',array('module' => 'Image','function' => 'viewExtension'));
					Twist::framework() -> module() -> extend('View','session',array('module' => 'Session','function' => 'viewExtension'));
					Twist::framework() -> module() -> extend('View','user',array('module' => 'User','function' => 'viewExtension'));
					Twist::framework() -> module() -> extend('View','twist-ajax',array('module' => 'AJAX','function' => 'viewExtension'));
					Twist::framework() -> module() -> extend('View','ajax',array('module' => 'AJAX','function' => 'viewExtension'));

					//Register the framework resources handler into the template system
					\Twist\Core\Instance::storeObject('twistCoreResources',new \Twist\Core\PackagesResources());
					Twist::framework() -> module() -> extend('View','resource',array('instance' => 'twistCoreResources','function' => 'viewExtension'));

					//Register all the modules that have been installed in the framework
					Twist::framework() -> register() -> modules();
					Twist::framework() -> register() -> interfaces();

					\Twist\Core\Autoloader::init();

					Twist::framework()->register()->autoloaderPath('\\Twist\\Controllers\\','controllers','.controller.php');
					Twist::framework()->register()->autoloaderPath('\\Twist\\Modules\\','twist/modules','.controller.php');

					//Stop tracking the framework boot time
					Twist::Timer('TwistPageLoad') -> start();
					Twist::Timer('TwistPageLoad') -> log('Twist Core Loaded');

					self::coreResources();
					self::showSetup();
					self::phpSettings();
					self::maintenanceMode();
					self::autoAuthenticate();

					self::define('TWIST_LAUNCHED',1);
				}
			}

            protected static function coreResources(){

	            $strResourcesURI = sprintf('%score/resources/',FRAMEWORK_URI);

                $arrResources = array(
                    'arable' => sprintf('%sarable/arable.min.css',$strResourcesURI),
                    'arable-reset' => sprintf('%sarable/arable-reset.min.css',$strResourcesURI),
                    'font-awesome' => sprintf('%sfont-awesome/css/font-awesome.min.css',$strResourcesURI),
                    'jquery' => sprintf('%sjquery/jquery-2.1.3.min.js',$strResourcesURI),
                    'jquery-legacy' => sprintf('%sjquery/jquery-1.11.2.min.js',$strResourcesURI),
	                'logo' => sprintf('%slogos/logo.png',$strResourcesURI),
	                'logo-favicon' => sprintf('%slogos/favicon.ico',$strResourcesURI),
	                'logo-32' => sprintf('%slogos/logo-32.ico',$strResourcesURI),
	                'logo-64' => sprintf('%slogos/logo-64.ico',$strResourcesURI),
	                'logo-128' => sprintf('%slogos/logo-128.ico',$strResourcesURI),
	                'logo-256' => sprintf('%slogos/logo-256.ico',$strResourcesURI),
	                'logo-512' => sprintf('%slogos/logo-512.ico',$strResourcesURI),
	                'logo-640' => sprintf('%slogos/logo-640.ico',$strResourcesURI),
	                'logo-800' => sprintf('%slogos/logo-800.ico',$strResourcesURI),
	                'logo-1024' => sprintf('%slogos/logo-1024.ico',$strResourcesURI),
	                'logo-large' => sprintf('%slogos/logo-512.png',$strResourcesURI),
	                'logo-small' => sprintf('%slogos/logo-32.png',$strResourcesURI),
                    'modernizr' => sprintf('%smodernizr/modernizr-2.8.3.min.js',$strResourcesURI),
                    'rummage' => sprintf('%srummage/rummage.min.js',$strResourcesURI),
                    'shadow-js' => sprintf('%sshadow-js/shadow-js.min.js',$strResourcesURI),
	                'unsemantic' => sprintf('%sunsemantic/unsemantic-grid-responsive-tablet-no-ie7.css',$strResourcesURI),
	                'resources_uri' => $strResourcesURI,
	                'uri' => FRAMEWORK_URI
                );

	            //Integrate the basic core href tag support - legacy support
                Twist::framework() -> module() -> extend('Template','core',$arrResources);
            }

			/**
			 * Show the setup process if required
			 */
			protected static function showSetup(){

				if(Twist::framework() -> settings() -> showSetup()){

					//Check that the setup interface exists before outputting the setup page
					if(Twist::framework()->interfaces()->exists('Setup')){
						Twist::Route()->purge();
						Twist::Route()->baseURI(BASE_URI);
						Twist::Route()->ui('/%','Setup');
						Twist::Route()->serve();
					}else{
						throw new Exception("TwistPHP has not been setup, please consult the documentation or install the 'Setup' interface");
					}
				}
			}

			/**
			 * Auto authenticate the user when the framework starts, if enabled
			 */
			protected static function autoAuthenticate(){

				//If auto authenticate is enabled then authenticate the user at this point
				if(Twist::framework()->setting('USER_AUTO_AUTHENTICATE')){
					Twist::User()->authenticate();
					Twist::Timer('TwistPageLoad') -> log('User Authenticated');
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
					\Twist\Core\Error::errorPage(503);
				}
			}

			/**
			 * Register the PHP handlers for errors, exceptions and log outputs
			 */
			protected static function errorHandlers(){

				if(Twist::framework()->setting('ERROR_HANDLING')){
					Twist::framework() -> register() -> handler('error','Twist\Core\Error','handleError');
				}

				if(Twist::framework()->setting('ERROR_FATAL_HANDLING')){
					Twist::framework() -> register() -> handler('fatal','Twist\Core\Error','handleFatal');
				}

				if(Twist::framework()->setting('ERROR_EXCEPTION_HANDLING')){
					Twist::framework() -> register() -> handler('exception','Twist\Core\Error','handleException');
				}

				if(Twist::framework()->setting('ERROR_LOG')){
					Twist::framework() -> register() -> shutdownEvent('errorLog','Twist\Core\Error','outputLog');
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
				\Twist\Core\Error::errorPage($intResponseCode);
			}

			/**
			 * Dump data to the screen in a nice format with other key debug information
			 * @param null $mxdData
			 * @throws Exception
			 */
			public static function dump($mxdData = null){
				throw new \Exception(json_encode($mxdData),1200);
			}
		}
	}