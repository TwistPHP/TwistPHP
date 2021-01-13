<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Shadow Technologies Ltd.
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

namespace Twist\Core\Helpers;

use \Twist\Classes\Instance;

/**
 * A helper to simplify database connections for your PHP projects. Allowing connections to be made using MySQL, MySQLi and PDO.
 * Connections to multiple database servers can be created and used side by site with this unique instancable helper.
 */
class Database extends Base{

	protected $resLibrary = null;
	protected $resResult = null;
	protected $strConnectionKey = null;
	protected $blConnectionAttempt = false;
	protected $arrConnectionDetails = array();
	protected $strDatabaseName = null;
	protected $blNoDatabase = false;
	protected $blDebugMode = false;
	protected $strLastRunQuery = '';
	public $strConnectionError = '';

	/**
	 * @var \Twist\Core\Models\Database\Records
	 */
	protected $resRecords = null;

	/**
	 * @var \Twist\Core\Models\Database\Table
	 */
	protected $resTables = null;

	public function __construct($strConnectionKey){
		$this->strConnectionKey = $strConnectionKey;
	}

	public function __destruct(){
		$this->close();
	}

	/**
	 * Make the main connection to the database, automatically called if a custom connection is not required
	 * @param string $strHost
	 * @param string $strUsername
	 * @param string $strPassword
	 * @param string $strDatabaseName
	 * @param string $strProtocol
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
				}else{
					//Store the connection details here, if custom details where used this will allow for reconnection if required
					$this->arrConnectionDetails = array(
						'host' => $strHost,
						'username' => $strUsername,
						'password' => $strPassword,
						'database' => $strDatabaseName,
						'protocol' => $strProtocol
					);
				}
			}else{
				$this->checkSettings(true);

				if(count($this->arrConnectionDetails) == 0){
					$this->arrConnectionDetails = array(
						'host' => TWIST_DATABASE_HOST,
						'username' => TWIST_DATABASE_USERNAME,
						'password' => TWIST_DATABASE_PASSWORD,
						'database' => TWIST_DATABASE_NAME,
						'protocol' => TWIST_DATABASE_PROTOCOL
					);
				}
			}

			$this->resLibrary = new $strLibraryClass();
			$this->resLibrary->connect($this->arrConnectionDetails['host'],$this->arrConnectionDetails['username'],$this->arrConnectionDetails['password'],$this->arrConnectionDetails['database']);

			//Set the parameter to say that the database has already been connected
			$this->blConnectionAttempt = true;

			if($this->connected()){
				$this->setDatabase($this->arrConnectionDetails['database']);
				$this->resLibrary->setCharset('UTF8');
				$this->autoCommit(true);
			}
		}else{
			$this->blNoDatabase = true;
		}
	}

	/**
	 * Check to see if a connection is present to a database server
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
				$this->strConnectionError = $this->resLibrary->connectionError();
				$this->resLibrary = null;
				throw new \Exception('Failed to connect to the database server');
			}
			return true;
		}

		return false;
	}

	/**
	 * Check to see if a connection is present and return true/false (does not attempt to make a connection if not already)
	 * @return bool Returns status of connection
	 */
	public function isConnected(){
		return (is_object($this->resLibrary) && $this->resLibrary->connected());
	}

	/**
	 * Closes the connection and removes the database instance
	 */
	public function close(){

		if(!is_null($this->resLibrary)){
			$this->resLibrary->close();
			$this->resLibrary = null;
		}

		Instance::removeObject(($this->strConnectionKey == 'twist') ? 'helperDatabase' : sprintf('helperDatabase-%s',$this->strConnectionKey));
	}

	/**
	 * Check for default settings for the database
	 * @param bool $blThrowException
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
	 * Get the version for the connected mySQL server version
	 * @return array Returns array of server information
	 */
	public function getServerInfo(){
		return $this->resLibrary->serverInfo();
	}

	/**
	 * Check to see if the database has multi-byte support enabled
	 * @return bool|mixed
	 */
	public function mbSupport(){

		$blMultibyteSupport = false;
		$resResult = $this->query("SELECT VERSION() as mysql_version");

		if($resResult->status() && $resResult->numberRows()){
			$arrSQLData = $resResult->row();
			$blMultibyteSupport = version_compare($arrSQLData['mysql_version'], '5.5.3', '>=');
		}

		return $blMultibyteSupport;
	}

	/**
	 * Select the default database for this connection to be using, all further queries will then be run on the newly selected database
	 * @param string $strDatabase Name of the database to be selected
	 * @return bool True indicates a successful database switch
	 */
	public function setDatabase($strDatabase){

		if($this->resLibrary->selectDatabase($strDatabase)){

			$this->arrConnectionDetails['database'] = $strDatabase;
			$this->strDatabaseName = $strDatabase;
			return true;
		}

		return false;
	}

