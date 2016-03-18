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
	 * The library of MySQL, this will allow the Database class to
	 * communicate with a MySQL server using the standard protocol
	 */
	class ProtocolMYSQL{

		public $resLink = null;
		public $blActiveTransaction = false;
		public $blAutoCommit = false;

		public function validConnectionObject(){
			return (!is_null($this->resLink) && is_object($this->resLink));
		}

		public function connect($strServer,$strUsername,$strPassword,$strDatabase){
			$this->resLink = @mysql_connect($strServer,$strUsername,$strPassword);
			return $this->validConnectionObject();
		}

		public function connected(){
			return ($this->validConnectionObject() && $this->ping());
		}

		public function close(){
			return false;
		}

		public function connectionError(){
			return $this->errorString();
		}

		public function ping(){
			return ($this->validConnectionObject()) ? mysql_ping($this->resLink) : false;
		}

		public function selectDatabase($strDatabase){
			return mysql_select_db($strDatabase,$this->resLink);
		}

		public function setCharset($strCharset){
			return mysql_set_charset($strCharset,$this->resLink);
		}

		public function escapeString($strRawString){
			return mysql_real_escape_string($strRawString,$this->resLink);
		}

		public function numberRows($resResult){
			return mysql_num_rows($resResult);
		}

		public function insertId(){
			return ($this->validConnectionObject()) ? mysql_insert_id($this->resLink) : 0;
		}

		public function affectedRows($resResult){
			return ($this->validConnectionObject()) ? mysql_affected_rows($this->resLink) : 0;
		}

		public function query($strQuery){
			$this->blActiveTransaction = true;
			return mysql_query($strQuery,$this->resLink);
		}

		public function fetchArray($resResult){
			return mysql_fetch_array($resResult,MYSQL_ASSOC);
		}

		public function freeResult($resResult){
			return mysql_free_result($resResult);
		}

		public function errorString(){
			return ($this->validConnectionObject()) ? mysql_error($this->resLink) : 'MySQL Connection Error: no connection object found, new connection failed.';
		}

		public function errorNumber(){
			return ($this->validConnectionObject()) ? mysql_errno($this->resLink) : 893;
		}

		public function autoCommit($blEnable = true){
			$this->blAutoCommit = $blEnable;
			return false;
		}

		public function commit(){
			$this->blActiveTransaction = false;
			return false;
		}

		public function rollback(){
			$this->blActiveTransaction = false;
			return false;
		}

		public function serverInfo(){
			return mysql_get_server_info($this->resLink);
		}
	}