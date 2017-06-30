<?php

class File extends \PHPUnit_Framework_TestCase{

	public function testFileSize(){

		$this->assertEquals('1.5kB',\Twist::File()->bytesToSize(1536));
	}

	public function testFileName(){

		$this->assertEquals('a-funny-file-name-ok.png',\Twist::File()->sanitizeName('A funny - File name (OK).png'));

		$this->assertEquals('php',\Twist::File()->extension('/some/file/name.php'));

		$this->assertEquals('name.php',\Twist::File()->name('/some/file/name.php'));
	}

	public function testMimeType(){

		$this->assertEquals('image/png',\Twist::File()->mimeType('/some/file/name.png'));

		$arrInfo = \Twist::File()->mimeTypeInfo('/some/file/name.png');
		$this->assertEquals('Image',$arrInfo['name']);

		$arrTypes = \Twist::File()->mimeTypes();
		$this->assertTrue(count($arrTypes) > 1);
	}

	public function testCreate(){

		$this->assertTrue(\Twist::File()->recursiveCreate(TWIST_APP.'/test'));

		$this->assertFalse(\Twist::File()->recursiveCreate(TWIST_APP.'/test&(@HF@(F* 1`{][,.,./,+==%``~§ ^1w w-1 ?. w//s[qs\||'));
	}
}