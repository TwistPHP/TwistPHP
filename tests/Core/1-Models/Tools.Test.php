<?php

use PHPUnit\Framework\TestCase;

class Tools extends TestCase{

	public function testTraverseURI(){
		//Need to look at this
		$this->assertEquals('/my/test',\Twist::framework()->tools()->traverseURI('./','/my/test/uri'));
		$this->assertEquals('/my/test',\Twist::framework()->tools()->traverseURI('../','/my/test/uri'));
	}

}