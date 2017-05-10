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

/**
 * An REST API base controller that can be used instead of Base when adding REST API support to your site. This controller should be used as an extension to a route controller class.
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
	    self::$srtSessionToken = (self::$blMetaAuth) ? $_SERVER['AUTH_TOKEN'] : $_REQUEST['auth_token'];

	    //If a session key is set try to validate the session
	    if(self::$srtSessionToken != ''){

		    \Twist::Session()->data('user-session_key',self::$srtSessionToken);

		    self::$arrSessionData = Auth::current();
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
	    $srtApiEmail = (self::$blMetaAuth) ? $_SERVER['AUTH_EMAIL'] : $_REQUEST['auth_email'];
	    $srtApiPassword = (self::$blMetaAuth) ? $_SERVER['AUTH_PASSWORD'] : $_REQUEST['auth_password'];

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