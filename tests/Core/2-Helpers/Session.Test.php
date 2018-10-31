<?php

use PHPUnit\Framework\TestCase;

class Session extends TestCase{

	public function testReadWrite(){
		\Twist::Session()->data('session-test','pass');
		$this -> assertEquals('pass',\Twist::Session()->data('session-test'));
	}
}