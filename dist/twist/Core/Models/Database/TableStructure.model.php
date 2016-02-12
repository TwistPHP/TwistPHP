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
	class TableStructure{

		protected $strDatabase = null;
		protected $strTable = null;

		protected $arrStructure = array();
		protected $arrStructureChanges = array();
		protected $blNewTable = true;

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
		public function __construct($strDatabase,$strTable,$arrStructure = array()){

			$this->strDatabase = $strDatabase;
			$this->strTable = $strTable;

			if(count($arrStructure)){
				$this->blNewTable = false;
				$this->arrStructure = $arrStructure['columns'];

				if(!is_null($arrStructure['auto_increment'])){
					$this->autoIncrement($arrStructure['auto_increment'],$arrStructure['auto_increment_start']);
				}

				//Set all the other key information for this table
				$this->arrUniqueKey = $arrStructure['unique_keys'];
				$this->arrIndexs = $arrStructure['indexes'];
				$this->mxdTableComment = $arrStructure['table_comment'];
				$this->strCollation = $arrStructure['collation'];
				$this->strCharset = $arrStructure['charset'];
				$this->strEngine = $arrStructure['engine'];
			}
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
		 * Set a new table/database, this is only to be used when cloning/copying a database
		 * @param string $strTable
		 * @param null|string $strDatabase
		 */
		public function copyTo($strTable,$strDatabase = null){

			$this->strTable = $strTable;

			if(!is_null($strDatabase)){
				$this->strDatabase = $strDatabase;
			}
		}

		/**
		 * Set the Collation for the database table
		 * @param $strCollation
		 */
		public function collation($strCollation){

			$this->strCollation = $strCollation;

			$strNewCharset = '';

			//Lookup and set charset based on collation
			$this->charset($strNewCharset);
			$this->arrStructureChanges['collation'] = true;
		}

		/**
		 * Set the character set for the database table
		 * @param $strCharset
		 */
		protected function charset($strCharset){
			$this->strCharset = $strCharset;
		}

		/**
		 * Set the database engine to use for this table
		 * @param $strEngine
		 */
		public function engine($strEngine){
			$this->strEngine = $strEngine;
			$this->arrStructureChanges['engine'] = true;
		}

		/**
		 * Set the main Database comment
		 * @param $strComment
		 */
		public function comment($strComment){
			$this->mxdTableComment = $strComment;
			$this->arrStructureChanges['comment'] = true;
		}

		/**
		 * Set a field to be autoincrement
		 * @param $strField
		 * @param int $intStartNumber
		 * @throws \Exception
		 */
		public function autoIncrement($strField,$intStartNumber = 1){

			if(array_key_exists($strField,$this->arrStructure) && $this->arrStructure[$strField]['data_type'] == 'int'){
				$this->mxdAutoIncrement = null;

				$this->primaryKey($strField);
				$this->mxdAutoIncrement = $strField;
				$this->intAutoIncrementStart = $intStartNumber;

				$this->arrStructureChanges['auto_increment'] = true;

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
		public function primaryKey($strField){

			if(is_null($this->mxdAutoIncrement)){
				$this->mxdPrimaryKey = $strField;

				$this->arrStructureChanges['primary_key'] = true;
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

			//Add a unique field key to the table
			$this->arrStructureChanges['add_unique'][$strName] = $mxdFields;
		}

		/**
		 * Set a Index, you can have multiple indexes per table. To create a index from more than 1 field pass the second parameter as an array of fields
		 * @param $strName
		 * @param $mxdFields
		 */
		public function addIndex($strName,$mxdFields){
			$this->arrIndexs[$strName] = $mxdFields;

			//Add an field index key to the table
			$this->arrStructureChanges['add_index'][$strName] = $mxdFields;
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
						'nullable' => $blNullable,
						'default_value' => $strDefaultValue,
						'comment' => null,
						'order' => count($this->arrStructure)+1
					);

					//Add a new field to the database, we will generate this ALTER SQL upon commit to ensure correct positioning
					$this->arrStructureChanges['add_field'][] = $strColumnName;
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
		 * Set the order of any given field by its name, this will adjust all other field accordingly
		 * @param string $strColumnName Name of field to ne reordered
		 * @param int $intOrder New order position within the table
		 */
		public function setFieldOrder($strColumnName,$intOrder){

			foreach($this->arrStructure as $strKey => $arrEachField){

				if($arrEachField['column_name'] == $strColumnName){
					$this->arrStructure[$strKey]['order'] = $intOrder;
				}elseif($arrEachField['order'] >= $intOrder && $arrEachField['column_name'] != $strColumnName){
					$this->arrStructure[$strKey]['order']++;
				}
			}
		}

		/**
		 * Final call, this will create/alter the tables structure in the database.
		 * @return bool
		 */
		public function commit(){

			$blOut = false;

			if($this->blNewTable){

				$strSQL = $this->sql();
				$blOut = \Twist::Database()->query($strSQL)->status();

				//Reset the new table key so that you can now alter the table
				$this->blNewTable = false;

			}elseif(count($this->arrStructureChanges)){

				foreach($this->arrStructureChanges as $strKeyChange => $mxdValue){
					//Generate and run each alter query to make all the necessary changes
					$blAlterStatus = \Twist::Database()->query($this->generateAlterQuery($strKeyChange,$mxdValue))->status();
				}

				//Reset the changes array so that you can continue using the db object
				$this->arrStructureChanges = array();
				$blOut = true;
			}

			return $blOut;
		}

		/**
		 * This will generate the create SQL command and output for you to use.
		 * @return string
		 */
		public function sql(){

			$strKeyList = $this->generatePrimaryKey();
			$strKeyList .= $this->generateUniqueKey();
			$strKeyList .= $this->generateIndexes();

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
		protected function generateIndexes(){

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

			//Sort the fields by order
			$arrStructure = \Twist::framework()->tools()->arrayReindex($this->arrStructure,'order');
			ksort($arrStructure);

			foreach($arrStructure as $strEachColumn){

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

		protected function generateAlterQuery($strType,$mxdData){

			$strAlterSQL = '';
			$strTableName = \Twist::Database()->escape($this->strTable);

			switch($strType){

				case'collation':
					$strAlterSQL = sprintf("ALTER TABLE `%s` DEFAULT CHARACTER SET %s COLLATE %s;",
						$strTableName,
						\Twist::Database()->escape($this->strCharset),
						\Twist::Database()->escape($this->strCollation)
					);
					break;

				case'engine':
					$strAlterSQL = sprintf("ALTER TABLE `%s` ENGINE = %s;",
						$strTableName,
						\Twist::Database()->escape($this->strEngine)
					);
					break;

				case'comment':
					$strAlterSQL = sprintf("ALTER TABLE `%s` COMMENT = '%s';",
						$strTableName,
						\Twist::Database()->escape($this->mxdTableComment)
					);
					break;

				case'auto_increment':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'primary_key':

					//ALTER TABLE `%s` change id id int(11);
					//ALTER TABLE `%s` DROP PRIMARY KEY;
					//ALTER TABLE `%s` ADD PRIMARY KEY (uuid);


					//ALTER TABLE `%s` DROP PRIMARY KEY;
					//ALTER TABLE `%s` ADD PRIMARY KEY(`id`);

					$strAlterSQL = sprintf("",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'add_index':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'add_unique':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'remove_index':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'remove_unique':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'add_field':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'alter_field':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;

				case'remove_field':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escape($this->intAutoIncrementStart)
					);
					break;
			}

			return $strAlterSQL;
		}
	}