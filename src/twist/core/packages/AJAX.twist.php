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

namespace Twist\Core\Packages;
use \Twist\Core\Classes\PackageBase;

/**
 * AJAX server to use along with twist ajax and twist debug (optional)
 * Class AJAX
 * @package Twist\Core\Packages
 */
class AJAX extends PackageBase{

	var $arrResponse = array(
		'status' => true,
		'message' => '',
		'title' => '',
		'data' => array(),
		'sticky' => false,
		'importance' => 0,
		'loggedin' => false,
		'login_redirect' => false,
		'output' => ''
	);

	var $arrRequest = array();
	var $strFunction = '';
	var $strFunctionsDirectory = '';
	var $mxdLoginRedirectURL = false;

	/**
	 * Start up the server, process and respond to the request. Handles most errors and Twist Debug extension also integrated.
	 * System contains a fallback when you need to submit but ajax is not an option. To use the fallback you will need two
	 * ensure you send the POST parameters. 1. function, the function to call in the ajax system 2. oncomplete, the url to direct the
	 * to once function complete.
	 * @param $strFunctionsDirectory
	 * @param null $dirView
	 */
	public function server($strFunctionsDirectory,$dirView = null){

		//ob_start(array('TwistAJAX','obHandler'));

		//Check that the functions directory has been setup correctly
		if(is_dir($strFunctionsDirectory)){

			$this->strFunctionsDirectory = $strFunctionsDirectory;

			if(!is_null($dirView)){
				\Twist::View() -> setDirectory($dirView);
			}

			ignore_user_abort( true );
			set_time_limit( 60 );

			//Only process the request if post data is valid
			if(is_array($_POST) && count($_POST) > 0){
				$this->process();
			}else{
				$this->errorResponse("Error: Missing POST parameters, refer to documentation");
			}
		}else{
			$this->errorResponse("Error: Invalid functions directory");
		}

		$this->attachDebugData();
		$this->respond();
	}

	public function requireLogin(){

		if(!\Twist::User() -> loggedIn()){

			$this->redirect((defined('USER_DEFAULT_LOGIN_URI')) ? USER_DEFAULT_LOGIN_URI : '/login.php');
			$this->errorResponse("Error: Login required to run this script");

			$this->attachDebugData();
			$this->respond();
		}
	}

	public function redirect($strURL){
		$this->mxdLoginRedirectURL = $strURL;
	}

	/**
	 * Process the request and pre-pair it ready for the response output.
	 */
	private function process(){

		if(array_key_exists('function',$_POST)){

			$this->strFunction = $_POST['function'];
			$this->arrRequest = $_POST['data'];

			$strFunctionFile = sprintf("%s/%s.php",
				rtrim($this->strFunctionsDirectory,'/'),
				$this->strFunction
			);

			if(file_exists($strFunctionFile)){

				try{
					include $strFunctionFile;
				}catch(\Exception $resException){
					$this->errorResponse($resException->getMessage());
				}
			}else{
				$this->errorResponse("Error: invalid function call passed");
			}
		}else{
			$this->errorResponse("Error: All requests require the 'function' parameter to be set");
		}
	}

	/**
	 * Get the request data set sent by the browser to the server
	 * @return array
	 */
	public function getRequestData(){
		return $this->arrRequest;
	}

	/**
	 * Set the response data, message and status
	 * @param $arrData
	 * @param bool $strStatus
	 * @param null $strMessage
	 */
	public function setResponse($arrData,$strStatus = true,$strMessage = null,$strTitle = null,$blSticky = false,$intImportance = 0){

		$this->arrResponse['data'] = $arrData;
		$this->arrResponse['status'] = $strStatus;
		$this->arrResponse['message'] = (is_null($strMessage)) ? '' : $strMessage;
		$this->arrResponse['title'] = (is_null($strTitle)) ? '' : $strTitle;
		$this->arrResponse['sticky'] = $blSticky;
		$this->arrResponse['importance'] = $intImportance;

		//Return additional data
		$this->arrResponse['loggedin'] = \Twist::User() -> loggedIn();
		$this->arrResponse['login_redirect'] = $this->mxdLoginRedirectURL;
	}

