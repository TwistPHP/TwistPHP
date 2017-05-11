<?php

class Localisation extends \PHPUnit_Framework_TestCase{

	public function testFileSize(){

		$this->assertEquals('1.5kB',\Twist::File()->bytesToSize(1536));
	}

	public function testFileName(){

		$this->assertEquals('A-funny-File-name-OK.png',\Twist::File()->sanitizeName('A funny - File name (OK).png'));

		$this->assertEquals('php',\Twist::File()->extension('/some/file/name.php'));

		$this->assertEquals('name.php',\Twist::File()->name('/some/file/name.php'));
	}

	public function testMimeType(){

		$this->assertEquals('image/png',\Twist::File()->mimeType('/some/file/name.png'));

		$arrInfo = \Twist::File()->mimeTypeInfo('/some/file/name.png');
		$this->assertEquals('Portable Network Graphics (PNG)',$arrInfo['name']);

		$arrTypes = \Twist::File()->mimeTypes();
		$this->assertTrue(count($arrTypes) > 1);
	}

}