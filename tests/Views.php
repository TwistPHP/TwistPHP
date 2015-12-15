<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Views extends \PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		$this -> assertEquals('pass',\Twist::View()->replace('{data:test}',array('test' => 'pass')));
	}

	public function testTagIf(){
		$this -> assertEquals('pass',\Twist::View()->replace("{data:test=='OK'?'pass':'fail'}",array('test' => 'OK')));
	}

	public function testTagYear(){
		$this -> assertEquals(date('Y'),\Twist::View()->replace("{date:Y}"));
	}

	public function testTagMD5(){
		$this -> assertEquals(md5('pass'),\Twist::View()->replace("{md5[data:test]}",array('test' => 'pass')));
	}

	public function testTagResource(){

		$strTagOutput = \Twist::View()->replace("{resource:jquery}");
		$this->assertContains('<script', $strTagOutput);
		$this->assertContains('twist/Core/Resources/jquery/jquery-2.1.4.min.js', $strTagOutput);
	}

	public function testTagCSS(){

		//Check the tag is being output
		$strTagOutput = \Twist::View()->replace("{css:twist/Core/Resources/arable/arable.min.css}");
		$this->assertContains('<link', $strTagOutput);
		$this->assertContains('twist/Core/Resources/arable/arable.min.css', $strTagOutput);
		
		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/arable/',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/arable/arable.min.css',TWIST_APP),'test over-ride file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/arable/arable.min.css',TWIST_APP)));

		//Check the replacement is being used
		$strTagOutput = \Twist::View()->replace("{css:twist/Core/Resources/arable/arable.min.css}");
		$this->assertContains('<link', $strTagOutput);
		$this->assertContains('app/Twist/Core/Resources/arable/arable.min.css', $strTagOutput);
	}

	public function testTagJS(){

		$strTagOutput = \Twist::View()->replace("{js:twist/Core/Resources/jquery/jquery-2.1.4.min.js}");
		$this->assertContains('<script', $strTagOutput);
		$this->assertContains('twist/Core/Resources/jquery/jquery-2.1.4.min.js', $strTagOutput);

		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/jquery/',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/jquery/jquery-2.1.4.min.js',TWIST_APP),'test over-ride file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/jquery/jquery-2.1.4.min.js',TWIST_APP)));

		$strTagOutput = \Twist::View()->replace("{js:twist/Core/Resources/jquery/jquery-2.1.4.min.js}");
		$this->assertContains('<script', $strTagOutput);
		$this->assertContains('app/Twist/Core/Resources/jquery/jquery-2.1.4.min.js', $strTagOutput);
		
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
		file_put_contents(sprintf('%s/Twist/Core/Resources/twist/logos/logo.png',TWIST_APP),'test over-ride file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/twist/logos/logo.png',TWIST_APP)));

		$strTagOutput = \Twist::View()->replace("{img:twist/Core/Resources/twist/logos/logo.png}");
		$this->assertContains('<img', $strTagOutput);
		$this->assertContains('app/Twist/Core/Resources/twist/logos/logo.png', $strTagOutput);
	}
}