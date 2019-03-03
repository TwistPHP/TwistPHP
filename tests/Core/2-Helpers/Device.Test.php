<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

class Device extends PHPUnitSupport{

	public function testGet(){
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1';
		$arrDevice = Twist::Device()->get();
		$this->assertEquals('Firefox',$arrDevice['browser']['title']);
	}

	public function testGetOS(){
		$this->assertEquals('Windows',Twist::Device()->getOS());
	}

	public function testGetOSVersion(){
		$this->assertEquals('Windows 7',Twist::Device()->getOSVersion());
	}

	public function testGetDevice(){
		$this->assertEquals('Desktop',Twist::Device()->getDevice());
	}

	public function testGetBrowser(){
		$this->assertEquals('Firefox',Twist::Device()->getBrowser());
	}

}