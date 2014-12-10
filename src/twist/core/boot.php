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
	 * @link       http://twistphp.com
	 *
	 */

	if(!headers_sent()){
		//IE8 Session Fix
		header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
		header('X-Frame-Options: SAMEORIGIN');

		//Set the Twist Identifier
		header('X-Powered-By: TwistPHP');

		//Set twist session cookie
		setcookie('twst_session',sha1(time().rand(1,9999)),time()+3600);
	}

	error_reporting(E_ALL);
 	ini_set("display_errors", 1);

	//Preset the timezone, once framework is up and running set from the settings table
	date_default_timezone_set('Europe/London');
	$_SERVER['TWIST_BOOT'] = microtime();

	$strCoreDir = sprintf('%s/classes/',dirname(__FILE__));

	require_once sprintf('%sBase.twist.php',$strCoreDir);
	require_once sprintf('%sBaseController.twist.php',$strCoreDir);
	require_once sprintf('%sBaseInterface.twist.php',$strCoreDir);
	require_once sprintf('%sBaseModules.twist.php',$strCoreDir);
	require_once sprintf('%sModuleBase.twist.php',$strCoreDir);
	require_once sprintf('%sFramework.twist.php',$strCoreDir);
	require_once sprintf('%sInstance.twist.php',$strCoreDir);
	require_once sprintf('%sRC4.twist.php',$strCoreDir);
	require_once sprintf('%sCore.twist.php',$strCoreDir);

	$arrShadowCoreInfo = json_decode(file_get_contents(sprintf('%s/../info.json',dirname(__FILE__))),true);
	Twist::define('TWIST_VERSION',$arrShadowCoreInfo['version']);

	//Get the base location of the site, based on this config file (should be in the doc_root)
	Twist::define('DIR_FRAMEWORK',realpath(sprintf('%s/../',dirname(__FILE__))).'/');
	Twist::define('DIR_FRAMEWORK_CONFIG',sprintf('%sconfig/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_MODULES',sprintf('%smodules/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_INTERFACES',sprintf('%sinterfaces/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_CLASSES',sprintf('%score/classes/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_PACKAGES',sprintf('%score/packages/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_TEMPLATES',sprintf('%score/templates/',DIR_FRAMEWORK));
	Twist::define('DIR_FRAMEWORK_RESOURCES',sprintf('%score/resources/',DIR_FRAMEWORK));
	Twist::define('DIR_BASE',realpath(sprintf('%s/../',DIR_FRAMEWORK)));

	//Include the config file
	if(file_exists(sprintf('%s/../config/config.php',dirname(__FILE__)))){
		require_once sprintf('%s/../config/config.php',dirname(__FILE__));
	}
