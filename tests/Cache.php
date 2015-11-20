<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Cache extends \PHPUnit_Framework_TestCase{

	public function testValidCache(){
		\Twist::Cache()->write('cache-test','pass',10);
		sleep(1);
		$this -> assertEquals('pass',\Twist::Cache()->read('cache-test'));
	}

	public function testExpiredCache(){
		\Twist::Cache()->write('cache-test2','pass',1);
		sleep(1);
		$this -> assertEquals(null,\Twist::Cache()->read('cache-test2'));
	}
}