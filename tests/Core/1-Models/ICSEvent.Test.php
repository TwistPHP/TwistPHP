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
			$this->assertTrue(strstr($resEvent->getRaw(true), 'DTSTART') !== false);

			$this->assertNull($resEvent->endDate());
			$resEvent->endDate('2018-11-10 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'DTEND') !== false);

			$this->assertNull($resEvent->allDay());
			$resEvent->allDay('2018-11-09 00:00:00');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'ALLDAY') !== false);

			$this->assertNull($resEvent->title());
			$resEvent->title('test-title');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'SUMMARY') && strstr($resEvent->getRaw(true), 'test-title'));

			$this->assertNull($resEvent->description());
			$resEvent->description('test-desc');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'DESCRIPTION') && strstr($resEvent->getRaw(true), 'test-desc'));

			$this->assertNull($resEvent->location());
			$resEvent->location('test-location');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'LOCATION') && strstr($resEvent->getRaw(true), 'test-location'));

			$this->assertNull($resEvent->url());
			$resEvent->url('https://twistphp.com');
			$this->assertTrue(strstr($resEvent->getRaw(true), 'URL') && strstr($resEvent->getRaw(true), 'https://twistphp.com'));

			//Now check the output without bypassing validation
			$this->assertTrue(strstr($resEvent->getRaw(), 'BEGIN:VEVENT') !== false);
		}
	}