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

	namespace Twist\Core\Routes;

	/**
	 * Manager route file that registers all the routes and restrictions required to allow the Manager to be run.
	 * The manager route can be easily added to your site by calling the Twist::Route()->manager() alias function.
	 * @package string|Twist\Core\Routes
	 */
	class Manager extends Base{

		public function load(){

			//Allow the manager to still be accessible even in maintenance mode
			$this->bypassMaintenanceMode( '/%' );

			$this->baseView('manager/_base.tpl');
			$this->controller('/%','Twist\Core\Controllers\Manager');

			$this->restrictSuperAdmin('/%','/login');
			$this->unrestrict('/authenticate');
			$this->unrestrict('/cookies');
			$this->unrestrict('/forgotten-password');

			//Load in all any hooks registered to extend the Twist Manager
			$arrRoutes = \Twist::framework() -> hooks() -> getAll( 'TWIST_MANAGER_ROUTE' );

			if( count( $arrRoutes ) ) {
				foreach( $arrRoutes as $strEachHook ) {
					if( file_exists( $strEachHook ) ) {
						include $strEachHook;
					}
				}
			}
		}
	}