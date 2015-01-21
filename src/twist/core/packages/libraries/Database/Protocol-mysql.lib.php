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
	 * @link       http://twistphp.com
	 *
	 */

	namespace TwistPHP\Packages;

	/**
	 * The library of MySQL, this will allow the Database class to
	 * communicate with a MySQL server using the standard protocol
	 */
	class ProtocolMYSQL{

		var $resLink = null;
		var $blActiveTransaction = false;
		var $blAutoCommit = false;

		function validConnectionObject(){
			return (!is_null($this->resLink) && is_object($this->resLink));
		}

		function connect($strServer,$strUsername,$strPassword,$strDatabase){
			$this->resLink = @mysql_connect($strServer,$strUsername,$strPassword);
			return $this->validConnectionObject();
		}

		function connected(){
			return ($this->validConnectionObject() && $this->ping());
		}

		function close(){
			return false;
		}

		function connectionError(){
			return $this->errorString();
		}

		function ping(){
			return ($this->validConnectionObject()) ? mysql_ping($this->resLink) : false;
		}

		function selectDatabase($strDatabase){
			return mysql_select_db($strDatabase,$this->resLink);
		}

		function setCharset($strCharset){
			return mysql_set_charset($strCharset,$this->resLink);
		}

		function escapeString($strRawString){
			return mysql_real_escape_string($strRawString,$this->resLink);
		}

		function numberRows($resResult){
			return mysql_num_rows($resResult);
		}

		function insertId(){
			return ($this->validConnectionObject()) ? mysql_insert_id($this->resLink) : 0;
		}

		function affectedRows($resResult){
			return ($this->validConnectionObject()) ? mysql_affected_rows($this->resLink) : 0;
		}

		function query($strQuery){
			$this->blActiveTransaction = true;
			return mysql_query($strQuery,$this->resLink);
		}

		function fetchArray($resResult){
			return mysql_fetch_array($resResult,MYSQL_ASSOC);
		}

		function freeResult($resResult){
			return mysql_free_result($resResult);
		}

		function errorString(){
			return ($this->validConnectionObject()) ? mysql_error($this->resLink) : 'MySQL Connection Error: no connection object found, new connection failed.';
		}

		function errorNumber(){
			return ($this->validConnectionObject()) ? mysql_errno($this->resLink) : 893;
		}

		function autoCommit($blEnable = true){
			$this->blAutoCommit = $blEnable;
			return false;
		}

		function commit(){
			$this->blActiveTransaction = false;
			return false;
		}

		function rollback(){
			$this->blActiveTransaction = false;
			return false;
		}

		function serverInfo(){
			return mysql_get_server_info($this->resLink);
		}
	}

?>