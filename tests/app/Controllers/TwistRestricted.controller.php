<?php

	namespace App\Controllers;
	use Twist\Core\Controllers\BaseUser;

	class TwistRestricted extends BaseUser{

		public function test(){
			return 'test';
		}
	}