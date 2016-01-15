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
	 * Database result object
	 */
	class Result{

		protected $strQuery = null;
		protected $blStatus = false;
		protected $arrResults = array();

		protected $intNumberRows = 0;
		protected $intAffectedRows = 0;
		protected $intInsertID = 0;

		public function status(){
			return $this->blStatus;
		}

		public function sql(){
			return $this->strQuery;
		}

		public function insertID(){
			return $this->intInsertID;
		}

		public function affectedRows(){
			return $this->intAffectedRows;
		}

		public function numberRows(){
			return $this->intNumberRows;
		}

		public function getArray(){
			return $this->arrResults[0];
		}

		public function getFullArray(){
			return $this->arrResults;
		}

		public function errorNo(){

		}

		public function errorMessage(){

		}
	}