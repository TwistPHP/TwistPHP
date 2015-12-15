<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Database extends \PHPUnit_Framework_TestCase{

	public function testQuery(){

		$blQuery = \Twist::Database()->query("SELECT * FROM `twist_settings` LIMIT 1");

		$this -> assertEquals(true,$blQuery);
		$this -> assertEquals(1,\Twist::Database()->getNumberRows());
	}

	public function testGet(){

		$arrResult = \Twist::Database()->get('twist_settings','SITE_NAME','key');

		$this -> assertEquals('Travis CI Test',$arrResult['value']);
	}

	public function testRecordObject(){

		$resRecord = \Twist::Database()->getRecord('twist_settings','SITE_NAME','key');
		$resRecord->set('value','Travis CI Test - Updated');
		$resRecord->commit();

		$this->assertEquals('Travis CI Test - Updated',$resRecord->get('value'));
		unset($resRecord);

		$resRecord = \Twist::Database()->getRecord('twist_settings','SITE_NAME','key');
		$this->assertEquals('Travis CI Test - Updated',$resRecord->get('value'));
		unset($resRecord);

		$resRecord = \Twist::Database()->cloneRecord('twist_settings','SITE_NAME','key');
		$resRecord->set('key','SITE_NAME_TEST');
		$resRecord->commit();

		$arrResult = \Twist::Database()->getAll('twist_settings','SITE_NAME_TEST','key');
		$this -> assertEquals('clone passed',(count($arrResult) == 1) ? 'clone passed' : 'incorrect number of results, expecting 1, got '.count($arrResult));

		$resRecord->delete();
		unset($resRecord);

		$arrResult = \Twist::Database()->getAll('twist_settings','SITE_NAME_TEST','key');
		$this -> assertEquals('delete passed',(count($arrResult) == 0) ? 'delete passed' : 'incorrect number of results, expecting 0, got '.count($arrResult));

		//Reset the site name as settings uses it for a test also
		$resRecord = \Twist::Database()->getRecord('twist_settings','SITE_NAME','key');
		$resRecord->set('value','Travis CI Test');
		$resRecord->commit();
	}
}