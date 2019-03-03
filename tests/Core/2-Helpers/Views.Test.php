<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

class Views extends PHPUnitSupport{

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
		$this -> assertEquals(base64_encode('pass'),\Twist::View()->replace("{base64_encode[data:test]}",array('test' => 'pass')));
		$this -> assertEquals('pass',\Twist::View()->replace("{base64_decode[data:test]}",array('test' => base64_encode('pass'))));
	}

	public function testTagResource(){

		\Twist::framework()->setting('RESOURCE_INCLUDE_ONCE',true);

		$strTagOutput = \Twist::View()->replace("{resource:debug}");
		$this->assertStringContainsString('<script', $strTagOutput);
		$this->assertStringContainsString('twist/Core/Resources/debug/js/twistdebug.js', $strTagOutput);
		$this->assertStringContainsString('<link', $strTagOutput);
		$this->assertStringContainsString('twist/Core/Resources/debug/css/twistdebug.css', $strTagOutput);

		//A resource an only be included once per page load when RESOURCE_INCLUDE_ONCE is enabled
		$strTagOutput = \Twist::View()->replace("{resource:debug,js=true}");
		$this->assertEquals('', $strTagOutput);

		//Turn include once off so that we can test multiple times with the same resource
		\Twist::framework()->setting('RESOURCE_INCLUDE_ONCE',false);

		$strTagOutput = \Twist::View()->replace("{resource:debug,js=true}");
		$this->assertStringContainsString('<script', $strTagOutput);
		$this->assertStringContainsString('twistdebug.js', $strTagOutput);

		$strTagOutput = \Twist::View()->replace("{resource:debug,css=true}");
		$this->assertStringContainsString('<link', $strTagOutput);
		$this->assertStringContainsString('twistdebug.css', $strTagOutput);

		$strTagOutput = \Twist::View()->replace("{resource:debug,js,async=async}");
		$this->assertStringContainsString('<script', $strTagOutput);
		$this->assertStringContainsString('async', $strTagOutput);
		$this->assertStringContainsString('twistdebug.js', $strTagOutput);

		$strTagOutput = \Twist::View()->replace("{resource:debug,js,async=defer}");
		$this->assertStringContainsString('<script', $strTagOutput);
		$this->assertStringContainsString('defer', $strTagOutput);
		$this->assertStringContainsString('twistdebug.js', $strTagOutput);

		$strTagOutput = \Twist::View()->replace("{resource:debug,js=true,inline=true}");
		$this->assertStringContainsString('<script', $strTagOutput);
		$this->assertStringContainsString('twist.debug=', $strTagOutput);

		\Twist::framework()->setting('RESOURCE_INCLUDE_ONCE',true);
	}

	public function testTagCSS(){

		//Check the tag is being output
		$strTagOutput = \Twist::View()->replace("{css:twist/Core/Resources/debug/css/twistdebug.css}");
		$this->assertStringContainsString('<link', $strTagOutput);
		$this->assertStringContainsString('twist/Core/Resources/debug/css/twistdebug.css', $strTagOutput);
		
		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/debug/css',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/debug/css/twistdebug.css',TWIST_APP),'test override file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/debug/css/twistdebug.css',TWIST_APP)));

		//Check the replacement is being used
		$strTagOutput = \Twist::View()->replace("{css:twist/Core/Resources/debug/css/twistdebug.css}");
		$this->assertStringContainsString('<link', $strTagOutput);
		$this->assertStringContainsString('app/Twist/Core/Resources/debug/css/twistdebug.css', $strTagOutput);
	}

	public function testTagJS(){

		$strTagOutput = \Twist::View()->replace("{js:twist/Core/Resources/debug/js/twistdebug.js}");
		$this->assertStringContainsString('<script', $strTagOutput);
		$this->assertStringContainsString('twist/Core/Resources/debug/js/twistdebug.js', $strTagOutput);

		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/debug/js',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/debug/js/twistdebug.js',TWIST_APP),'test override file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/debug/js/twistdebug.js',TWIST_APP)));

		$strTagOutput = \Twist::View()->replace("{js:twist/Core/Resources/debug/js/twistdebug.js}");
		$this->assertStringContainsString('<script', $strTagOutput);
		$this->assertStringContainsString('app/Twist/Core/Resources/debug/js/twistdebug.js', $strTagOutput);
	}

	public function testTagImg(){

		$strTagOutput = \Twist::View()->replace("{img:twist/Core/Resources/logos/logo.png}");
		$this->assertStringContainsString('<img', $strTagOutput);
		$this->assertStringContainsString('twist/Core/Resources/logos/logo.png', $strTagOutput);

		$strTagOutput = \Twist::View()->replace("{img:twist/Core/Resources/logos/logo.png,id=test1}");
		$this->assertStringContainsString('<img', $strTagOutput);
		$this->assertStringContainsString('twist/Core/Resources/logos/logo.png', $strTagOutput);
		$this->assertStringContainsString(' id="test1"', $strTagOutput);
		
		//Create an override JS file
		mkdir(sprintf('%s/Twist/Core/Resources/logos/',TWIST_APP),0777,true);
		file_put_contents(sprintf('%s/Twist/Core/Resources/logos/logo.png',TWIST_APP),'test override file');
		$this -> assertTrue(file_exists(sprintf('%s/Twist/Core/Resources/logos/logo.png',TWIST_APP)));

		$strTagOutput = \Twist::View()->replace("{img:twist/Core/Resources/logos/logo.png}");
		$this->assertStringContainsString('<img', $strTagOutput);
		$this->assertStringContainsString('app/Twist/Core/Resources/logos/logo.png', $strTagOutput);
	}
}
