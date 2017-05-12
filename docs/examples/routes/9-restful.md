# RESTful Routing

You can use routes to create a REST API that can have a multitude of uses, for example the TwistPHP Ajax server, Inline file uploader and of course a REST API.

To use RESTful routing you will need a Controller that extends *BaseREST* or *BaseRESTUser*.

## BaseREST (Base Controller with API Key)
BaseREST requires an API Key to make valid requests to the controller functions, the API Keys are stored in the apikeys database table. You can add/edit and manage the API keys though the framework manager. IP address restrictions can also be setup along with the API key.

Create a new file called `BasicAPI.controller.php` in your `/app/Controllers` directory:

```php
<?php

    /*
     * ================================
     * The PSR namespace for your app's
     * controllers
     * ================================
     */
    namespace App\Controllers;
    
    /*
     * ================================
     * The TwistPHP base controller
     * ================================
     */
    use Twist\Core\Controllers\BaseREST;
    
    /*
     * ================================
     * This new controller class should
     * be named exactly the same as the
     * filename and extend the TwistPHP
     * base controller
     * ================================
     */
    class BasicAPI extends BaseREST {
    
        /*
         * ================================
         * Add any other methods in here to
         * return data for that URI
         * ================================
         */
        public function doSomthing(){
            return $this->_response(array(1 => 'test'),1);
        }
    }
```

** Note: If your controller implements the `_baseCalls()` function ensure you add `return parent::_baseCalls()` at the end of the function. Not doing so will prevent the API Key, IP Restriction and User validation from working.

Register your REST controller by adding the following lines to your main `index.php` file in your site root:

```php
<?php

    /*
     * ================================
     * Require the TwistPHP framework
     * ================================
     */
    require_once( 'twist/framework.php' );
    
    /*
     * ================================
     * Register the 'BasicAPI' controller
     * for all requests that start with
     * the URI '/' (which should be the
     * base for the site)
     * ================================
     */
    Twist::Route() -> rest( '/api/basic/%', 'BasicAPI' );
    
    /*
     * ================================
     * Respond to all requests with the
     * relevant registered routes
     * ================================
     */
	Twist::Route() -> serve();
```

An example of calling the REST controller using the TwistPHP Curl package, you do not need to use Twist or even CURL for that matter as long as you can make a HTTP request to the REST controller you can use the API. You will need to pass in the `Auth-Key` as a request header (or via GET/POST if you have disabled `API_REQUEST_HEADER_AUTH`).

```php
<?php

    /*
     * ================================
     * Require the TwistPHP framework
     * ================================
     */
    require_once( 'twist/framework.php' );
    
    $arrRequestHeaders = array(
        'Auth-Key' => 'XXXXXXXXXXXXXXXX'
    );

    $jsonData = \Twist::Curl()->get('https://www.example.com/api/basic/doSomthing',array(),$arrRequestHeaders);
    $arrOut = json_decode($jsonData,true);

    print_r($arrOut);
    /**
     * 'status' => 'success',
     * 'format' => 'json',
     * 'count' => 1,
     * 'results' => array(1 => 'test')
     */
    
```

## BaseRESTUser (Base Controller with API Key and User Login)
BaseRESTUser extends the BaseREST controller which means all the above functionality applies with the addition of a user login being required to access the REST controller. Once successfully logged in a token will be returned that will allow all subsequent requests to be make without sending the login credentials.

Using the same code example as above to create and add your controller to twist apart from you will need to use and extend from `BaseRESTUser` rather than `BaseREST`

```php
use Twist\Core\Controllers\BaseRESTUser;
```

```php
class RestrictedAPI extends BaseRESTUser {
```

```php
Twist::Route() -> rest( '/api/restricted/%', 'RestrictedAPI' );
```

When connecting to your API you will now need to pass in two new header parameters (or via GET/POST if you have disabled `API_REQUEST_HEADER_AUTH`). These parameters are `Auth-Email` and `Auth-Password` which upon successful authentication will return you with a auth token.

