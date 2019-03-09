<?php

	use PHPUnit\Framework\TestCase;

	class Boot extends TestCase{

		public function testLaunchFramework(){

			require_once dirname(__FILE__).'/../../../dist/twist/framework.php';
			$this->assertTrue(defined('TWIST_LAUNCHED'));
		}
	}