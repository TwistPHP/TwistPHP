<?php

	namespace Twist\Core\Routes;

	/**
	 * Manager route file that registers all the routes and restrictions required to allow the Manager to be run.
	 * The manager route can be easily added to your site by calling the Twist::Route()->manager() alias function.
	 * @package Twist\Core\Routes
	 */
	class Manager extends Base{

		public function load(){

			//Allow the manager to still be accessible even in maintenance mode
			$this->bypassMaintenanceMode( $this->baseURI().'%s' );

			$this->baseView('manager/_base.tpl');
			$this->controller('/%','Twist\Core\Controllers\Manager');

			$this->restrictSuperAdmin('/%','/login');
			$this->unrestrict('/authenticate');
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