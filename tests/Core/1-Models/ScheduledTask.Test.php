<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class ScheduledTask extends PHPUnitSupport{

		public function testRun(){

			//Enable a task
			$intTaskID = Twist\Core\Models\ScheduledTasks::createTask(
				'Travis Test',
				1,
				'twist/Core/Crons/ProtectFirewall.cron.php',
				0,
				'',
				true,
				'travis'
			);

			//Run the Cron
			$arrPulse = Twist\Core\Models\ScheduledTasks::pulseInfo();
			$this->assertFalse($arrPulse['active']);

			ob_start();

			Twist\Core\Models\ScheduledTasks::processor();
			sleep(1);
			Twist\Core\Models\ScheduledTasks::processor();
			sleep(1);
			Twist\Core\Models\ScheduledTasks::processor();

			ob_end_clean();

			$arrPulse = Twist\Core\Models\ScheduledTasks::pulseInfo();
			$this->assertTrue($arrPulse['active']);

			$arrTask = Twist\Core\Models\ScheduledTasks::get($intTaskID);
			$this->assertStringContainsString($arrTask['status'],'finished');

			$this->assertTrue(Twist\Core\Models\ScheduledTasks::editTask(
				$intTaskID,
				'Travis Test',
				1,
				'twist/Core/Crons/ProtectFirewall.cron.php',
				0,
				'',
				false
			));

			$this->assertTrue(Twist\Core\Models\ScheduledTasks::deleteTask(
				$intTaskID
			));

		}
	}