<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Shadow Technologies Ltd.
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
 * An REST API base controller (no authentication) that can be used instead of Base when adding REST API support to your site. This controller should be used as an extension to a route controller class.
 * See twistphp.com/examples for a full guide on RESTful routing
 *
 * @package Twist\Core\Controllers
 */
class BaseREST extends Base{

    protected static $srtFormat = 'json';

    /**
     * Default functionality for every request that is made though the REST API.
     */
    public function _baseCalls(){

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");

        $this->_timeout(60);
        $this->_ignoreUserAbort(true);

		//If the method is POST and the data has been sent as JSON, extract the JSON into the global $_POST var
		if(strtoupper($_SERVER['REQUEST_METHOD'] == 'POST') && strstr($_SERVER['CONTENT_TYPE'], 'application/json')){

			$resSTDIN = (defined('STDIN')) ? STDIN : 'php://input';
			$strSDIN = file_get_contents($resSTDIN);

			$arrPostedJSON = json_decode($strSDIN, true);
			if(json_last_error() === JSON_ERROR_NONE){
				$_POST = $arrPostedJSON;
			}
		}

        //Determine the format in which to return the data, default is JSON
        self::$srtFormat = (array_key_exists('format',$_REQUEST)) ?  $_REQUEST['format'] : strtolower(self::$srtFormat);

        return $this->_auth();
    }

    /**
     * Open REST does not require any auth but this function is needed for RESTKey and RESTUser
     * @return bool
     */
    public function _auth(){
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
	 * @return string Response to serve to the client
	 */
    public function _respond($mxdResults,$intCount = 1,$intResponseCode = 200){

		$arrResponse = Error::responseInfo($intResponseCode);
		header(sprintf("HTTP/1.1 %s %s",$intResponseCode,$arrResponse['return']));

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

        return $strOutput;
    }

    /**
     * Error response to an API call should be used to return a standardised RESTful error response
     * @param string $strErrorMessage Error message to indicate what when wrong
     * @param int $intResponseCode HTTP response code for the call
	 * @return string Response to the client with an error
	 */
    public function _respondError($strErrorMessage,$intResponseCode = 404){

		$arrResponse = Error::responseInfo($intResponseCode);
        header(sprintf("HTTP/1.1 %s %s",$intResponseCode,$arrResponse['return']));

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

        return $strOutput;
    }

}