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

		var $resLink = null;
		var $blActiveTransaction = false;
		var $blAutoCommit = false;

		function connect($strServer,$strUsername,$strPassword,$strDatabase){
			$this->resLink = mysqli_connect($strServer,$strUsername,$strPassword,$strDatabase);
		}

		function connected(){
			return (!is_null($this->resLink) && $this->ping());
		}

		function close(){
			return $this->resLink->close();
		}

		function connectionError(){
			return $this->resLink->connect_error;
		}

		function ping(){
			return (!is_null($this->resLink) && is_object($this->resLink)) ? $this->resLink->ping() : false;
		}

		function selectDatabase($strDatabase){
			return $this->resLink->select_db($strDatabase);
		}

		function setCharset($strCharset){
			return $this->resLink->set_charset($strCharset);
		}

		function escapeString($strRawString){
			return $this->resLink->real_escape_string($strRawString);
		}

		function numberRows($resResult){
			return (!is_null($resResult) && is_object($resResult)) ? $resResult->num_rows : 0;
		}

		function insertId(){
			return $this->resLink->insert_id;
		}

		function affectedRows($resResult){
			return $this->resLink->affected_rows;
		}

		function query($strQuery){
			$resOut = $this->resLink->query($strQuery);
			$this->blActiveTransaction = ($this->blAutoCommit) ? false : true;
			return $resOut;
		}

		function fetchArray($resResult){
			return $resResult->fetch_array(MYSQLI_ASSOC);
		}

		function freeResult($resResult){
			return $resResult->free();
		}

		function errorString(){
			return $this->resLink->error;
		}

		function errorNumber(){
			return $this->resLink->errno;
		}

		function autoCommit($blEnable = true){
			$this->blAutoCommit = $blEnable;
			return (!is_null($this->resLink) && is_object($this->resLink)) ? $this->resLink->autocommit($blEnable) : false;
		}

		function commit(){
			$this->blActiveTransaction = false;
			return $this->resLink->commit();
		}

		function rollback(){
			$this->blActiveTransaction = false;
			return $this->resLink->rollback();
		}

		function serverInfo(){
			return mysqli_get_server_info($this->resLink);
		}
	}

?>