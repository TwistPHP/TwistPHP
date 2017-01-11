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
	 * The library of PDO, this will allow the Database class to
	 * communicate with a MySQL server using the advanced PDO Protocol
	 */
	class ProtocolPDO{

		var $resLink = null;
		var $strConnectionError = '';
		var $blActiveTransaction = false;
		var $blAutoCommit = false;

		function connect($strServer,$strUsername,$strPassword,$strDatabase,$intPort = 3306){//done
			try{
				$this->resLink = new \PDO(sprintf('mysql:dbname=%s;host=%s;port=%s',$strDatabase,$strServer,$intPort), $strUsername, $strPassword);
				$this->resLink->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			}catch (\PDOException $e){
				$this->strConnectionError = sprintf('Connection failed: %s',$e->getMessage());
			}
		}

		function connected(){
			return (!is_null($this->resLink) && $this->ping());
		}

		function connectionError(){//done
			return $this->strConnectionError;
		}

		function close(){
			return false;
		}

		function ping(){
			try{
				$this->resLink->query('SELECT 1');
				return true;
			}catch(PDOException $e){
				return false;
			}
		}

		function selectDatabase($strDatabase){
			try{
				$this->resLink->query("USE ".$strDatabase);
				return true;
			}catch (\PDOException $e){
				$this->strConnectionError = sprintf('Failed to select Database: %s',$e->getMessage());
				return false;
			}
		}

		function setCharset($strCharset){//done
			return $this->resLink->exec(sprintf('SET NAMES %s',$strCharset));
		}

		function escapeString($strRawString){//done
			return $this->resLink->quote($strRawString);
		}

		function numberRows($resResult){//done
			return $resResult->rowCount();
		}

		function insertId($strName = null){//done
			return $this->resLink->lastInsertId($strName);
		}

		function affectedRows($resResult){//done
			return $resResult->rowCount();
		}

		function query($strQuery){//done
			$resResult = null;
			try{
				$resResult = $this->resLink->query($strQuery);
			}catch (\PDOException $e){
				$this->strConnectionError = sprintf('Query failed: %s',$e->getMessage());
			}
			return $resResult;
		}

		function fetchArray($resResult){//done
			return $resResult->fetch(\PDO::FETCH_ASSOC);
		}

		function freeResult($resResult){//done
			return $resResult->closeCursor();
		}

		function errorString(){//done
			$arrErrorInfo = $this->resLink->errorInfo();
			return $arrErrorInfo[2];
		}

		function errorNumber(){//done
			return $this->resLink->errorCode();
		}

		function autocommit($blEnable = true){
			$blOut = false;
			$this->blAutoCommit = $blEnable;
			if($blEnable == true){
				$blOut = $this->resLink->beginTransaction();
			}
			return $blOut;
		}

		function commit(){
			$this->blActiveTransaction = false;
			return $this->resLink->commit();
		}

		function rollback(){
			$this->blActiveTransaction = false;
			return $this->resLink->rollBack();
		}

		function serverInfo(){
			return 'unknown';
		}
	}

?>