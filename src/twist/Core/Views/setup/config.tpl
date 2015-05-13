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

	/**
	 * Set the licence details for your copy of Twist Framework v2
	 */
	Twist::define('TWIST_ACCOUNT_TOKEN','{data:account_token}');
	Twist::define('TWIST_LICENCE_KEY','{data:licence_key}');

	/**
	 * Database Configuration Settings
	 * DATABASE_NAME			- The name of your primary database
	 * DATABASE_HOST			- Host IP / Name of the database server
	 * DATABASE_USERNAME		- Username to use whilst connecting to the database
	 * DATABASE_PASSWORD		- Password to use whilst connecting to the database
	 * DATABASE_PROTOCOL		- Select the protocol to use (mysql,mysqli) default: mysqli
	 * DATABASE_TABLE_PREFIX 	- Prefix that will be applied to all tables installed by Twist
	 */
	Twist::define('DATABASE_NAME','{data:database_name}');
	Twist::define('DATABASE_HOST','{data:database_server}');
	Twist::define('DATABASE_USERNAME','{data:database_username}');
	Twist::define('DATABASE_PASSWORD','{data:database_password}');
	Twist::define('DATABASE_PROTOCOL','{data:database_protocol}');
	Twist::define('DATABASE_TABLE_PREFIX','{data:database_table_prefix}');