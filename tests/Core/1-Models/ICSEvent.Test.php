<?php

	use PHPUnit\Framework\TestCase;

	class ICSEvent extends TestCase{

		public function testCreate(){

			$resEvent = \Twist::ICS()->createEvent();

			//Check that the validation works
			$this->assertTrue(strstr($resEvent->getRaw(), 'Event validation failed') !== false);

			//Check the output when bypassing validation
			$this->assertTrue(strstr($resEvent->getRaw(true), 'BEGIN:VEVENT') !== false);

			$this->assertNull($resEvent->startDate());
			$resEvent->startDate('2018-11-09 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'DSTART') && strstr($resEvent->getRaw(), '20181109'));

			$this->assertNull($resEvent->endDate());
			$resEvent->endDate('2018-11-10 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'DEND') && strstr($resEvent->getRaw(), '20181110'));

			$this->assertNull($resEvent->allDay());
			$resEvent->allDay('2018-11-09 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'ALLDAY') !== false);

			$this->assertNull($resEvent->title());
			$resEvent->title('test-title');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'SUMMARY') && strstr($resEvent->getRaw(), 'test-title'));

			$this->assertNull($resEvent->description());
			$resEvent->description('test-desc');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'DESCRIPTION') && strstr($resEvent->getRaw(), 'test-desc'));

			$this->assertNull($resEvent->location());
			$resEvent->location('test-location');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'LOCATION') && strstr($resEvent->getRaw(), 'test-location'));

			$this->assertNull($resEvent->url());
			$resEvent->url('https://twistphp.com');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'URL') && strstr($resEvent->getRaw(), 'https://twistphp.com'));

			//Now check teh output without bypassing validation
			$this->assertTrue(strstr($resEvent->getRaw(), 'BEGIN:VEVENT') !== false);
		}
	}