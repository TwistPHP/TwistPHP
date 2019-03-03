<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

class ICSCalendar extends PHPUnitSupport{

	public function testCreate(){

		$resCalendar = \Twist::ICS()->createCalendar();
		$this->assertTrue(strstr($resCalendar->getRaw(), 'BEGIN:VCALENDAR') !== false);

		$resCalendar->prodID('test-id');
		$this->assertTrue(strstr($resCalendar->getRaw(), 'PRODID') && strstr($resCalendar->getRaw(), 'test-id'));

		$resCalendar->version('test-ver');
		$this->assertTrue(strstr($resCalendar->getRaw(), 'VERSION') && strstr($resCalendar->getRaw(), 'test-ver'));

		$resCalendar->name('test-title');
		$this->assertTrue(strstr($resCalendar->getRaw(), 'NAME') && strstr($resCalendar->getRaw(), 'test-title'));

		$resCalendar->description('test-desc');
		$this->assertTrue(strstr($resCalendar->getRaw(), 'DESCRIPTION') && strstr($resCalendar->getRaw(), 'test-desc'));

		$resCalendar->refreshInterval('PT1D');
		$this->assertTrue(strstr($resCalendar->getRaw(), 'REFRESH-INTERVAL') && strstr($resCalendar->getRaw(), 'PT1D'));

		$resCalendar->color(120,121,122);
		$this->assertTrue(strstr($resCalendar->getRaw(), 'COLOR') && strstr($resCalendar->getRaw(), '120:121:122'));

	}
}