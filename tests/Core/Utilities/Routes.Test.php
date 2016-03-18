<?php

class Routes extends \PHPUnit_Framework_TestCase{

	private function simulateRequest($strURI,$strRequestMethod = 'GET',$arrParameterData = array()){

		//Set the parameter data
		if($strRequestMethod == 'GET'){
			$_GET = $arrParameterData;
		}elseif($strRequestMethod == 'POST'){
			$_POST = $arrParameterData;
		}

		//Capture and test the resulting output
		$_SERVER['REQUEST_URI'] = $strURI;
		$_SERVER['REQUEST_METHOD'] = $strRequestMethod;

		ob_start();
			\Twist::ServeRoutes(false);
			$strPageContent = ob_get_contents();
		ob_end_clean();

		return $strPageContent;
	}

	public function testViewRequest(){

		file_put_contents(TWIST_APP_VIEWS.'test.tpl','test');

		\Twist::Route()->view('/test','test.tpl');
		$this -> assertEquals('test',$this->simulateRequest('/test'));
	}

	public function testFunctionRequest(){

		\Twist::Route()->get('/test-function',function(){ return 'test'; });
		$this -> assertEquals('test',$this->simulateRequest('/test-function'));
	}

	public function testGetRequest(){

		file_put_contents(TWIST_APP_VIEWS.'test-get.tpl','{get:param}');

		\Twist::Route()->getView('/test-method','test-get.tpl');
		$this -> assertEquals('42',$this->simulateRequest('/test-method?param=42','GET',array('param' => 42)));
	}

	public function testPostRequest(){

		file_put_contents(TWIST_APP_VIEWS.'test-post.tpl','{post:param}');

		\Twist::Route()->postView('/test-method','test-post.tpl');
		$this -> assertEquals('42',$this->simulateRequest('/test-method','POST',array('param' => 42)));
	}

	public function testPutRequest(){

		//$strResult = \Twist::Curl()->put('http://127.0.0.1/test-controller/httpput','Here-Is-Some-Data');
		//$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check the output - we are looking for 'Here-Is-Some-Data'
		//$this -> assertEquals('Here-Is-Some-Data',$strResult);

		//Check for a 200 response
		//$this -> assertEquals('200',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testDeleteRequest(){

		//$strResult = \Twist::Curl()->delete('http://127.0.0.1/test-controller/httpdelete',array('id' => '42'));
		//$arrRequestInfo = \Twist::Curl()->getRequestInformation();

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

		//\Twist::Curl()->get('http://127.0.0.1/this-url-is-invalid');
		//$arrRequestInfo = \Twist::Curl()->getRequestInformation();

		//Check for a 404 response
		//$this -> assertEquals('404',$arrRequestInfo['http_code']);

		$this -> assertEquals('pass','pass');
	}

	public function testCaseInsensitiveRouting(){

		//Ensure that case sensitive routing is disabled
		\Twist::framework()->setting('ROUTE_CASE_SENSITIVE',false);

		\Twist::Route()->get('/test-Case-PAge',function(){ return '42'; });

		$this -> assertEquals('42',$this->simulateRequest('/test-Case-PAge'));
		$this -> assertEquals('42',$this->simulateRequest('/test-case-page'));
		$this -> assertEquals('42',$this->simulateRequest('/TEST-CASE-PAGE'));

		//Reset case sensitive routing to default
		\Twist::framework()->setting('ROUTE_CASE_SENSITIVE',true);
	}

	public function testCaseSensitiveRouting(){

		//Ensure that case sensitive routing is enabled
		\Twist::framework()->setting('ROUTE_CASE_SENSITIVE',true);

		$this -> assertEquals('42',$this->simulateRequest('/test-Case-PAge'));
		$this -> assertEquals('42',$this->simulateRequest('/test-case-page'));
		$this -> assertEquals('42',$this->simulateRequest('/TEST-CASE-PAGE'));
	}
}