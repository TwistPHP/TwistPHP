<?php

	use PHPUnit\Framework\TestCase;

	class ICSEvent extends TestCase{

		public function testCreate(){

			$resEvent = \Twist::ICS()->createEvent()();
			$this->assertTrue(strstr($resEvent->getRaw(), 'BEGIN:VEVENT') !== false);

			$this->assertNull($resEvent->startDate());
			$resEvent->startDate('2018-11-09 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(), 'DSTART') && strstr($resEvent->getRaw(), '20181109'));

			$this->assertNull($resEvent->endDate());
			$resEvent->endDate('2018-11-10 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(), 'DEND') && strstr($resEvent->getRaw(), '20181109'));

			$this->assertNull($resEvent->allDay());
			$resEvent->allDay('2018-11-09 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(), 'ALLDAY') !== false);

			$this->assertNull($resEvent->title());
			$resEvent->title('test-title');
			$this->assertTrue(strstr($resEvent->getRaw(), 'SUMMARY') && strstr($resEvent->getRaw(), 'test-title'));

			$this->assertNull($resEvent->description());
			$resEvent->description('test-desc');
			$this->assertTrue(strstr($resEvent->getRaw(), 'DESCRIPTION') && strstr($resEvent->getRaw(), 'test-desc'));

			$this->assertNull($resEvent->location());
			$resEvent->location('test-location');
			$this->assertTrue(strstr($resEvent->getRaw(), 'LOCATION') && strstr($resEvent->getRaw(), 'test-location'));

			$this->assertNull($resEvent->url());
			$resEvent->url('https://twistphp.com');
			$this->assertTrue(strstr($resEvent->getRaw(), 'URL') && strstr($resEvent->getRaw(), 'https://twistphp.com'));

		}
	}