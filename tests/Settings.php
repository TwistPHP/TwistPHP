<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Settings extends \PHPUnit_Framework_TestCase{

	public function testGet(){
		$this -> assertEquals('Travis CI Test',\Twist::framework()->setting('SITE_NAME'));
	}
}