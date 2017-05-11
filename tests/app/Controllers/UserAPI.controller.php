<?php

	namespace App\Controllers;
	use Twist\Core\Controllers\BaseRESTUser;

	class UserAPI extends BaseRESTUser{

		public function test(){
			return $this->_respond(array(1 => 'test'));
		}
	}