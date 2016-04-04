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

namespace Twist\Classes;

/**
 * A custom AutoLoader that loads in all TwistPHP files, packages and App content. The AutoLoader is included automatically by the framework when used within your site.
 * @package Twist\Classes
 */
class Autoload{

	public static $strBaseDir = null;
	public static $arrClassLoaded = array();

	/**
	 * Initialise the AutoLoader and register the class as an AutoLoader
	 * @param $strBaseDir Base directory of the framework
	 */
	public static function init($strBaseDir){
		self::$strBaseDir = $strBaseDir;
		spl_autoload_register(__NAMESPACE__ .'\Autoload::load');
	}

	/**
	 * Handler for each individual request, the path for the required file will be worked out here
	 * @param $strRequest The full class and namespace of the file to be loaded
	 * @throws \Exception
	 */
	public static function load($strRequest){

		//Fix for matches that are in 'Twist\Core\Classes' namespace
		if(!strstr($strRequest,'\\')){
			$strRequest = sprintf('%s\\%s',__NAMESPACE__,$strRequest);
		}

		if(!array_key_exists($strRequest,self::$arrClassLoaded)){

			$strFile = str_replace('\\','/',$strRequest);

			if(strstr($strRequest,'\\Classes\\')){
				$strFile .= '.class.php';
			}elseif(strstr($strRequest,'\\Routes\\')){
				$strFile .= '.route.php';
			}elseif(strstr($strRequest,'\\Controllers\\')){
				$strFile .= '.controller.php';
			}elseif(strstr($strRequest,'\\Models\\')){
				$strFile .= '.model.php';
			}elseif(strstr($strRequest,'\\Utilities\\')){
				$strFile .= '.utility.php';
			}else{
				$strFile .= '.php';
			}

			$arrPrats = explode('/',$strFile);
			$arrPrats[0] = strtolower($arrPrats[0]);
			$strFile = implode('/',$arrPrats);
			unset($arrPrats[0]);
			$strFileWithoutRoot = implode('/',$arrPrats);

			if(substr($strRequest,0,6) == 'Twist\\'){

				//First check if there is an over-ride
				$dirRequire = sprintf('%s/Twist/%s',rtrim(TWIST_APP,'/'),ltrim($strFileWithoutRoot));

				if(!file_exists($dirRequire)){
					$dirRequire = sprintf('%s/%s',rtrim(self::$strBaseDir,'/'),ltrim($strFile));
				}

			}elseif(substr($strRequest,0,4) == 'App\\'){
				$dirRequire = sprintf('%s/%s',rtrim(TWIST_APP,'/'),ltrim($strFileWithoutRoot));
			}elseif(substr($strRequest,0,9) == 'Packages\\'){

				//First check if there is an over-ride
				$dirRequire = sprintf('%s/Packages/%s',rtrim(TWIST_APP,'/'),ltrim($strFileWithoutRoot));

				if(!file_exists($dirRequire)){
					$dirRequire = sprintf('%s/%s',rtrim(TWIST_PACKAGES,'/'),ltrim($strFileWithoutRoot));
				}

			}else{
				$dirRequire = sprintf('%s/%s',rtrim(TWIST_PUBLIC_ROOT,'/'),ltrim($strFile));
			}

			if(file_exists($dirRequire)){
				require_once $dirRequire;
				self::$arrClassLoaded[$strRequest] = $dirRequire;
			}/**else{
				throw new \Exception(sprintf("TwistPHP AutoLoader: Unable to load the requested class '%s', please check to see if the file exists",$strRequest));
			}*/
		}
	}
}