	/**
	 * Set the error response, this is for the AJAX module itself not the users.
	 * @param $strMessage
	 */
	private function errorResponse($strMessage){

		$this->arrResponse['data'] = "";
		$this->arrResponse['status'] = false;
		$this->arrResponse['message'] = $strMessage;
		$this->arrResponse['title'] = 'Error';
		$this->arrResponse['sticky'] = false;
		$this->arrResponse['importance'] = 0;

		//Return additional data
		$this->arrResponse['loggedin'] = \Twist::User() -> loggedIn();
		$this->arrResponse['login_redirect'] = $this->mxdLoginRedirectURL;
	}

	/**
	 * Automatically process and attach the debug data to the ajax response
	 */
	private function attachDebugData(){

		$arrResponseData['errors'] = \Twist\Core\Classes\Error::$arrErrorLog;
	}

	/**
	 * Send the response back to the user by outputting the correct heads and encoding the data
	 */
	private function respond(){

		//ob_end_clean();

		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Wed, 24 Sep 1986 14:20:00 GMT' );
		header( 'Content-type: application/json' );
		header( sprintf('Content-length: %d', mb_strlen(json_encode( $this->arrResponse ))) );

		echo json_encode( $this->arrResponse );
		exit;
	}

	public function processFunction($strFunction){
		$strFunction(new AjaxRequest());
	}

	public function debug($mxdData){

		if(is_object($mxdData) || is_resource($mxdData) || is_array($mxdData)){
			$this->arrResponse['output'] .= print_r($mxdData,true)."\n\n-----\n\n";
		}else{
			$this->arrResponse['output'] .= $mxdData."\n\n-----\n\n";
		}
	}

	/**
	 * At the moment only supports 'latest' or a 'static' version
	 * {shadow-ajax:css,3.0.2} or {shadow-ajax:js,3.0.2} [Version not required]
	 * @param $strReference
	 * @return string
	 */
	function viewExtension($strReference){

		$strOut = "";
		$arrData = (strstr($strReference,',')) ? explode(',',$strReference) : array($strReference,'latest');

		//$strModuleBase = $this->__uri();

		switch($arrData[0]){

			case'javascript':
			case'js':
				//$strFile = \Twist::File() -> findVersion(sprintf('%s/js/',$strModuleBase),'twist-ajax',$arrData[1]);
				//$strFile = (file_exists(BASE_LOCATION.str_replace('.js','.min.js',$strFile))) ? str_replace('.js','.min.js',$strFile) : $strFile;
				//$strOut = sprintf('<script src="/%s"></script>',$strFile);

				$strOut = '<script src="/twist/core/packages/resources/AJAX/js/twist-ajax.min.js"></script>';
				break;

			case'js-dev':
				$strOut = '<script src="/twist/core/packages/resources/AJAX/js/twist-ajax.js"></script>';
				break;

			case'css':
				//$strFile = \Twist::File() -> findVersion(sprintf('%s/css/',$strModuleBase),'twist-ajax',$arrData[1]);
				//$strOut = sprintf('<link href="/%s" type="text/css" rel="stylesheet">',$strFile);

				$strOut = '<link href="/twist/core/packages/resources/AJAX/css/twist-ajax.css" type="text/css" rel="stylesheet">';
				break;

			case'resources':
				//$strFile = \Twist::File() -> findVersion(sprintf('%s/js/',$strModuleBase),'twist-ajax',$arrData[1]);
				//$strFile = (file_exists(BASE_LOCATION.str_replace('.js','.min.js',$strFile))) ? str_replace('.js','.min.js',$strFile) : $strFile;
				//$strOut = sprintf('<script src="/%s"></script>',$strFile);

				//$strFile = \Twist::File() -> findVersion(sprintf('%s/css/',$strModuleBase),'twist-ajax',$arrData[1]);
				//$strOut .= sprintf('<link href="/%s" type="text/css" rel="stylesheet">',$strFile);

				$strOut = '<script src="/twist/core/packages/resources/AJAX/js/twist-ajax.min.js"></script>';
				$strOut .= '<link href="/twist/core/packages/resources/AJAX/css/twist-ajax.css" type="text/css" rel="stylesheet">';
				break;
		}

		return $strOut;
	}
}

class AjaxRequest{

	public function restrict(){
		\Twist::User()->restrict();
	}

	public function getData(){
		return \Twist::AJAX()->getRequestData();
	}

	public function respond($arrData,$strStatus = true,$strMessage = null,$strTitle = null,$blSticky = false,$intImportance = 0){
		\Twist::AJAX()->setResponse($arrData,$strStatus,$strMessage,$strTitle,$blSticky,$intImportance);
	}
}