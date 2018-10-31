<?php

use PHPUnit\Framework\TestCase;

class ICS extends TestCase{

	public function testCalendar(){

		$resCalendar = \Twist::ICS()->createCalendar();

		$this->assertTrue(is_resource($resCalendar) || is_object($resCalendar));
	}

	public function testEvent(){

		$resEvent = \Twist::ICS()->createEvent();

		$this->assertTrue(is_resource($resEvent) || is_object($resEvent));



	}
}