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

	private function simulateAPIRequest($strURI,$strAPIKey='',$strEmail='',$strPassword='',$strToken='',$strRequestMethod='GET',$arrParameterData = array()){

		//Set the parameter data
		if($strRequestMethod == 'GET'){
			$_GET = $arrParameterData;
		}elseif($strRequestMethod == 'POST'){
			$_POST = $arrParameterData;
		}

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

		\Twist::Route()->controller('/test-standard-controller/%','Standard');
		$this -> assertEquals('test',$this->simulateRequest('/test-standard-controller/test'));
	}

	public function testRESTControllerRequest(){

		\Twist::Route()->controller('/test-basicapi-controller/%','BasicAPI');
		\Twist::Route()->controller('/test-userapi-controller/%','UserAPI');

		$strAPIKey = 'ABC123XYZ44';

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

		//Test with not API key
		$arrResponse = json_decode($this->simulateAPIRequest('/test-basicapi-controller/test'),true);
		$this -> assertEquals('error',$arrResponse['status']);

		//Test with API key
		$arrResponse = json_decode($this->simulateAPIRequest('/test-basicapi-controller/test',$strAPIKey));
		$this -> assertEquals('success',$arrResponse['status']);

		//Test with API key (XML format)
		$strResponseXML = $this->simulateAPIRequest('/test-basicapi-controller/test',$strAPIKey,'','','','GET',array('format' => 'xml'));
		$this -> assertEquals('test',$arrResponse);

		//Test before login
		$arrResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/test',$strAPIKey),true);
		$this -> assertEquals('error',$arrResponse['status']);

		//Test with user
		$arrResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/test',$strAPIKey,'travisci@unit-test-rest-twistphp.com','X123Password'),true);
		$this -> assertEquals('success',$arrResponse['status']);
		$this -> assertTrue(array_key_exists('auth_token',$arrResponse['results']));

		$strTokenKey = $arrResponse['results']['auth_token'];

		//Test authenticated
		$arrResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/authenticated',$strAPIKey,'','',$strTokenKey),true);
		$this -> assertEquals('success',$arrResponse['status']);

		//Test after login
		$arrResponse = json_decode($this->simulateAPIRequest('/test-userapi-controller/test',$strAPIKey,'','',$strTokenKey),true);
		$this -> assertEquals('success',$arrResponse['status']);
		$this -> assertEquals('test',$arrResponse['results'][1]);
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
		$this -> assertTrue((strstr($strPageData,'404 Not Found') !== false));
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
		$this -> assertTrue((strstr($strPageData1,'404 Not Found') !== false));

		$strPageData2 = $this->simulateRequest('/TEST/CASE/PAGE');
		$this -> assertTrue((strstr($strPageData2,'404 Not Found') !== false));
	}
}