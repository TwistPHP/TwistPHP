<?php

	use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

	class Image extends PHPUnitSupport{

		public static $resImage = null;
		public static $strImageCode = '';

		public function testCreate(){

			self::$resImage = \Twist::Image()->create(16,16, '#000000');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testGetInfo(){

			$arrInfo = self::$resImage->currentInfo();
			$this->assertEquals($arrInfo['orientation'], 'square');
		}

		public function testFill(){

			self::$resImage->fill('#FF0000');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testLine(){

			self::$resImage->line(0,0,16,16);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testRectangle(){

			self::$resImage->rectangle(4,4,12,8);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testString(){

			self::$resImage->string(5,5,'T');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testFlip(){

			self::$resImage->flip('vertical');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testOpacity(){

			self::$resImage->opacity(0.5);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testRotate(){

			self::$resImage->rotate(90);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function testCrop(){

			self::$resImage->crop(1,1,15,15);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			$arrInfo = self::$resImage->currentInfo();
			$this->assertEquals($arrInfo['width'], 14);
			$this->assertEquals($arrInfo['height'], 14);

			self::$strImageCode = $strNewImageCode;
		}

		public function testInvert(){

			self::$resImage->filterInvert();
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->_assertStringContainsString('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

	}