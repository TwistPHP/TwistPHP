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
	 * Manage rows in your SQL database in an object orientated way. The model allows you to call up single records as objects or arrays.
	 * You can edit a record object and commit back to the database or count, search or delete from your tables with a single function call.
	 */
	class Records{

		protected $strTable = null;
		protected $strDatabase = TWIST_DATABASE_NAME;

		/**
		 * Set the table that is being used in the current request
		 * @param string $strTable SQL table name
		 */
		public function __setTable($strTable){
			$this->strTable = $strTable;
		}

		/**
		 * Set the database that is being used in the current request if it is different from TWIST_DATABASE_NAME.
		 * @param string $strDatabase SQL database name
		 */
		public function __setDatabase($strDatabase){
			$this->strDatabase = $strDatabase;
		}

		/**
		 * Create an object of a new database record, the object must be filled out and committed before it will be a record in the database. Once committed you can carry on editing the object if required.
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
		 */
		public function create(){

			//Get the structure of the table
			$arrStructure = \Twist::Database()->table($this->strTable,$this->strDatabase)->structure();

			return (is_null($arrStructure)) ? null : new Record($this->strDatabase,$this->strTable,$arrStructure,array());
		}

		/**
		 * Get an object of a database record with the ability to update and delete, pass in the
		 * If the filter value contains a '%' it will be filtered as a LIKE, if the value contains an array it will be imploded and filtered as an IN otherwise filtering will be done as an EQUALS.
		 * @param null|string|array $mxdValue Value(s) to filter by
		 * @param null|string $strField Field to be filtered
		 * @param bool $blReturnArray Output the raw record array rather an an object (Default: returns an object)
		 * @return null|array|\Twist\Core\Models\Database\Record Returns an editable object of the database record or array if $blReturnArray is true
		 */
		public function get($mxdValue,$strField = 'id',$blReturnArray = false){

			$mxdRecord = ($blReturnArray) ? array() : null;

			$resResult = \Twist::Database()->query("SELECT * FROM `%s`.`%s` WHERE `%s` = '%s' LIMIT 1",
				$this->strDatabase,
				$this->strTable,
				$strField,
				$mxdValue
			);

			if($resResult->status() && $resResult->numberRows()){
				$mxdRecord = $resResult->row();

				if($blReturnArray == false){

					//Get the editable database record
					$mxdRecord = new Record(
						$this->strDatabase,
						$this->strTable,
						\Twist::Database()->table($this->strTable,$this->strDatabase)->structure(),
						$mxdRecord
					);
				}
			}

			return $mxdRecord;
		}

		/**
		 * Get a clone of a database record as an object to be stored as a new record (auto-increment fields will be nulled). The cloned record will not be created/stored until commit has been called on the returned record object.
		 * If the filter value contains a '%' it will be filtered as a LIKE, if the value contains an array it will be imploded and filtered as an IN otherwise filtering will be done as an EQUALS.
		 * @param null|string|array $mxdValue Value(s) to filter by
		 * @param null|string $strField Field to be filtered
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
		 */
		public function copy($mxdValue,$strField = 'id'){

			$resRecord = null;

			//Get the structure of the table
			$arrStructure = \Twist::Database()->table($this->strTable,$this->strDatabase)->structure();

			if(!is_null($arrStructure)){

				$arrRecord = $this->get($mxdValue,$strField,true);
				if(count($arrRecord)){

					//Nullify any auto increment fields
					if(!is_null($arrStructure['auto_increment'])){
						$arrRecord[$arrStructure['auto_increment']] = null;
					}

					$resRecord = new Record($this->strDatabase,$this->strTable,$arrStructure,$arrRecord,true);
				}
			}

			return $resRecord;
		}

		/**
		 * Permanently delete a record form the selected database table, you can set a limit if you only want to delete a specific amount of records.
		 * If the filter value contains a '%' it will be filtered as a LIKE, if the value contains an array it will be imploded and filtered as an IN otherwise filtering will be done as an EQUALS.
		 * @param null|string|array $mxdValue Value(s) to filter by
		 * @param null|string $strField Field to be filtered
		 * @param int $intLimit Limit the number of records to be deleted if more than one match found, pass in NULL to remove the limit
		 * @return bool Status of the delete query
		 */
		public function delete($mxdValue,$strField = 'id',$intLimit = 1){

			$blOut = false;

			$strSQL = sprintf("DELETE FROM `%s`.`%s`%s%s",
				$this->strDatabase,
				$this->strTable,
				$this->buildWhereClause($mxdValue,$strField),
				(is_null($intLimit)) ? '' : sprintf(' LIMIT %d',\Twist::Database()->escapeString($intLimit))
			);

			if(\Twist::Database()->query($strSQL)->status()){
				$blOut = true;
			}

			return $blOut;
		}

		/**
		 * Get a record count of rows in the selected table. You can filter the results using the field and value parameters.
		 * If the filter value contains a '%' it will be filtered as a LIKE, if the value contains an array it will be imploded and filtered as an IN otherwise filtering will be done as an EQUALS.
		 * @param null|string|array $mxdValue Value(s) to filter by
		 * @param null|string $strField Field to be filtered
		 * @return int Number of rows found when searching by the field and value pair
		 */
		public function count($mxdValue = null,$strField = null){

			$intOut = 0;

			$resResult = \Twist::Database()->query(sprintf("SELECT COUNT(*) AS `total` FROM `%s`.`%s`%s",
				\Twist::Database()->escapeString($this->strDatabase),
				\Twist::Database()->escapeString($this->strTable),
				$this->buildWhereClause($mxdValue,$strField)
			));

			if($resResult->status() && $resResult->numberRows()){
				$arrRecord = $resResult->row();
				$intOut = $arrRecord['total'];
			}

			return $intOut;
		}

		/**
		 * Get/Search data in the database, leaving all the parameters blank will return all records form the database. Set the parameters accordingly if you need to filter, order or limit.
		 * If the filter value contains a '%' it will be filtered as a LIKE, if the value contains an array it will be imploded and filtered as an IN otherwise filtering will be done as an EQUALS.
		 * @param null|string|array $mxdValue Value(s) to filter by
		 * @param null|string $strField Field to be filtered
		 * @param null|string $strOrderBy Field to order the search by
		 * @param string $strDirection Direction to order the results in ASC,DESC
		 * @param null|int $intLimit X number of rows to be returned, pass in NULL for no limit
		 * @param null|int $intOffset Offset the results by X amount
		 * @return array Multi-dimensional array of database records/rows
		 */
		public function find($mxdValue = null,$strField = null,$strOrderBy = null,$strDirection = 'ASC',$intLimit = null,$intOffset = null){

			$arrRecords = array();

			$strOrderClause = '';
			if(!is_null($strOrderBy)){

				$arrOrder = (strstr($strOrderBy,',')) ? explode(',',$strOrderBy) : array($strOrderBy);

				$strOrderClause = ' ORDER BY';
				foreach($arrOrder as $strEachOrder){
					$strOrderClause .= sprintf(' `%s` %s,',\Twist::Database()->escapeString($strEachOrder),\Twist::Database()->escapeString($strDirection));
				}

				$strOrderClause = rtrim($strOrderClause,',');
			}

			$resResult = \Twist::Database()->query(sprintf("SELECT * FROM `%s`.`%s`%s%s%s%s",
				\Twist::Database()->escapeString($this->strDatabase),
				\Twist::Database()->escapeString($this->strTable),
				$this->buildWhereClause($mxdValue,$strField),
				$strOrderClause,
				(!is_null($intLimit)) ? sprintf(' LIMIT %d',\Twist::Database()->escapeString($intLimit)) : '',
				(!is_null($intLimit) && !is_null($intOffset)) ? sprintf(',%d',\Twist::Database()->escapeString($intOffset)) : ''
			));

			if($resResult->status() && $resResult->numberRows()){
				$arrRecords = $resResult->rows();
			}

			return $arrRecords;
		}

		/**
		 * Get all the records from a database table.
		 * @alias find
		 * @return array Multi-dimensional array of database records/rows
		 */
		public function all(){
			return $this->find();
		}

		/**
		 * Dynamically build a where clause that will be used to get, search and delete records form the database.
		 * The where clause contains a single parameter and can be a LIKE, Equals or an IN statement.
		 * @param null|string|array $mxdValue Value(s) to filter by
		 * @param null|string $strField Field to be filtered
		 * @return string Formatted where clause that can be used from the query methods in this model
		 */
		protected function buildWhereClause($mxdValue,$strField){

			$strWhereClause = '';

			if(!is_null($mxdValue)){

				if(is_array($mxdValue)){

					array_walk( $mxdValue, array( \Twist::Database(), 'escapeString' ) );

					$strWhereClause = sprintf(" WHERE `%s` IN ('%s')",
						\Twist::Database()->escapeString($strField),
						implode("','",$mxdValue)
					);

				}else{

					$strWhereClause = sprintf(" WHERE `%s` %s '%s'",
						\Twist::Database()->escapeString($strField),
						(strstr($mxdValue,'%')) ? 'LIKE' : '=',
						\Twist::Database()->escapeString($mxdValue)
					);
				}
			}

			return $strWhereClause;
		}
	}
