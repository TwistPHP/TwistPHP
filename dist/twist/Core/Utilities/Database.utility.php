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

		public function connect(){}
		public function close(){}
		public function connected(){}
		public function mbSupport(){}

		public function query($strSQL){
			return new \Twist\Core\Models\Database\Result();
		}
		public function table($strTable,$strDatabase = null){
			//create
			//copy
			//truncate
			//rename
			//get
			//delete
			//optimize
		}
		public function records($strTable){
			//create
			//get
			//copy
			//delete
			//count
			//search
			//all
		}
		public function import($dirSQLFile,$strDatabaseName = null){}
		public function export($dirSQLFile,$strDatabaseName = null){}

		public function escapeString($strRawString){}

		public function autoCommit($blStatus = true){}
		public function commit(){}
		public function rollback(){}
		public function uncommittedQueries(){}
		public function autocommitStatus(){}

	}