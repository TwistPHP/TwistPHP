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

	/**
	 * Database Configuration Settings
	 * TWIST_DATABASE_NAME			- The name of your primary database
	 * TWIST_DATABASE_HOST			- Host IP / Name of the database server
	 * TWIST_DATABASE_USERNAME		- Username to use whilst connecting to the database
	 * TWIST_DATABASE_PASSWORD		- Password to use whilst connecting to the database
	 * TWIST_DATABASE_PROTOCOL		- Select the protocol to use (mysql,mysqli) default: mysqli
	 * TWIST_DATABASE_TABLE_PREFIX 	- Prefix that will be applied to all tables installed by Twist
	 */
	//Twist::define('TWIST_DATABASE_NAME','');
	Twist::define('TWIST_DATABASE_HOST','localhost');
	//Twist::define('TWIST_DATABASE_USERNAME','');
	//Twist::define('TWIST_DATABASE_PASSWORD','');
	//Twist::define('TWIST_DATABASE_PROTOCOL','mysqli');
	//Twist::define('TWIST_DATABASE_TABLE_PREFIX','twist_');