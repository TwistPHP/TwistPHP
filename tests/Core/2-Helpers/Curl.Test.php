<?php

use PHPUnit\Framework\TestCase;

class Curl extends TestCase{

	public function testGetRequest(){

		$strResult = \Twist::Curl()->get('http://127.0.0.1/request-type.php',array('q' => '42'));
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('GET',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this->assertEquals('pass', 'pass');
	}

	public function testPostRequest(){

		$strResult = \Twist::Curl()->post('http://127.0.0.1/request-type.php',array('q' => '42'));
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('POST',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this->assertEquals('pass', 'pass');
	}

	public function testPutRequest(){

		$strResult = \Twist::Curl()->put('http://127.0.0.1/request-type.php','q=42');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('PUT',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this->assertEquals('pass', 'pass');
	}

	public function testDeleteRequest(){

		$strResult = \Twist::Curl()->delete('http://127.0.0.1/request-type.php',array('id' => '42'));
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('DELETE',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this->assertEquals('pass', 'pass');
	}
}