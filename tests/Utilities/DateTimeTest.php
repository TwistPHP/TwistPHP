<?php

	use Twist\Core\Utilities\DateTime;

	class DateTimeTest extends PHPUnit_Framework_TestCase {

		public function testPushAndPop() {
			$stack = array();
			$this -> assertEquals(0, count($stack));

			array_push($stack, 'foo');
			$this -> assertEquals('foo', $stack[count($stack)-1]);
			$this -> assertEquals(1, count($stack));

			$this -> assertEquals('foo', array_pop($stack));
			$this -> assertEquals(0, count($stack));
		}

		public function testDate) {
			$strDateTime = \Twist::DateTime() -> date( 'Y-m-d H:i:s', 527955600 );
			$this -> assertEquals( '1986-09-24 14:20:00', $strDateTime );
		}

	}
