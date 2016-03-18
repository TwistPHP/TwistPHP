<?php

class Tools extends \PHPUnit_Framework_TestCase{

	public function testTraverseURI(){
		$this->assertEquals('/my/test',\Twist::framework()->tools()->traverseURI('./','/my/test/uri'));
		$this->assertEquals('/my',\Twist::framework()->tools()->traverseURI('../','/my/test/uri'));
	}

}