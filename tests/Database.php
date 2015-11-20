<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Database extends \PHPUnit_Framework_TestCase{

	public function testQuery(){

		$blQuery = \Twist::Database()->query("SELECT * FROM `twist_settings` LIMIT 1");

		$this -> assertEquals(true,$blQuery);
		$this -> assertEquals(1,\Twist::Database()->getNumberRows());
	}
}