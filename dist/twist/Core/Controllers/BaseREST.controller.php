<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html GPL License
 * @link       https://twistphp.com
 */

namespace Twist\Core\Controllers;
use Twist\Classes\Error;

/*
 * An REST API base controller (basic API Key auth) that can be used instead of Base when adding REST API support to your site. This controller should be used as an extension to a route controller class.
 *
 * Twist Base REST Controller
 * ==========================
 *
 * When creating the REST controllers you have the choice of extending one of two base controllers BaseREST or BaseRESTUser.
 *
 *  BaseREST - Authentication is achieved using the auth_key (API Key) and IP restrictions can be applied
 *  BaseRESTUser - Same as above with the extra security of requiring a valid user login using Twist Users++
 *
 *  ++Once logged in the use of an auth_token will allow you to continue to use the API until it expires or canceled
 *
 * API Keys and IP restrictions can be setup in the apikeys database table
 *
 * By default all authentication is done in the request headers, a setting in framework settings 'API_REQUEST_HEADER_AUTH' can be disabled,
 * this will allow the authentication to be done via GET/POST parameters
 *
 * The API can be locked down to only accept certain request methods, by default the API will only accept GET,POST requests. To allow
 * other types of requests to reach the controller i.e GET,POST,PUT,DELETE,HEAD,OPTIONS,CONNECT you can edit the following framework
 * setting 'API_ALLOWED_REQUEST_METHODS'
 *
 * The output format can be either JSON or XML, by default it will be JSON. by passing in the GET/POST parameter of ?format=xml you get
 * and XML response back from the system.
 *
 * By default a HTTP response code of 200 is good and anything else is an error. As these can be changed by the developer you can refer
 * to the 'status' field in the response which will either be 'success' or 'error'. An error response will have a field of 'message'
 * which will contain the error message.
 *
 * In the response 'count' should be an indication of how may results have been found, plans to add in pagination and offset
 * later will make this feature become more useful. 'results' will be the data that is returned.
 *
 *
 *
 * ===== Creating a REST Controller =====
 *
 * class MyAPI extends BaseREST{}
 *
 * -OR-
 *
 * class MyAPI extends BaseRESTUser{}
 *
 *
 *
 * ===== Returning data from a REST function =====
 *
 * return $this->_response($arrResults,2,200);      //Params: Result Data, Result Count, HTTP Response Code**
 *
 * -OR-
 *
 * return $this->_responseError('This is my error message',404);    //Params: Error Message, HTTP Response Code++
 *
 *
 *
 * ++The HTTP response code is reset but can be passed as a param if you want to customise them
 *
 * @package Twist\Core\Controllers
 */
class BaseREST extends Base{

	protected static $blRequestHeaderAuth = true;
	protected static $srtFormat = 'json';
	protected static $srtApiKey = '';
	protected static $arrKeyInfo = array();

	/**
	 * Default functionality for every request that is made though the REST API. Every call must be authenticated.
	 */
    public function _baseCalls(){

	    header("Access-Control-Allow-Orgin: *");
	    header("Access-Control-Allow-Methods: *");

        $this->_timeout(60);
        $this->_ignoreUserAbort(true);

	    //Determine the format in which to return the data, default is JSON
	    self::$srtFormat = (array_key_exists('format',$_REQUEST)) ?  $_REQUEST['format'] : strtolower(self::$srtFormat);

	    $this->_auth();
    }

	/**
	 * Standard function to validate the Auth Key and connection IP address to ensure that access has been granted
	 */
    public function _auth(){

        //Basic Auth is an API key, BaseRESTUser has a more advance auth
	    self::$blRequestHeaderAuth = \Twist::framework()->setting('API_REQUEST_HEADER_AUTH');

	    self::$srtApiKey = (self::$blRequestHeaderAuth) ? $_SERVER['HTTP_AUTH_KEY'] : $_REQUEST['auth_key'];
	    self::$arrKeyInfo = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'apikeys')->get(self::$srtApiKey,'key',true);

	    if(count(self::$arrKeyInfo) == 0){
		    //Invalid API Key
		    return $this->_respondError('Unauthorized Access: Invalid API key passed',401);

	    }elseif(self::$arrKeyInfo['enabled'] == '0'){
		    //Disabled API key
		    return $this->_respondError('Disabled: API key has been disabled',401);

	    }elseif(self::$arrKeyInfo['allowed_ips'] != '' && !in_array($_SERVER['REMOTE_ADDR'],explode(',',self::$arrKeyInfo['allowed_ips']))){
		    //Invalid IP address
		    return $this->_respondError('Forbidden: you IP address is not on the allowed list',403);
	    }

	    return true;
    }

	/**
	 * The main response of the controller, treat this function as though it where an index.php file.
	 */
    public function _index(){
	    return $this->_respond('Welcome: API connection successful',1);
    }

	/**
	 * This is the function that will be called in the even that Routes was unable to find a exact controller response.
	 */
	public function _fallback(){
		return $this->_respondError('Invalid function called',404);
	}

	/**
	 * Successful response to an API call should be used to return a standardised RESTful success response
	 * @param mixed $mxdResults Results of the function call to be returned to the user
	 * @param int $intCount Number of results returned by the function call
	 * @param int $intResponseCode HTTP response code for the call
	 */
    public function _respond($mxdResults,$intCount = 1,$intResponseCode = 200){

	    header(sprintf("HTTP/1.1 %s %s",$intResponseCode,Error::responseInfo($intResponseCode)));
	    header("Cache-Control: no-cache, must-revalidate");
	    header("Expires: Wed, 24 Sep 1986 14:20:00 GMT");

	    $strOutput = '';
	    $arrOut = array(
	    	'status' => 'success',
	    	'format' => self::$srtFormat,
	    	'count' => $intCount,
	    	'results' => $mxdResults
	    );

	    if(self::$srtFormat == 'json'){
		    header("Content-type: application/json");
		    $strOutput = json_encode($arrOut);
	    }elseif(self::$srtFormat == 'xml'){
		    header("Content-type: text/xml");
		    $strOutput = \Twist::XML()->arrayToXML($arrOut);
	    }

	    header(sprintf("Content-length: %d", function_exists('mb_strlen') ? mb_strlen($strOutput) : strlen($strOutput)));

	    echo $strOutput;
	    die();
    }

	/**
	 * Error response to an API call should be used to return a standardised RESTful error response
	 * @param string $strErrorMessage Error message to indicate what when wrong
	 * @param int $intResponseCode HTTP response code for the call
	 */
	public function _respondError($strErrorMessage,$intResponseCode = 404){

		header(sprintf("HTTP/1.1 %s %s",$intResponseCode,Error::responseInfo($intResponseCode)));
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Wed, 24 Sep 1986 14:20:00 GMT");

		$strOutput = '';
		$arrOut = array(
			'status' => 'error',
			'error' => $strErrorMessage,
			'count' => 0,
			'format' => self::$srtFormat,
		);

		if(self::$srtFormat == 'json'){
			header("Content-type: application/json");
			$strOutput = json_encode($arrOut);
		}elseif(self::$srtFormat == 'xml'){
			header("Content-type: text/xml");
			$strOutput = \Twist::XML()->arrayToXML($arrOut);
		}

		header(sprintf("Content-length: %d", function_exists('mb_strlen') ? mb_strlen($strOutput) : strlen($strOutput)));

		echo $strOutput;
		die();
	}

}