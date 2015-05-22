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
	 * Simply edit/delete a mysql record from any table in an object orientated way
	 */
	class Record{

		protected $strDatabase = null;
		protected $strTable = null;
		protected $arrOriginalRecord = array();
		protected $arrRecord = array();
		protected $arrStructure = array();

		/**
		 * Construct the class with all the required data to make usable
		 * @param $strDatabase
		 * @param $strTable
		 * @param $arrStructure
		 * @param $arrRecord
		 * @param $blClone
		 */
		public function __construct($strDatabase,$strTable,$arrStructure,$arrRecord,$blClone = false){
			$this->strDatabase = $strDatabase;
			$this->strTable = $strTable;
			$this->arrStructure = $arrStructure;
			$this->arrRecord = $arrRecord;
			$this->arrOriginalRecord = ($blClone) ? array() : $arrRecord;
			($blClone) ? $this->nullAutoIncrement() : null;
		}

		/**
		 * Destruct the class so it cannot be used anymore
		 */
		public function __destruct(){
			$this->strDatabase = null;
			$this->strTable = null;
			$this->arrRecord = null;
			$this->arrStructure = null;
			$this->arrOriginalRecord = null;
		}

		/**
		 * Return the auto increment field name if one exists
		 * @return int|null|string
		 */
		protected function detectAutoIncrement(){
			$strField = null;
			foreach($this->arrStructure['columns'] as $strField => $arrOptions){
				if($arrOptions['auto_increment'] == '1'){
					break;
				}
			}
			return $strField;
		}

		/**
		 * Nullify an auto increment field, used when cloning a database record so that you wont get duplicate keys
		 */
		protected function nullAutoIncrement(){
			foreach($this->arrStructure['columns'] as $strField => $arrOptions){
				if($arrOptions['auto_increment'] == '1'){
					$this->arrRecord[$strField] = null;
					break;
				}
			}
		}

		/**
		 * Return an array of all the fields with their settings/types/lengths (without values)
		 * @return array
		 */
		public function fields(){
			return $this->arrStructure;
		}

		/**
		 * Return an associative array of key/value pairs
		 * @return array
		 */
		public function values(){
			return $this->arrRecord;
		}

		/**
		 * Get a field value
		 * @param $strField
		 * @return null
		 */
		public function get($strField){
			return (array_key_exists($strField,$this->arrRecord)) ? $this->arrRecord[$strField] : null;
		}

		/**
		 * Set a single field in the record to a new value, you must call "->save()" to store any changes made to the database
		 * @param $strField
		 * @param $strValue
		 * @return bool
		 */
		public function set($strField,$strValue){

			$blOut = false;

			if(array_key_exists($strField,$this->arrStructure['columns'])){
				$this->arrRecord[$strField] = $strValue;
				$blOut = true;
			}else{
				throw new \Exception(sprintf("Error adding data to database record, invalid field '%s' passed",$strField));
			}

			return $blOut;
		}

		/**
		 * Delete the record form the database table
		 * @return null
		 */
		public function delete(){

			$blOut = false;
			$objDatabase = \Twist::Database();

			$strSQL = sprintf("DELETE FROM `%s`.`%s` WHERE %s LIMIT 1",
				$this->strDatabase,
				$this->strTable,
				$this->whereClause()
			);

			if($objDatabase->query($strSQL)){
				$this->__destruct();
				$blOut = true;
			}

			return $blOut;
		}

		/**
		 * Commit the updated record to the database table, setting the second parameter true - adds as new row (default: false). False is returned if the query fails, a successful insert returns insertID, a successful update returns numAffectedRows or true if numAffectedRows is 0.
		 * @param bool $blInsert
		 * @return bool|int
		 */
		public function commit($blInsert = false){

			$mxdOut = false;

			if(json_encode($this->arrOriginalRecord) !== json_encode($this->arrRecord)){

				$objDatabase = \Twist::Database();
				$strSQL = $this->sql($blInsert);

				if($objDatabase->query($strSQL)){
					//Now that the record has been updated in the database the original data must equal the current data
					$this->arrOriginalRecord = $this->arrRecord;

					if(substr($strSQL,0,6) == 'INSERT'){
						$mxdOut = $objDatabase->getInsertID();

						//Find an auto increment field and update the ID in the record
						foreach($this->arrStructure['columns'] as $strField => $arrOptions){
							if($arrOptions['auto_increment'] == '1'){
								$this->arrOriginalRecord[$strField] = $mxdOut;
								$this->arrRecord[$strField] = $mxdOut;
								break;
							}
						}
					}else{
						if($objDatabase->getAffectedRows() == 0){
							$mxdOut = true;
						}else{
							$mxdOut = $objDatabase->getAffectedRows();
						}
					}
				}
			}

			return $mxdOut;
		}

		/**
		 * Get the query that will be applied, settings the second parameter true - adds as new row (default: false)
		 * @param bool $blInsert
		 * @return string
		 */
		public function sql($blInsert = false){

			$blInsert = (count($this->arrOriginalRecord) > 0) ? $blInsert : true;

			if($blInsert == true){

				$strSQL = sprintf("INSERT INTO `%s`.`%s` SET %s",
					$this->strDatabase,
					$this->strTable,
					$this->queryValues()
				);
			}else{

				$strSQL = sprintf("UPDATE `%s`.`%s` SET %s WHERE %s LIMIT 1",
					$this->strDatabase,
					$this->strTable,
					$this->queryValues(),
					$this->whereClause()
				);
			}

			return $strSQL;
		}

		/**
		 * Process the values into a usable SQL string
		 * @return string
		 */
		protected function queryValues(){

			$arrValueClause = array();
			$objDatabase = \Twist::Database();

			foreach($this->arrRecord as $strField => $strValue){

				if(count($this->arrOriginalRecord) == 0 || $strValue !== $this->arrOriginalRecord[$strField]){

					//When storing/updating data allow null if field is auto increment or nullable
					if(is_null($strValue) && ($this->arrStructure['columns'][$strField]['nullable'] == '1' || $this->arrStructure['columns'][$strField]['auto_increment'] == '1')){
						$strFieldString = "`%s` = NULL";
					}else{
						//Get the correct field string for each value
						if(strstr($this->arrStructure['columns'][$strField]['data_type'],'int')){
							$strFieldString = "`%s` = %d";
						}else{
							$strFieldString = "`%s` = '%s'";
						}
					}

					$arrValueClause[] = sprintf($strFieldString, $objDatabase->escapeString($strField), $objDatabase->escapeString($strValue));
				}
			}

			return implode(', ',$arrValueClause);
		}

		/**
		 * Process the values into a usable where clause
		 * @return string
		 */
		protected function whereClause(){

			$arrWhereClause = array();
			$objDatabase = \Twist::Database();

			//@todo detect for unique keys also
			$strAutoIncrementField = $this->detectAutoIncrement();

			if(!is_null($strAutoIncrementField)){
				$arrWhereClause[] = sprintf("`%s` = %d", $objDatabase->escapeString($strAutoIncrementField), $objDatabase->escapeString($this->arrOriginalRecord[$strAutoIncrementField]));
			}else{
				foreach($this->arrOriginalRecord as $strField => $strValue){

					if(is_null($strValue) && $this->arrStructure['columns'][$strField]['nullable'] == '1'){
						$strFieldString = "`%s` IS NULL";
					}else{
						//Get the correct field string for each value
						if(strstr($this->arrStructure['columns'][$strField]['data_type'],'int')){
							$strFieldString = "`%s` = %d";
						}else{
							$strFieldString = "`%s` = '%s'";
						}
					}

					$arrWhereClause[] = sprintf($strFieldString, $objDatabase->escapeString($strField), $objDatabase->escapeString($strValue));
				}
			}

			return implode(' AND ',$arrWhereClause);
		}
	}