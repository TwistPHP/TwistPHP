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

	namespace Twist\Core\Models;
	use Twist\Classes\Shutdown;

	/**
	 * Register Shutdown and Event handlers, also handlers can be canceled if required
	 */
	class Register{

		/**
		 * Register a handler for shutdown events, errors and exceptions. All default handlers are registered with this method by TwistPHP.
		 * @param string $strType
		 * @param string $strClass
		 * @param string $strFunction
		 * @param null $strEventKey
		 */
		public function handler($strType,$strClass,$strFunction,$strEventKey = null){

			switch($strType){

				case'error':
					set_error_handler(array($strClass, $strFunction));
					break;

				case'fatal':
					Shutdown::registerEvent(array('TwistFatalError',$strClass,$strFunction));
					break;

				case'exception':
					set_exception_handler(array($strClass, $strFunction));
					break;

				case'shutdown':
					Shutdown::registerEvent(array($strEventKey,$strClass,$strFunction));
					break;
			}
		}

		/**
		 * Cancel a registered handler, these handlers can be for shutdown events, errors and exceptions.
		 * @param string $strType
		 * @param null $strEventKey
		 */
		public function cancelHandler($strType,$strEventKey = null){

			switch($strType){

				case'error':
					restore_error_handler();
					break;

				case'fatal':
					Shutdown::cancelEvent('TwistFatalError');
					break;

				case'exception':
					restore_exception_handler();
					break;

				case'shutdown':
					Shutdown::cancelEvent($strEventKey);
					break;
			}
		}

		/**
		 * Alias function with the first parameter preset to 'shutdown'.
		 * @alias handler
		 * @param string $strEventKey
		 * @param string $strClass
		 * @param string $strFunction
		 */
		public function shutdownEvent($strEventKey,$strClass,$strFunction){
			$this->handler('shutdown',$strClass,$strFunction,$strEventKey);
		}

		/**
		 * Alias function with the first parameter preset to 'shutdown'.
		 * @alias cancelHandler
		 * @param string $strEventKey
		 */
		public function cancelShutdownEvent($strEventKey){
			$this->cancelHandler('shutdown',$strEventKey);
		}
	}