<?php

	namespace App\Controllers;
	use Twist\Core\Controllers\BaseRESTKey;

	class TwistBasicAPI extends BaseRESTKey{

		public function test(){
			return $this->_respond(array(1 => 'test'));
		}
	}