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

	namespace TwistPHP;

	final class Shutdown{

		public static $blShutdownRegistered = false;
		public static $arrCallbackEvents = array(); // array to store user callbacks

		public static function enableHandler(){
			register_shutdown_function(array('TwistPHP\Shutdown', 'callEvents'));
			self::$blShutdownRegistered = true;
		}

		public static function registerEvent(){

			$arrCallback = func_get_args();

			if(empty($arrCallback)){
				trigger_error('No callback passed to '.__FUNCTION__.' method', E_TWIST_ERROR);
				return false;
			}

			if(count($arrCallback[0]) != 3){
				trigger_error('Invalid callback parameters, 3 are required key,method,function when passed to the '.__FUNCTION__.' method', E_TWIST_ERROR);
				return false;
			}

			$strEventKey = $arrCallback[0][0];
			$resCallback = array($arrCallback[0][1],$arrCallback[0][2]);

			if(strstr($resCallback[0],'Twist::')){
				//Ignore the Error
			}elseif(!is_callable($resCallback)){
				trigger_error('Invalid callback passed to the '.__FUNCTION__.' method', E_TWIST_ERROR);
				return false;
			}

			//Register the Shutdown Handler if an event has been added
			if(self::$blShutdownRegistered == false){
				self::enableHandler();
			}

			self::$arrCallbackEvents[$strEventKey] = $resCallback;

			return true;
		}

		public static function callEvents(){

			foreach(self::$arrCallbackEvents as $arrArguments){
				//$resCallbackEvent = array_shift($arrArguments);
				if(strstr($arrArguments[0],'Twist::')){
					$strPackage = str_replace('Twist::','',$arrArguments[0]);
					\Twist::$strPackage()->$arrArguments[1]();
				}else{
					call_user_func_array($arrArguments, array());
				}
			}
		}

		public static function cancelEvent($strEventKey){
			unset(self::$arrCallbackEvents[$strEventKey]);
		}

		public static function cancelEvents(){
			self::$arrCallbackEvents = array();
		}
	}