<?php

	namespace Twist\Core\Routes;

	class Manager extends Base{

		public function load(){

			//Allow the manager to still be accessible even in maintenance mode
			$this->bypassMaintenanceMode( $this->baseURI().'%s' );

			$this->controller('/%','Twist\Core\Controllers\Manager','_base.tpl');

			$this->restrictSuperAdmin('/%','/login');
			$this->unrestrict('/authenticate');
			$this->unrestrict('/forgotten-password');
		}
	}