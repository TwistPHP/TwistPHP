<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

class ICS extends PHPUnitSupport{

	public function testCalendar(){

		$resCalendar = \Twist::ICS()->createCalendar();

		$this->assertTrue(is_resource($resCalendar) || is_object($resCalendar));
	}

	public function testEvent(){

		$resEvent = \Twist::ICS()->createEvent();

		$this->assertTrue(is_resource($resEvent) || is_object($resEvent));



	}
}