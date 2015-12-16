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

	namespace Twist\Core\Utilities;

	use \Twist\Classes\Instance;

	/**
	 * A utility to simplify database connections for your PHP projects. Allowing connections to be made using MySQL, MySQLi and PDO.
	 * Connections to multiple database servers can be created and used side by site with this unique instancable utility.
	 */
	class Database extends Base{

		protected $resLibrary = null;
		protected $resResult = null;
		protected $strConnectionKey = null;
		protected $blConnectionAttempt = false;
		protected $strDatabaseName = null;
		protected $blNoDatabase = false;
		protected $blDebugMode = false;
		protected $strLastRunQuery = '';

		public function __construct($strConnectionKey){
			$this->strConnectionKey = $strConnectionKey;
		}

		public function __destruct(){
			$this->close();
		}

		public function debugMode(){

			if($this->blDebugMode == false && defined('TWIST_LAUNCHED')){
				if(\Twist::framework()->setting('DEVELOPMENT_MODE') && \Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR')){
					$this->blDebugMode = true;
				}
			}

			return $this->blDebugMode;
		}

		/**
		 * Make the main connection to the database, automatically called if a custom connection is not required
		 *
		 * @param $strHost
		 * @param $strUsername
		 * @param $strPassword
		 * @param $strDatabaseName
		 * @param $strProtocol
		 * @throws \Exception
		 */
		public function connect($strHost = null,$strUsername = null,$strPassword = null,$strDatabaseName = null,$strProtocol = null){

			if(defined('TWIST_DATABASE_PROTOCOL') && TWIST_DATABASE_PROTOCOL != 'none' || !is_null($strProtocol)){

				if(is_null($strProtocol)){
					$strProtocol = TWIST_DATABASE_PROTOCOL;
				}

				$strLibraryClass = sprintf('\Twist\Core\Models\Database\Protocol%s',strtoupper($strProtocol));

				if(!class_exists($strLibraryClass)){
					throw new \Exception(sprintf("Database protocol library '%s' is not installed or supported",$strProtocol));
				}

				if(!is_null($strHost) || !is_null($strUsername) || !is_null($strPassword) || !is_null($strDatabaseName)){
					if( !( !is_null($strHost) && !is_null($strUsername) && !is_null($strPassword) && !is_null($strDatabaseName) ) ){
						throw new \Exception('Missing parameters passed into database connect');
					}
				}else{
					$this->checkSettings(true);
					$strHost = TWIST_DATABASE_HOST;
					$strUsername = TWIST_DATABASE_USERNAME;
					$strPassword = TWIST_DATABASE_PASSWORD;
					$strDatabaseName = TWIST_DATABASE_NAME;
				}

				$this->resLibrary = new $strLibraryClass();
				$this->resLibrary->connect($strHost,$strUsername,$strPassword,$strDatabaseName);

				//Set the parameter to say that the database has already been connected
				$this->blConnectionAttempt = true;

				if($this->connected()){
					$this->strDatabaseName = $strDatabaseName;
					$this->resLibrary->selectDatabase($strDatabaseName);
					$this->resLibrary->setCharset('UTF8');
					$this->autoCommit(true);
				}
			}else{
				$this->blNoDatabase = true;
			}
		}

		/**
		 * Closes the connection and removes the database instance
		 */
		public function close(){

			if(!is_null($this->resLibrary)){
				$this->resLibrary->close();
				$this->resLibrary = null;
			}

			Instance::removeObject(($this->strConnectionKey == 'twist') ? 'pkgDatabase' : sprintf('pkgDatabase-%s',$this->strConnectionKey));
		}

		/**
		 * Check for default settings for the database
		 *
		 * @param $blThrowException
		 * @return bool
		 * @throws \Exception
		 */
		public function checkSettings($blThrowException = false){

			$blOut = true;

			if(defined('TWIST_DATABASE_PROTOCOL') && TWIST_DATABASE_PROTOCOL == 'none'){
				$blOut = false;
				if($blThrowException == true){
					throw new \Exception('No database connection has been setup for this installation');
				}
			}else{
				//TWIST_DATABASE_PASSWORD must be defined but can be set to blank (although not recommended)
				if(!defined('TWIST_DATABASE_HOST') || !defined('TWIST_DATABASE_USERNAME') || !defined('TWIST_DATABASE_PASSWORD') || is_null(TWIST_DATABASE_HOST) || is_null(TWIST_DATABASE_USERNAME) || is_null(TWIST_DATABASE_PASSWORD) || is_null(TWIST_DATABASE_NAME) ||	TWIST_DATABASE_HOST == '' ||  TWIST_DATABASE_USERNAME == '' ||  TWIST_DATABASE_NAME == '' ){
					$blOut = false;
					if($blThrowException == true){
						throw new \Exception('Missing parameters passed into database connect');
					}
				}
			}

			return $blOut;
		}

		/**
		 * Import the contents of an SQL file '.sql' directly into your database, providing a database name will allow you to deviate from the default if required
		 * @param $dirSQLFile Full local path to the SQL file
		 * @param $strDatabaseName Name of the database to import into
		 * @return bool|null
		 */
		public function importSQL($dirSQLFile,$strDatabaseName = null){

			$blOut = false;

			if($dirSQLFile){

				$arrResult = array();
				if(\Twist::Command()->isEnabled()){
					$arrResult = \Twist::Command()->execute('/usr/bin/mysql -v');
				}

				if(count($arrResult) && $arrResult['status'] && $arrResult['errors'] == ''){

					//Run the MYSQL import command on command line
					$strCommand = sprintf('/usr/bin/mysql -h%s -u%s%s %s < %s',
						TWIST_DATABASE_HOST,
						TWIST_DATABASE_USERNAME,
						(TWIST_DATABASE_PASSWORD == '') ? '' : sprintf(' -p%s',TWIST_DATABASE_PASSWORD),
						(is_null($strDatabaseName)) ? TWIST_DATABASE_NAME : trim($strDatabaseName),
						$dirSQLFile
					);

					$blOut = \Twist::Command()->execute($strCommand);
				}else{
					//Run the import using the query function. May want to do some sanitation here?
					$strSQLData = file_get_contents($dirSQLFile);
					$arrQueries = explode(';',$strSQLData);

					foreach($arrQueries as $strQuery){
						$blOut = $this->query($strQuery.';');
					}
				}
			}

			return $blOut;
		}

		/**
		 * Run the query on the database, optionally pass the query in as a raw sprintf() string "SELECT * FROM `table` WHERE `id` = %d" followed by all the parameters to fill the string
	     * All parameters are escaped before being entered into the sprintf()
		 *
		 * @param $strQuery
		 * @return null
		 */
		public function query($strQuery){

			if(func_num_args() > 1){

				//Set the query as the first parameter
				$arrParams = array();
				foreach(func_get_args() as $intKey => $mxdValue){
					$arrParams[] = ($intKey > 0) ? $this->escapeString($mxdValue) : $mxdValue;
				}

				//Get the sprintf result, all parameters are escaped
				$strQuery = call_user_func_array('sprintf',$arrParams);
			}

			$this->connected();
			$this->strLastRunQuery = $strQuery;

			return ($this->debugMode()) ? $this->queryDebug($strQuery) : $this->queryStandard($strQuery);
		}

		/**
		 * Run the standard query with no debug enabled
		 *
		 * @param $strQuery
		 * @return null
		 */
		private function queryStandard($strQuery){
			$this->resResult = $this->resLibrary->query($strQuery);
			return (is_object($this->resResult) || $this->resResult);
		}

		/**
		 * Debug and log the query results for use in the debug interface
		 *
		 * @param $strQuery
		 * @return null
		 */
		private function queryDebug($strQuery){

			//Time how long the query took to run
			\Twist::Timer('database-query')->start();
			$this->resResult = $this->resLibrary->query($strQuery);
			$arrResult = \Twist::Timer('database-query')->stop();

			$arrTrace = debug_backtrace();
			$arrStack = array();

			$intKey = 1;
			$arrStack[] = array(
				'file' => $arrTrace[$intKey]['file'],
				'line' => $arrTrace[$intKey]['line'],
				'function' => $arrTrace[$intKey]['function']
			);

			while(strstr($arrTrace[$intKey]['file'],'Database.utility.php')){

				$intKey++;
				$arrStack[] = array(
					'file' => $arrTrace[$intKey]['file'],
					'line' => $arrTrace[$intKey]['line'],
					'function' => $arrTrace[$intKey]['function']
				);
			}

			//Log the stats of the query
			\Twist::framework()->debug()->log('Database','queries',array(
				'instance' => $this->strConnectionKey,
				'query' => $strQuery,
				'time' => $arrResult['total'],
				'status' => (is_object($this->resResult) || $this->resResult),
				'error' => $this->resLibrary->errorString(),
				'affected_rows' => $this -> getAffectedRows(),
				'insert_id' => $this -> getInsertID(),
				'num_rows' => $this -> getNumberRows(),
				'trace' => array_reverse($arrStack)
			));

			return ($this->resResult);
		}

		/**
		 * Return the last run query by that database class
		 *
		 * @return string
		 */
		public function lastQuery(){
			return $this->strLastRunQuery;
		}

		/**
		 * Create a new database record (row) an empty object will be returned from this function, you can then populate the record and commit your changes
		 *
		 * @param $strTable
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
		 */
		public function createRecord($strTable){

			$resRecord = null;

			//Get the structure of the table
			$arrStructure = $this->getStructure($strTable,$this->strDatabaseName);

			if(!is_null($arrStructure)){
				$resRecord = new \Twist\Core\Models\Database\Record($this->strDatabaseName,$strTable,$arrStructure,array());
			}

			return $resRecord;
		}

		/**
		 * Get an object of a database record with the ability to update and delete
	     * A WHERE clause is generated in the form "WHERE $strField = $mxdValue", the default field being "id"
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param string $strField
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
		 */
		public function getRecord($strTable,$mxdValue,$strField = 'id'){

			$resRecord = null;

			//Get the structure of the table
			$arrStructure = $this->getStructure($strTable,$this->strDatabaseName);

			if(!is_null($arrStructure)){

				$arrRecord = $this->get($strTable,$mxdValue,$strField);
				if(count($arrRecord)){
					$resRecord = new \Twist\Core\Models\Database\Record($this->strDatabaseName,$strTable,$arrStructure,$arrRecord);
				}
			}

			return $resRecord;
		}

		/**
		 * Get an array of objects of database records with the ability to update and delete
		 *
		 * @param $strTable
		 * @param array $arrFieldValues
		 * @return null|array Returns an array of editable database record objects
		 */
		public function getRecords($strTable,$arrFieldValues = array()){

			$arrRecords = array();

			if(count($arrFieldValues)){
				//Get the structure of the table
				$arrStructure = $this->getStructure($strTable,$this->strDatabaseName);

				foreach($arrFieldValues as $strField => $mxdValue) {

					$arrRecords = $this->find($strTable,$mxdValue,$strField);

					if(count($arrRecords)){
						foreach($arrRecords as $arrRecord) {
							$arrRecords[] = new \Twist\Core\Models\Database\Record($this->strDatabaseName,$strTable,$arrStructure,$arrRecord);
						}
					}
				}
			} else {
				throw new \Exception('No fields and values specified');
			}

			return $arrRecords;
		}

		/**
		 * Get a clone of a database record as an object to be stored as a new record (auto-increment fields will be nulled). The cloned record will not be created/stored until commit has been called on the returned record object.
	     * The where clause is generated from the second parameter, must be an array
		 * For example to get the user with the 'id' of 1 pass in array('id' => 1)
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param string $strField
		 * @return null|\Twist\Core\Models\Database\Record Returns an editable object of the database record
		 */
		public function cloneRecord($strTable,$mxdValue,$strField = 'id'){

			$resRecord = null;

			//Get the structure of the table
			$arrStructure = $this->getStructure($strTable,$this->strDatabaseName);

			if(!is_null($arrStructure)){

				$arrRecord = $this->get($strTable,$mxdValue,$strField);
				if(count($arrRecord)){

					//Nullify any auto increment fields
					if(!is_null($arrStructure['auto_increment'])){
						$arrRecord[$arrStructure['auto_increment']] = null;
					}

					$resRecord = new \Twist\Core\Models\Database\Record($this->strDatabaseName,$strTable,$arrStructure,$arrRecord,true);
				}
			}

			return $resRecord;
		}

		/**
		 * Get the record (row) back as an array. The where clause is generated from the second parameter, must be an array
		 * For example to get the user with the 'id' of 1 pass in array('id' => 1)
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param string $strField
		 * @return array Array of the database record containing Field => Value pairs
		 */
 		public function get($strTable,$mxdValue,$strField = 'id'){

			$arrRecord = array();

			$strSQL = sprintf("SELECT * FROM `%s`.`%s` WHERE `%s` = '%s' LIMIT 1",
				$this->escapeString($this->strDatabaseName),
				$this->escapeString($strTable),
				$this->escapeString($strField),
				$this->escapeString($mxdValue)
			);

			if($this->query($strSQL) && $this->getNumberRows()){
				$arrRecord = $this->getArray();
			}

			return $arrRecord;
		}

		/**
		 * Get an array of all the records in a table
		 *
		 * @param $strTable
		 * @param $strOrderBy
		 * @return array Multi-dimensional array of each record containing it's Field => Value pairs
		 */
		public function getAll($strTable,$strOrderBy = null,$strDirection = 'ASC'){

			$arrOut = array();

			$strSQL = sprintf("SELECT * FROM `%s`.`%s`%s",
				$this->escapeString($this->strDatabaseName),
				$this->escapeString($strTable),
				is_null($strOrderBy) ? '' : sprintf(' ORDER BY `%s` %s',$this->escapeString($strOrderBy),$this->escapeString($strDirection))
			);

			if($this->query($strSQL) && $this->getNumberRows()){
				$arrOut = $this->getFullArray();
			}

			return $arrOut;
		}

		/**
		 * Delete a record permanent form the database
		 *
		 * @return boolean
		 */
		public function delete($strTable,$mxdValue,$strField = 'id',$intLimit = 1){

			$blOut = false;

			$strSQL = sprintf("DELETE FROM `%s`.`%s` WHERE `%s` = '%s'%s",
				$this->strDatabaseName,
				$strTable,
				$this->escapeString($strField),
				$this->escapeString($mxdValue),
				($intLimit == 0) ? '' : sprintf(' LIMIT %d',$this->escapeString($intLimit))
			);

			if($this->query($strSQL)){
				$blOut = true;
			}

			return $blOut;
		}

		/**
		 * Get a count of records (rows) as an array
	     * The where clause is generated from the second parameter, must be an array
	     * For example to get the user with the 'id' of 1 pass in array('id' => 1) you could look for the user by email with a wild card array('email' => 'dan@%')
		 * The where array accepts multiple parameters at a time
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param $strField
		 * @return array
		 * @throws \Exception
		 */
		public function count($strTable,$mxdValue = null,$strField = null){

			$intOut = 0;

			$strWhere = '';
			if(!is_null($mxdValue)){
				$strWhere = sprintf(" WHERE `%s` %s '%s'",
					$this->escapeString($strField),
					(strstr($mxdValue,'%')) ? 'LIKE' : '=',
					$this->escapeString($mxdValue)
				);
			}

			$strSQL = sprintf("SELECT COUNT(*) AS `total` FROM `%s`.`%s`%s",
				$this->escapeString($this->strDatabaseName),
				$this->escapeString($strTable),
				$strWhere
			);

			if($this->query($strSQL)){
				if($this->getNumberRows()){
					$arrRecord = $this->getArray();
					$intOut = $arrRecord['total'];
				}else{
					$intOut = 0;
				}
			}

			return $intOut;
		}

		/**
		 * Get a set of records (rows) as an array. The where clause is generated from the second parameter, must be an array. For example to get the user with the 'id' of 1 pass in array('id' => 1) you could look for the user by email with a wild card array('email' => 'dan@%')
		 * The where array accepts multiple parameters at a time.
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param $strField
		 * @param $strOrderBy
		 * @param $intLimit
		 * @param $intOffset
		 * @return array
		 * @throws \Exception
		 */
		public function find($strTable,$mxdValue,$strField,$strOrderBy = null,$strDirection = 'ASC',$intLimit = null,$intOffset = null){

			$arrRecord = array();

			$strOrder = '';
			if(!is_null($strOrderBy)){
				$arrOrder = (strstr($strOrderBy,',')) ? explode(',',$strOrderBy) : array($strOrderBy);
				$strOrder = ' ORDER BY';
				foreach($arrOrder as $strEachOrder){
					$strOrder .= sprintf(' `%s` %s,',$this->escapeString($strEachOrder),$this->escapeString($strDirection));
				}
				$strOrder = rtrim($strOrder,',');
			}

			$blIn = false;

			if(is_array($mxdValue) && count($mxdValue)) {
				array_walk( $mxdValue, array( $this, 'escapeString' ) );
				$blIn = true;
			}

			$strSQL = sprintf("SELECT * FROM `%s`.`%s` WHERE `%s` %s%s%s%s",
				$this->escapeString($this->strDatabaseName),
				$this->escapeString($strTable),
				$this->escapeString($strField),
				$blIn ? sprintf('IN(%s)',implode(',',$mxdValue)) : sprintf(strstr($mxdValue,'%') ? "LIKE '%s'" : "= '%s'", $this->escapeString($mxdValue)),
				$strOrder,
				(!is_null($intLimit)) ? sprintf(' LIMIT %d',$this->escapeString($intLimit)) : '',
				(!is_null($intLimit) && !is_null($intOffset)) ? sprintf(',%d',$this->escapeString($intOffset)) : ''
			);

			if($this->query($strSQL)){
				if($this->getNumberRows()){
					$arrRecord = $this->getFullArray();
				}
			}

			return $arrRecord;
		}

		/**
		 * Search a table for records with more than one field and value using AND to find matching rows
		 * $arrFieldValues should be an array of key/value pairs e.g. array( 'visible' => '1', 'status' => array( 1, 2 ) ) makes [`visible` = 1 AND `status` IN (1,2)]
		 *
		 * @param        $strTable
		 * @param array  $arrFieldValues
		 * @param null   $strOrderBy
		 * @param string $strDirection
		 * @param null   $intLimit
		 * @param null   $intOffset
		 * @return array
		 * @throws \Exception
		 */
		public function search($strTable,$arrFieldValues = array(),$strOrderBy = null,$strDirection = 'ASC',$intLimit = null,$intOffset = null){
			return $this->performSearch('AND',$strTable,$arrFieldValues,$strOrderBy,$strDirection,$intLimit,$intOffset);
		}

		/**
		 * Search a table for records with more than one field and value using OR to find matching rows
		 * $arrFieldValues should be an array of key/value pairs e.g. array( 'visible' => '1', 'status' => array( 1, 2 ) ) makes [`visible` = 1 AND `status` IN (1,2)]
		 *
		 * @param        $strTable
		 * @param array  $arrFieldValues
		 * @param null   $strOrderBy
		 * @param string $strDirection
		 * @param null   $intLimit
		 * @param null   $intOffset
		 * @return array
		 * @throws \Exception
		 */
		public function searchOr($strTable,$arrFieldValues = array(),$strOrderBy = null,$strDirection = 'ASC',$intLimit = null,$intOffset = null){
			return $this->performSearch('OR',$strTable,$arrFieldValues,$strOrderBy,$strDirection,$intLimit,$intOffset);
		}

		/**
		 * Perform a search on a table for records with more than one field and value
		 *
		 * @param        $strTable
		 * @param array  $arrFieldValues
		 * @param null   $strOrderBy
		 * @param string $strDirection
		 * @param null   $intLimit
		 * @param null   $intOffset
		 * @return array
		 * @throws \Exception
		 */
		private function performSearch($strAndOr,$strTable,$arrFieldValues = array(),$strOrderBy = null,$strDirection = 'ASC',$intLimit = null,$intOffset = null){

			$arrRecord = array();

			$strOrder = '';
			if(!is_null($strOrderBy)){
				$arrOrder = (strstr($strOrderBy,',')) ? explode(',',$strOrderBy) : array($strOrderBy);
				$strOrder = ' ORDER BY';
				foreach($arrOrder as $strEachOrder){
					$strOrder .= sprintf(' `%s` %s,',$this->escapeString($strEachOrder),$this->escapeString($strDirection));
				}
				$strOrder = rtrim($strOrder,',');
			}

			if( count( $arrFieldValues ) ) {
				$arrWhere = array();

				foreach( $arrFieldValues as $strField => $mxdValue ) {

					$blIn = false;

					if(is_array($mxdValue) && count($mxdValue)) {
						array_walk( $mxdValue, array( $this, 'escapeString' ) );
						$blIn = true;
					}

					$arrWhere[] = sprintf("`%s` %s",
						$strField,
						$blIn ? sprintf('IN(%s)',implode(',',$mxdValue)) : sprintf(strstr($mxdValue,'%') ? "LIKE '%s'" : "= '%s'", $this->escapeString($mxdValue))
					);
				}

				$strSQL = sprintf("SELECT * FROM `%s`.`%s` WHERE %s%s%s%s",
					$this->escapeString($this->strDatabaseName),
					$this->escapeString($strTable),
					implode(sprintf(' %s ',$strAndOr),$arrWhere),
					$strOrder,
					(!is_null($intLimit)) ? sprintf(' LIMIT %d',$this->escapeString($intLimit)) : '',
					(!is_null($intLimit) && !is_null($intOffset)) ? sprintf(',%d',$this->escapeString($intOffset)) : ''
				);

				if($this->query($strSQL)){
					if($this->getNumberRows()){
						$arrRecord = $this->getFullArray();
					}
				}
			} else {
				throw new \Exception('No fields and values specified');
			}

			return $arrRecord;
		}

		/**
		 * Get a database table as an object, if that tables has not been created then you can create the table from this object
		 *
		 * @param $strTable Name of that table to lookup
		 * @param $strDatabase Name of the tables database if not the current database
		 * @return null|\Twist\Core\Models\Database\Record Returns and object of the database table
		 */
		public function table($strTable,$strDatabase = null){

			$resTable = null;
			$strDatabaseName = (is_null($strDatabase)) ? $this->strDatabaseName : $strDatabase;

			$resTable = new \Twist\Core\Models\Database\Table($strDatabaseName,$strTable);

			return $resTable;
		}

		/**
		 * Test to see if a database table exists already, returns a boolean stats for the table
		 * @param $strTable
		 * @param null $strDatabase
		 * @return bool
		 */
		public function tableExists($strTable,$strDatabase = null){

			$blQuery = $this->query("SELECT 'exists' AS `status` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '%s' AND  TABLE_NAME = '%s'",
				(is_null($strDatabase)) ? $this->strDatabaseName : $strDatabase,
				$strTable
			);

			return ($blQuery && $this->getNumberRows());
		}

		/**
		 * Test to see if a Twist table exists, automatically adds the table prefix to the request
		 * @related tableExists
		 * @param $strTable
		 * @param null $strDatabase
		 * @return bool]
		 */
		public function twistTableExists($strTable,$strDatabase = null){
			return $this->tableExists(sprintf('%s%s',TWIST_DATABASE_TABLE_PREFIX,$strTable),$strDatabase);
		}

		/**
		 * Get the structure information of a table by table name
		 *
		 * @related table
		 * @param $strTable Name of that table to lookup
		 * @param $strDatabase Name of the tables database if not the current database
		 * @return array Single dimensional array of structure info
		 */
		protected function getStructure($strTable,$strDatabase = null){

			$arrStructure = null;
			$strDatabaseName = (is_null($strDatabase)) ? $this->strDatabaseName : $strDatabase;

			$arrStructure = \Twist::Cache('twist/utility/database')->read(sprintf('dbStructure-%s+%s',$strDatabaseName,$strTable));

			if(is_null($arrStructure)){

				$strSQL = sprintf("SELECT `COLUMN_NAME` AS `column_name`,
										`DATA_TYPE` AS `data_type`,
										`CHARACTER_MAXIMUM_LENGTH` AS `character_length`,
										`IS_NULLABLE` AS `nullable`,
										`COLUMN_DEFAULT` AS `default_value`,
										`EXTRA` AS `extra`,
										`COLUMN_COMMENT` AS `comment`,
										`CHARACTER_SET_NAME` AS `charset`,
										`COLLATION_NAME` AS `collation`,
										`ORDINAL_POSITION` AS `order`
									FROM `information_schema`.`COLUMNS`
									WHERE `TABLE_NAME` = '%s'
									AND `TABLE_SCHEMA` = '%s'",
					$this->escapeString($strTable),
					$this->escapeString($strDatabaseName)
				);

				if($this->query($strSQL) && $this->getNumberRows()){
					$arrStructureData = $this->getFullArray();

					$arrStructure = array(
						'columns' => array(),
						'auto_increment' => null
					);

					foreach($arrStructureData as $arrEachItem){
						$arrEachItem['auto_increment'] = ($arrEachItem['extra'] == 'auto_increment');
						$arrEachItem['nullable'] = ($arrEachItem['nullable'] == 'YES');

						//Set the data back into the structure array
						$arrStructure['auto_increment'] = ($arrEachItem['auto_increment']) ? $arrEachItem['column_name'] : null;
						$arrStructure['columns'][$arrEachItem['column_name']] = $arrEachItem;
					}

					//PHP session cache only expire when page is loaded
					\Twist::Cache('twist/utility/database')->write(sprintf('dbStructure-%s+%s',$strDatabaseName,$strTable),$arrStructure,0);
				}
			}

			return $arrStructure;
		}

		/**
		 * Change the status of autocommit on the current database connection
		 *
		 * @related commit
		 * @param $blStatus Required status of autocommit
		 * @return boolean Returns the status of the call
		 */
		public function autoCommit($blStatus = true){
			return $this->resLibrary->autocommit($blStatus);
		}

		/**
		 * Commit a query that has not be committed
		 *
		 * @related commit
		 * @return boolean Returns the status of the commit
		 */
		public function commit(){
			return $this->resLibrary->commit();
		}

		/**
		 * Roll back when using the commit rollback methods
		 *
		 * @related commit
		 * @return boolean Returns the status of the rollback
		 */
		public function rollback(){
			return $this->resLibrary->rollback();
		}

		/**
		 * Check if the database session has uncommitted queries
		 *
		 * @related commit
		 * @return boolean Returns the uncommitted queries status
		 */
		public function uncommittedQueries(){
			return $this->resLibrary->blActiveTransaction;
		}

		/**
		 * Check if autocommit is turned on in the current database setting
		 *
		 * @related commit
		 * @return boolean Returns the autocommit status
		 */
		public function autocommitStatus(){
			return $this->resLibrary->blAutoCommit;
		}

		/**
		 * Get the insert ID from the result of the last query that has been run
		 *
		 * @related getArray
		 * @return integer Returns ID of newly inserted row
		 */
		public function getInsertID(){
			return $this->resLibrary->insertId();
		}

		/**
		 * Get the count of affected rows from the last run query
		 *
		 * @related getArray
		 * @return mixed Returns a count of effected rows
		 */
		public function getAffectedRows(){
			return ($this->resResult) ? $this->resLibrary->affectedRows($this->resResult) : 0;
		}

		/**
		 * Get the count of found rows in the current result set of the last run query
		 *
		 * @related getArray
		 * @return int Returns a count of query results
		 */
		public function getNumberRows(){
			return ($this->resResult) ? $this->resLibrary->numberRows($this->resResult) : 0;
		}

		/*
		 * Get a single row (result) from the last run query as a single dimensional array
		 *
		 * @return array Returns as single dimensional array
		 */
		public function getArray($blFullArray = false){
			$arrOut = array();
			if($this->resResult && $this->getNumberRows() > 0){
				if($blFullArray){
					while($arrRow = $this->resLibrary->fetchArray($this->resResult)){
						$arrOut[] = $arrRow;
					}
				}else{
					$arrOut = $this->resLibrary->fetchArray($this->resResult);
				}
				$this->resLibrary->freeResult($this->resResult);
			}
			return $arrOut;
		}

		/**
		 * Get all rows (results) from the last run query as a multi-dimensional array of data
		 *
		 * @related getArray
		 * @return array Returns a multi-dimensional array
		 */
		public function getFullArray(){
			return $this->getArray(true);
		}

		/**
		 * Escape/Pre-pair and sanitise a string ready to be used in a database query
		 *
		 * @param $strRawString Unsanitized string to be escaped
		 * @return string Sanatized/escaped string
		 */
		public function escapeString($strRawString){
			$strOut = strval($strRawString);
			$strOut = (get_magic_quotes_gpc()) ? stripslashes($strOut) : $strOut;
			return (!is_numeric($strOut) && $this->connected()) ? $this->resLibrary->escapeString($strOut) : $strOut;
		}

		public function mbSupport() {
			$blMultibyteSupport = false;

			if($this->query("SELECT VERSION() as mysql_version")
					&& $this->getNumberRows()) {
				$arrSQLData = \Twist::Database()->getArray();
				$blMultibyteSupport = version_compare($arrSQLData['mysql_version'], '5.5.3', '>=');
			}

			return $blMultibyteSupport;
		}

		/**
		 * Check to see if a connection is present to a database server
		 *
		 * @return bool Returns status of connection
		 * @throws \Exception
		 */
		protected function connected(){

			if($this->blNoDatabase == false){
				if($this->blConnectionAttempt == false){
					$this->blConnectionAttempt = true;
					$this->connect();
				}

				if(is_object($this->resLibrary) && !$this->resLibrary->connected()){
					$strErrorMessage = $this->resLibrary->connectionError();
					$this->resLibrary = null;
					throw new \Exception('Failed to connect to the database server');
				}
				return true;
			}

			return false;
		}

		/**
		 * Check to see if a connection is present and return true/false (does not attempt to make a connection if not already)
		 *
		 * @return bool Returns status of connection
		 */
		public function isConnected(){
			return (is_object($this->resLibrary) && $this->resLibrary->connected());
		}

		/**
		 * Get the version for the connected mySQL server version
		 *
		 * @return array Returns array of server information
		 */
		public function getServerInfo(){
			return $this->resLibrary->serverInfo();
		}

		/**
		 * @alias getInsertID
		 */
		public function getInsID(){ return $this->getInsertID(); }

		/**
		 * @alias getInsertID
		 */
		public function insID(){ return $this->getInsertID(); }

		/**
		 * @alias getAffectedRows
		 */
		public function getAffRows(){ return $this->getAffectedRows(); }

		/**
		 * @alias getAffectedRows
		 */
		public function affRows(){ return $this->getAffectedRows(); }

		/**
		 * @alias getNumberRows
		 */
		public function getNumRows(){ return $this->getNumberRows(); }

		/**
		 * @alias getNumberRows
		 */
		public function numRows(){ return $this->getNumberRows(); }

		/**
		 * @alias escapeString
		 */
		public function escape($strRawString){ return $this->escapeString($strRawString); }
	}