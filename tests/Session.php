<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Session extends \PHPUnit_Framework_TestCase{

	public function testReadWrite(){
		\Twist::Session()->data('session-test','pass');
		$this -> assertEquals('pass',\Twist::Session()->data('session-test'));
	}
}