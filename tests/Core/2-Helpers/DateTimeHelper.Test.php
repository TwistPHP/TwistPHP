<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class DateTimeHelper extends PHPUnitSupport{

		public function testPastFuture(){

			$this -> assertTrue(\Twist::DateTime()->inFuture(time()+10));
			$this -> assertTrue(\Twist::DateTime()->inPast(time()-10));
		}

		public function testAge(){

			$this -> assertStringContainsString('moment',\Twist::DateTime()->getAge(time()+59));
			$this -> assertStringContainsString('minute',\Twist::DateTime()->getAge(time()+119));
			$this -> assertStringContainsString('minutes',\Twist::DateTime()->getAge(time()+3599));
			$this -> assertStringContainsString('hour',\Twist::DateTime()->getAge(time()+7199));
			$this -> assertStringContainsString('hours',\Twist::DateTime()->getAge(time()+86399));
			$this -> assertStringContainsString('Tomorrow',\Twist::DateTime()->getAge(time()+172799));
			$this -> assertStringContainsString('days',\Twist::DateTime()->getAge(time()+(86400*28)));
			$this -> assertStringContainsString('month',\Twist::DateTime()->getAge(time()+(86400*33)));
			$this -> assertStringContainsString('months',\Twist::DateTime()->getAge(time()+(86400*90)));
			$this -> assertStringContainsString('year',\Twist::DateTime()->getAge(time()+(86400*364)));
			$this -> assertStringContainsString('years',\Twist::DateTime()->getAge(time()+(86400*700)));

		}

		public function testPretty(){

			$strPrettyTime = \Twist::DateTime()->prettyTime(time()+(86400+3600+120));

			$this -> assertStringContainsString('day',$strPrettyTime);
			$this -> assertStringContainsString('hour',$strPrettyTime);
			$this -> assertStringContainsString('minute',$strPrettyTime);
		}

		public function testPersonAge(){

			$this -> assertEquals(33,\Twist::DateTime()->getPersonAge('1985-01-01'));
		}

		public function testBetween(){

			$this -> assertEquals(10,count(\Twist::DateTime()->getDayBetweenDates('2013-01-01','2013-01-10',1)));
		}
	}