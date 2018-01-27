<?php

use PHPUnit\Framework\TestCase;

class Timer extends TestCase{

	public function testStartStop(){

		\Twist::Timer()->start();
		sleep(1);
		\Twist::Timer()->log('test-point');
		usleep(100);
		$arrTimer = \Twist::Timer()->stop();

		//Check the total time is bigger than 0
		$this->assertTrue(($arrTimer['total'] > 0));

		//Check the total time was bigger than 1 second (should be as we sleep for 1 second)
		$this->assertTrue(($arrTimer['total'] > 1));

		//Check the test-point was logged during the test
		$this->assertTrue(count($arrTimer['log']) == 1 && $arrTimer['log'][0]['title'] == 'test-point');

		//Clear the results
		\Twist::Timer()->clear();

		//Check the results have been cleared
		$this->assertEquals(array(),\Twist::Timer()->results());
	}
}