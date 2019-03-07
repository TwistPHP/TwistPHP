<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class Cookie extends PHPUnitSupport{

		public function testReadWrite(){

			//Create the cookie
			\Twist::Cookie()->set('travis-test-cookie','42',2);

			$this -> assertTrue(\Twist::Cookie()->exists('travis-test-cookie'));
			$this -> assertEquals('42',\Twist::Cookie()->get('travis-test-cookie'));

			//Cant accurately test cookie delete as would need to make a remote test
			//\Twist::Cookie()->delete('travis-test-cookie');
			//$this -> assertFalse(\Twist::Cookie()->exists('travis-test-cookie'));
		}
	}