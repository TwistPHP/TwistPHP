<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Views extends \PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		$this -> assertEquals('pass',\Twist::View()->replace('{data:test}',array('test' => 'pass')));
	}

	public function testTagIf(){
		$this -> assertEquals('pass',\Twist::View()->replace("{data:test=='OK'?'pass':'fail'}",array('test' => 'OK')));
	}
}