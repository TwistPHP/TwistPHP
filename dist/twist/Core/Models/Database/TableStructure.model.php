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

					$this->mxdAutoIncrement = $arrStructure['auto_increment'];
					$this->intAutoIncrementStart = $arrStructure['auto_increment_start'];
					$this->mxdPrimaryKey = $arrStructure['primary_key'];
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
		 * Set the Collation for the database table, calling this method will also set the charset of the Table accordingly
		 * @param $strCollation
		 */
		public function collation($strCollation){

			$strCharset = $this->getCollationCharset($strCollation);

			if(!is_null($strCharset)){

				$this->strCollation = $strCollation;

				//Lookup and set charset based on collation
				$this->charset($strCharset);
				$this->arrStructureChanges['collation'] = true;
			}else{
				//Throw invalid collation error
			}
		}

		/**
		 * Get the Charset for a particular collation
		 * @param $strCollation
		 * @return int|null|string
		 */
		protected function getCollationCharset($strCollation){

			$strCharset = null;
			$strCollation = strtolower($strCollation);

			$jsonCharsetCollations = file_get_contents(sprintf('%sCore/Data/database/charset-collations.json',TWIST_FRAMEWORK));

			foreach(json_decode($jsonCharsetCollations,true) as $strCharsetKey => $arrCharSet){
				if(array_key_exists($strCollation,$arrCharSet['collations'])){
					$strCharset = $strCharsetKey;
					break;
				}
			}

			return $strCharset;
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
		 * @param $strComment
		 * @return string
		 */
		public function addUniqueKey($strName,$mxdFields,$strComment = null){

			$this->arrUniqueKey[$strName] = array('comment' => $strComment,'columns' => $mxdFields);
			$this->arrStructureChanges['add_unique'][$strName] = array('comment' => $strComment,'columns' => $mxdFields);
		}

		/**
		 * Set a Index, you can have multiple indexes per table. To create a index from more than 1 field pass the second parameter as an array of fields
		 * @param $strName
		 * @param $mxdFields
		 * @param $strComment
		 */
		public function addIndex($strName,$mxdFields,$strComment = null){

			$this->arrIndexs[$strName] = array('comment' => $strComment,'columns' => $mxdFields);
			$this->arrStructureChanges['add_index'][$strName] = array('comment' => $strComment,'columns' => $mxdFields);
		}

		/**
		 * Drop a unique key from the table structure
		 * @param $strName
		 */
		public function dropUniqueKey($strName){

			if(array_key_exists($strName,$this->arrUniqueKey)){
				unset($this->arrUniqueKey[$strName]);
				$this->arrStructureChanges['drop_unique'][$strName] = true;
			}
		}

		/**
		 * Drop a Index from the table structure
		 * @param $strName
		 */
		public function dropIndex($strName){

			if(array_key_exists($strName,$this->arrIndexs)){
				unset($this->arrIndexs[$strName]);
				$this->arrStructureChanges['drop_index'][$strName] = true;
			}
		}

		/**
		 * Get all the data/information that makes up the column within the table structure
		 * @param string $strColumnName
		 * @return array Column data as an array
		 * @throws \Exception
		 */
		public function column($strColumnName){

			if(array_key_exists($strColumnName,$this->arrStructure)){
				return $this->arrStructure[$strColumnName];
			}else{
				throw new \Exception(sprintf("Column '%s' dose not exist in this table structure",$strColumnName));
			}
		}

		/**
		 * Detect if a column exists in the table structure and return a boolean result. TRUE if the table exists.
		 * @param string $strColumnName Name of the column to be tested
		 * @return bool True returned if table exists
		 */
		public function isColumn($strColumnName){
			return (array_key_exists($strColumnName,$this->arrStructure));
		}

		/**
		 * Add a column into the table, the columns will be added into the table in the order they have been entered
		 * @param string $strColumnName
		 * @param string $strDataType
		 * @param null|int|array $mxdCharLength Char length of field, set an array for enum values
		 * @param null|string $strDefaultValue
		 * @param bool $blNullable
		 * @param null|string $strComment Comment to be stored against the field
		 * @param null|string $strCollation Set the collation if different from that of the table
		 * @throws \Exception
		 */
		public function addColumn($strColumnName,$strDataType,$mxdCharLength=null,$strDefaultValue = null,$blNullable = false,$strComment = null,$strCollation = null){

			if(!array_key_exists($strColumnName,$this->arrStructure)){

				$this->setColumnData($strColumnName,$strDataType,$mxdCharLength,$strDefaultValue,$blNullable,$strComment,$strCollation);

				//Add a new column to the database, we will generate this ALTER SQL upon commit to ensure correct positioning
				$this->arrStructureChanges['add_column'][] = $strColumnName;

			}else{
				//Field already created
				throw new \Exception(sprintf("Column '%s' has already been added to the tables structure",$strColumnName));
			}
		}

		/**
		 * Alter an existing column in the table
		 * @param string $strColumnName
		 * @param string $strDataType
		 * @param null|int|array $mxdCharLength Char length of field, set an array for enum values
		 * @param null|string $strDefaultValue
		 * @param bool $blNullable
		 * @param null|string $strComment Comment to be stored against the field
		 * @param null|string $strCollation Set the collation if different from that of the table
		 * @throws \Exception
		 */
		public function alterColumn($strColumnName,$strDataType,$mxdCharLength=null,$strDefaultValue = null,$blNullable = false,$strComment = null,$strCollation = null){

			if(array_key_exists($strColumnName,$this->arrStructure)){

				$this->setColumnData($strColumnName,$strDataType,$mxdCharLength,$strDefaultValue,$blNullable,$strComment,$strCollation);

				$this->arrStructureChanges['alter_column'][] = $strColumnName;
			}else{
				throw new \Exception(sprintf("Column '%s' doesn't exist in this table",$strColumnName));
			}
		}

		/**
		 * Set and process the column data on behalf of the public functions "addColumn" and "alterColumn"
		 * @param string $strColumnName
		 * @param string $strDataType
		 * @param null|int|array $mxdCharLengthValue Length or Value of field, set an array for enum values
		 * @param null|string $strDefaultValue
		 * @param bool $blNullable
		 * @param null|string $strComment Comment to be stored against the field
		 * @param null|string $strCollation Set the collation if different from that of the table
		 * @throws \Exception
		 */
		protected function setColumnData($strColumnName,$strDataType,$mxdCharLengthValue=null,$strDefaultValue = null,$blNullable = false,$strComment = null,$strCollation = null){

			$arrAllowedTypes = array('int', 'float', 'char', 'varchar', 'text', 'blob', 'enum', 'set', 'date', 'datetime');

			if(in_array(strtolower($strDataType),$arrAllowedTypes)){

				if(array_key_exists($strColumnName,$this->arrStructure)){
					$intOrder = $this->arrStructure[$strColumnName]['order'];
				}else{
					$intOrder = count($this->arrStructure)+1;
				}

				$this->arrStructure[$strColumnName] = array(
					'column_name' => $strColumnName,
					'data_type' => $strDataType,
					'character_length_value' => (in_array(strtolower($strDataType),array('text','date','datetime'))) ? null : $mxdCharLengthValue,
					'nullable' => $blNullable,
					'default_value' => $strDefaultValue,
					'collation' => $strCollation,
					'charset' => (is_null($strCollation)) ? null : $this->getCollationCharset($strCollation),
					'comment' => $strComment,
					'order' => $intOrder
				);

			}else{
				//Field is not an allowed type
				throw new \Exception(sprintf("Column type '%s' is not currently supported in this system",$strDataType));
			}
		}

		/**
		 * Rename a column in the table, provide both the original and new column name. Indexes will be updated accordingly.
		 * @param string $strColumnName Current column name
		 * @param string $strNewColumnName New column name
		 * @throws \Exception
		 */
		public function renameColumn($strColumnName,$strNewColumnName){

			if(!array_key_exists($strNewColumnName,$this->arrStructure)){

				$this->arrStructure[$strNewColumnName] = $this->arrStructure[$strColumnName];
				$this->arrStructure[$strNewColumnName]['column_name'] = $strNewColumnName;

				unset($this->arrStructure[$strColumnName]);

				$this->arrStructureChanges['rename_column'][$strColumnName] = $strNewColumnName;
			}else{
				throw new \Exception(sprintf("Column '%s' already exists",$strNewColumnName));
			}
		}

		/**
		 * Drop a column from the table structure by its column name.
		 * @param string $strColumnName Column to be dropped
		 */
		public function dropColumn($strColumnName){

			unset($this->arrStructure[$strColumnName]);
			$this->arrStructureChanges['drop_column'][] = $strColumnName;
		}

		/**
		 * Set the order of any given column by its name, this will adjust all other column accordingly
		 * @param string $strColumnName Name of column to ne reordered
		 * @param int $intOrder New order position within the table
		 */
		public function setColumnOrder($strColumnName,$intOrder){

			foreach($this->arrStructure as $strKey => $arrEachColumn){

				if($arrEachColumn['column_name'] == $strColumnName){
					$this->arrStructure[$strKey]['order'] = $intOrder;
				}elseif($arrEachColumn['order'] >= $intOrder && $arrEachColumn['column_name'] != $strColumnName){
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

					echo $this->generateAlterQuery($strKeyChange,$mxdValue)."<br>";

					//Generate and run each alter query to make all the necessary changes
					//$blAlterStatus = \Twist::Database()->query($this->generateAlterQuery($strKeyChange,$mxdValue))->status();
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

			//Sort the columns by order
			$arrStructure = \Twist::framework()->tools()->arrayReindex($this->arrStructure,'order');
			ksort($arrStructure);

			$strColumnList = '';
			foreach($arrStructure as $arrEachColumn){
				$strColumnList .= "\t".$this->generateColumnSQL($arrEachColumn).",\n";
			}

			$strColumnList = ($strKeyList == '') ? substr($strColumnList,0,-2)."\n" : $strColumnList;

			$strSQL = sprintf("CREATE TABLE IF NOT EXISTS `%s`.`%s` (\n%s%s) ENGINE=%s DEFAULT CHARSET=%s COLLATE=%s%s%s;",
				$this->strDatabase,
				$this->strTable,
				$strColumnList,
				$strKeyList,
				$this->strEngine,
				$this->strCharset,
				$this->strCollation,
				(!is_null($this->mxdTableComment)) ? sprintf(" COMMENT='%s'",\Twist::Database()->escapeString($this->mxdTableComment)) : '',
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
				foreach($this->arrUniqueKey as $strName => $mxdData){

					$strOut .= sprintf("\tUNIQUE KEY `%s` ( `%s` )%s,\n",
						$strName,
						(is_array($mxdData['columns'])) ? implode('`,`',$mxdData['columns']) : $mxdData['columns'],
						(!is_null($mxdData['comment']) && $mxdData['comment'] != '') ? sprintf(" COMMENT '%s'",\Twist::Database()->escapeString($mxdData['comment'])) : ''
					);
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
				foreach($this->arrIndexs as $strName => $mxdData){

					$strOut .= sprintf("\tKEY `%s` ( `%s` )%s,\n",
						$strName,
						(is_array($mxdData['columns'])) ? implode('`,`',$mxdData['columns']) : $mxdData['columns'],
						(!is_null($mxdData['comment']) && $mxdData['comment'] != '') ? sprintf(" COMMENT '%s'",\Twist::Database()->escapeString($mxdData['comment'])) : ''
					);
				}
			}

			return $strOut;
		}

		/**
		 * Generate a partial column SQL that can be used in CREATE and ALTER queries
		 * @param $arrColumn Array of column data
		 * @return string Partial Column SQL
		 */
		protected function generateColumnSQL($arrColumn){

			switch($arrColumn['data_type']){

				case'char':

					$strColumnSQL = sprintf("`%s` %s(%d) CHARACTER SET %s COLLATE %s%s%s%s",
						$arrColumn['column_name'],
						$arrColumn['data_type'],
						$arrColumn['character_length_value'],
						$this->getCollationCharset($this->strCollation),
						$this->strCollation,
						($arrColumn['nullable'] == true) ? '' : ' NOT NULL',
						(is_null($arrColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$arrColumn['default_value']),
						(!is_null($arrColumn['comment']) && $arrColumn['comment'] != '') ? sprintf(" COMMENT '%s'",\Twist::Database()->escapeString($arrColumn['comment'])) : ''
					);

					break;

				case'text':
				case'blob':

					$strColumnSQL = sprintf("`%s` %s CHARACTER SET %s COLLATE %s%s%s",
						$arrColumn['column_name'],
						$arrColumn['data_type'],
						$this->getCollationCharset($this->strCollation),
						$this->strCollation,
						($arrColumn['nullable'] == true) ? '' : ' NOT NULL',
						(!is_null($arrColumn['comment']) && $arrColumn['comment'] != '') ? sprintf(" COMMENT '%s'",\Twist::Database()->escapeString($arrColumn['comment'])) : ''
					);

					break;

				case'date':
				case'datetime':

					$strColumnSQL = sprintf("`%s` %s%s%s%s",
						$arrColumn['column_name'],
						$arrColumn['data_type'],
						($arrColumn['nullable'] == true) ? '' : ' NOT NULL',
						(is_null($arrColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$arrColumn['default_value']),
						(!is_null($arrColumn['comment']) && $arrColumn['comment'] != '') ? sprintf(" COMMENT '%s'",\Twist::Database()->escapeString($arrColumn['comment'])) : ''
					);

					break;

				case'enum':
				case'set':

					$strColumnSQL = sprintf("`%s` %s('%s') CHARACTER SET %s COLLATE %s%s%s%s",
						$arrColumn['column_name'],
						$arrColumn['data_type'],
						implode("','",$arrColumn['character_length_value']),
						$this->getCollationCharset($this->strCollation),
						$this->strCollation,
						($arrColumn['nullable'] == true) ? '' : ' NOT NULL',
						(is_null($arrColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$arrColumn['default_value']),
						(!is_null($arrColumn['comment']) && $arrColumn['comment'] != '') ? sprintf(" COMMENT '%s'",\Twist::Database()->escapeString($arrColumn['comment'])) : ''
					);

					break;

				default:

					$strColumnSQL = sprintf("`%s` %s(%s)%s%s%s%s",
						$arrColumn['column_name'],
						$arrColumn['data_type'],
						$arrColumn['character_length_value'],
						($arrColumn['nullable'] == true) ? '' : ' NOT NULL',
						(is_null($arrColumn['default_value'])) ? '' : sprintf(" DEFAULT '%s'",$arrColumn['default_value']),
						(!is_null($this->mxdAutoIncrement) && $this->mxdAutoIncrement == $arrColumn['column_name']) ?  ' AUTO_INCREMENT' : '',
						(!is_null($arrColumn['comment']) && $arrColumn['comment'] != '') ? sprintf(" COMMENT '%s'",\Twist::Database()->escapeString($arrColumn['comment'])) : ''
					);

					break;
			}

			return $strColumnSQL;
		}

		protected function getColumnType($strColumnName){

			$strColumnType = null;

			if(array_key_exists($strColumnName,$this->arrStructure)){
				$arrColumn = $this->arrStructure[$strColumnName];

				switch($arrColumn['data_type']){
					case 'text':
					case 'blob':
					case 'date':
					case 'datetime':
						$strColumnType = $arrColumn['data_type'];
						break;
					case'enum':
					case'set':
						$strColumnType = sprintf("%s('%s')",$arrColumn['data_type'],implode("','",$arrColumn['character_length_value']));
						break;
					default:
						$strColumnType = sprintf('%s(%s)',$arrColumn['data_type'],$arrColumn['character_length_value']);
						break;
				}
			}

			return $strColumnType;
		}

		protected function generateAlterQuery($strType,$mxdData){

			$strAlterSQL = '';
			$strTableName = \Twist::Database()->escapeString($this->strTable);

			switch($strType){

				case'collation':
					$strAlterSQL = sprintf("ALTER TABLE `%s` DEFAULT CHARACTER SET %s COLLATE %s;",
						$strTableName,
						\Twist::Database()->escapeString($this->strCharset),
						\Twist::Database()->escapeString($this->strCollation)
					);
					break;

				case'engine':
					$strAlterSQL = sprintf("ALTER TABLE `%s` ENGINE = %s;",
						$strTableName,
						\Twist::Database()->escapeString($this->strEngine)
					);
					break;

				case'comment':
					$strAlterSQL = sprintf("ALTER TABLE `%s` COMMENT = '%s';",
						$strTableName,
						\Twist::Database()->escapeString($this->mxdTableComment)
					);
					break;

				case'auto_increment':
					$strAlterSQL = sprintf("ALTER TABLE `%s` auto_increment = %d;",
						$strTableName,
						\Twist::Database()->escapeString($this->intAutoIncrementStart)
					);
					break;

				case'primary_key':

					//ALTER TABLE `%s` change id id int(11);
					//ALTER TABLE `%s` DROP PRIMARY KEY;
					//ALTER TABLE `%s` ADD PRIMARY KEY (uuid);


					//ALTER TABLE `%s` DROP PRIMARY KEY;
					//ALTER TABLE `%s` ADD PRIMARY KEY(`id`);

					//ALTER TABLE `asset_content` ADD PRIMARY KEY (`id`)
					$strAlterSQL = sprintf("todo -- primary key",
						$strTableName,
						\Twist::Database()->escapeString($this->intAutoIncrementStart)
					);
					break;

				case'add_index':

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $strName => $mxdKeyData){

						$strAlterSQL .= sprintf("ALTER TABLE `%s` ADD KEY `%s` ( `%s` )%s;",
							$strTableName,
							$strName,
							(is_array($mxdKeyData['columns'])) ? implode('`,`',$mxdKeyData['columns']) : $mxdKeyData['columns'],
							(!is_null($mxdKeyData['comment']) && $mxdKeyData['comment'] != '') ? sprintf(" COMMENT '%s'",$mxdKeyData['comment']) : ''
						);
					}

					break;

				case'add_unique':

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $strName => $mxdKeyData){

						//Could add comments later  COMMENT 'my commnet'
						$strAlterSQL .= sprintf("ALTER TABLE `%s` ADD UNIQUE KEY `%s` ( `%s` )%s;",
							$strTableName,
							$strName,
							(is_array($mxdKeyData['columns'])) ? implode('`,`',$mxdKeyData['columns']) : $mxdKeyData['columns'],
							(!is_null($mxdKeyData['comment']) && $mxdKeyData['comment'] != '') ? sprintf(" COMMENT '%s'",$mxdKeyData['comment']) : ''
						);
					}

					break;

				case'drop_index':

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $strName => $mxdFields){

						//Could add comments later  COMMENT 'my comment'
						$strAlterSQL .= sprintf("ALTER TABLE `%s` DROP INDEX `%s`;",
							$strTableName,
							$strName
						);
					}

					break;

				case'drop_unique':

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $strName => $mxdFields){

						//Could add comments later  COMMENT 'my comment'
						$strAlterSQL .= sprintf("ALTER TABLE `%s` DROP UNIQUE `%s`;",
							$strTableName,
							$strName
						);
					}

					break;

				case'add_column':

					//AFTER name;
					//FIRST;

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $intKey => $strColumnName){

						$strAlterSQL .= sprintf("ALTER TABLE `%s` ADD %s",
							$strTableName,
							$this->generateColumnSQL($this->arrStructure[$strColumnName])
						);
					}

					break;

				case'alter_column':

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $intKey => $strColumnName){

						$strAlterSQL .= sprintf("ALTER TABLE `%s` CHANGE `%s` %s",
							$strTableName,
							$strColumnName,
							$this->generateColumnSQL($this->arrStructure[$strColumnName])
						);
					}

					break;

				case'drop_column':

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $intKey => $strColumnName){

						$strAlterSQL .= sprintf("ALTER TABLE `%s` DROP `%s`;",
							$strTableName,
							\Twist::Database()->escapeString($strColumnName)
						);
					}

					break;

				case'rename_column':

					$strAlterSQL = '';

					//Loop through $mxdData
					foreach($mxdData as $strColumnName => $strNewColumnName){

						$strAlterSQL .= sprintf("ALTER TABLE `%s` CHANGE `%s` `%s` %s;",
							$strTableName,
							\Twist::Database()->escapeString($strColumnName),
							\Twist::Database()->escapeString($strNewColumnName),
							$this->getColumnType($strNewColumnName)
						);
					}

					break;
			}

			return $strAlterSQL;
		}
	}