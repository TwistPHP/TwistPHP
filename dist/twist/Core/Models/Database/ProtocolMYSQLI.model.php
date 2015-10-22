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

	namespace Twist\Core\Models\Database;

	/**
	 * The library of MySQLi, this will allow the Database class to
	 * communicate with a MySQL server using the advanced MySQLi Protocol
	 */
	class ProtocolMYSQLI{

		/**
		 * @var \mysqli
		 */
		public $resLink = null;

		public $blActiveTransaction = false;
		public $blAutoCommit = false;

		public function connect($strServer,$strUsername,$strPassword,$strDatabase){
			$this->resLink = new \mysqli($strServer,$strUsername,$strPassword,$strDatabase);
		}

		public function connected(){
			return (!is_null($this->resLink) && $this->ping());
		}

		public function close(){
			return $this->resLink->close();
		}

		public function connectionError(){
			return $this->resLink->connect_error;
		}

		public function ping(){
			return (!is_null($this->resLink) && is_object($this->resLink)) ? $this->resLink->ping() : false;
		}

		public function selectDatabase($strDatabase){
			return $this->resLink->select_db($strDatabase);
		}

		public function setCharset($strCharset){
			return $this->resLink->set_charset($strCharset);
		}

		public function escapeString($strRawString){
			return $this->resLink->real_escape_string($strRawString);
		}

		public function numberRows($resResult){
			return (!is_null($resResult) && is_object($resResult)) ? $resResult->num_rows : 0;
		}

		public function insertId(){
			return $this->resLink->insert_id;
		}

		public function affectedRows($resResult = null){
			return $this->resLink->affected_rows;
		}

		public function query($strQuery){
			$resOut = $this->resLink->query($strQuery);
			$this->blActiveTransaction = ($this->blAutoCommit) ? false : true;
			return $resOut;
		}

		public function fetchArray(\mysqli_result $resResult){
			return $resResult->fetch_array(MYSQLI_ASSOC);
		}

		public function freeResult(\mysqli_result $resResult){
			$resResult->free();
			return true;
		}

		public function errorString(){
			return $this->resLink->error;
		}

		public function errorNumber(){
			return $this->resLink->errno;
		}

		public function autoCommit($blEnable = true){
			$this->blAutoCommit = $blEnable;
			return (!is_null($this->resLink) && is_object($this->resLink)) ? $this->resLink->autocommit($blEnable) : false;
		}

		public function commit(){
			$this->blActiveTransaction = false;
			return $this->resLink->commit();
		}

		public function rollback(){
			$this->blActiveTransaction = false;
			return $this->resLink->rollback();
		}

		public function serverInfo(){
			return mysqli_get_server_info($this->resLink);
		}
	}