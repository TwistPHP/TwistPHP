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

	namespace Twist\Classes;

	/**
	 * Handle PHP7 throwable fatal errors, convert them into PHP Exceptions and return them
	 * @package Twist\Classes
	 * @extends \ErrorException
	 */
	class Throwable extends \ErrorException{

		public function __construct(\Throwable $resThrowable){

			if($resThrowable instanceof \ParseError){
				$strMessage = 'Parse error: '.$resThrowable->getMessage();
				$intSeverity = E_PARSE;
			}elseif($resThrowable instanceof \TypeError) {
				$strMessage = 'Type error: '.$resThrowable->getMessage();
				$intSeverity = E_RECOVERABLE_ERROR;
			}else{
				$strMessage = 'Fatal error: '.$resThrowable->getMessage();
				$intSeverity = E_ERROR;
			}

			\ErrorException::__construct(
				$strMessage,
				$resThrowable->getCode(),
				$intSeverity,
				$resThrowable->getFile(),
				$resThrowable->getLine()
			);
		}
	}