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

class Autoload{

	public static $strBaseDir = null;
	public static $arrClassLoaded = array();

	public static function init($strBaseDir){
		self::$strBaseDir = $strBaseDir;
		spl_autoload_register(__NAMESPACE__ .'\Autoload::load');
	}

	public static function load($strRequest){

		//Fix for matches that are in 'Twist\Core\Classes' namespace
		if(!strstr($strRequest,'\\')){
			$strRequest = sprintf('%s\\%s',__NAMESPACE__,$strRequest);
		}

		if(!array_key_exists($strRequest,self::$arrClassLoaded)){

			$strFile = str_replace('\\','/',$strRequest);

			if(strstr($strRequest,'\\Classes\\')){
				$strFile .= '.twist.php';
			}elseif(strstr($strRequest,'\\Routes\\')){
				$strFile .= '.route.php';
			}elseif(strstr($strRequest,'\\Controllers\\')){
				$strFile .= '.controller.php';
			}elseif(strstr($strRequest,'\\Models\\')){
				$strFile .= '.model.php';
			}elseif(strstr($strRequest,'\\Packages\\')){
				$strFile .= '.package.php';
			}else{
				$strFile .= '.php';
			}

			$arrPrats = explode('/',$strFile);
			$arrPrats[0] = strtolower($arrPrats[0]);
			$strFile = implode('/',$arrPrats);
			unset($arrPrats[0]);
			$strFileWithoutRoot = implode('/',$arrPrats);

			if(substr($strRequest,0,6) == 'Twist\\'){
				$dirRequire = sprintf('%s/%s',rtrim(self::$strBaseDir,'/'),ltrim($strFile));
			}elseif(substr($strRequest,0,4) == 'App\\'){
				$dirRequire = sprintf('%s/%s',rtrim(TWIST_APP,'/'),ltrim($strFileWithoutRoot));
			}elseif(substr($strRequest,0,9) == 'Packages\\'){
				$dirRequire = sprintf('%s/%s',rtrim(TWIST_PACKAGES,'/'),ltrim($strFileWithoutRoot));
			}else{
				$dirRequire = sprintf('%s/%s',rtrim(TWIST_PUBLIC_ROOT,'/'),ltrim($strFile));
			}

			if(file_exists($dirRequire)){
				require_once $dirRequire;
				self::$arrClassLoaded[$strRequest] = $dirRequire;
			}else{
				throw new \Exception(sprintf("TwistPHP AutoLoader: Unable to load the requested class '%s', please check to see if the file exists",$strRequest));
			}
		}
	}
}