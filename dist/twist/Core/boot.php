<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Copyright (C) 2016  Shadow Technologies Ltd.
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

	if(!headers_sent()){
		//IE8 Session Fix
		header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
		header('X-Frame-Options: SAMEORIGIN');

		//Set the Twist Identifier
		header('X-Powered-By: TwistPHP');

		//Set twist session cookie
		setcookie('twist_session',sha1(time().rand(1,9999)),time()+3600,'/');
	}

	error_reporting(E_ALL);
 	ini_set("display_errors", 1);

	//Preset the timezone, once framework is up and running set from the settings table
	date_default_timezone_set('Europe/London');
	$_SERVER['TWIST_BOOT'] = microtime();

	//Temp function to allow the easy definition of core defines in preparation for Twist to be included
	function TwistDefine($strKey,$mxdValue){
		if(!defined($strKey)){
			define($strKey,$mxdValue);
		}
	}

	//If the SITE_URI_REWRITE is not already defined then it will be defined here
	TwistDefine('SITE_URI_REWRITE','/');

	//TWIST_PUBLIC_ROOT - Can be defined in your index file
	TwistDefine('TWIST_PUBLIC_ROOT',$_SERVER['DOCUMENT_ROOT']);

	//TWIST_APP - Can be defined in your index file
	TwistDefine('TWIST_APP',sprintf('%s/app/',rtrim(TWIST_PUBLIC_ROOT,'/')));

	require_once sprintf('%s/../Classes/Autoload.class.php',dirname(__FILE__));
	use \Twist\Classes\Autoload;
	Autoload::init(realpath(sprintf('%s/../../',dirname(__FILE__))));

	//Get the base location of the site, based on this config file (should be in the doc_root)
	TwistDefine('TWIST_FRAMEWORK',realpath(sprintf('%s/../',dirname(__FILE__))).'/');
	TwistDefine('TWIST_FRAMEWORK_CONFIG',sprintf('%sConfig/',TWIST_FRAMEWORK));
	TwistDefine('TWIST_FRAMEWORK_CLASSES',sprintf('%sClasses/',TWIST_FRAMEWORK));
	TwistDefine('TWIST_FRAMEWORK_DATA',sprintf('%sCore/Data/',TWIST_FRAMEWORK));
	TwistDefine('TWIST_FRAMEWORK_MODELS',sprintf('%sCore/Models/',TWIST_FRAMEWORK));
	TwistDefine('TWIST_FRAMEWORK_HELPERS',sprintf('%sCore/Helpers/',TWIST_FRAMEWORK));
	TwistDefine('TWIST_FRAMEWORK_VIEWS',sprintf('%sCore/Views/',TWIST_FRAMEWORK));
	TwistDefine('TWIST_FRAMEWORK_RESOURCES',sprintf('%sCore/Resources/',TWIST_FRAMEWORK));
	TwistDefine('TWIST_FRAMEWORK_INSTALL',sprintf('%sInstall/',TWIST_FRAMEWORK));

	TwistDefine('TWIST_APP_AJAX',sprintf('%s/Ajax/',rtrim(TWIST_APP,'/')));
	TwistDefine('TWIST_APP_ASSETS',sprintf('%s/Assets/',rtrim(TWIST_APP,'/')));
	TwistDefine('TWIST_APP_CACHE',sprintf('%s/Cache/',rtrim(TWIST_APP,'/')));
	TwistDefine('TWIST_APP_CONFIG',sprintf('%s/Config/',rtrim(TWIST_APP,'/')));
	TwistDefine('TWIST_APP_CONTROLLERS',sprintf('%s/Controllers/',rtrim(TWIST_APP,'/')));
	TwistDefine('TWIST_APP_MODELS',sprintf('%s/Models/',rtrim(TWIST_APP,'/')));
	TwistDefine('TWIST_APP_VIEWS',sprintf('%s/Views/',rtrim(TWIST_APP,'/')));

	//TWIST_PACKAGES - Can be defined in your index file
	TwistDefine('TWIST_PACKAGES',sprintf('%s/packages/',rtrim(TWIST_PUBLIC_ROOT,'/')));

	//TWIST_UPLOADS - Can be defined in your index file
	TwistDefine('TWIST_UPLOADS',sprintf('%s/uploads/',rtrim(TWIST_PUBLIC_ROOT,'/')));

	/** From this point onwards you now have to use Twist::define() rather than TwistDefine */
	require_once sprintf('%sTwist.php',TWIST_FRAMEWORK);

	//Define the version number of the TwistPHP installation
	TwistDefine('TWIST_VERSION',Twist::version());

	if(defined('TWIST_APP_CONFIG') && file_exists(sprintf('%sconfig.php',TWIST_APP_CONFIG))){
		require_once sprintf('%sconfig.php',TWIST_APP_CONFIG);
	}

	//Include the config file
	if(file_exists(sprintf('%s/../Config/default.php',dirname(__FILE__)))){
		require_once sprintf('%s/../Config/default.php',dirname(__FILE__));
	}
