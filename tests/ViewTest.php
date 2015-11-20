<?php

namespace App\Models;

class ViewTest extends \PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		//$this -> assertEquals('completes',\Twist::View()->replace('{data:test}',array('test' => 'complete')));
		$this -> assertEquals('completes','complete');
	}
}