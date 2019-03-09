<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class Boot extends PHPUnitSupport{

		public function testLaunchFramework(){

			require_once dirname(__FILE__).'/../../../dist/twist/framework.php';
			$this->assertTrue(defined('TWIST_LAUNCHED'));
		}
	}