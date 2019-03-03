<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

class UserAgent extends PHPUnitSupport{

	public function testGet(){
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1';
		$this->assertEquals('Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',Twist\Core\Models\UserAgent::get());
	}

	public function testDetect(){
		$arrUserAgent = Twist\Core\Models\UserAgent::detect();
		$this->assertEquals('Firefox',$arrUserAgent['browser']['title']);
	}

	public function testDeviceType(){
		$this->assertEquals('Desktop',Twist\Core\Models\UserAgent::getDeviceType('win10'));
	}

	public function testOS(){
		$arrUserAgent = Twist\Core\Models\UserAgent::getOS('win10');
		$this->assertEquals('Windows 10',$arrUserAgent['version']);
	}

	public function testBrowser(){
		$arrUserAgent = Twist\Core\Models\UserAgent::getBrowser('firefox');
		$this->assertEquals('Firefox',$arrUserAgent['title']);
	}

}