```php
<?php

    $arrRequestHeaders = array(
		'Auth-Key' => 'XXXXXXXXXXXXXXXX',
		'Auth-Email' => 'xxxxxxxx@xxxxxx.xxx',
		'Auth-Password' => 'xxxxxxxxxx'
	);

	$jsonData = \Twist::Curl()->get('https://www.example.com/api/restricted/connect',array(),$arrRequestHeaders);
	$arrOut = json_decode($jsonData,true);

	if($arrOut['status'] == 'success'){
		$strAutToken = $arrOut['results']['auth_token'];
	}else{
		echo $arrOut['error'];
	}

	print_r($arrOut);
	/**
	 * 'status' => 'success',
	 * 'format' => 'json',
	 * 'count' => 1,
	 * 'results' => array(
	 *                  'message' => 'Authenticated: Successfully logged in as xxxxxxxx@xxxxxx.xxx',
	 *				    'auth_token' => 'xxXXxxxXXxxxxXXxXxXXXXXXXXxxxXXXXXX'
	 *              )
	 */
```

Now you can start using the API with your auth token, you will need to pass the auth token as a header parameter `Auth-Token` (or via GET/POST if you have disabled `API_REQUEST_HEADER_AUTH`).

```php
<?php

    $arrRequestHeaders = array(
		'Auth-Key' => 'XXXXXXXXXXXXXXXX',
		'Auth-Token' => 'xxXXxxxXXxxxxXXxXxXXXXXXXXxxxXXXXXX',
	);
	
    $jsonData = \Twist::Curl()->get('https://www.example.com/api/restricted/test',array(),$arrRequestHeaders);
	$arrOut = json_decode($jsonData,true);

	print_r($arrOut);
	/**
	 * 'status' => 'success',
	 * 'format' => 'json',
	 * 'count' => 1,
	 * 'results' => array(1 => 'test')
	 */
```

To determine if your auth token is still valid you can call the `authenticated` function that comes as part of the `BaseRESTUser` base controller.

```php
<?php

    $arrRequestHeaders = array(
   		'Auth-Key' => 'XXXXXXXXXXXXXXXX',
   		'Auth-Token' => 'xxXXxxxXXxxxxXXxXxXXXXXXXXxxxXXXXXX',
   	);

	$jsonData = \Twist::Curl()->get('http://twocoastmedia.com/dev/api/user/authenticated',array(),$arrRequestHeaders);
	$arrOut = json_decode($jsonData,true);

	print_r($arrOut);
	/**
	 * 'status' => 'success',
	 * 'format' => 'json',
	 * 'count' => 1,
	 * 'results' => 'Welcome: API connection successful, you are authenticated'
	 */
```

## Controller Responses

When creating your REST controller you have two choices for response, success or error which can be triggerd by calling the `_response` and `_responseError` functions. The output format can be either JSON or XML by passing in a GET/POST parameter of `format=xml` you will get an XML response, by default JSON is returned.

A successful response can be triggered as per below, the first param is the data to be returned, the second parameter indicates the number of results being returned and the third parameter is the HTTP response code that will be used. By default the response code is set to 200.
```php
return $this->_response($arrResults,1,200);
```

An error response can be triggers as follows, the first parameters is the error message to return and the second is the HTTP response code that will be used.
```php
return $this->_responseError('This is my error message',404);
```

In the response `count` should be an indication of how may results have been returned (Default is 1) and `results` contains the data that is being returned.

By default a HTTP response code of 200 is good and anything else is an error. As these can be changed by the developer you can refer to the `status` field in the response which will either be `success` or `error`. An error response will have a field of `message` which will contain the error message.

## RESTful Framework Settings

By default the RESTful routing requires you to send the `Auth-Key`, `Auth-Email`, `Auth-Password` and `Auth-Token` as part of the request headers, this can be disabled in the framework settings `API_REQUEST_HEADER_AUTH` which would allow you to send them via a GET/POST parameters.

The API can be locked down to only accept certain request methods, by default the API will only accept GET,POST requests. To allow other types of requests to reach the controller i.e GET,POST,PUT,DELETE,HEAD,OPTIONS,CONNECT you can edit the following framework setting 'API_ALLOWED_REQUEST_METHODS'.
