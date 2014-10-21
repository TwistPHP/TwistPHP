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
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP\Packages;
	use TwistPHP\ModuleBase;
	use TwistPHP\Instance;

	/**
	 * A package to simplify database connections for your PHP projects. Allowing connections to be made using MySQL, MySQLi and PDO.
	 * Connections to multiple database servers can be created and used side by site with this unique instanceable package.
	 */
	class Database extends ModuleBase{

		public $version = "3.0";
		protected $resLibrary = null;
		protected $resResult = null;
		protected $strConnectionKey = null;
		protected $blConnectionAttempt = false;
		protected $strDatabaseName = null;
		protected $blNoDatabase = false;
		protected $strLastRunQuery = '';

		public function __construct($strConnectionKey){
			$this->strConnectionKey = $strConnectionKey;
		}

		public function __destruct(){
			$this->close();
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

			if(defined('DATABASE_PROTOCOL') && DATABASE_PROTOCOL != 'none' || !is_null($strProtocol)){

				if(is_null($strProtocol)){
					$strProtocol = DATABASE_PROTOCOL;
				}

				$strProtocolFile = sprintf('%s/libraries/Database/Protocol-%s.lib.php',DIR_FRAMEWORK_PACKAGES,strtolower($strProtocol));

				if(!file_exists($strProtocolFile)){
					throw new \Exception(sprintf("Database protocol library '%s' is not installed or supported",$strProtocol));
				}

				require_once $strProtocolFile;

				if(!is_null($strHost) || !is_null($strUsername) || !is_null($strPassword) || !is_null($strDatabaseName)){
					if( !( !is_null($strHost) && !is_null($strUsername) && !is_null($strPassword) && !is_null($strDatabaseName) ) ){
						throw new \Exception('Missing parameters passed into database connect');
					}
				}else{
					$this->checkSettings(true);
					$strHost = DATABASE_HOST;
					$strUsername = DATABASE_USERNAME;
					$strPassword = DATABASE_PASSWORD;
					$strDatabaseName = DATABASE_NAME;
				}

				$strLibraryClass = sprintf('\TwistPHP\Packages\Protocol%s',strtoupper($strProtocol));
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

			if(defined('DATABASE_PROTOCOL') && DATABASE_PROTOCOL == 'none'){
				$blOut = false;
				if($blThrowException == true){
					throw new \Exception('No database connection has been setup for this installation');
				}
			}else{
				if(!defined('DATABASE_HOST') || !defined('DATABASE_USERNAME') || !defined('DATABASE_PASSWORD') || is_null(DATABASE_HOST) || is_null(DATABASE_USERNAME) || is_null(DATABASE_PASSWORD) || is_null(DATABASE_NAME) ||	DATABASE_HOST == '' ||  DATABASE_USERNAME == '' ||  DATABASE_PASSWORD == '' ||  DATABASE_NAME == '' ){
					$blOut = false;
					if($blThrowException == true){
						throw new \Exception('Missing parameters passed into database connect');
					}
				}
			}

			return $blOut;
		}

		/**
		 * Run the query on the database, optionally pass the query in as a raw sprintf string "SELECT * FROM `table` WHERE `id` = %d" followed by all the parameters to fill the string. All parameters are escaped before being entered into the sprintf.
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

			return (defined('TWIST_LAUNCHED') && $this -> framework() -> setting('DATABASE_DEBUG')) ? $this->queryDebug($strQuery) : $this->queryStandard($strQuery);
		}

		/**
		 * Run the standard query with no debug enabled
		 *
		 * @param $strQuery
		 * @return null
		 */
		private function queryStandard($strQuery){
			$this->resResult = $this->resLibrary->query($strQuery);
			return ($this->resResult);
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

			//Log the stats of the query
			$this -> framework() -> debug() -> log('Database','queries',array(
				'query' => $strQuery,
				'time' => $arrResult['total'],
				'status' => ($this->resResult),
				'result' => $this -> getNumberRows()
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
		 * @return null|DatabaseRecord
		 */
		public function createRecord($strTable){

			require_once sprintf('%s/libraries/Database/Record.lib.php',DIR_FRAMEWORK_PACKAGES);

			$resRecord = null;

			//Get the structure of the table
			$arrStructure = $this->getStructure($strTable,$this->strDatabaseName);

			if(!is_null($arrStructure)){
				$resRecord = new DatabaseRecord($this->strDatabaseName,$strTable,$arrStructure,array());
			}

			return $resRecord;
		}

		/**
		 * Get a database record as a object with the ability to updated, and delete. The where clause is generated from the second parameter, must be an array.
		 * For example to get the user with the 'id' of 1 pass in array('id' => 1).
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param string $strField
		 * @return null|DatabaseRecord
		 */
		public function getRecord($strTable,$mxdValue,$strField = 'id'){

			require_once sprintf('%s/libraries/Database/Record.lib.php',DIR_FRAMEWORK_PACKAGES);
			$resRecord = null;

			//Get the structure of the table
			$arrStructure = $this->getStructure($strTable,$this->strDatabaseName);

			if(!is_null($arrStructure)){

				$arrRecord = $this->get($strTable,$mxdValue,$strField);
				if(count($arrRecord)){
					$resRecord = new DatabaseRecord($this->strDatabaseName,$strTable,$arrStructure,$arrRecord);
				}
			}

			return $resRecord;
		}

		/**
		 * Get a clone of a database record as an object to be stored as a new record (auto-increment fields will be nulled). The where clause is generated from the second parameter, must be an array.
		 * For example to get the user with the 'id' of 1 pass in array('id' => 1).
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param string $strField
		 * @return null|DatabaseRecord
		 */
		public function cloneRecord($strTable,$mxdValue,$strField = 'id'){

			require_once sprintf('%s/libraries/Database/Record.lib.php',DIR_FRAMEWORK_PACKAGES);
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

					$resRecord = new DatabaseRecord($this->strDatabaseName,$strTable,$arrStructure,$arrRecord,true);
				}
			}

			return $resRecord;
		}

		/**
		 * Get the record (row) back as an array. The where clause is generated from the second parameter, must be an array.
		 * For example to get the user with the 'id' of 1 pass in array('id' => 1).
		 *
		 * @param $strTable
		 * @param $mxdValue
		 * @param string $strField
		 * @return array
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
		 * Get all the records from a table, this should only be used when absolutely required as is slower and you many not need all the data that is returned
		 *
		 * @param $strTable
		 * @param $strOrderBy
		 * @return array
		 */
		public function getAll($strTable,$strOrderBy = null){

			$arrOut = array();

			$strSQL = sprintf("SELECT * FROM `%s`.`%s`%s",
				$this->escapeString($this->strDatabaseName),
				$this->escapeString($strTable),
				is_null($strOrderBy) ? '' : sprintf(' ORDER BY `%s` ASC',$strOrderBy)
			);

			if($this->query($strSQL) && $this->getNumberRows()){
				$arrOut = $this->getFullArray();
			}

			return $arrOut;
		}

		/**
		 * Delete a record form the database
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
				($intLimit == 0) ? '' : sprintf(' LIMIT %d',$intLimit)
			);

			if($this->query($strSQL)){
				$blOut = true;
			}

			return $blOut;
		}

		/**
		 * Get a count of records (rows) as an array. The where clause is generated from the second parameter, must be an array. For example to get the user with the 'id' of 1 pass in array('id' => 1) you could look for the user by email with a wild card array('email' => 'dan@%')
		 * The where array accepts multiple parameters at a time.
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
			}else{
				throw new \Exception('Count query failed to run');
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
		public function find($strTable,$mxdValue,$strField,$strOrderBy = null,$intLimit = null,$intOffset = null){

			$arrRecord = array();

			$strOrder = '';
			if(!is_null($strOrderBy)){
				$arrOrder = (strstr($strOrderBy,',')) ? explode(',',$strOrderBy) : array($strOrderBy);
				$strOrder = ' ORDER BY';
				foreach($arrOrder as $strEachOrder){
					$strOrder .= sprintf(' `%s` ASC,',$strEachOrder);
				}
				$strOrder = rtrim($strOrder,',');
			}

			$strSQL = sprintf("SELECT * FROM `%s`.`%s` WHERE `%s` %s '%s'%s%s%s",
				$this->escapeString($this->strDatabaseName),
				$this->escapeString($strTable),
				$this->escapeString($strField),
				(strstr($mxdValue,'%')) ? 'LIKE' : '=',
				$this->escapeString($mxdValue),
				$strOrder,
				(!is_null($intLimit)) ? sprintf(' LIMIT %d',$intLimit) : '',
				(!is_null($intLimit) && !is_null($intOffset)) ? sprintf(',%d',$intOffset) : ''
			);

			if($this->query($strSQL)){
				if($this->getNumberRows()){
					$arrRecord = $this->getFullArray();
				}
			}else{
				throw new \Exception('Find query failed to run');
			}

			return $arrRecord;
		}

		/**
		 * Get a database table as an object, if that tables has not been created then you can create the table from this object
		 *
		 * @param $strTable Name of that table to lookup
		 * @param $strDatabase Name of the tables database if not the current database
		 * @return object Returns and object of the database table
		 */
		public function table($strTable,$strDatabase = null){

			$resTable = null;
			$strDatabaseName = (is_null($strDatabase)) ? $this->strDatabaseName : $strDatabase;

			require_once sprintf('%s/libraries/Database/Table.lib.php',DIR_FRAMEWORK_PACKAGES);
			$resTable = new DatabaseTable($strDatabaseName,$strTable);

			return $resTable;
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

			$arrStructure = \Twist::Cache('pkgDatabase') -> retrieve(sprintf('dbStructure-%s+%s',$strDatabaseName,$strTable));

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
						$arrEachItem['auto_increment'] = ($arrEachItem['extra'] == 'auto_increment') ? true : false;
						$arrEachItem['nullable'] = ($arrEachItem['nullable'] == 'YES') ? true : false;

						//Set the data back into the structure array
						$arrStructure['auto_increment'] = ($arrEachItem['auto_increment']) ? $arrEachItem['column_name'] : null;
						$arrStructure['columns'][$arrEachItem['column_name']] = $arrEachItem;
					}

					//PHP session cache only expire when page is loaded
					\Twist::Cache('pkgDatabase') -> store(sprintf('dbStructure-%s+%s',$strDatabaseName,$strTable),$arrStructure,0);
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
			$this->connected();
			return $this->resLibrary->insertId();
		}

		/**
		 * Get the count of affected rows from the last run query
		 *
		 * @related getArray
		 * @return mixed Returns a count of effected rows
		 */
		public function getAffectedRows(){
			$this->connected();
			return ($this->resResult) ? $this->resLibrary->affectedRows($this->resResult) : 0;
		}

		/**
		 * Get the count of found rows in the current result set of the last run query
		 *
		 * @related getArray
		 * @return int Returns a count of query results
		 */
		public function getNumberRows(){
			$this->connected();
			return ($this->resResult) ? $this->resLibrary->numberRows($this->resResult) : 0;
		}

		/*
		 * Get a single row (result) from the last run query as a single dimensional array
		 *
		 * @return array Returns as single dimensional array
		 */
		public function getArray($blFullArray = false){
			$this->connected();
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
			$this->connected();
			$strOut = strval($strRawString);
			$strOut = (get_magic_quotes_gpc()) ? stripslashes($strOut) : $strOut;
			return (!is_numeric($strOut) && $this->connected()) ? $this->resLibrary->escapeString($strOut) : $strOut;
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
			return (is_object($this->resLibrary) && $this->resLibrary->connected()) ? true : false;
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