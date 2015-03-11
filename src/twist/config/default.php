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
Twist::define('TWIST_ACCOUNT_TOKEN','');
Twist::define('TWIST_LICENCE_KEY','');

/**
 * Database Configuration Settings
 * DATABASE_NAME			- The name of your primary database
 * DATABASE_HOST			- Host IP / Name of the database server
 * DATABASE_USERNAME		- Username to use whilst connecting to the database
 * DATABASE_PASSWORD		- Password to use whilst connecting to the database
 * DATABASE_PROTOCOL		- Select the protocol to use (mysql,mysqli) default: mysqli
 * DATABASE_TABLE_PREFIX 	- Select the protocol to use (mysql,mysqli) default: mysqli
 */
Twist::define('DATABASE_NAME','');
Twist::define('DATABASE_HOST','localhost');
Twist::define('DATABASE_USERNAME','');
Twist::define('DATABASE_PASSWORD','');
Twist::define('DATABASE_PROTOCOL','mysqli');
Twist::define('DATABASE_TABLE_PREFIX','twist_');