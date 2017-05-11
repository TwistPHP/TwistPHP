<?php

	namespace App\Controllers;
	use Twist\Core\Controllers\BaseUser;

	class Standard extends BaseUser{

		public function test(){
			return 'test';
		}
	}