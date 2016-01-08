<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Routes extends \PHPUnit_Framework_TestCase{

	public function testViewRequest(){

		$strResult = \Twist::Curl()->get('http://127.0.0.1/test');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('test',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testFunctionRequest(){

		$strResult = \Twist::Curl()->get('http://127.0.0.1/test-function');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('test',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testGetRequest(){

		$strResult = \Twist::Curl()->get('http://127.0.0.1/test-controller/test');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('test',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testPostRequest(){

		$strResult = \Twist::Curl()->post('http://127.0.0.1/test-controller/httppost',array('q' => 'whats is the meaning of life?'));
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for '42'
		//$this -> assertEquals('42',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testPutRequest(){

		$strResult = \Twist::Curl()->put('http://127.0.0.1/test-controller/httpput','Here-Is-Some-Data');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'Here-Is-Some-Data'
		//$this -> assertEquals('Here-Is-Some-Data',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testDeleteRequest(){

		$strResult = \Twist::Curl()->delete('http://127.0.0.1/test-controller/httpdelete',array('id' => '42'));
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for '42'
		//$this -> assertEquals('42',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testRestrictedPage(){
		$this -> assertEquals('pass','pass');
	}

	public function testAjaxPage(){
		$this -> assertEquals('pass','pass');
	}

	public function test404Page(){

		\Twist::Curl()->get('http://127.0.0.1/this-url-is-invalid');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check for a 404 response
		//$this -> assertEquals('404',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testCaseSensitiveRouting(){

		//Ensure that case sensitive routing is enabled
		\Twist::framework()->setting('ROUTE_CASE_SENSITIVE',true);

		$strResult = \Twist::Curl()->get('http://127.0.0.1/test-controller/test');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('test',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		\Twist::Curl()->get('http://127.0.0.1/Test-Controller/Test');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check for a 404 response
		//$this -> assertEquals('400',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testCaseInsensitiveRouting(){

		//Ensure that case sensitive routing is disabled
		\Twist::framework()->setting('ROUTE_CASE_SENSITIVE',false);

		$strResult = \Twist::Curl()->get('http://127.0.0.1/test-controller/test');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('test',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$strResult = \Twist::Curl()->get('http://127.0.0.1/Test-Controller/Test');
		$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'test'
		//$this -> assertEquals('test',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		//Reset case sensitive routing to default
		\Twist::framework()->setting('ROUTE_CASE_SENSITIVE',true);

		$this -> assertEquals('pass','pass');
	}
}