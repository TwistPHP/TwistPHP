<?php

	namespace App\Controllers;
	use Twist\Core\Controllers\BaseREST;

	class TwistBasicAPI extends BaseREST{

		public function test(){
			return $this->_respond(array(1 => 'test'));
		}
	}