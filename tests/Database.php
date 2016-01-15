<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Database extends \PHPUnit_Framework_TestCase{

	public function testQuery(){

		$resResult = \Twist::Database()->query("SELECT * FROM `twist_settings` LIMIT 1");

		$this -> assertTrue($resResult->status());
		$this -> assertEquals(1,$resResult->numberRows());
	}

	public function testQueryFail(){

		$resResult = \Twist::Database()->query("SELECT * FROM `--table-failed--` LIMIT 1");

		$this -> assertFalse($resResult->status());
	}

	public function testGet(){

		$arrResult = \Twist::Database()->get('twist_settings','SITE_NAME','key',true);

		$this -> assertEquals('Travis CI Test',$arrResult['value']);
	}

	public function testRecordObject(){

		$resRecord = \Twist::Database()->get('twist_settings','SITE_NAME','key');
		$resRecord->set('value','Travis CI Test - Updated');
		$resRecord->commit();

		$this->assertEquals('Travis CI Test - Updated',$resRecord->get('value'));
		unset($resRecord);

		$resRecord = \Twist::Database()->getRecord('twist_settings','SITE_NAME','key');
		$this->assertEquals('Travis CI Test - Updated',$resRecord->get('value'));
		unset($resRecord);

		/**
		 There is a bug here somewhere, to be fixed
		$resRecord = \Twist::Database()->records('twist_settings')->copy('SITE_NAME','key');
		$resRecord->set('key','SITE_NAME_TEST');
		$resRecord->commit();

		$arrResult = \Twist::Database()->getAll('twist_settings','SITE_NAME_TEST','key');
		$this -> assertEquals('clone passed',(count($arrResult) == 1) ? 'clone passed' : 'incorrect number of results, expecting 1, got '.count($arrResult));

		$resRecord->delete();
		unset($resRecord);

		$arrResult = \Twist::Database()->getAll('twist_settings','SITE_NAME_TEST','key');
		$this -> assertEquals('delete passed',(count($arrResult) == 0) ? 'delete passed' : 'incorrect number of results, expecting 0, got '.count($arrResult));
		*/

		//Reset the site name as settings uses it for a test also
		$resRecord = \Twist::Database()->getRecord('twist_settings','SITE_NAME','key');
		$resRecord->set('value','Travis CI Test');
		$resRecord->commit();
	}

	public function testCreateDelete(){

		$resNewRecord = \Twist::Database()->records('user_levels')->create();
		$resNewRecord->set('slug','test');
		$resNewRecord->set('description','test level');
		$resNewRecord->set('level',1000);

		$intLevelID = $resNewRecord->commit();

		$arrResult1 = \Twist::Database()->get('user_levels',$intLevelID,'id',true);
		$this->assertEquals('test',$arrResult1['slug']);

		$this -> assertTrue(\Twist::Database()->records('user_levels')->delete($intLevelID,'id'));

		$arrResult2 = \Twist::Database()->get('user_levels',$intLevelID,'id',true);
		$this->assertEquals(0,count($arrResult2));
	}

	public function testFindCount(){

		$intResult = \Twist::Database()->records('twist_settings')->count('SITE_%','key');

		$arrResult = \Twist::Database()->records('twist_settings')->find('SITE_%','key');

		$this->assertEquals($intResult,count($arrResult));
	}
}