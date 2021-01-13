<?php

	use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

	class Asset extends PHPUnitSupport{

		public function testAdd(){
			$intAssetID = \Twist::Asset()->add('https://twistphp.com',9,'TwistPHP');
			$this -> assertEquals($intAssetID,1);
		}

		public function testGet(){
			$arrAsset = \Twist::Asset()->get(1);
			$this -> assertEquals($arrAsset['data'],'https://twistphp.com');
		}
	}