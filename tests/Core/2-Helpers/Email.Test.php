<?php

use PHPUnit\Framework\TestCase;

require_once '../../phpunit-support.php';

class Email extends PHPUnitSupport{

	public static $arrSource = array();

	public function testCreate(){

		$resEmail = \Twist::Email()->create();
		$resEmail->setEncoding('7bit');
		$resEmail->setCharEncoding();
		$resEmail->setSensitivity();
		$resEmail->setPriority();
		$resEmail->addTo('travisci2@unit-test-twistphp.com','Travis CI 2');
		$resEmail->addCc('travisci3@unit-test-twistphp.com','Travis CI 3');
		$resEmail->addBcc('travisci4@unit-test-twistphp.com','Travis CI 4');
		$resEmail->setFrom('travisci@unit-test-twistphp.com','Travis CI');
		$resEmail->setSubject('A test email');
		$resEmail->setBodyHTML('Body of a test email<br>From TwistPHP');

		self::$arrSource = $resEmail->source();

		$this->assertStringContainsString('travisci2@unit-test-twistphp.com',self::$arrSource['to']);
		$this->assertEquals('A test email',self::$arrSource['subject']);
		$this->assertStringContainsString('Body of a test email', self::$arrSource['body']);
		$this->assertStringContainsString('From: Travis CI <travisci@unit-test-twistphp.com>', self::$arrSource['raw']);

		//Adding an attachment will force encoding to be base64
		$resEmail->addAttachment(TWIST_APP.'/Data/test.json');
		self::$arrSource = $resEmail->source();

		$this->assertStringContainsString('Qm9keSBvZiBhIHRlc3QgZW1haWwKRnJvbSBUd2lzdFBIUA==', self::$arrSource['body']);
		$this->assertStringContainsString('Content-Description: test.json', self::$arrSource['body']);
	}

	public function testParser(){

		$arrEmailData = \Twist::Email()->parseSource(self::$arrSource['raw']);

		$this->assertEquals('travisci2@unit-test-twistphp.com',$arrEmailData['to']);
		$this->assertEquals('travisci@unit-test-twistphp.com',$arrEmailData['from']);
		$this->assertEquals('A test email',$arrEmailData['subject']);
		
		$this->assertTrue(count($arrEmailData['attachments']) > 0);
	}
}