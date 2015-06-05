<?php
/**
 * This file is part of TwistPHP.
 *
 * TwistPHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TwistPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
 * @link       https://twistphp.com
 *
 */

namespace Twist\Core\Classes;

class BaseControllerAJAX extends BaseController{

	protected $blAjaxResponse = true;
	protected $strAjaxResponseMessage = '';

	public function __construct(){

		//@todo Should these two options still be set by default
		$this->_ignoreUserAbort(true);
		$this->_timeout(60);
	}

	/**
	 * Set the status for the Ajax response, true by default
	 * @param $blStatus
	 */
	public function _ajaxStatus($blStatus){
		$this->blAjaxResponse = ($blStatus !== false);
	}

	public function _ajaxSucceed(){
		$this->_ajaxStatus(true);
	}

	public function _ajaxFail(){
		$this->_ajaxStatus(false);
	}

	/**
	 * Set a message to be returned to the Ajax call, can be used for an error message
	 * @param $strMessage
	 */
	public function _ajaxMessage($strMessage=''){
		$this->strAjaxResponseMessage = $strMessage;
	}

	/**
	 * Encode the response of the AJAX output
	 * @param array $mxdData
	 * @return string
	 */
	public function _ajaxRespond($mxdData=array(), $blDebug = false){
		$arrResponse = array();
		$arrResponse['status'] = $this->blAjaxResponse;
		$arrResponse['message'] = $this->strAjaxResponseMessage;
		$arrResponse['data'] = $mxdData;
		if( $blDebug === true && \Twist::framework()->setting('DEVELOPMENT_MODE') ) {
			$this->arrAjaxResponse['debug'] = array();
			$this->arrAjaxResponse['debug']['route'] = $this->_route();
		}
		return json_encode($arrResponse).'123';
	}
}