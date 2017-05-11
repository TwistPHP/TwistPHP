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
use Twist\Core\Models\User\Auth;

/*
 * An REST API base controller (API Key auth as well as User Authentication) that can be used instead of Base when adding REST API support to your site. This controller should be used as an extension to a route controller class.
 *
 * Twist Base RESTUser Controller
 * ==============================
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
class BaseRESTUser extends BaseREST{

	protected static $srtSessionToken = '';
	protected static $arrSessionData = array();

	/**
	 * An extension of BaseRest->_auth to determine if the request is a logged in user, if so validate the auth token otherwise try to log the user in.
	 * A valid auth key and IP address is still required here, Failure to get a valid user session will terminate the request here, the controller function will not be run
	 * @return bool|void
	 */
    public function _auth(){

	    //Call the default API key and IP restrictions before validating the users session
	    parent::_auth();

	    //Basic Auth is an API key, BaseRESTUser has a more advance auth
	    self::$srtSessionToken = (self::$blRequestHeaderAuth) ? $_SERVER['HTTP_AUTH_TOKEN'] : $_REQUEST['auth_token'];

	    //If a session key is set try to validate the session
	    if(self::$srtSessionToken != ''){

		    \Twist::Session()->data('user-session_key',self::$srtSessionToken);

		    self::$arrSessionData = Auth::current(false);
		    if(count(self::$arrSessionData) && self::$arrSessionData['status'] == true){

			    //Valid user has been detected, allow the controller to continue
			    return true;
		    }

		    //Error user is not logged in
		    return $this->_respondError('Unauthorized Access: Invalid auth token provided',401);
	    }

	    //If no session key has been passed in then try to authenticate the user
	    //Every possible outcome of this function call will end the script here
	    return $this->_authenticate();
    }

	/**
	 * Authenticate the users login credentials and return an auth token to be used when accessing any of the API functions
	 */
    public function _authenticate(){

	    //Create a valid session that can be used for all connections
	    $srtApiEmail = (self::$blRequestHeaderAuth) ? $_SERVER['HTTP_AUTH_EMAIL'] : $_REQUEST['auth_email'];
	    $srtApiPassword = (self::$blRequestHeaderAuth) ? $_SERVER['HTTP_AUTH_PASSWORD'] : $_REQUEST['auth_password'];

	    //Advanced AUTH using framework user authentication
	    $arrResult = Auth::login($srtApiEmail,$srtApiPassword);

	    if($arrResult['issue'] != ''){

		    return $this->_respondError('Authentication Failed: '.$arrResult['message'],401);

	    }elseif($arrResult['status']){

		    $arrResponseData = array(
			    'message' => 'Authenticated: Successfully logged in as '.$srtApiEmail,
			    'auth_token' => $arrResult['session_key']
		    );

		    return $this->_respond($arrResponseData,1);
	    }

	    return $this->_respondError('Authentication Failed: Invalid login credentials, please try again',401);
    }

	/**
	 * Determine if your auth token is valid, simple function that will not do anything other then report connection status
	 */
    public function authenticated(){
	    return $this->_respond('Welcome: API connection successful, you are authenticated',1);
    }
}