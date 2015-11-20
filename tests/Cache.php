<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Cache extends \PHPUnit_Framework_TestCase{

	public function testReadWrite(){
		\Twist::Cache()->write('cache-test','pass',10);
		sleep(1);
		$this -> assertEquals('pass',\Twist::Cache()->read('cache-test'));
	}

	public function testReadWriteExpired(){
		\Twist::Cache()->write('cache-test2','pass',1);
		sleep(2);
		$this -> assertEquals(null,\Twist::Cache()->read('cache-test2'));
	}

	public function testRemove(){
		\Twist::Cache()->write('cache-test3','pass',1);
		\Twist::Cache()->remove('cache-test3');
		$this -> assertEquals(null,\Twist::Cache()->read('cache-test3'));
	}
}