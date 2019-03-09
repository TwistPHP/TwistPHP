<?php

	use PHPUnit\Framework\TestCase;

	class Boot extends TestCase{

		public function testLaunchFramework(){

			//Include the boot file
			require_once dirname(__FILE__).'/../../../dist/twist/Core/boot.php';

			//Launch the framework ready for use
			Twist::launch();
			Twist::sheduledtasks();

			$this->assertTrue(defined('TWIST_LAUNCHED'));
		}
	}