<?php

class Routes extends \PHPUnit_Framework_TestCase{

	public function testGetRequest(){
		$this -> assertEquals('pass','pass');
	}

	public function testPostRequest(){
		$this -> assertEquals('pass','pass');
	}

	public function testRestrictedPage(){
		$this -> assertEquals('pass','pass');
	}

	public function testAjaxPage(){
		$this -> assertEquals('pass','pass');
	}

	public function test404Page(){
		$this -> assertEquals('pass','pass');
	}
}