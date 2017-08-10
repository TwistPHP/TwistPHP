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
	 * Simply create and maintain tables in an object orientated way with no need to write a SQL queries
	 */
	class Table{

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
		 * Test to see if a database table exists already, returns a boolean stats for the table
		 * @param bool $blAddTwistPrefix Add the TWIST_DATABASE_TABLE_PREFIX to the start of the table name
		 * @return bool
		 */
		public function exists($blAddTwistPrefix = false){

			$strFindTable = ($blAddTwistPrefix) ? sprintf('%s%s',TWIST_DATABASE_TABLE_PREFIX,$this->strTable) : $this->strTable;

			$resResult = \Twist::Database()->query("SELECT 'exists' AS `status` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '%s' AND  TABLE_NAME = '%s'",
				$this->strDatabase,
				$strFindTable
			);

			return ($resResult->status() && $resResult->numberRows());
		}

		/**
		 * Get a database table as an object, if the table does not exist you are returned null
		 * @return null|\Twist\Core\Models\Database\TableStructure Returns and object of the database table
		 */
		public function get(){

			if($this->exists()){
				return new TableStructure($this->strDatabase,$this->strTable,$this->structure());
			}

			return null;
		}

		/**
		 * Create a database table as an object, if the table already exists you are returned null
		 * @return null|\Twist\Core\Models\Database\TableStructure Returns and object of the database table
		 */
		public function create(){

			if(!$this->exists()){
				return new TableStructure($this->strDatabase,$this->strTable);
			}

			return null;
		}

		/**
		 * Get the structure information of a database table, the structure is returned as an array.
		 * @related table
		 * @return array Single dimensional array of structure info
		 */
		public function structure(){

			$arrStructure = null;
			$strTable = $this->strTable;
			$strDatabaseName = $this->strDatabase;

			$arrStructure = \Twist::Cache('twist/helper/database')->read(sprintf('dbStructure-%s+%s',$strDatabaseName,$strTable));

			if(is_null($arrStructure)){

				$resResult = \Twist::Database()->query("SELECT `COLUMN_NAME` AS `column_name`,
										`DATA_TYPE` AS `data_type`,
										`CHARACTER_MAXIMUM_LENGTH` AS `character_length_value`,
										`IS_NULLABLE` AS `nullable`,
										`COLUMN_DEFAULT` AS `default_value`,
										`COLUMN_KEY` AS `key`,
										`COLUMN_TYPE` AS `column_type`,
										`EXTRA` AS `extra`,
										`COLUMN_COMMENT` AS `comment`,
										`CHARACTER_SET_NAME` AS `charset`,
										`COLLATION_NAME` AS `collation`,
										`ORDINAL_POSITION` AS `order`
									FROM `information_schema`.`COLUMNS`
									WHERE `TABLE_NAME` = '%s'
									AND `TABLE_SCHEMA` = '%s'",
					$strTable,
					$strDatabaseName
				);

				if($resResult->status() && $resResult->numberRows()){
					$arrStructureData = $resResult->rows();

					$arrStructure = array(
						'columns' => array(),
						'auto_increment' => null,
						'auto_increment_start' => 1,
						'primary_key' => null,
						'unique_keys' => array(),
						'indexes' => array(),
						'table_comment' => null,
						'collation' => 'utf8_unicode_ci',
						'charset' => 'utf8',
						'engine' => 'MyISAM',
					);

					$resKeyResults = \Twist::Database()->query("SELECT `INDEX_NAME` AS `index_name`,
										`NON_UNIQUE` AS `non_unique`,
										`SEQ_IN_INDEX` AS `order`,
										`COLUMN_NAME` AS `column_name`,
										`INDEX_COMMENT` AS `comment`
									FROM `information_schema`.`statistics`
									WHERE `table_schema` NOT IN ('information_schema','mysql','performance_schema')
									AND `TABLE_NAME` = '%s'
									AND `TABLE_SCHEMA` = '%s'",
						$strTable,
						$strDatabaseName
					);

					if($resKeyResults->status() && $resKeyResults->numberRows()) {
						$arrKeyData = $resKeyResults->rows();

						foreach($arrKeyData as $arrKeys){

							if($arrKeys['index_name'] == 'PRIMARY'){
								$arrStructure['primary_key'] = $arrKeys['column_name'];
							}else{

								$strKeyType = ($arrKeys['non_unique'] == '0') ? 'unique_keys' : 'indexes';

								if(!array_key_exists($arrKeys['index_name'],$arrStructure[$strKeyType])){
									$arrStructure[$strKeyType][$arrKeys['index_name']] = array('comment' => '', 'columns' => array());
								}

								$arrStructure[$strKeyType][$arrKeys['index_name']]['comment'] = $arrKeys['comment'];
								$arrStructure[$strKeyType][$arrKeys['index_name']]['columns'][$arrKeys['order']] = $arrKeys['column_name'];
							}
						}
					}

					foreach($arrStructureData as $arrEachItem){

						$arrEachItem['nullable'] = ($arrEachItem['nullable'] == 'YES');

						if($arrEachItem['extra'] == 'auto_increment'){
							$arrEachItem['auto_increment'] = true;
							$arrStructure['auto_increment'] = $arrEachItem['column_name'];
						}

						//Place the enum values in the char length field
						if($arrEachItem['data_type'] == 'enum' || $arrEachItem['data_type'] == 'set'){
							preg_match_all("#\'([^\']*)\'#",$arrEachItem['column_type'],$arrMatches);
							$arrEachItem['character_length_value'] = (count($arrMatches) == 2) ? $arrMatches[1] : array();
						}elseif($arrEachItem['data_type'] == 'int'){
							preg_match("#int\(([0-9]+)\)#",$arrEachItem['column_type'],$arrMatches);
							$arrEachItem['character_length_value'] = (count($arrMatches)) ? $arrMatches[1] : 11;
						}elseif($arrEachItem['data_type'] == 'float'){
							preg_match("#float\(([0-9]+\,[0-9]+)\)#",$arrEachItem['column_type'],$arrMatches);
							$arrEachItem['character_length_value'] = (count($arrMatches)) ? $arrMatches[1] : '6,2';
						}

						//Set the data back into the structure array
						$arrStructure['columns'][$arrEachItem['column_name']] = $arrEachItem;
					}

					//PHP session cache only expire when page is loaded
					\Twist::Cache('twist/helper/database')->write(sprintf('dbStructure-%s+%s',$strDatabaseName,$strTable),$arrStructure,0);
				}
			}

			return $arrStructure;
		}

		/**
		 * Remove the database table structure cache file, this is used when alterning a table or can be caled manualy if required
		 */
		public function clearStructureCache(){
			\Twist::Cache('twist/helper/database')->remove(sprintf('dbStructure-%s+%s',$this->strDatabase,$this->strTable));
		}

		/**
		 * Copy an excising table structure into a new object, the new table will not exists until you commit the returned object.
		 * @param string $strNewTable
		 * @param null $strNewDatabase
		 * @return null|\Twist\Core\Models\Database\TableStructure Returns and object of the database table
		 */
		public function copy($strNewTable,$strNewDatabase = null){

			$resTable = $this->get();
			$resTable->copyTo($strNewTable,$strNewDatabase);

			return $resTable;
		}

		/**
		 * Rename the table to a new name withing your database, this command will only work if your database user has the RENAME privilege or above.
		 * @param string $strNewTable New table name to be used
		 * @return bool
		 */
		public function rename($strNewTable){
			return \Twist::Database()->query("RENAME TABLE `%s`.`%s` TO `%s`.`%s`;",$this->strDatabase,$this->strTable,$this->strDatabase,$strNewTable)->status();
		}

		/**
		 * Optimize the table in your database, this command will only work if your database user has the OPTIMIZE privilege or above.
		 * @return bool
		 */
		public function optimize(){
			return \Twist::Database()->query("OPTIMIZE TABLE `%s`.`%s`;",$this->strDatabase,$this->strTable)->status();
		}

		/**
		 * Truncate the table in your database, this command will only work if your database user has the TRUNCATE privilege or above.
		 * @return bool
		 */
		public function truncate(){
			return \Twist::Database()->query("TRUNCATE TABLE `%s`.`%s`;",$this->strDatabase,$this->strTable)->status();
		}

		/**
		 * Drop the requested table form your database, this command will only work if your database user has the DROP privilege or above.
		 * @return bool
		 */
		public function drop(){
			return \Twist::Database()->query("DROP TABLE IF EXISTS `%s`.`%s`;",$this->strDatabase,$this->strTable)->status();
		}
	}