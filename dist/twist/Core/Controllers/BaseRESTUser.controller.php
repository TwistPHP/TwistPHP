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

	protected static $srtApiSession = '';
	protected static $arrSessionData = array();

    public function _auth(){

	    //Call the default API key and IP restrictions before validating the users session
	    parent::_auth();

	    //Basic Auth is an API key, BaseRESTUser has a more advance auth
	    self::$srtApiSession = (self::$blMetaAuth) ? $_SERVER['TWIST_API_SESSION'] : $_REQUEST['session'];

	    if(self::$srtApiSession != ''){
		    \Twist::Session()->data('user-session_key',self::$srtApiSession);
	    }

	    self::$arrSessionData = Auth::current();

	    if(self::$arrSessionData['status'] == false){
		    //Error user is not logged in
		    return $this->_respondError('Unauthorized Access: Invalid session token',401);
	    }

	    return true;
    }

    public function connect(){

        //Create a valid session that can be used for all connections
	    $srtApiEmail = (self::$blMetaAuth) ? $_SERVER['TWIST_API_EMAIL'] : $_REQUEST['email'];
	    $srtApiPassword = (self::$blMetaAuth) ? $_SERVER['TWIST_API_PASSWORD'] : $_REQUEST['email'];

	    //Advanced AUTH using framework user authentication
	    $arrResult = Auth::login($srtApiEmail,$srtApiPassword);

	    if($arrResult['issue'] != ''){

		    return $this->_respondError('Authentication Failed: '.$arrResult['message'],401);

	    }elseif($arrResult['status']){

		    $arrResponseData = array(
		    	'message' => 'Authenticated: Successfully logged in as '.$srtApiEmail,
			    'session' => $arrResult['status']
		    );

		    return $this->_respond($arrResponseData,1);
	    }else{
		    //Failed to login
		    return $this->_respondError('Authentication Failed: Invalid login credentials, please try again',401);
	    }
    }
}