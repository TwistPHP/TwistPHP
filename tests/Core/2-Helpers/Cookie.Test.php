<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class Cookie extends PHPUnitSupport{

		public function testReadWriteExpire(){

			//Create the cookie
			\Twist::Cookie()->set('travis-test-cookie','42',2);


			//Check the cookie exists and contains the correct data after 1 second
			sleep(1);
			$this -> assertTrue(\Twist::Cookie()->exists('travis-test-cookie'));
			$this -> assertEquals('42',\Twist::Cookie()->get('travis-test-cookie'));

			//Wait one more second and check that the cookie has now been deleted
			sleep(1);
			$this -> assertFalse(\Twist::Cookie()->exists('travis-test-cookie'));
		}

		public function testDelete(){

			\Twist::Cookie()->set('travis-test-cookie2','42',2);
			$this -> assertTrue(\Twist::Cookie()->exists('travis-test-cookie2'));

			\Twist::Cookie()->delete('travis-test-cookie2');
			$this -> assertFalse(\Twist::Cookie()->exists('travis-test-cookie2'));
		}
	}