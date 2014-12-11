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
	 * Simply create tables in an object orientated way with no need to write a mysql query
	 */
	class DatabaseTable{

		protected $strDatabase = null;
		protected $strTable = null;
		protected $arrStructure = array();
		protected $mxdAutoIncrement = null;
		protected $intAutoIncrementStart = 1;
		protected $mxdPrimaryKey = null;
		protected $arrUniqueKey = array();
		protected $arrIndexs = array();
		protected $mxdTableComment = null;
		protected $strCollation = 'utf8_unicode_ci';
		protected $strCharset = 'utf8';
		protected $strEngine = 'MyISAM';

		/**
		 * Construct the class with all the required data to make usable
		 * @param $strDatabase
		 * @param $strTable
		 */
		public function __construct($strDatabase,$strTable){
			$this->strDatabase = $strDatabase;
			$this->strTable = $strTable;
		}

		/**
		 * Destruct the class so it cannot be used anymore
		 */
		public function __destruct(){
			$this->strDatabase = null;
			$this->strTable = null;
			$this->arrStructure = null;
			$this->mxdAutoIncrement = null;
			$this->intAutoIncrementStart = null;
			$this->mxdPrimaryKey = null;
			$this->arrUniqueKey = null;
			$this->arrIndexs = null;
			$this->mxdTableComment = null;
			$this->strCollation = null;
			$this->strCharset = null;
			$this->strEngine = null;
		}

		/**
		 * Set the Collation for the database table
		 * @param $strCollation
		 */
		public function setCollation($strCollation){
			$this->strCollation = $strCollation;
		}

		/**
		 * Set the character set for the database table
		 * @param $strCharset
		 */
		public function setCharset($strCharset){
			$this->strCharset = $strCharset;
		}

		/**
		 * Set the database engine to use for this table
		 * @param $strEngine
		 */
		public function setEngine($strEngine){
			$this->strEngine = $strEngine;
		}

		/**
		 * Set the main Database comment
		 * @param $strComment
		 */
		public function setComment($strComment){
			$this->mxdTableComment = $strComment;
		}

		/**
		 * Set a field to be autoincrement
		 * @param $strField
		 * @param int $intStartNumber
		 * @throws \Exception
		 */
		public function setAutoIncrement($strField,$intStartNumber = 1){

			if(array_key_exists($strField,$this->arrStructure) && $this->arrStructure[$strField]['data_type'] == 'int'){
				$this->mxdAutoIncrement = null;

				$this->setPrimaryKey($strField);
				$this->mxdAutoIncrement = $strField;
				$this->intAutoIncrementStart = $intStartNumber;
			}else{
				//Field must have already been added and can only be an integer
				throw new \Exception(sprintf("Field '%s' must have already been added to the table and can only be an integer",$strField));
			}
		}

		/**
		 * Set a field to be a primary key
		 * @param $strField
		 * @throws \Exception
		 */
		public function setPrimaryKey($strField){

			if(is_null($this->mxdAutoIncrement)){
				$this->mxdPrimaryKey = $strField;
			}else{
				//error cannot set primary key, when using auto increment
				throw new \Exception("Error, cannot set a primary key when using auto increment");
			}
		}

		/**
		 * Set a unique key, you can have multiple unique keys per table. To create a unique key from more than 1 field pass the second parameter as an array of fields
		 * @param $strName
		 * @param $mxdFields
		 * @return string
		 */
		public function addUniqueKey($strName,$mxdFields){
			$this->arrUniqueKey[$strName] = $mxdFields;
		}

		/**
		 * Set a Index, you can have multiple indexs per table. To create a index from more than 1 field pass the second parameter as an array of fields
		 * @param $strName
		 * @param $mxdFields
		 */
		public function addIndex($strName,$mxdFields){
			$this->arrIndexs[$strName] = $mxdFields;
		}

		/**
		 * Add a field into the table, the fields will be added into the table in the order they have been entered
		 * @param $strColumnName
		 * @param $strDataType
		 * @param null $mxdCharLength
		 * @param null $strDefaultValue
		 * @param bool $blNullable
		 * @throws \Exception
		 */
		public function addField($strColumnName,$strDataType,$mxdCharLength=null,$strDefaultValue = null,$blNullable = false){

			$arrAllowedTypes = array('int', 'char', 'varchar', 'text', 'enum', 'date', 'datetime');

			if(!array_key_exists($strColumnName,$this->arrStructure)){

				if(in_array(strtolower($strDataType),$arrAllowedTypes)){

					$this->arrStructure[$strColumnName] = array(
						'column_name' => $strColumnName,
						'data_type' => $strDataType,
						'character_length' => (in_array(strtolower($strDataType),array('text','date','datetime'))) ? null : $mxdCharLength,
						'nullable' => ($blNullable) ? true : false,
						'default_value' => $strDefaultValue,
						'comment' => null
					);
				}else{
					//Field is not an allowed type
					throw new \Exception(sprintf("Field type '%s' is not currently supported in this system",$strDataType));
				}
			}else{
				//Field already created
				throw new \Exception(sprintf("Field '%s' has already been added to the tables structure",$strColumnName));
			}
		}

		/**
		 * Final call, this will create the table in the database, once created this resource will become unusable
		 * @return bool
		 */
		public function create(){

			$blOut = false;
			$strSQL = $this->createSQL();

			$objDatabase = \Twist::Database();

			if($objDatabase->query($strSQL)){
				$blOut = true;
			}

			$this->__destruct();
			return $blOut;
		}

		/**
		 * This will generate the create SQL command and output for you to use.
		 * @return string
		 */
		public function createSQL(){

			$strKeyList = $this->generatePrimaryKey();
			$strKeyList .= $this->generateUniqueKey();
			$strKeyList .= $this->generateIndexs();

			$strKeyList = ($strKeyList != '') ? substr($strKeyList,0,-2)."\n" : '';

			$strFieldList = $this->generateFieldList();
			$strFieldList = ($strKeyList == '') ? substr($strFieldList,0,-2)."\n" : $strFieldList;

			$strSQL = sprintf("CREATE TABLE IF NOT EXISTS `%s`.`%s` (\n%s%s) ENGINE=%s DEFAULT CHARSET=%s COLLATE=%s%s%s;",
				$this->strDatabase,
				$this->strTable,
				$strFieldList,
				$strKeyList,
				$this->strEngine,
				$this->strCharset,
				$this->strCollation,
				(!is_null($this->mxdTableComment)) ? sprintf(" COMMENT='%s'",$this->mxdTableComment) : '',
				(!is_null($this->mxdAutoIncrement)) ? sprintf(' AUTO_INCREMENT=%d',$this->intAutoIncrementStart) : ''
			);

			return $strSQL;
		}

		/**
		 * Generate the primary keys that can be used in the create query
		 * @return string
		 */
		protected function generatePrimaryKey(){

			$strOut = '';

			if(!is_null($this->mxdPrimaryKey)){
				$strOut .= sprintf("\tPRIMARY KEY (`%s`),\n",$this->mxdPrimaryKey);
			}

			return $strOut;
		}

		/**
		 * Generate the unique keys that can be used in the create query
		 * @return string
		 */
		protected function generateUniqueKey(){

			$strOut = '';

			if(count($this->arrUniqueKey) > 0){
				foreach($this->arrUniqueKey as $strName => $mxdFields){

					$arrFields = array();

					if(is_array($mxdFields)){

						foreach($mxdFields as $strFiled){
							$arrFields[] = sprintf("`%s`",$strFiled);
						}

						$strOut .= sprintf("\tUNIQUE KEY `%s` ( %s ),\n",$strName,implode(',',$arrFields));
					}else{
						$strOut .= sprintf("\tUNIQUE KEY `%s` ( %s ),\n",$strName,$mxdFields);
					}
				}
			}

			return $strOut;
		}

		/**
		 * Generate the indexes that can be used in the create query
		 * @return string
		 */
		protected function generateIndexs(){

			$strOut = '';

			if(count($this->arrIndexs) > 0){
				foreach($this->arrIndexs as $strName => $mxdFields){

					$arrFields = array();

					if(is_array($mxdFields)){

						foreach($mxdFields as $strFiled){
							$arrFields[] = sprintf("`%s`",$strFiled);
						}

						$strOut .= sprintf("\tKEY `%s` ( %s ),\n",$strName,implode(',',$arrFields));
					}else{
						$strOut .= sprintf("\tKEY `%s` ( %s ),\n",$strName,$mxdFields);
					}
				}
			}

			return $strOut;
		}

		/**
		 * Generate a field list to be added to the create query
		 * @return string
		 */
		protected function generateFieldList(){

			$strOut = '';

			foreach($this->arrStructure as $strEachColumn){

				switch($strEachColumn['data_type']){

					case'char':

						$strOut .= sprintf("\t`%s` %s(%d) COLLATE %s%s%s,\n",
							$strEachColumn['column_name'],
							$strEachColumn['data_type'],
							$strEachColumn['character_length'],
							$this->strCollation,
							($strEachColumn['nullable'] == true) ? '' : ' NOT NULL',
							(is_null($strEachColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$strEachColumn['default_value'])

						);

						break;

					case'text':

						$strOut .= sprintf("\t`%s` %s COLLATE %s%s%s,\n",
							$strEachColumn['column_name'],
							$strEachColumn['data_type'],
							$this->strCollation,
							($strEachColumn['nullable'] == true) ? '' : ' NOT NULL',
							(is_null($strEachColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$strEachColumn['default_value'])

						);

						break;

					case'date':
					case'datetime':

						$strOut .= sprintf("\t`%s` %s%s%s,\n",
							$strEachColumn['column_name'],
							$strEachColumn['data_type'],
							($strEachColumn['nullable'] == true) ? '' : ' NOT NULL',
							(is_null($strEachColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$strEachColumn['default_value'])
						);

						break;

					default:

						$strOut .= sprintf("\t`%s` %s(%s)%s%s%s,\n",
							$strEachColumn['column_name'],
							$strEachColumn['data_type'],
							$strEachColumn['character_length'],
							($strEachColumn['nullable'] == true) ? '' : ' NOT NULL',
							(is_null($strEachColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$strEachColumn['default_value']),
							(!is_null($this->mxdAutoIncrement) && $this->mxdAutoIncrement == $strEachColumn['column_name']) ?  ' AUTO_INCREMENT' : ''

						);

						break;
				}
			}

			return $strOut;
		}
	}