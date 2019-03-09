<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class Command extends PHPUnitSupport{

		public function testSingleCall(){

			//Test a single process
			$arrResult = \Twist::Command()->execute('ls',TWIST_PUBLIC_ROOT);
			$this -> assertEquals('ls',$arrResult['command']);
			$this -> assertTrue($arrResult['status']);
			$this -> _assertStringContainsString('index.php',$arrResult['output']);
		}

		public function testMultiCall(){

			$intPID_1 = \Twist::Command()->executeChild('ls',TWIST_PUBLIC_ROOT);
			$intPID_2 = \Twist::Command()->executeChild('sleep 1 && ls',TWIST_PUBLIC_ROOT.'/Core/2-Helpers/');

			//The second process should still be running
			$this -> assertTrue(\Twist::Command()->childRunning($intPID_2));

			//Sleep for one second and check the first process has ended with a status of 1
			sleep(1);
			$this -> assertFalse(\Twist::Command()->childRunning($intPID_1));
			$this -> assertEquals($intPID_1,\Twist::Command()->childStatus($intPID_1)['pid']);

			//Check that there are two porcesses that we have not yet collected results for
			$arrProcesses = \Twist::Command()->childProcesses();
			$this -> assertTrue((count($arrProcesses) == 2));

			//Sleep for another second and check the results of both processes
			sleep(1);
			$this -> _assertStringContainsString('index.php',\Twist::Command()->childResult($intPID_1)['output']);
			$this -> _assertStringContainsString('Command.Test.php',\Twist::Command()->childResult($intPID_2)['output']);

			//Check that there are no running processes left to collect
			$arrProcesses = \Twist::Command()->childProcesses();
			$this -> assertTrue((count($arrProcesses) == 0));
		}
	}