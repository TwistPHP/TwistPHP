<?php

use PHPUnit\Framework\TestCase;

class Validate extends TestCase{

	public function testCreateTest(){

		$resValidation = \Twist::Validate()->createTest();

		$this->assertTrue(is_resource($resValidation) || is_object($resValidation));
	}

	public function testComparisons(){

		//Test compare
		$this->assertTrue(\Twist::Validate()->compare('String1','String1'));
		$this->assertFalse(\Twist::Validate()->compare('String1','somthingElse'));
		$this->assertFalse(\Twist::Validate()->compare('String1',12));
		$this->assertFalse(\Twist::Validate()->compare('String1',true));

		//Test email
		$this->assertTrue(\Twist::Validate()->email('test@twistphp.com') !== false);
		$this->assertFalse(\Twist::Validate()->email('test@test@twistphp.com'));
		$this->assertFalse(\Twist::Validate()->email('test@twistphp..com'));

		//Test domain
		$this->assertTrue(\Twist::Validate()->domain('twistphp.com') !== false);
		$this->assertFalse(\Twist::Validate()->domain('twistphp.com£ee'));

		//Test URL
		$this->assertTrue(\Twist::Validate()->url('http://twistphp.com/docs') !== false);
		$this->assertFalse(\Twist::Validate()->url('http://twistphp|.com/d*ocs'));

		//Test IP address
		$this->assertTrue(\Twist::Validate()->ip('192.168.0.1') !== false);
		$this->assertFalse(\Twist::Validate()->ip('192.ww1.233.21'));

		//Test Time String
		$this->assertTrue(\Twist::Validate()->timestring('00:01') !== false);
		$this->assertTrue(\Twist::Validate()->timestring('00:01:56') !== false);
		$this->assertFalse(\Twist::Validate()->timestring('99:01:56'));

		//Test boolean
		$this->assertTrue(\Twist::Validate()->boolean(true) !== false);
		$this->assertFalse(\Twist::Validate()->boolean('99:01:56'));

		//Test float
		$this->assertTrue(\Twist::Validate()->float(1.62) !== false);
		$this->assertFalse(\Twist::Validate()->float('1.62.22'));

		//Test integer
		$this->assertTrue(\Twist::Validate()->integer(1000) !== false);
		$this->assertFalse(\Twist::Validate()->integer('a1000'));

		//Test string
		$this->assertTrue(\Twist::Validate()->string('test-string') !== false);
		$this->assertFalse(\Twist::Validate()->string(true));

		//Test telephone
		$this->assertTrue(\Twist::Validate()->telephone('44 (1752) 800000 ext.6') !== false);
		$this->assertFalse(\Twist::Validate()->telephone('44 (1752) 800000 ext.6 or 0800626262'));

		//Test postcode
		$this->assertTrue(\Twist::Validate()->postcode('PL1 1PL') !== false);
		$this->assertFalse(\Twist::Validate()->postcode('PLED1 1PL'));
	}
}