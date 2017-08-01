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

	class ProtocolJSON{

		public $intVersion = '0.1';
		public $resDatabase = null;
		public $strCurrentDatabase = null;

		public $arrResults = array();
		public $intInsertID = 0;
		public $intAffectedCount = 0;

		public $intErrorNumber = 0;
		public $strErrorMessage = '';

		public function connect($strDatabase){

			$this->strCurrentDatabase = $strDatabase;

			$strDatabaseFile = sprintf("%s/%s.json",dirname(__FILE__),$this->strCurrentDatabase);

			if(file_exists($strDatabaseFile)){
				$strTemp = file_get_contents($strDatabaseFile);
				$this->resDatabase = json_decode($strTemp,true);
			}
		}

		public function createDB($strDatabase){

			//Create the main database headers
			$arrDatabase = array(
				'info' => 'ShadowJSONDatabase',
				'version' => $this->intVersion,
				'created' => date('Y-m-d H:i:s'),
				'updated' => date('Y-m-d H:i:s'),
				'database' => $strDatabase,
				'table_schema' => array(),
				'tables' => array()
			);

			//Store the database file
			$strDatabaseFile = sprintf("%s/%s.json",dirname(__FILE__),$strDatabase);
			file_put_contents($strDatabaseFile,json_encode($arrDatabase));
		}

		public function createTable($strTable,$arrFields,$strAutoIncrement = null){

			if(!is_null($strAutoIncrement) && !in_array($strAutoIncrement,$arrFields)){
				$strAutoIncrement = null;
			}

			//Create the table schema
			$this->resDatabase['table_schema'][$strTable] = array(
				'auto_increment' => $strAutoIncrement,
				'fields' => $arrFields,
			);

			//Create the table itself
			$this->resDatabase['tables'][$strTable] = array(
				'created' => date('Y-m-d H:i:s'),
				'updated' => date('Y-m-d H:i:s'),
				'next_increment' => 1,
				'data' => array()
			);

			$this->resDatabase['updated'] = date('Y-m-d H:i:s');
			return $this->writeChanges();
		}

		public function deleteTable($strTable){

			unset($this->resDatabase['table_schema'][$strTable]);
			unset($this->resDatabase['tables'][$strTable]);

			$this->resDatabase['updated'] = date('Y-m-d H:i:s');
			return $this->writeChanges();
		}

		public function getTableInfo($strTable){

			//Get and expand the table info for use
			$arrTable = $this->resDatabase['table_schema'][$strTable];
			$arrTable['field_lookup'] = array_flip($arrTable['fields']);

			return $arrTable;
		}

		/**
		 * Insert a record into a table
		 * @param string $strTable
		 * @param array $arrData
		 * @return int
		 */
		public function insertRow($strTable,$arrData){

			$intInsertID = 0;
			$arrTable = $this->getTableInfo($strTable);

			if($this->testDataValidity($strTable,$arrData)){

				//Add all the data to the array in the correct formation
				$arrNewRow = array();
				foreach($arrTable['field_lookup'] as $strFieldKey => $intFieldID){
					$arrNewRow[$intFieldID] = (array_key_exists($strFieldKey,$arrData)) ? $arrData[$strFieldKey] : '';
				}

				//Update the auto increment if required
				if(!is_null($arrTable['auto_increment'])){
					$strIncrementFieldKey = $arrTable['field_lookup'][$arrTable['auto_increment']];
					$arrNewRow[$strIncrementFieldKey] = $this->resDatabase['tables'][$strTable]['next_increment'];
					$intInsertID = $this->resDatabase['tables'][$strTable]['next_increment'];
					$this->resDatabase['tables'][$strTable]['next_increment']++;
				}

				//Add the array to the table
				$this->resDatabase['tables'][$strTable]['data'][] = $arrNewRow;
				$this->resDatabase['tables'][$strTable]['updated'] = date('Y-m-d H:i:s');
				return $this->writeChanges();
			}

			return null;
		}

		/**
		 * Select record(s) from a table
		 * @param string $strTable
		 * @param array $arrWhere
		 * @param array $arrOrder
		 * @param array $arrLimit
		 * @return array
		 */
		public function selectRow($strTable,$arrWhere = array(),$arrOrder = array(),$arrLimit = array()){

			$arrResults = array();

			//Go through each record one by one and combine the keys and values
			foreach($this->resDatabase['tables'][$strTable]['data'] as $arrEachItem){

				$mxdResult = $this->processDataWhere($strTable,$arrEachItem,$arrWhere);

				if(is_array($mxdResult)){
					$arrResults[] = $mxdResult;
				}
			}

			if(count($arrOrder)){
				$strDirection = ($arrOrder['direction'] == 'ASC') ? SORT_ASC : SORT_DESC;
				$this->orderByColumn($arrResults,$arrOrder['field'],$strDirection);
			}

			if(count($arrLimit)){
				$intCurrentCount = 0 - $arrLimit['offset'];
				$arrTemp = $arrResults;
				$arrResults = array();

				foreach($arrTemp as $intKey => $arrData){

					if($intCurrentCount >= 0){
						$arrResults[$intKey] = $arrData;
					}

					$intCurrentCount++;

					if($intCurrentCount == $arrLimit['limit']){
						break;
					}
				}
			}

			return $arrResults;
		}

		/**
		 * Update a record in a table
		 * @param       $strTable
		 * @param array $arrData
		 * @param array $arrWhere
		 * @param array $arrLimit
		 * @return bool
		 */
		public function updateRow($strTable,$arrData = array(),$arrWhere = array(),$arrLimit = array()){ //TODO: $arrLimit isn't used

			$arrTable = $this->getTableInfo($strTable);

			if($this->testDataValidity($strTable,$arrData)){

				//Go through each record one by one and combine the keys and values
				foreach($this->resDatabase['tables'][$strTable]['data'] as $intRowKey => $arrEachItem){

					$mxdResult = $this->processDataWhere($strTable,$arrEachItem,$arrWhere);

					if(is_array($mxdResult)){
						foreach($arrData as $strFieldKey => $mxdValue){
							$intFieldID = $arrTable['field_lookup'][$strFieldKey];
							$this->resDatabase['tables'][$strTable]['data'][$intRowKey][$intFieldID] = $mxdValue;
						}
					}
				}

				$this->resDatabase['tables'][$strTable]['updated'] = date('Y-m-d H:i:s');
				$this->writeChanges();
			}

			return true;
		}

		/**
		 * Delete a record from a table
		 * @param       $strTable
		 * @param array $arrWhere
		 * @param array $arrLimit
		 * @return int
		 */
		public function deleteRow($strTable,$arrWhere = array(),$arrLimit = array()){

			//Go through each record one by one and combine the keys and values
			foreach($this->resDatabase['tables'][$strTable]['data'] as $intRowKey => $arrEachItem){

				$mxdResult = $this->processDataWhere($strTable,$arrEachItem,$arrWhere);

				if(is_array($mxdResult)){
					unset($this->resDatabase['tables'][$strTable]['data'][$intRowKey]);
				}
			}

			$this->resDatabase['tables'][$strTable]['updated'] = date('Y-m-d H:i:s');
			return $this->writeChanges();
		}

		/**
		 * Test to see if the fields that are passed in are correct / valid
		 * @param string $strTable
		 * @param array $arrData
		 * @return bool
		 */
		protected function testDataValidity($strTable,$arrData){

			$arrTable = $this->getTableInfo($strTable);

			//Check that all the data passed in is valid
			foreach($arrData as $strFieldKey => $mxdValue){
				if(!array_key_exists($strFieldKey,$arrTable['field_lookup'])){
					$this->setError(1001,sprintf('Invalid field "%s" entered into table "%s"',$strFieldKey,$strTable));
					return false;
				}
			}

			return true;
		}

		/**
		 * Process each row to see if it matches the where requirements
		 * @param string $strTable
		 * @param array $arrDataRow
		 * @param array $arrWhere
		 * @return array|bool
		 */
		protected function processDataWhere($strTable,$arrDataRow,$arrWhere){

			$mxdOut = array();

			$arrTable = $this->getTableInfo($strTable);
			$arrTempData = array_combine($arrTable['fields'], array_values($arrDataRow));

			if(is_array($arrWhere) && count($arrWhere)){
				foreach($arrWhere as $strKey => $strValue){
					if($arrTempData[$strKey] != $strValue){
						$mxdOut = false;
						break;
					}
				}
			}

			//If no errors have been detected return the temp data
			if(is_array($mxdOut)){
				$mxdOut = $arrTempData;
			}

			return $mxdOut;
		}

		public function setError($intNumber,$strMessage){
			$this->intErrorNumber = $intNumber;
			$this->strErrorMessage = $strMessage;
		}

		public function writeChanges($strDatabase = null){

			$strWriteDB = (is_null($strDatabase)) ? $this->strCurrentDatabase : $strDatabase;

			$strDatabaseFile = sprintf("%s/%s.json",dirname(__FILE__),$strWriteDB);
			return file_put_contents($strDatabaseFile,json_encode($this->resDatabase));
		}

		/**
		 * Extract the table name form the query matches
		 * @param array $arrMatches
		 * @return string
		 */
		protected function processQueryTable($arrMatches){

			if(strstr($arrMatches['table'],'.')){
				$arrDatabaseTable = explode('.',$arrMatches['table']);
				$strTable = trim(str_replace("`","",$arrDatabaseTable[0]));
			}else{
				$strTable = trim(str_replace("`","",$arrMatches['table']));
			}

			return $strTable;
		}

		/**
		 * Extract the options/Data form the query matches
		 * @param array $arrMatches
		 * @return array
		 */
		protected function processQueryOptions($arrMatches){

			$arrData = array();

			if(array_key_exists('data',$arrMatches) || array_key_exists('options',$arrMatches)){

				$strOptions = (array_key_exists('data',$arrMatches)) ? str_replace("SET ","",$arrMatches['data']) : $arrMatches['options'];

				if(stristr($strOptions,',')){
					$arrParts = explode(",",$strOptions);
					foreach($arrParts as $strEachPart){
						preg_match_all("#\`([a-z0-9\-\_]+)\`\s([\=]+)\s([0-9]+|\'.*\')#i",$strEachPart,$arrOptionFound);
						$arrData[$arrOptionFound[1][0]] = str_replace("'","",$arrOptionFound[3][0]);
					}
				}else{
					preg_match_all("#\`([a-z0-9\-\_]+)\`\s([\=]+)\s([0-9]+|\'.*\')#i",$strOptions,$arrOptionFound);

					if(trim($strOptions) != '*' && count($arrOptionFound[1])){
						$arrData[$arrOptionFound[1][0]] = str_replace("'","",$arrOptionFound[3][0]);
					}
				}
			}

			return $arrData;
		}

		/**
		 * Extract the Where data form the query matches
		 * @param array $arrMatches
		 * @return array
		 */
		protected function processQueryWhere($arrMatches){

			$arrWheres = array();

			if(array_key_exists('where',$arrMatches)){
				$strWhere = str_replace("WHERE ","",$arrMatches['where']);

				if(stristr($strWhere,'AND')){
					$arrParts = explode("AND",$strWhere);
					foreach($arrParts as $strEachPart){
						preg_match_all("#\`([a-z0-9\-\_]+)\`\s([\=\>\<\!]+)\s([0-9]+|\'.*\')#i",$strEachPart,$arrWheresFound);
						$arrWheres[$arrWheresFound[1][0]] = str_replace("'","",$arrWheresFound[3][0]);
					}
				}else{
					preg_match_all("#\`([a-z0-9\-\_]+)\`\s([\=\>\<\!]+)\s([0-9]+|\'.*\')#i",$strWhere,$arrWheresFound);
					$arrWheres[$arrWheresFound[1][0]] = str_replace("'","",$arrWheresFound[3][0]);
				}
			}

			return $arrWheres;
		}

		protected function processQueryOrder($arrMatches){

			$arrOrder = array();

			if(array_key_exists('order',$arrMatches)){
				preg_match("#ORDER BY [\`]?([a-z0-9\-\_]+)[\`]? ([a-z]{3,4})#i",$arrMatches['order'],$arrOrderItems);
				$arrOrder = array(
					'field' => $arrOrderItems[1],
					'direction' => $arrOrderItems[2]
				);
			}

			return $arrOrder;
		}

		protected function processQueryLimit($arrMatches){

			$arrLimit = array();

			if(array_key_exists('order',$arrMatches)){
				$arrLimitParts = explode(' ',trim($arrMatches['limit']));

				if(strstr($arrLimitParts[1],',')){
					$arrSubParts = explode(',',$arrLimitParts[1]);
					$arrLimit['limit'] = trim($arrSubParts[0]);
					$arrLimit['offset'] = trim($arrSubParts[1]);
				}else{
					$arrLimit['limit'] = trim($arrLimitParts[1]);
					$arrLimit['offset'] = 0;
				}
			}

			return $arrLimit;
		}




		/**
		 * PUBLIC FUNCTIONS
		 */

		/**
		 * @param $resResult
		 */
		function numberRows($resResult){

		}

		function insertId(){

		}

		function affectedRows($resResult){

		}

		function query($strQuery){

			$arrKeys = array('full','type');
			$strExpression = "([a-z]+)";

			if(strstr($strQuery,'DELETE ')){
				$strExpression .= "\sFROM\s([a-z0-9\-\_\.\`]+)";
				$arrKeys[] = 'table';
			}elseif(strstr($strQuery,' FROM ')){
				$strExpression .= "([a-z0-9\*\-\_\.\,\s\`]+)\sFROM\s([a-z0-9\-\_\.\`]+)";
				$arrKeys[] = 'options';
				$arrKeys[] = 'table';
			}elseif(strstr($strQuery,' INTO ')){
				$strExpression .= "\sINTO\s([a-z0-9\-\_\.\`]+)(\sSET\s[\w\W]+)";
				$arrKeys[] = 'table';
				$arrKeys[] = 'data';
			}elseif(strstr($strQuery,'UPDATE ')){
				$strExpression .= "\s([a-z0-9\-\_\.\`]+)(\sSET\s[\w\W]+)";
				$arrKeys[] = 'table';
				$arrKeys[] = 'data';
			}

			if(strstr($strQuery,' WHERE ')){
				$strExpression .= "(\sWHERE\s[a-z0-9\-\_\.\s\=\<\>\`\']+)";
				$arrKeys[] = 'where';
			}

			if(strstr($strQuery,' ORDER BY ')){
				$strExpression .= "(\sORDER\sBY\s[a-z0-9\-\_\.\`]+ [a-z]{3,4})";
				$arrKeys[] = 'order';
			}

			if(strstr($strQuery,' LIMIT ')){
				$strExpression .= "(\sLIMIT\s[0-9\,]+)";
				$arrKeys[] = 'limit';
			}

			//echo $strExpression."<br />";
			//echo $strQuery."<br />";

			preg_match(sprintf("#^%s$#i",$strExpression),$strQuery,$arrMatches);
			$arrMatches = array_combine($arrKeys,$arrMatches);

			$strTable = $this->processQueryTable($arrMatches);
			$arrData = $this->processQueryOptions($arrMatches);
			$arrWheres = $this->processQueryWhere($arrMatches);
			$arrOrder = $this->processQueryOrder($arrMatches);
			$arrLimit = $this->processQueryLimit($arrMatches);

			echo "<pre>Parts: ".print_r($arrMatches,true)."</pre><hr>";

			$arrResult = array();

			switch(strtoupper($arrMatches['type'])){

				case'CREATE':

					break;

				case'DROP':

					break;

				case'INSERT':
					$arrResult = $this->insertRow($strTable,$arrData);
					break;

				case'SELECT':
					$arrResult = $this->selectRow($strTable,$arrWheres,$arrOrder,$arrLimit);
					break;

				case'UPDATE':
					$arrResult = $this->updateRow($strTable,$arrData,$arrWheres,$arrLimit);
					break;

				case'DELETE':
					$arrResult = $this->deleteRow($strTable,$arrWheres,$arrLimit);
					break;
			}

			echo "<pre>".print_r($arrResult,true)."</pre>";

			return true;
		}

		function fetchArray($resResult){
			return $resResult->fetch_array(MYSQLI_ASSOC);
		}

		function freeResult($resResult){
			return $resResult->free();
		}

		function orderByColumn(&$arrData, $strColumn, $refDirection = SORT_ASC){

			$arrSortCol = array();
			foreach ($arrData as $mxdKey=> $arrRow) {
				$arrSortCol[$mxdKey] = $arrRow[$strColumn];
			}

			array_multisort($arrSortCol, $refDirection, $arrData);
		}
	}