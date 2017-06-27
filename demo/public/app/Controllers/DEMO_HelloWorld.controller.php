<?php

	namespace App\Controllers;
	use \Twist\Core\Controllers\Base;

	class DEMO_HelloWorld extends Base {

		public function _index() {
			return $this -> _view( 'DEMO_welcome.tpl' );
		}

	}
