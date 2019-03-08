<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

class File extends PHPUnitSupport{

	public function testFileSize(){

		$this->assertEquals('100B',\Twist::File()->bytesToSize(100));
		$this->assertEquals('1kB',\Twist::File()->bytesToSize(1024));
		$this->assertEquals('1.5kB',\Twist::File()->bytesToSize(1536));
		$this->assertEquals('1.25kB',\Twist::File()->bytesToSize(1280));
		$this->assertEquals('1MB',\Twist::File()->bytesToSize(1048576));
		$this->assertEquals('1GB',\Twist::File()->bytesToSize(1073741824));
		$this->assertEquals('1TB',\Twist::File()->bytesToSize(1099511627776));
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

		$this->assertFalse(\Twist::File()->recursiveCreate(TWIST_APP.'/../../../../../../../../../../../../../../../../test'));
	}

	public function testDownload(){

		$arrResult = \Twist::File()->download('https://github.com/TwistPHP/TwistPHP/archive/v3.1.1.zip',TWIST_PUBLIC_ROOT.'/twistphp.zip',10);
		$this->assertTrue($arrResult['status']);
	}

	public function testHash(){

		$this->assertEquals('b6d68cf97891c60252db96bf61ce6309',\Twist::File()->hash(TWIST_PUBLIC_ROOT.'/app/hashtest/hashtest.php','md5'));
		$this->assertEquals('5e926875f1e8b3200b2403dd3f8946fc',\Twist::File()->directoryHash(TWIST_PUBLIC_ROOT.'/app/hashtest','md5'));

		$this->assertEquals('afd89cab0949bfbd55d5a0087ad24225451b70cf',\Twist::File()->hash(TWIST_PUBLIC_ROOT.'/app/hashtest/hashtest.php','sha1'));
		$this->assertEquals('045d457c2e241bb32e28d5e6e9b38c946faab6d6',\Twist::File()->directoryHash(TWIST_PUBLIC_ROOT.'/app/hashtest','sha1'));
	}

	public function testDirSize(){

		$this->assertEquals('244',\Twist::File()->directorySize(TWIST_PUBLIC_ROOT.'/app/hashtest'));
		$this->assertEquals('244B',\Twist::File()->directorySize(TWIST_PUBLIC_ROOT.'/app/hashtest',true));
	}

	public function testExists(){

		$this->assertTrue(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/app/hashtest/hashtest.php'));
		$this->assertFalse(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/app/hashtest/hashtest-invalid.php'));
	}

	public function testWrite(){

		\Twist::File()->write(TWIST_PUBLIC_ROOT.'/test-write.log','some-test-data',null,false);
		$this->assertTrue(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/test-write.log'));

		\Twist::File()->write(TWIST_PUBLIC_ROOT.'/test-write-delayed.log','some-test-data',null,true);
		$this->assertTrue(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/test-write-delayed.log',true));
		$this->assertFalse(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/test-write-delayed.log',false));

		\Twist::File()->writeDelayedFiles();
		$this->assertTrue(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/test-write-delayed.log'));
	}

	public function testRead(){

		$this->assertEquals('some-test-data',\Twist::File()->read(TWIST_PUBLIC_ROOT.'/test-write.log'));

		\Twist::File()->write(TWIST_PUBLIC_ROOT.'test-write-delayed2.log','/some-test-data',null,true);
		$this->assertEquals('some-test-data',\Twist::File()->read(TWIST_PUBLIC_ROOT.'/test-write-delayed2.log'));

		$this->assertEquals('test',\Twist::File()->read(TWIST_PUBLIC_ROOT.'/test-write.log',5,9));
		$this->assertEquals('-',\Twist::File()->read(TWIST_PUBLIC_ROOT.'/test-write-delayed2.log',4,5));
	}

	public function testMoveCopy(){

		//Make dir /move-test
		\Twist::File()->recursiveCreate(TWIST_PUBLIC_ROOT.'/move-test');
		\Twist::File()->move(TWIST_PUBLIC_ROOT.'/test-write.log',TWIST_PUBLIC_ROOT.'/move-test/test-write.log');
		\Twist::File()->move(TWIST_PUBLIC_ROOT.'/test-write-delayed.log',TWIST_PUBLIC_ROOT.'/move-test/test-write-delayed.log');

		$this->assertTrue(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/move-test/test-write-delayed.log'));

		\Twist::File()->recursiveCopy(TWIST_PUBLIC_ROOT.'/move-test',TWIST_PUBLIC_ROOT.'/copy-test');

		$this->assertTrue(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/copy-test/test-write-delayed.log'));
	}

	public function testRemove(){

		\Twist::File()->recursiveRemove(TWIST_PUBLIC_ROOT.'/move-test');
		\Twist::File()->recursiveRemove(TWIST_PUBLIC_ROOT.'/copy-test');

		$this->assertFalse(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/copy-test/test-write-delayed.log'));

		//Now remove the delayed file 'test-write-delayed2.log' from the read test
		\Twist::File()->remove(TWIST_PUBLIC_ROOT.'test-write-delayed2.log');

		$this->assertFalse(\Twist::File()->exists(TWIST_PUBLIC_ROOT.'/test-write-delayed2.log'));
	}


}