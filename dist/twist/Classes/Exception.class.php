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
	 * Interface ExceptionInterface
	 * @package Twist\Classes
	 */
	interface ExceptionInterface{

		/* Protected methods inherited from Exception class */
		public function getMessage();                 // Exception message
		public function getCode();                    // User-defined Exception code
		public function getFile();                    // Source filename
		public function getLine();                    // Source line
		public function getTrace();                   // An array of the backtrace()
		public function getTraceAsString();           // Formatted string of trace

		/* Overrideable methods inherited from Exception class */
		public function __toString();                 // Formatted string for display
		public function __construct($message = null, $code = 0);
	}

	/**
	 * Class Exception
	 * @package Twist\Classes
	 */
	class Exception extends \Exception implements ExceptionInterface{

		protected $message = 'Unknown exception';     // Exception message
		private   $string;                            // Unknown
		protected $code    = 0;                       // User-defined exception code
		protected $file;                              // Source filename of exception
		protected $line;                              // Source line of exception
		private   $trace;                             // Unknown

		public function __construct($message = null, $code = 0, $file = '', $line = 0){
			if(!$message){
				throw new $this('Unknown Exception (No message passed) '. get_class($this));
			}

			parent::__construct($message, $code);

			$this->file = $file;
			$this->line = $line;
		}

		public function __toString(){
			return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . "{$this->getTraceAsString()}";
		}
	}