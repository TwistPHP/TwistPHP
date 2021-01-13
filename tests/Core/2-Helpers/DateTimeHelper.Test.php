<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class DateTimeHelper extends PHPUnitSupport{

		public function testPastFuture(){

			$this -> assertTrue(\Twist::DateTime()->inFuture(time()+10));
			$this -> assertTrue(\Twist::DateTime()->inPast(time()-10));
		}

		public function testAge(){

			$this -> _assertStringContainsString('moment',\Twist::DateTime()->getAge(time()+59));
			$this -> _assertStringContainsString('minute',\Twist::DateTime()->getAge(time()+119));
			$this -> _assertStringContainsString('minutes',\Twist::DateTime()->getAge(time()+3599));
			$this -> _assertStringContainsString('hour',\Twist::DateTime()->getAge(time()+7199));
			$this -> _assertStringContainsString('hours',\Twist::DateTime()->getAge(time()+86399));
			$this -> _assertStringContainsString('Tomorrow',\Twist::DateTime()->getAge(time()+172799));
			$this -> _assertStringContainsString('days',\Twist::DateTime()->getAge(time()+(86400*28)));
			$this -> _assertStringContainsString('month',\Twist::DateTime()->getAge(time()+(86400*33)));
			$this -> _assertStringContainsString('months',\Twist::DateTime()->getAge(time()+(86400*90)));
			$this -> _assertStringContainsString('year',\Twist::DateTime()->getAge(time()+(86400*364)));
			$this -> _assertStringContainsString('years',\Twist::DateTime()->getAge(time()+(86400*740)));

		}

		public function testPretty(){

			$strPrettyTime = \Twist::DateTime()->prettyTime(time()+(86400+3600+120));

			$this -> _assertStringContainsString('day',$strPrettyTime);
			$this -> _assertStringContainsString('hour',$strPrettyTime);
			$this -> _assertStringContainsString('minute',$strPrettyTime);
		}

		public function testPersonAge(){

			$this -> assertEquals(34,\Twist::DateTime()->getPersonAge((date('Y')-34).'-01-01'));
		}

		public function testBetween(){
			//Sat -> Sat looking for day5 a friday
			$this -> assertEquals('2019-03-15',\Twist::DateTime()->getDayBetweenDates('2019-03-09','2019-03-16',5)[0]);
		}
	}