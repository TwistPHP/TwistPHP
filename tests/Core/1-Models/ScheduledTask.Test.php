<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class ScheduledTask extends PHPUnitSupport{

		public function testRun(){

			$arrPulse = Twist\Core\Models\ScheduledTasks::pulseInfo();
			$this->assertFalse($arrPulse['active']);

			Twist\Core\Models\ScheduledTasks::processor();
			sleep(1);
			Twist\Core\Models\ScheduledTasks::processor();
			sleep(1);
			Twist\Core\Models\ScheduledTasks::processor();

			$arrPulse = Twist\Core\Models\ScheduledTasks::pulseInfo();
			$this->assertTrue($arrPulse['active']);
		}
	}