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
 * See twistphp.com/examples for a full guide on RESTful routing
 *
 * @package Twist\Core\Controllers
 */
class BaseRESTKey extends BaseREST{

	protected static $blRequestHeaderAuth = true;
	protected static $srtApiKey = '';
	protected static $arrKeyInfo = array();

	/**
	 * Default functionality for every request that is made though the REST API. Every call must be authenticated.
	 */
    public function _baseCalls(){

        $mxdResponse = parent::_baseCalls();

	    return ($mxdResponse === true || is_null($mxdResponse)) ? $this->_auth() : $mxdResponse;
    }

	/**
	 * Standard function to validate the Auth Key and connection IP address to ensure that access has been granted
	 */
    public function _auth(){

        $mxdResponse = parent::_auth();

        if($mxdResponse === true || is_null($mxdResponse)){

            //Basic Auth is an API key, BaseRESTUser has a more advance auth
            self::$blRequestHeaderAuth = \Twist::framework()->setting('API_REQUEST_HEADER_AUTH');

            self::$srtApiKey = (self::$blRequestHeaderAuth) ? $_SERVER['HTTP_AUTH_KEY'] : $_REQUEST[(array_key_exists('Auth-Key',$_REQUEST)) ? 'Auth-Key' : 'auth-key'];
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

	    return $mxdResponse;
    }
}