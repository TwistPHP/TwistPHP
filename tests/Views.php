<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Views extends \PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		$this -> assertEquals('pass',\Twist::View()->replace('{data:test}',array('test' => 'pass')));
	}

	public function testTagIf(){
		$this -> assertEquals('pass',\Twist::View()->replace("{data:test=='OK'?'pass':'fail'}",array('test' => 'OK')));
		$this -> assertEquals('fail',\Twist::View()->replace("{data:test=='OK'?'pass':'fail'}",array('test' => 'NOT-OK')));
	}

	public function testTagDate(){
		$this -> assertEquals(date('Y'),\Twist::View()->replace("{date:Y}"));
		$this -> assertEquals(date('Y-m-d'),\Twist::View()->replace("{date:Y-m-d}"));
	}

	public function testTagHashes(){
		$this -> assertEquals(md5('pass'),\Twist::View()->replace("{md5[data:test]}",array('test' => 'pass')));
		$this -> assertEquals(sha1('pass'),\Twist::View()->replace("{sha1[data:test]}",array('test' => 'pass')));
	}

	public function testTagWordFunctions(){
		$this -> assertEquals(ucfirst('pass test'),\Twist::View()->replace("{ucfirst[data:test]}",array('test' => 'pass test')));
		$this -> assertEquals(ucwords('pass test'),\Twist::View()->replace("{ucwords[data:test]}",array('test' => 'pass test')));
		$this -> assertEquals(strtoupper('Pass'),\Twist::View()->replace("{strtoupper[data:test]}",array('test' => 'Pass')));
		$this -> assertEquals(strtolower('Pass'),\Twist::View()->replace("{strtolower[data:test]}",array('test' => 'Pass')));
	}

	public function testTagBase64(){
		$this -> assertEquals(base64_encode('pass'),\Twist::View()->replace("{base64_encode[data:test]}",array('test' => base64_encode('pass'))));
		$this -> assertEquals('pass',\Twist::View()->replace("{base64_decode[data:test]}",array('test' => base64_encode('pass'))));
	}

	public function testTagResource(){

		$strTagOutput = \Twist::View()->replace("{resource:twist/ajax}");
		$this->assertContains('<script', $strTagOutput);
		$this->assertContains('twist/Core/Resources/twist/ajax/js/twist-ajax.min.js', $strTagOutput);
	}

	public function testTagCSS(){

		//Check the tag is being output
		$strTagOutput = \Twist::View()->replace("{css:twist/Core/Resources/twist/ajax/css/twist-ajax.min.css}");
		$this->assertContains('<link', $strTagOutput);
		$this->assertContains('twist/Core/Resources/twist/ajax/css/twist-ajax.min.css', $strTagOutput);
		
		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/twist/ajax/css',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/twist/ajax/css/twist-ajax.min.css',TWIST_APP),'test override file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/twist/ajax/css/twist-ajax.min.css',TWIST_APP)));

		//Check the replacement is being used
		$strTagOutput = \Twist::View()->replace("{css:twist/Core/Resources/twist/ajax/css/twist-ajax.min.css}");
		$this->assertContains('<link', $strTagOutput);
		$this->assertContains('app/Twist/Core/Resources/twist/ajax/css/twist-ajax.min.css', $strTagOutput);
	}

	public function testTagJS(){

		$strTagOutput = \Twist::View()->replace("{js:twist/Core/Resources/twist/ajax/css/twist-ajax.min.js}");
		$this->assertContains('<script', $strTagOutput);
		$this->assertContains('twist/Core/Resources/twist/ajax/css/twist-ajax.min.js', $strTagOutput);

		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/twist/ajax/js',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/twist/ajax/css/twist-ajax.min.js',TWIST_APP),'test override file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/twist/ajax/css/twist-ajax.min.js',TWIST_APP)));

		$strTagOutput = \Twist::View()->replace("{js:twist/Core/Resources/twist/ajax/css/twist-ajax.min.js}");
		$this->assertContains('<script', $strTagOutput);
		$this->assertContains('app/Twist/Core/Resources/twist/ajax/css/twist-ajax.min.js', $strTagOutput);
		
	}

	public function testTagImg(){

		$strTagOutput = \Twist::View()->replace("{img:twist/Core/Resources/twist/logos/logo.png}");
		$this->assertContains('<img', $strTagOutput);
		$this->assertContains('twist/Core/Resources/twist/logos/logo.png', $strTagOutput);

		$strTagOutput = \Twist::View()->replace("{img:twist/Core/Resources/twist/logos/logo.png,id=test1}");
		$this->assertContains('<img', $strTagOutput);
		$this->assertContains('twist/Core/Resources/twist/logos/logo.png', $strTagOutput);
		$this->assertContains(' id="test1"', $strTagOutput);
		
		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/twist/logos/',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/twist/logos/logo.png',TWIST_APP),'test override file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/twist/logos/logo.png',TWIST_APP)));

		$strTagOutput = \Twist::View()->replace("{img:twist/Core/Resources/twist/logos/logo.png}");
		$this->assertContains('<img', $strTagOutput);
		$this->assertContains('app/Twist/Core/Resources/twist/logos/logo.png', $strTagOutput);
	}
}