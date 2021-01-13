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
			$this->assertArrayNotHasKey('mailqueue',$arrPackages);

			$arrPackages = \Twist::framework()->package()->getRepository();
			$this->assertArrayHasKey('mailqueue',$arrPackages);

			\Twist::framework()->package()->download($arrPackages['mailqueue']['key']);

			$arrPackages = \Twist::framework()->package()->getAll();
			$this->assertArrayHasKey('mailqueue',$arrPackages);
		}

		public function testInstall(){

			$this->assertFalse(\Twist::framework()->package()->isInstalled('mailqueue'));

			\Twist::framework()->package()->installer('mailqueue');

			$this->assertTrue(\Twist::framework()->package()->isInstalled('mailqueue'));
		}

		public function testInformation(){

			$this->assertArrayHasKey('slug',\Twist::framework()->package()->information('mailqueue'));
		}

		public function testUninstall(){

			\Twist::framework()->package()->uninstaller('mailqueue');

			$this->assertFalse(\Twist::framework()->package()->isInstalled('mailqueue'));
		}

	}