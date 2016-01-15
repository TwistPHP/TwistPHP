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
	 * Object containing the results of a query run by the TwistPHP database utility. The object contains all the information from the query run including SQL the query itself.
	 */
	class Result{

		protected $strQuery = null;
		protected $blStatus = false;
		protected $arrResults = array();

		protected $intNumberRows = 0;
		protected $intAffectedRows = 0;
		protected $intInsertID = 0;

		public function __construct($blStatus,$strSQL,$intNumRows,$intAffRows,$intInsID,$arrResults){
			$this->strQuery = $strSQL;
			$this->blStatus = $blStatus;
			$this->arrResults = $arrResults;
			$this->intNumberRows = $intNumRows;
			$this->intAffectedRows = $intAffRows;
			$this->intInsertID = $intInsID;
		}

		/**
		 * Status of the result set, if the query was successful this will return true. If the status is true this only means the query did not fail, it dose not indicate if you have any results.
		 * @return bool Status of the query run.
		 */
		public function status(){
			return $this->blStatus;
		}

		/**
		 * SQL query that was run to produce this result object.
		 * @return string SQL Query that was run
		 */
		public function sql(){
			return $this->strQuery;
		}

		/**
		 * Get the insert ID from the result from the current result set
		 * @related getArray
		 * @return integer Returns ID of newly inserted row
		 */
		public function insertID(){
			return $this->intInsertID;
		}

		/**
		 * Get the count of affected rows from the current result set
		 * @related getArray
		 * @return mixed Returns a count of effected rows
		 */
		public function affectedRows(){
			return $this->intAffectedRows;
		}

		/**
		 * Get the count of found rows in the current result set
		 * @related getArray
		 * @return int Returns a count of query results
		 */
		public function numberRows(){
			return $this->intNumberRows;
		}

		/*
		 * Get a single row (first result) from the current result set as a single dimensional array
		 * @return array Returns as single dimensional array
		 */
		public function getArray(){
			return ($this->intNumberRows > 0) ? $this->arrResults[0] : array();
		}

		/**
		 * Get all rows (results) from the current result set as a multi-dimensional array of data
		 * @related getArray
		 * @return array Returns a multi-dimensional array
		 */
		public function getFullArray(){
			return $this->arrResults;
		}

		public function errorNo(){

		}

		public function errorMessage(){

		}
	}