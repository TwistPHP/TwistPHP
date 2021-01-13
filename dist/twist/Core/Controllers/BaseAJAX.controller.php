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

/**
 * An AJAX base controller that can be used instead of Base when adding AJAX support to your site. This controller should be used as an extension to a route controller class.
 * @package Twist\Core\Controllers
 */
class BaseAJAX extends Base{

	protected $blAjaxResponse = true;
	protected $strAjaxResponseMessage = '';

	public function _baseCalls(){

		//If the method is POST and the data has been sent as JSON, extract the JSON into the global $_POST var
		if(strtoupper($_SERVER['REQUEST_METHOD'] == 'POST') && strstr($_SERVER['CONTENT_TYPE'], 'application/json')){

			$resSTDIN = (defined('STDIN')) ? STDIN : 'php://input';
			$strSDIN = file_get_contents($resSTDIN);

			$arrPostedJSON = json_decode($strSDIN, true);
			if(json_last_error() === JSON_ERROR_NONE){
				$_POST = $arrPostedJSON;
			}else{
				$this->_ajaxFail();
				$this->_ajaxMessage(json_last_error_msg());
			}
		}

		$this->_timeout(60);
		return true;
	}

	/**
	 * Set the status for the Ajax response, true by default
	 *
	 * @param bool $blStatus
	 */
	public function _ajaxStatus($blStatus){
		$this->blAjaxResponse = ($blStatus !== false);
	}

	/**
	 * Call to mark the AJAX request as successfully complete, calls the _ajaxStatus function and passes in true.
	 */
	public function _ajaxSucceed(){
		$this->_ajaxStatus(true);
	}

	/**
	 * Call to mark the AJAX request as failed, calls the _ajaxStatus function and passes in false.
	 */
	public function _ajaxFail(){
		$this->_ajaxStatus(false);
	}

	/**
	 * Set a message to be returned to the Ajax call, can be used for an error message
	 * @param string $strMessage
	 */
	public function _ajaxMessage($strMessage=''){
		$this->strAjaxResponseMessage = $strMessage;
	}

	/**
	 * Encode the response of the AJAX output
	 * @param array $mxdData
	 * @param bool  $blDebug
	 * @return string
	 */
	public function _ajaxRespond($mxdData=array(), $blDebug = false){
		$arrResponse = array();
		$arrResponse['status'] = $this->blAjaxResponse;
		$arrResponse['message'] = $this->strAjaxResponseMessage;
		$arrResponse['data'] = $mxdData;
		if( $blDebug === true && \Twist::framework()->setting('DEVELOPMENT_MODE') ) {
			$arrResponse['debug'] = array();
			$arrResponse['debug']['route'] = $this->_route();
		}
		return json_encode($arrResponse);
	}
}