	/**
	 * Run a fully formed SQL query on the database, optionally pass the query in as a raw sprintf() string "SELECT * FROM `table` WHERE `id` = %d" followed by all the parameters to fill the string
	 * All parameters are escaped before being entered into the sprintf(). A Database result object will be returned containing all the stats and results for the query.
	 * @param string $strQuery SQL query to be run against the database
	 * @return \Twist\Core\Models\Database\Result Database result model
	 * @throws \Exception
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

		if($this->debugMode()){
			//Time how long the query took to run
			\Twist::Timer('database-query')->start();
			$this->resResult = $this->resLibrary->query($strQuery);
			$this->debug($strQuery,\Twist::Timer('database-query')->stop());
		}else{
			$this->resResult = $this->resLibrary->query($strQuery);
		}

		$blQueryStatus = (is_object($this->resResult) || $this->resResult);

		$intNumberRows = ($this->resResult) ? $this->resLibrary->numberRows($this->resResult) : 0;
		$intAffectedRows = ($this->resResult) ? $this->resLibrary->affectedRows($this->resResult) : 0;
		$intInsertID = $this->resLibrary->insertId();

		$arrResults = array();

		if($this->resResult && $this->resLibrary->numberRows($this->resResult) > 0){
			while($arrRow = $this->resLibrary->fetchArray($this->resResult)){
				$arrResults[] = $arrRow;
			}

			$this->resLibrary->freeResult($this->resResult);
		}

		$strErrorMessage = $intErrorNo = null;

		if(!$blQueryStatus){
			$strErrorMessage = $this->resLibrary->errorString();
			$intErrorNo = $this->resLibrary->errorNumber();
		}

		return new \Twist\Core\Models\Database\Result(
			$blQueryStatus,
			$strQuery,
			$intNumberRows,
			$intAffectedRows,
			$intInsertID,
			$arrResults,
			$strErrorMessage,
			$intErrorNo
		);
	}

	/**
	 * Get, Create, Copy and Manipulate database records/rows as objects. Find an array of database records/rows, count and delete others with a single function call.
	 * @param string $strTable Name of the database table
	 * @param null|string $strDatabase Database name if different from TWIST_DATABASE_NAME
	 * @return \Twist\Core\Models\Database\Records Database records model
	 */
	public function records($strTable,$strDatabase = null){

		if(is_null($this->resRecords)){
			$this->resRecords = new \Twist\Core\Models\Database\Records();
		}

		$this->resRecords->__setTable($strTable);
		$this->resRecords->__setDatabase((is_null($strDatabase)) ? $this->arrConnectionDetails['database'] : $strDatabase);

		return $this->resRecords;
	}

	/**
	 * Get, Create and Manipulate the structure of database tables using a table object. Truncate, Optimize, Rename and Drop tables with a single function call. All dependant on your database privileges.
	 * @param string $strTable Name of the database table
	 * @param null|string $strDatabase Database name if different from TWIST_DATABASE_NAME
	 * @return \Twist\Core\Models\Database\Table Database tables model
	 */
	public function table($strTable,$strDatabase = null){

		if(is_null($this->resTables)){
			$this->resTables = new \Twist\Core\Models\Database\Table();
		}

		$this->resTables->__setTable($strTable);
		$this->resRecords->__setDatabase((is_null($strDatabase)) ? $this->arrConnectionDetails['database'] : $strDatabase);

		return $this->resTables;
	}

	/**
	 * Return the last run query by that database class
	 * @return string Last SQL query to be run
	 */
	public function lastQuery(){
		return $this->strLastRunQuery;
	}

	/**
	 * Escape and sanitise a string ready to be used in a SQL database query
	 * @param string $strRawString Raw unescaped string
	 * @return string Sanitised and escaped string
	 */
	public function escapeString($strRawString){
		$strOut = strval($strRawString);
		//$strOut = (get_magic_quotes_gpc()) ? stripslashes($strOut) : $strOut;//PHP7.4 deprecated
		return (!is_numeric($strOut) && $this->connected()) ? $this->resLibrary->escapeString($strOut) : $strOut;
	}

	/**
	 * Import the contents of an SQL file '.sql' directly into your database, providing a database name will allow you to deviate from the default if required
	 * @param string $dirSQLFile Path to the SQL file that will be imported
	 * @param null|string $strDatabaseName Database name if different from TWIST_DATABASE_NAME
	 * @return bool|null
	 */
	public function importSQL($dirSQLFile,$strDatabaseName = null){

		$blOut = false;

		if(file_exists($dirSQLFile)){

			$arrResult = array();
			if(\Twist::Command()->isEnabled()){
				$arrResult = \Twist::Command()->execute('/usr/bin/mysql -v');
			}

			if(count($arrResult) && $arrResult['status'] && $arrResult['errors'] == ''){

				//Run the MYSQL import command on command line
				$strCommand = sprintf('/usr/bin/mysql -h%s -u%s%s %s < %s',
					TWIST_DATABASE_HOST,
					TWIST_DATABASE_USERNAME,
					(TWIST_DATABASE_PASSWORD == '') ? '' : sprintf(' -p"%s"',TWIST_DATABASE_PASSWORD),
					(is_null($strDatabaseName)) ? TWIST_DATABASE_NAME : trim($strDatabaseName),
					$dirSQLFile
				);

				$arrResult = \Twist::Command()->execute($strCommand);
				$blOut = $arrResult['status'];
			}else{

				if(!is_null($strDatabaseName)){
					//Set the database for an inport
					$this->resLibrary->selectDatabase($strDatabaseName);
				}

				//Run the import using the query function. May want to do some sanitation here?
				$strSQLData = file_get_contents($dirSQLFile);
				$arrQueries = explode(';',$strSQLData);

				$blAnyIssues = false;
				foreach($arrQueries as $strQuery){
					$blResponse = $this->query($strQuery.';');
					if(!$blResponse){
						$blAnyIssues = true;
					}
				}

				$blOut = !$blAnyIssues;

				if(!is_null($strDatabaseName)){
					//Reset the database after the import
					$this->resLibrary->selectDatabase($this->strDatabaseName);
				}
			}
		}

		return $blOut;
	}

