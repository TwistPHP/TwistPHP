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
	 * Simply create tables in an object orientated way with no need to write a mysql query
	 */
	class Records{

		protected $strTable = null;
		protected $strDatabase = TWIST_DATABASE_NAME;

		public function __setTable($strTable){
			$this->strTable = $strTable;
		}

		public function __setDatabase($strDatabase){
			$this->strDatabase = $strDatabase;
		}

		/**
		 * Create a new database record (row) an empty object will be returned from this function, you can then populate the record and commit your changes
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
		 */
		public function create(){

			//Get the structure of the table
			$arrStructure = \Twist::Database()->tables($this->strTable,$this->strDatabase)->structure();

			return (is_null($arrStructure)) ? null : new \Twist\Core\Models\Database\Record($this->strDatabase,$this->strTable,$arrStructure,array());
		}

		/**
		 * Get an object of a database record with the ability to update and delete A WHERE clause is generated in the form "WHERE $strField = $mxdValue", the default field being "id"
		 * @param $mxdValue
		 * @param string $strField
		 * @param bool $blReturnArray Output the raw record array rather an an object (Default: returns an object)
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
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
				$mxdRecord = $resResult->getArray();

				if($blReturnArray == false){

					//Get the editable database record
					$mxdRecord = new \Twist\Core\Models\Database\Record(
						$this->strDatabase,
						$this->strTable,
						\Twist::Database()->tables($this->strTable,$this->strDatabase)->structure(),
						$mxdRecord
					);
				}
			}

			return $mxdRecord;
		}

		/**
		 * Get a clone of a database record as an object to be stored as a new record (auto-increment fields will be nulled). The cloned record will not be created/stored until commit has been called on the returned record object.
		 * The where clause is generated from the second parameter, must be an array for example to get the user with the 'id' of 1 pass in array('id' => 1)
		 * @param $mxdValue
		 * @param string $strField
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
		 */
		public function copy($mxdValue,$strField = 'id'){

			$resRecord = null;

			//Get the structure of the table
			$arrStructure = \Twist::Database()->tables($this->strTable,$this->strDatabase)->structure();

			if(!is_null($arrStructure)){

				$arrRecord = $this->get($mxdValue,$strField,true);
				if(count($arrRecord)){

					//Nullify any auto increment fields
					if(!is_null($arrStructure['auto_increment'])){
						$arrRecord[$arrStructure['auto_increment']] = null;
					}

					$resRecord = new \Twist\Core\Models\Database\Record($this->strDatabase,$this->strTable,$arrStructure,$arrRecord,true);
				}
			}

			return $resRecord;
		}

		/**
		 * Delete a record permanent form the database
		 * @param $mxdValue
		 * @param string $strField
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
		 * Get a count of records (rows) as an array. The where clause is generated from the second parameter, must be an array.
		 * For example to get the user with the 'id' of 1 pass in array('id' => 1) you could look for the user by email with a wild card array('email' => 'dan@%')
		 * The where array accepts multiple parameters at a time
		 * @param $mxdValue
		 * @param $strField
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
				$arrRecord = $resResult->getArray();
				$intOut = $arrRecord['total'];
			}

			return $intOut;
		}

		/**
		 * Get a set of records (rows) as an array. The where clause is generated from the second parameter, must be an array. For example to get the user with the 'id' of 1 pass in array('id' => 1) you could look for the user by email with a wild card array('email' => 'dan@%')
		 * The where array accepts multiple parameters at a time.
		 *
		 * @param $mxdValue
		 * @param $strField
		 * @param $strOrderBy
		 * @param $intLimit
		 * @param $intOffset
		 * @return array
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
				$arrRecords = $resResult->getFullArray();
			}

			return $arrRecords;
		}

		/**
		 * @param $mxdValue
		 * @param $strField
		 * @return string
		 */
		protected function buildWhereClause($mxdValue,$strField){

			$strWhereClause = '';

			if(!is_null($mxdValue)){

				if(is_array($mxdValue)){

					array_walk( $mxdValue, array( \Twist::Database(), 'escapeString' ) );

					$strWhereClause = sprintf(" WHERE `%s` IN (%s)",
						\Twist::Database()->escapeString($strField),
						implode(',',$mxdValue)
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