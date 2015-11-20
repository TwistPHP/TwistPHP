<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class ViewTest extends \PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		$this -> assertEquals('complete',\Twist::View()->replace('{data:test}',array('test' => 'complete')));
	}

	public function testReplaceTag2(){
		$this -> assertEquals('completes',\Twist::View()->replace('{data:test}',array('test' => 'complete')));
	}
}