<?php

	namespace App\Controllers;
	use \Twist\Core\Controllers\Base;

	class HelloWorld extends Base {

		public function _index() {
			return $this -> _view( 'welcome.tpl' );
		}

		public function view() {
			return $this -> _view( 'views.tpl' );
		}

		public function ajaxrequests() {
			return $this -> _view( 'ajax.tpl' );
		}

		public function testuploads() {
			return $this -> _view( 'upload.tpl' );
		}

	}