	/**
	 * Export/dump the contents of a database to an SQL file
	 * @param string $dirSQLFile Path to the file that will contain the exported database
	 * @param null|string $strDatabaseName Database name if different from TWIST_DATABASE_NAME
	 * @return boolean
	 */
	public function exportSQL($dirSQLFile,$strDatabaseName = null){

		$blOut = false;

		if(!file_exists($dirSQLFile)){

			$arrResult = array();
			if(\Twist::Command()->isEnabled()){
				$arrResult = \Twist::Command()->execute('/usr/bin/mysql -v');
			}

			if(count($arrResult) && $arrResult['status'] && $arrResult['errors'] == ''){

				//Run the MYSQL import command on command line
				$strCommand = sprintf('/usr/bin/mysqldump -h%s -u%s%s %s > %s',
					TWIST_DATABASE_HOST,
					TWIST_DATABASE_USERNAME,
					(TWIST_DATABASE_PASSWORD == '') ? '' : sprintf(' -p"%s"',TWIST_DATABASE_PASSWORD),
					(is_null($strDatabaseName)) ? TWIST_DATABASE_NAME : trim($strDatabaseName),
					$dirSQLFile
				);

				$blOut = \Twist::Command()->execute($strCommand);
			}else{
				/**
				foreach($arrQueries as $strQuery){
				$blOut = $this->query($strQuery.';');
				}

				//Run the import using the query function. May want to do some sanitation here?
				$strSQLData = file_put_contents($dirSQLFile);
				$arrQueries = explode(';',$strSQLData);*/
			}
		}

		return $blOut;
	}

	/**
	 * Change the status of autocommit on the current database connection
	 * @related commit
	 * @param boolean $blStatus Required status of autocommit
	 * @return boolean Returns the status of the call
	 */
	public function autoCommit($blStatus = true){
		return $this->resLibrary->autocommit($blStatus);
	}

	/**
	 * Check if autocommit is turned on in the current database setting
	 * @related commit
	 * @return boolean Returns the autocommit status
	 */
	public function autocommitStatus(){
		return $this->resLibrary->blAutoCommit;
	}

	/**
	 * Commit a query that has not be committed
	 * @related commit
	 * @return boolean Returns the status of the commit
	 */
	public function commit(){
		return $this->resLibrary->commit();
	}

	/**
	 * Roll back when using the commit rollback methods
	 * @related commit
	 * @return boolean Returns the status of the rollback
	 */
	public function rollback(){
		return $this->resLibrary->rollback();
	}

	/**
	 * Check if the database session has uncommitted queries
	 * @related commit
	 * @return boolean Returns the uncommitted queries status
	 */
	public function uncommittedQueries(){
		return $this->resLibrary->blActiveTransaction;
	}

	/**
	 * Detect if the database debug mode is enabled or disabled, based upon the DEVELOPMENT_MODE and DEVELOPMENT_DEBUG_BAR settings.
	 * @return boolean Debug status
	 */
	public function debugMode(){

		if($this->blDebugMode == false && defined('TWIST_LAUNCHED')){
			if(\Twist::framework()->setting('DEVELOPMENT_MODE') && \Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR')){
				$this->blDebugMode = true;
			}
		}

		return $this->blDebugMode;
	}

	/**
	 * Log the SQL query debug results for use within the TwistPHP debug bar.
	 * @param string $strQuery A fully formed SQL query
	 * @param array $arrExecTime Stats for the time taken to run the query
	 * @return bool Query result status
	 */
	private function debug($strQuery,$arrExecTime){

		$arrTrace = debug_backtrace();
		$arrStack = array();

		$intKey = 1;
		$arrStack[] = array(
			'file' => $arrTrace[$intKey]['file'],
			'line' => $arrTrace[$intKey]['line'],
			'function' => $arrTrace[$intKey]['function']
		);

		while(strstr($arrTrace[$intKey]['file'],'Database.helper.php')){

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
			'time' => $arrExecTime['total'],
			'status' => (is_object($this->resResult) || $this->resResult),
			'error' => $this->resLibrary->errorString(),
			'affected_rows' => ($this->resResult) ? $this->resLibrary->affectedRows($this->resResult) : 0,
			'insert_id' => $this->resLibrary->insertId(),
			'num_rows' => ($this->resResult) ? $this->resLibrary->numberRows($this->resResult) : 0,
			'trace' => array_reverse($arrStack)
		));
	}
}
