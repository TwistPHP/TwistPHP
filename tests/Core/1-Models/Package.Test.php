<?php

	use PHPUnit\Framework\TestCase;

	require_once dirname(__FILE__).'/../../phpunit-support.php';

	class Package extends PHPUnitSupport{

		public function testList(){

			$arrPackages = \Twist::framework()->package()->getAll();
			$this->assertArrayHasKey('manager',$arrPackages);
		}

		public function testDownload(){

			$arrPackages = \Twist::framework()->package()->getAll();
			$this->assertArrayNotHasKey('notifications',$arrPackages);

			$arrPackages = \Twist::framework()->package()->getRepository();
			$this->assertArrayHasKey('notifications',$arrPackages);

			\Twist::framework()->package()->download('notifications');

			$arrPackages = \Twist::framework()->package()->getAll();
			$this->assertArrayHasKey('notifications',$arrPackages);
		}

		public function testInstall(){

			$this->assertFalse(\Twist::framework()->package()->isInstalled('notifications'));

			\Twist::framework()->package()->installer('notifications');

			$this->assertTrue(\Twist::framework()->package()->isInstalled('notifications'));
		}

		public function testInformation(){

			$this->assertArrayHasKey('slug',\Twist::framework()->package()->information('notifications'));
		}

		public function testUninstall(){

			\Twist::framework()->package()->uninstaller('notifications');

			$this->assertFalse(\Twist::framework()->package()->isInstalled('notifications'));
		}

	}