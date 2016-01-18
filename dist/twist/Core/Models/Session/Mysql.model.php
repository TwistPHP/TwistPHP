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

	namespace Twist\Core\Models\Session;

	class Mysql{

		protected $savePath;
		protected $sessionName;
		protected $maxLifetime = 0;

		public function __construct(){
			//Use the database to store session info
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
			//\Twist::framework()->register()->shutdownEvent('SessionWrite','session_write_close');
			register_shutdown_function('session_write_close');
		}

		public function open($savePath, $sessionName){

			$this->savePath = $savePath;
			$this->savePath = $sessionName;
			$this->maxLifetime = ini_get('session.gc_maxlifetime');

			return true;
		}

		public function close(){
			//Select OS do not call Garbage Collection, so we will need to do it in close
			$this->gc($this->maxLifetime);
			return true;
		}

		public function read($intSessionID){

			$mxdOut = null;

			//Read from the current session
			$arrData = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'sessions')->get($intSessionID,'id',true);

			if(count($arrData)){
				$mxdOut = $arrData['data'];
			}

			return $mxdOut;
		}

		public function write($intSessionID, $mxdData){

			//Write to the current session
			$resResult = \Twist::Database()->query("INSERT INTO `%s`.`%ssessions`
								(`id`,`data`,`last_modified`) VALUES ('%s','%s',NOW())
								ON DUPLICATE KEY UPDATE `data` = '%s',`last_modified` = NOW()",
				TWIST_DATABASE_NAME,
				TWIST_DATABASE_TABLE_PREFIX,
				$intSessionID,
				$mxdData,
				$mxdData
			);

			return $resResult->status();
		}

		public function destroy($intSessionID){
			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'sessions')->delete($intSessionID,'id');
		}

		public function gc($intMaxLifetime){

			//Remove all the expired sessions form the database
			return \Twist::Database()->query("DELETE FROM `%s`.`%ssessions` WHERE `last_modified` < %d",
				TWIST_DATABASE_NAME,
				TWIST_DATABASE_TABLE_PREFIX,
				\Twist::DateTime()->time()-$intMaxLifetime
			)->status();
		}
	}