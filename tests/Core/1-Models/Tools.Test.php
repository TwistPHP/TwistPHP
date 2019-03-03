<?php

use PHPUnit\Framework\TestCase;

require_once '../../phpunit-support.php';

class Tools extends PHPUnitSupport{

	public function testTraverseURI(){
		//Need to look at this
		$this->assertEquals('/my/test',\Twist::framework()->tools()->traverseURI('./','/my/test/uri'));
		$this->assertEquals('/my/test',\Twist::framework()->tools()->traverseURI('../','/my/test/uri'));
	}

}