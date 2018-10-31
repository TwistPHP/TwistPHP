<?php

use PHPUnit\Framework\TestCase;

class Settings extends TestCase{

	public function testGet(){
		$this -> assertEquals('Travis CI Test',\Twist::framework()->setting('SITE_NAME'));
	}

	public function testSet(){
		$this -> assertTrue(\Twist::framework()->setting('SITE_NAME','Travis CI Test - Updated'));
		$this -> assertEquals('Travis CI Test - Updated',\Twist::framework()->setting('SITE_NAME'));

		//Reset the site title back to its original setting - not a test
		\Twist::framework()->setting('SITE_NAME','Travis CI Test');
	}
}