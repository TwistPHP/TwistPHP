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

	class SessionMysql{

		protected $savePath;
		protected $sessionName;
		protected $objDB = null;
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

			//Check to see if the session table has been created
			$this->objDB = \Twist::Database();

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
			$strSQL = sprintf("SELECT *
								FROM `%s`.`%ssession`
								WHERE `id` = '%s'
								LIMIT 1",
				DATABASE_NAME,
				DATABASE_TABLE_PREFIX,
				$this->objDB->escapeString($intSessionID)
			);

			if($this->objDB->query($strSQL) && $this->objDB->getNumberRows()){
				$arrData = $this->objDB->getArray();
				$mxdOut = $arrData['data'];
			}

			return $mxdOut;
		}

		public function write($intSessionID, $mxdData){

			$blOut = false;

			//Write to the current session
			$strSQL = sprintf("INSERT INTO `%s`.`%ssession`
								(`id`,`data`,`last_modified`) VALUES ('%s','%s',NOW())
								ON DUPLICATE KEY UPDATE `data` = '%s',`last_modified` = NOW()",
				DATABASE_NAME,
				DATABASE_TABLE_PREFIX,
				$this->objDB->escapeString($intSessionID),
				$this->objDB->escapeString($mxdData),
				$this->objDB->escapeString($mxdData)
			);

			if($this->objDB->query($strSQL)){
				$blOut = true;
			}

			return $blOut;
		}

		public function destroy($intSessionID){

			//Destroy the current session
			$strSQL = sprintf("DELETE
								FROM `%s`.`%ssession`
								WHERE `id` = '%s'
								LIMIT 1",
				DATABASE_NAME,
				DATABASE_TABLE_PREFIX,
				$this->objDB->escapeString($intSessionID)
			);

			$this->objDB->query($strSQL);

			return true;
		}

		public function gc($intMaxLifetime){

			//Remove all the expired sessions form the database
			$strSQL = sprintf("DELETE
								FROM `%s`.`%ssession`
								WHERE `last_modified` < %d",
				DATABASE_NAME,
				DATABASE_TABLE_PREFIX,
				$this->objDB->escapeString(\Twist::DateTime()->time()-$intMaxLifetime)
			);

			$this->objDB->query($strSQL);

			return true;
		}
	}