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

	require_once sprintf('%s/Classes/Autoload.twist.php',dirname(__FILE__));
	use \Twist\Core\Classes\Autoload;
	Autoload::init(realpath(sprintf('%s/../../',dirname(__FILE__))));

	require_once sprintf('%s/Classes/Twist.twist.php',dirname(__FILE__));

	$arrShadowCoreInfo = json_decode(file_get_contents(sprintf('%s/../info.json',dirname(__FILE__))),true);
	Twist::define('TWIST_VERSION',$arrShadowCoreInfo['version']);

	//Get the base location of the site, based on this config file (should be in the doc_root)
	Twist::define('DIR_FRAMEWORK',realpath(sprintf('%s/../',dirname(__FILE__))).'/');
	Twist::define('DIR_FRAMEWORK_CONFIG',sprintf('%sConfig/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_CLASSES',sprintf('%sCore/Classes/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_MODELS',sprintf('%sCore/Models/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_PACKAGES',sprintf('%sCore/Packages/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_VIEWS',sprintf('%sCore/Views/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_RESOURCES',sprintf('%sCore/Resources/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_INSTALL',sprintf('%sInstall/',DIR_FRAMEWORK));
	Twist::define('DIR_BASE',realpath(sprintf('%s/../',DIR_FRAMEWORK)).'/');

	if(file_exists(sprintf('%s/../Config/app.php',dirname(__FILE__)))){
		require_once sprintf('%s/../Config/app.php',dirname(__FILE__));

		Twist::define('DIR_APP_AJAX',sprintf('%s/Ajax/',rtrim(DIR_APP,'/')));
		Twist::define('DIR_APP_ASSETS',sprintf('%s/Assets/',rtrim(DIR_APP,'/')));
		Twist::define('DIR_APP_CACHE',sprintf('%s/Cache/',rtrim(DIR_APP,'/')));
		Twist::define('DIR_APP_CONFIG',sprintf('%s/Config/',rtrim(DIR_APP,'/')));
		Twist::define('DIR_APP_CONTROLLERS',sprintf('%s/Controllers/',rtrim(DIR_APP,'/')));
		Twist::define('DIR_APP_MODELS',sprintf('%s/Models/',rtrim(DIR_APP,'/')));
		Twist::define('DIR_APP_VIEWS',sprintf('%s/Views/',rtrim(DIR_APP,'/')));
	}

	if(defined('DIR_APP_CONFIG') && file_exists(sprintf('%sconfig.php',DIR_APP_CONFIG))){
		require_once sprintf('%sconfig.php',DIR_APP_CONFIG);
	}

	//Include the config file
	if(file_exists(sprintf('%s/../Config/default.php',dirname(__FILE__)))){
		require_once sprintf('%s/../Config/default.php',dirname(__FILE__));
	}


