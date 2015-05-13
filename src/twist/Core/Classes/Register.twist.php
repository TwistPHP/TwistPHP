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

	/**
	 * Register Shutdown and Event handlers, also handlers can be canceled if required
	 */
	final class Register{

		public function autoloadPath($strMatch,$dirPath,$strExtension = '.php'){
			Autoload::registerPath($strMatch,$dirPath,$strExtension);
		}

		public function autoloadClass($strMatch,$strClass,$strFunction){
			Autoload::registerClass($strMatch,$strClass,$strFunction);
		}

		public function handler($strType,$strClass,$strFunction){

			switch($strType){

				case'error':
					set_error_handler(array($strClass, $strFunction));
					break;

				case'fatal':
					$this->shutdownEvent('TwistFatalError',$strClass,$strFunction);
					break;

				case'exception':
					set_exception_handler(array($strClass, $strFunction));
					break;
			}
		}

		public function cancelHandler($strType){

			switch($strType){

				case'error':
					restore_error_handler();
					break;

				case'fatal':
					$this->cancelShutdownEvent('TwistFatalError');
					break;

				case'exception':
					restore_exception_handler();
					break;
			}
		}

		public function shutdownEvent($strEventKey,$strClass,$strFunction){
			Shutdown::registerEvent(array($strEventKey,$strClass,$strFunction));
		}

		public function cancelShutdownEvent($strEventKey){
			Shutdown::cancelEvent($strEventKey);
		}
	}