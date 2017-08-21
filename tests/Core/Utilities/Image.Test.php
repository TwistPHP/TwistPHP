<?php

	class Image extends \PHPUnit_Framework_TestCase{

		public static $resImage = null;
		public static $strImageCode = '';

		public function create(){

			self::$resImage = \Twist::Image()->create(16,16, '#000000');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function detectOrientation(){

			$strOrientation = self::$resImage->detectOrientation();
			$this->assertEquals($strOrientation, 'square');

			self::$strImageCode = self::$resImage->outputBase64();
		}

		public function aspectRatio(){

			$intAspectRatio = self::$resImage->aspectRatio();
			$this->assertEquals($intAspectRatio, 1);

			self::$strImageCode = self::$resImage->outputBase64();
		}

		public function normalizeColor(){

			$arrColour = self::$resImage->normalizeColor('#FF0000');
			$this->assertTrue(is_array($arrColour));

			//set the alpha too high
			$arrColour['a'] = 129;
			$arrColour = self::$resImage->normalizeColor($arrColour);

			//Check is has been lowered
			$this->assertEquals($arrColour['a'], 127);

			self::$strImageCode = self::$resImage->outputBase64();
		}

		public function fill(){

			self::$resImage->fill('#FF0000');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function line(){

			self::$resImage->line(0,0,16,16);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function rectangle(){

			self::$resImage->rectangle(4,4,12,8);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function text(){

			self::$resImage->string(5,5,'T');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function flip(){

			self::$resImage->flip('vertical');
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function opacity(){

			self::$resImage->opacity(0.5);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function rotate(){

			self::$resImage->rotate(90);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			self::$strImageCode = $strNewImageCode;
		}

		public function crop(){

			self::$resImage->crop(1,1,15,15);
			$strNewImageCode = self::$resImage->outputBase64();

			$this->assertTrue(!empty($strNewImageCode));
			$this->assertContains('base64', $strNewImageCode);
			$this->assertNotEquals($strNewImageCode, self::$strImageCode);

			$arrInfo = self::$resImage->currentInfo();
			$this->assertEquals($arrInfo['width'], 14);
			$this->assertEquals($arrInfo['height'], 14);

			self::$strImageCode = $strNewImageCode;
		}
		
	}