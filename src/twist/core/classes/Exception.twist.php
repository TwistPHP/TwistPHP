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

	namespace Twist\Core;

	if(!class_exists('TwistException')){

		interface TwistExceptionInterface{

			/* Protected methods inherited from Exception class */
			public function getMessage();                 // Exception message
			public function getCode();                    // User-defined Exception code
			public function getFile();                    // Source filename
			public function getLine();                    // Source line
			public function getTrace();                   // An array of the backtrace()
			public function getTraceAsString();           // Formated string of trace

			/* Overrideable methods inherited from Exception class */
			public function __toString();                 // formated string for display
			public function __construct($message = null, $code = 0);
		}

		class TwistException extends \Exception implements TwistExceptionInterface{

			protected $message = 'Unknown exception';     // Exception message
			private   $string;                            // Unknown
			protected $code    = 0;                       // User-defined exception code
			protected $file;                              // Source filename of exception
			protected $line;                              // Source line of exception
			private   $trace;                             // Unknown

			public function __construct($message = null, $code = 0, $file = '', $line = 0){
				if(!$message){
					throw new $this('Unknown '. get_class($this));
				}

				parent::__construct($message, $code);

				$this->file = $file;
				$this->line = $line;
			}

			public function __toString(){
				return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . "{$this->getTraceAsString()}";
			}
		}
	}