<?php

class CSV extends \PHPUnit_Framework_TestCase{

	public function testGenerate(){

		$arrData = array(
			0 => array('id' => 1,'name' => 'Dan','description' => 'test desc 1'),
			1 => array('id' => 2,'name' => 'Andi','description' => 'test desc 2')
		);

		$strCSV = \Twist::CSV()->export(TWIST_UPLOADS.'test.csv',$arrData);

		$this->assertTrue(strstr($strCSV,',') && strstr($strCSV,"\n") && strstr($strCSV,'id'));
	}

	public function testImport(){

		$arrData = \Twist::CSV()->import(TWIST_UPLOADS.'test.csv',"\n",",",'""',"\\",true);

		$this->assertTrue(count($arrData) == 2 && $arrData[0]['id'] == 1 && $arrData[1]['name'] == 'Andi');
	}

}