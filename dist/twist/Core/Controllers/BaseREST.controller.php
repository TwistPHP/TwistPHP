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

/**
 * An REST API base controller that can be used instead of Base when adding REST API support to your site. This controller should be used as an extension to a route controller class.
 * @package Twist\Core\Controllers
 */
class BaseREST extends Base{

	protected static $blMetaAuth = true;
	protected static $srtFormat = 'json';
	protected static $srtApiKey = '';
	protected static $arrKeyInfo = array();

    public function _baseCalls(){

	    header("Access-Control-Allow-Orgin: *");
	    header("Access-Control-Allow-Methods: *");

        $this->_timeout(60);
	    $this->_auth();
    }

	/**
	 * API Key and IP address authentication
	 */
    public function _auth(){

        //Basic Auth is an API key, BaseRESTUser has a more advance auth
	    self::$blMetaAuth = \Twist::framework()->setting('API_META_AUTH');

	    self::$srtApiKey = (self::$blMetaAuth) ? $_SERVER['TWIST_API_KEY'] : $_REQUEST['key'];
	    self::$arrKeyInfo = \Twist::Database()->records('twist_apikeys')->get(self::$srtApiKey,'key',true);

	    if(count(self::$arrKeyInfo) == 0){
		    //Invalid API Key
		    return $this->_respondError('Unauthorized Access: Invalid API key passed',401);
	    }

	    if(!is_null(self::$arrKeyInfo['allowed_ips']) && !in_array($_SERVER['REMOTE_ADDR'],explode(',',self::$arrKeyInfo['allowed_ips']))){
		    //Invalid IP address
		    return $this->_respondError('Forbidden: you IP address is not on the allowed list',403);
	    }

	    return true;
    }

    public function _index(){
	    return $this->_respond('Welcome: API connection successful',1);
    }

	public function _fallback(){
		return $this->_respondError('Invalid function called',404);
	}

    public function _respond($mxdData,$intCount = 1,$intResponseCode = 200){

	    header(sprintf("HTTP/1.1 %s %s",$intResponseCode,$this->responseStatus($intResponseCode)));

	    $strOutput = '';
	    $arrOut = array(
	    	'status' => 'success',
	    	'type' => '',
	    	'format' => self::$srtFormat,
	    	'count' => $intCount,
	    	'results' => $mxdData
	    );

	    if(self::$srtFormat == 'json'){
		    $strOutput = json_encode($arrOut);
	    }elseif(self::$srtFormat == 'xml'){
		    $strOutput = \Twist::XML()->arrayToXML($arrOut);
	    }

	    echo $strOutput;
    }

	public function _respondError($strErrorMessage,$intResponseCode = 404){

		header(sprintf("HTTP/1.1 %s %s",$intResponseCode,$this->responseStatus($intResponseCode)));

		$strOutput = '';
		$arrOut = array(
			'status' => 'error',
			'error' => $strErrorMessage,
			'count' => 0,
			'type' => '',
			'format' => self::$srtFormat,
		);

		if(self::$srtFormat == 'json'){
			$strOutput = json_encode($arrOut);
		}elseif(self::$srtFormat == 'xml'){
			$strOutput = \Twist::XML()->arrayToXML($arrOut);
		}

		echo $strOutput;
	}

}