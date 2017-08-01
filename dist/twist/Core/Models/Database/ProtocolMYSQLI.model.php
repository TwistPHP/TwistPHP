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

		/**
		 * @param string $strServer
		 * @param string $strUsername
		 * @param string $strPassword
		 * @param string $strDatabase
		 */
		public function connect($strServer,$strUsername,$strPassword,$strDatabase){
			$this->resLink = new \mysqli($strServer,$strUsername,$strPassword,$strDatabase);
		}

		/**
		 * @return bool
		 */
		public function connected(){
			return (!is_null($this->resLink) && $this->ping());
		}

		/**
		 * @return bool
		 */
		public function close(){
			return $this->resLink->close();
		}

		/**
		 * @return string
		 */
		public function connectionError(){
			return $this->resLink->connect_error;
		}

		/**
		 * @return bool
		 */
		public function ping(){
			return (!is_null($this->resLink) && is_object($this->resLink)) ? $this->resLink->ping() : false;
		}

		/**
		 * @param string $strDatabase
		 * @return bool
		 */
		public function selectDatabase($strDatabase){
			return $this->resLink->select_db($strDatabase);
		}

		/**
		 * @param string $strCharset
		 * @return bool
		 */
		public function setCharset($strCharset){
			return $this->resLink->set_charset($strCharset);
		}

		/**
		 * @param string $strRawString
		 * @return string
		 */
		public function escapeString($strRawString){
			return $this->resLink->real_escape_string($strRawString);
		}

		/**
		 * @param string $resResult
		 * @return integer
		 */
		public function numberRows($resResult){
			return (!is_null($resResult) && is_object($resResult)) ? $resResult->num_rows : 0;
		}

		/**
		 * @return mixed
		 */
		public function insertId(){
			return $this->resLink->insert_id;
		}

		/**
		 * @return int
		 */
		public function affectedRows(){
			return $this->resLink->affected_rows;
		}

		/**
		 * @param string $strQuery
		 * @return bool|\mysqli_result
		 */
		public function query($strQuery){
			$resOut = $this->resLink->query($strQuery);
			$this->blActiveTransaction = ($this->blAutoCommit) ? false : true;
			return $resOut;
		}

		/**
		 * @param \mysqli_result $resResult
		 * @return mixed
		 */
		public function fetchArray(\mysqli_result $resResult){
			return $resResult->fetch_array(MYSQLI_ASSOC);
		}

		/**
		 * @param \mysqli_result $resResult
		 * @return bool
		 */
		public function freeResult(\mysqli_result $resResult){
			$resResult->free();
			return true;
		}

		/**
		 * @return string
		 */
		public function errorString(){
			return $this->resLink->error;
		}

		/**
		 * @return integer
		 */
		public function errorNumber(){
			return $this->resLink->errno;
		}

		/**
		 * @param bool $blEnable
		 * @return bool
		 */
		public function autoCommit($blEnable = true){
			$this->blAutoCommit = $blEnable;
			return (!is_null($this->resLink) && is_object($this->resLink)) ? $this->resLink->autocommit($blEnable) : false;
		}

		/**
		 * @return bool
		 */
		public function commit(){
			$this->blActiveTransaction = false;
			return $this->resLink->commit();
		}

		/**
		 * @return bool
		 */
		public function rollback(){
			$this->blActiveTransaction = false;
			return $this->resLink->rollback();
		}

		/**
		 * @return string
		 */
		public function serverInfo(){
			return mysqli_get_server_info($this->resLink);
		}
	}