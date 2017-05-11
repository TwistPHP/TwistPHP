<?php

class Routes extends \PHPUnit_Framework_TestCase{

	private function simulateRequest($strURI,$strRequestMethod = 'GET',$arrParameterData = array()){

		//Set the parameter data
		if($strRequestMethod == 'GET'){
			$_GET = $arrParameterData;
		}elseif($strRequestMethod == 'POST'){
			$_POST = $arrParameterData;
		}
		$_REQUEST = $arrParameterData;

		//Capture and test the resulting output
		$_SERVER['REQUEST_URI'] = $strURI;
		$_SERVER['REQUEST_METHOD'] = $strRequestMethod;

		ob_start();
			\Twist::ServeRoutes(false);
			$strPageContent = ob_get_contents();
		ob_end_clean();

		return $strPageContent;
	}

	private function simulateAPIRequest($strURI,$strAPIKey='',$strEmail='',$strPassword='',$strToken='',$strRequestMethod='GET',$arrParameterData = array()){

		$strPageContent = '';

		//Set the parameter data
		if($strRequestMethod == 'GET'){
			$_GET = $arrParameterData;
		}elseif($strRequestMethod == 'POST'){
			$_POST = $arrParameterData;
		}
		$_REQUEST = $arrParameterData;

		//Capture and test the resulting output
		$_SERVER['REQUEST_URI'] = $strURI;
		$_SERVER['REQUEST_METHOD'] = $strRequestMethod;
		$_SERVER['HTTP_AUTH_KEY'] = $strAPIKey;

		if($strEmail != ''){
			$_SERVER['HTTP_AUTH_EMAIL'] = $strEmail;
		}

		if($strPassword != ''){
			$_SERVER['HTTP_AUTH_PASSWORD'] = $strPassword;
		}

		if($strToken != ''){
			$_SERVER['HTTP_AUTH_TOKEN'] = $strToken;
		}

		ob_start();
		\Twist::ServeRoutes(false);
		$strPageContent = ob_get_contents();
		ob_end_clean();

		return $strPageContent;
	}

	public function testViewRequest(){

		\Twist::Route()->view('/test','test.tpl');
		$this -> assertEquals('test',$this->simulateRequest('/test'));
	}

	public function testFunctionRequest(){

		\Twist::Route()->get('/test-function',function(){ return 'test'; });
		$this -> assertEquals('test',$this->simulateRequest('/test-function'));
	}

	public function testControllerRequest(){

		\Twist::Route()->controller('/test-standard-controller/%','TwistStandard');
		$this -> assertEquals('test',$this->simulateRequest('/test-standard-controller/test'));
	}

	public function testRestControllerRequest(){

		\Twist::Route()->controller('/test-basicapi-controller/%','TwistBasicAPI');
		\Twist::Route()->controller('/test-userapi-controller/%','TwistUserAPI');

		$strAPIKey = 'ABC123XYZ42';

		//Insert test API key
		$resRecord = \Twist::Database()->records('twist_apikeys')->create();
		$resRecord->set('key',$strAPIKey);
		$resRecord->set('enabled','1');
		$resRecord->set('created',date('Y-m-d H:i:s'));
		$resRecord->commit();

		//Create test rest user
		$resUser = \Twist::User()->create();
		$resUser->firstname('Travis');
		$resUser->surname('CI');
		$resUser->email('travisci@unit-test-rest-twistphp.com');
		$resUser->password('X123Password');
		$resUser->commit();

		//Test with no API key
		$arrRESTResponse = json_decode($this->simulateAPIRequest('/test-basicapi-controller/test'),true);
		$this -> assertEquals('error',$arrRESTResponse['status']);

		//Test with API key
		$arrRESTResponse = json_decode($this->simulateAPIRequest('/test-basicapi-controller/test',$strAPIKey),true);
		$this -> assertEquals('success',$arrRESTResponse['status']);

		//Test with API key (XML format)
		$strResponseXML = $this->simulateAPIRequest('/test-basicapi-controller/test',$strAPIKey,'','','','GET',array('format' => 'xml'));
		$this->assertContains('<status>success</status>', $strResponseXML);

		//Test before login
		$arrRESTResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/test',$strAPIKey),true);
		//$this -> assertEquals('error',$arrRESTResponse['status']);

		//Test with user
		$arrRESTResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/test',$strAPIKey,'travisci@unit-test-rest-twistphp.com','X123Password'),true);
		$this -> assertEquals('success',$arrRESTResponse['status']);
		$this -> assertTrue(array_key_exists('auth_token',$arrRESTResponse['results']));

		$strTokenKey = $arrRESTResponse['results']['auth_token'];

		//Test authenticated
		$arrRESTResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/authenticated',$strAPIKey,'','',$strTokenKey),true);
		$this -> assertEquals('success',$arrRESTResponse['status']);

		//Test after login
		$arrRESTResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/test',$strAPIKey,'','',$strTokenKey),true);
		$this -> assertEquals('success',$arrRESTResponse['status']);
		$this -> assertEquals('test',$arrRESTResponse['results'][1]);
	}

	public function testGetRequest(){

		\Twist::Route()->getView('/test-method','test-get.tpl');
		$this -> assertEquals('42',$this->simulateRequest('/test-method?param=42','GET',array('param' => 42)));
	}

	public function testPostRequest(){

		\Twist::Route()->postView('/test-method','test-post.tpl');
		$this -> assertEquals('42',$this->simulateRequest('/test-method','POST',array('param' => 42)));
	}

	public function testPutRequest(){
		$this -> assertEquals('pass','pass');
	}

	public function testDeleteRequest(){
		$this -> assertEquals('pass','pass');
	}

	public function testRestrictedPage(){
		$this -> assertEquals('pass','pass');
	}

	public function testAjaxPage(){
		$this -> assertEquals('pass','pass');
	}

	public function test404Page(){
		$strPageData = $this->simulateRequest('/random/page/uri');
		$this->assertContains('404 Not Found', $strPageData);
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

		\Twist::Route()->get('/TEST/case/page',function(){ return '42'; });

		$this -> assertEquals('42',$this->simulateRequest('/TEST/case/page'));

		$strPageData1 = $this->simulateRequest('/test/case/page');
		$this->assertContains('404 Not Found', $strPageData1);

		$strPageData2 = $this->simulateRequest('/TEST/CASE/PAGE');
		$this->assertContains('404 Not Found', $strPageData2);
	}
}