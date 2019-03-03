<?php

use PHPUnit\Framework\TestCase;

require_once '../../phpunit-support.php';

class Session extends PHPUnitSupport{

	public function testReadWrite(){
		\Twist::Session()->data('session-test','pass');
		$this -> assertEquals('pass',\Twist::Session()->data('session-test'));
	}
}