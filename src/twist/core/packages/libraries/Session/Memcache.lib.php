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
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP\Packages;

	class SessionMemcache{

		protected $savePath;
		protected $sessionName;
		protected $maxLifetime = 0;

		public function register(){

			//Use files to store the session info
			$this->setHandlers();
		}

		public function __destruct(){
			//Save the session upon destruct of the handler
			session_write_close();
		}

		protected function setHandlers(){

			session_set_save_handler(
				array($this, 'open'),
				array($this, 'close'),
				array($this, 'read'),
				array($this, 'write'),
				array($this, 'destroy'),
				array($this, 'gc')
			);

			//the following prevents unexpected effects when using objects as save handlers
			register_shutdown_function('session_write_close');
		}

		public function open($savePath, $sessionName){

			$this->savePath = $savePath;
			$this->savePath = $sessionName;
			$this->maxLifetime = ini_get('session.gc_maxlifetime');

			//Nothing to do at this point
			return true;
		}

		public function close(){
			//Select OS do not call Garbage Collection, so we will need to do it in close
			$this->gc($this->maxLifetime);
			return true;
		}

		public function read($intSessionID){

			$mxdOut = null;
			$mxdOut = memcached::get(spritnf("sessions/sess_%s",$intSessionID));

			return $mxdOut;
		}

		public function write($intSessionID, $mxdData){

			$blOut = false;
			$blOut = memcached::set(spritnf("sessions/sess_%s",$intSessionID), $mxdData, $this->maxLifetime);

			return $blOut;
		}

		public function destroy($intSessionID){

			memcached::delete(spritnf("sessions/sess_%s",$intSessionID));
			return true;
		}

		public function gc($intMaxLifetime){
			//Nothing to do
			return true;
		}
	}