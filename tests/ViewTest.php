<?php

require_once './index.php';

class ViewTest extends \PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		$this -> assertEquals('complete',\Twist::View()->replace('{data:test}',array('test' => 'complete')));
	}

	public function testReplaceTag2(){
		$this -> assertEquals('completes',\Twist::View()->replace('{data:test}',array('test' => 'complete')));
	}
}