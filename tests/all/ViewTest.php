<?php

class viewTest extends PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		$this -> assertEquals('complete',\Twist::View()->replace('{data:test}',array('test' => 'complete')));
	}
}