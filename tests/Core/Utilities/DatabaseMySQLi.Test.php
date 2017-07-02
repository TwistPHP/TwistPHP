<?php

class DatabaseMySQLi extends \PHPUnit_Framework_TestCase{

	public function testQuery(){

		$resResult = \Twist::Database()->query("SELECT * FROM `twist_settings` LIMIT 1");

		$this->assertTrue($resResult->status());
		$this->assertEquals(1,$resResult->numberRows());

		$resResult = \Twist::Database()->query("SELECT * FROM `twist_settings` LIMIT 3");

		$this->assertTrue($resResult->status());
		$this->assertEquals(3,$resResult->numberRows());

		$this->assertTrue(array_key_exists('key',$resResult->row()));
		$this->assertEquals(3,count($resResult->rows()));

		$arrResults = $resResult->rows();
		$arrResult = $resResult->row(2);

		$this->assertEquals($arrResults[2]['key'],$arrResult['key']);

	}

	public function testQueryFail(){

		$resResult = \Twist::Database()->query("SELECT * FROM `--table-failed--` LIMIT 1");

		$this->assertFalse($resResult->status());
	}

	public function testGet(){

		$arrResult = \Twist::Database()->records('twist_settings')->get('SITE_NAME','key',true);

		$this->assertTrue(is_array($arrResult));
		$this->assertEquals('Travis CI Test',$arrResult['value']);
	}

	public function testGetModify(){

		$resRecord = \Twist::Database()->records('twist_settings')->get('SITE_NAME','key');
		$resRecord->set('value','Travis CI Test - Updated');
		$resRecord->commit();

		$this->assertEquals('Travis CI Test - Updated',$resRecord->get('value'));
		unset($resRecord);

		$resRecord = \Twist::Database()->records('twist_settings')->get('SITE_NAME','key');
		$this->assertEquals('Travis CI Test - Updated',$resRecord->get('value'));
		unset($resRecord);

		//Reset the site name as settings uses it for a test also
		$resRecord = \Twist::Database()->records('twist_settings')->get('SITE_NAME','key');
		$resRecord->set('value','Travis CI Test');
		$resRecord->commit();
	}

	public function testCopy(){

		$resRecord = \Twist::Database()->records('twist_settings')->copy('SITE_NAME','key');
		$resRecord->set('key','SITE_NAME_TEST');
		$resRecord->set('value','copy-test');
		$resRecord->commit();
		unset($resRecord);

		$arrResult = \Twist::Database()->records('twist_settings')->get('SITE_NAME_TEST','key',true);
		$this -> assertEquals('copy-test',$arrResult['value']);

		$resRecord = \Twist::Database()->records('twist_settings')->get('SITE_NAME_TEST','key');
		$this->assertTrue($resRecord->delete());
	}

	public function testCreateDelete(){

		$resNewRecord = \Twist::Database()->records('twist_user_levels')->create();
		$resNewRecord->set('slug','test');
		$resNewRecord->set('description','test level');
		$resNewRecord->set('level',1000);

		$intLevelID = $resNewRecord->commit();

		$arrResult1 = \Twist::Database()->records('twist_user_levels')->get($intLevelID,'id',true);
		$this->assertEquals('test',$arrResult1['slug']);

		$this->assertTrue(\Twist::Database()->records('twist_user_levels')->delete($intLevelID,'id'));

		$arrResult2 = \Twist::Database()->records('twist_user_levels')->get($intLevelID,'id',true);
		$this->assertEquals(0,count($arrResult2));
	}

	public function testImport(){

		$blResult = \Twist::Database()->importSQL(TWIST_APP.'/Data/import.sql',TWIST_DATABASE_NAME);
		$this->assertTrue($blResult);

		$resRecord = \Twist::Database()->records('twist_settings')->get('SITE_AUTHOR','key',true);
		$this->assertEquals('import-test',$resRecord['value']);
	}

	public function testSetDatabase(){

		$blResult = \Twist::Database()->setDatabase('something-blah');
		$this->assertTrue(!$blResult);
	}

	public function testFindCount(){

		$intResult = \Twist::Database()->records('twist_settings')->count('SITE_%','key');

		$arrResult = \Twist::Database()->records('twist_settings')->find('SITE_%','key');

		$this->assertEquals($intResult,count($arrResult));
	}

	public function testIncrementDecrement(){

		$resNewRecord = \Twist::Database()->records('twist_settings')->get('EMAIL_SMTP_PORT','key');
		$intOldValue = $resNewRecord->get('default');

		$intNewValue = $resNewRecord->increment('default');
		$this->assertEquals($intOldValue+1,$intNewValue);

		$intNewValue = $resNewRecord->decrement('default');
		$this->assertEquals($intOldValue,$intNewValue);
	}

	public function testCreateAlterTable(){

		$resNewTable = \Twist::Database()->table('test_table')->create();
		$resNewTable->addColumn('id','int',11);
		$resNewTable->addColumn('name','char',30);
		$resNewTable->autoIncrement('id');
		$this->assertTrue($resNewTable->commit());

		$this->assertTrue(\Twist::Database()->table('test_table')->exists());

		$resNewRecord = \Twist::Database()->records('test_table')->create();
		$resNewRecord->set('name','test');
		$this->assertEquals(1,$resNewRecord->commit());

		$this->assertEquals(1,\Twist::Database()->records('test_table')->count());

		$arrResult = \Twist::Database()->records('test_table')->get(1,'id',true);
		$this->assertEquals('test',$arrResult['name']);

		//Alter the table
		$resExistingTable = \Twist::Database()->table('test_table')->get();
		$resExistingTable->addColumn('slug','char',20);
		$resExistingTable->addColumn('description','text',null,null,false,'My Description Test');
		$resExistingTable->addUniqueKey('slug','slug');
		$resExistingTable->commit();

		//Check for the new field in the result set
		$arrResult = \Twist::Database()->records('test_table')->get(1,'id',true);
		$this->assertTrue(array_key_exists('slug',$arrResult));
		$this->assertTrue(array_key_exists('description',$arrResult));

		//Add data to the table and fill the new fields
		$resNewRecord = \Twist::Database()->records('test_table')->create();
		$resNewRecord->set('name','test2');
		$resNewRecord->set('description','this is the second');
		$resNewRecord->set('slug','test2');
		$this->assertEquals(2,$resNewRecord->commit());

		//Check the new count
		$this->assertEquals(2,\Twist::Database()->records('test_table')->count());

		//Drop the slug field
		$resExistingTable = \Twist::Database()->table('test_table')->get();
		$resExistingTable->dropColumn('slug');
		$resExistingTable->commit();

		//Check that the data is still in the table and the slug field has gone
		$arrResult = \Twist::Database()->records('test_table')->get(2,'id',true);
		$this->assertEquals('test2',$arrResult['name']);
		$this->assertFalse(array_key_exists('slug',$arrResult));

		$this->assertTrue(\Twist::Database()->table('test_table')->truncate());

		$this->assertEquals(0,\Twist::Database()->records('test_table')->count());

		$this->assertTrue(\Twist::Database()->table('test_table')->drop());

		$this->assertFalse(\Twist::Database()->table('test_table')->exists());
	}
}