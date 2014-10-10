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
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP\Packages;
	use TwistPHP\ModuleBase;

	/**
	 * Simply make GET, POST, PUT and DELETE CURL requests, set custom headers, decode JSON responses. Transferring data via many different protocols including HTTP, HTTPS, FTP, FTPS, TELNET, LDAP and many more.
	 */
	class Curl extends ModuleBase{

		protected $blResponseJSON = false;
		protected $blDisableUrlEncoding = false;
		protected $strDefaultUserAgent = 'TwistPHP Curl';
		protected $strUserAgent = '';
		protected $strUserPassword = null;
		protected $arrRequestInfo = array();
		protected $arrRequestError = array();
		protected $intTimeout = 5;

		function __construct(){
			$this->setUserAgent();
		}

		/**
		 * Automatically return a JSON response as an array
		 *
		 * @param $blEnable Determines if functionality should be used
		 */
		public function decodeResponseJSON($blEnable = false){
			$this->blResponseJSON = $blEnable;
		}

		/**
		 * Stop the system from URL encoding all parameters before they are sent
		 *
		 * @param $blEnable Determines if functionality should be used
		 */
		public function disableUrlEncoding($blEnable = true){
			$this->blDisableUrlEncoding = $blEnable;
		}

		/**
		 * Set the max timeout for the requests to be made
		 *
		 * @param $intTimeout Time in seconds
		 */
		public function setTimeout($intTimeout = 5){
			$this->intTimeout = $intTimeout;
		}

		/**
		 * Set a custom user agent header to be used when making the request, pass in null to use default user agent
		 *
		 * @param $strUserAgent Custom User Agent Header
		 */
		public function setUserAgent($strUserAgent = null){
			$this->strUserAgent = (is_null($strUserAgent)) ? $this->strDefaultUserAgent : $strUserAgent;
		}

		/**
		 * Set a username and password, this will log you into any request that may have HTTP User Restriction in place
		 *
		 * @param $strUsername Username required for the request
		 * @param $strPassword Password required for the request
		 */
		public function setUserPass($strUsername,$strPassword){
			$this->strUserPassword = sprintf("%s:%s",$strUsername,$strPassword);
		}

		/**
		 * Make a GET request to the provided URL, set the User Agent header when required
		 *
		 * @param $strURL Full URL for the request
		 * @param $arrRequestData Array of get parameters
		 * @param $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function get($strURL,$arrRequestData,$arrHeaders = array()){
			return $this->makeRequest($strURL,$arrRequestData,'get',$arrHeaders);
		}

		/**
		 * Make a POST request to the provided URL, set the User Agent header when required
		 *
		 * @related get
		 *
		 * @param $strURL Full URL for the request
		 * @param $arrData Array of post parameters
		 * @param $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function post($strURL,$arrData,$arrHeaders = array()){
			return $this->makeRequest($strURL,$arrData,'post',$arrHeaders);
		}

		/**
		 * Make a PUT request to the provided URL, set the User Agent header when required, put request can contain get parameters and file data should be passed in
		 *
		 * @related get
		 *
		 * @param $strURL Full URL for the request
		 * @param $strRawData Raw data to be posted
		 * @param $mxdRequestData Array of get parameters
		 * @param $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function put($strURL,$strRawData,$mxdRequestData = array(),$arrHeaders = array()){
			return $this->makeRequest($strURL,$mxdRequestData,'put',$arrHeaders,$strRawData);
		}

		/**
		 * Make a DELETE request to the provided URL, set the User Agent header when required
		 *
		 * @related get
		 *
		 * @param $strURL Full URL for the request
		 * @param $mxdRequestData Array of get parameters
		 * @param $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function delete($strURL,$mxdRequestData,$arrHeaders = array()){
			return $this->makeRequest($strURL,$mxdRequestData,'delete',$arrHeaders);
		}

		/**
		 * The function that makes the CURL requests, all data is passed in and the response is returned
		 *
		 * @param $strURL Full URL for the request
		 * @param $mxdRequestData Array of post/get parameters
		 * @param $strType HTTP protocol of the request
		 * @param $arrHeaders Array of additional headers to be sent
		 * @param $strRawData Raw data to be posted
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		protected function makeRequest($strURL,$mxdRequestData,$strType = 'get',$arrHeaders=array(),$strRawData = ''){

			$strData = "";

			//Set the all the parameters
			if(is_array($mxdRequestData) && count($mxdRequestData)){
				foreach($mxdRequestData as $mxdKey => $mxdData){
					$strParamData = ($this->blDisableUrlEncoding) ? $mxdData : urlencode($mxdData);
					$strData .= sprintf("%s=%s&",$mxdKey,$strParamData);
				}
			}

			//open connection
			$resCurl = curl_init();

			switch( $strType ) {
				case 'get':

					if($strData != ''){
						$strURL = sprintf("%s?%s",$strURL,$strData);
					}
					break;

				case 'post':
					//Set the post vars if a post request
					if($strData != ''){
						curl_setopt($resCurl, CURLOPT_POST, count($mxdRequestData)+1);
						curl_setopt($resCurl, CURLOPT_POSTFIELDS, $strData);
					}
					break;

				case 'put':

					if($strData != ''){
						$strURL = sprintf("%s?%s",$strURL,$strData);
					}

					//Max 256KB of RAM can be assigned before creating a file
					$resRequestBody = fopen('php://temp/maxmemory:256000', 'w');
					(!is_resource($resRequestBody)) ? new \Exception('Unable to assign memory to create request body!') : null;
					fwrite($resRequestBody, $strRawData);
					fseek($resRequestBody, 0);

					//Send the PUT data in the body of the request
					curl_setopt($resCurl, CURLOPT_PUT, true);
					curl_setopt($resCurl, CURLOPT_INFILE, $resRequestBody);
					curl_setopt($resCurl, CURLOPT_INFILESIZE, strlen($strRawData));
					break;

				case 'delete':
					curl_setopt($resCurl, CURLOPT_CUSTOMREQUEST, 'DELETE');
					break;
			}

			//set the url
			curl_setopt($resCurl, CURLOPT_URL,$strURL);

			//Set the username and password when required
			if(!is_null($this->strUserPassword)){
				curl_setopt($resCurl, CURLOPT_USERPWD, $this->strUserPassword);
			}

			//Set the other data
			curl_setopt($resCurl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($resCurl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($resCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($resCurl, CURLOPT_TIMEOUT, $this->intTimeout);
			curl_setopt($resCurl, CURLOPT_FAILONERROR, true);

			//Set the custom headers
			if(is_array($arrHeaders) && count($arrHeaders) > 0){
				curl_setopt($resCurl, CURLOPT_HTTPHEADER,$arrHeaders);
			}

			//Set the custom User Agent Header
			if(!is_null($this->strUserAgent) && $this->strUserAgent != ''){
				curl_setopt($resCurl, CURLOPT_USERAGENT, $this->strUserAgent);
			}

			//execute post
			$mxdResponse = curl_exec($resCurl);
			$this->arrRequestInfo = curl_getinfo($resCurl);

			if(curl_errno($resCurl)){
				$this->arrRequestError = array(
					'number' => curl_errno($resCurl),
					'message' => curl_error($resCurl)
				);
			}else{
				$this->arrRequestError = array();
			}

			//close connection
			curl_close($resCurl);

			if($this->blResponseJSON == true){
				$mxdResponse = (array)json_decode($mxdResponse,true);
			}

			return $mxdResponse;
		}

		/**
		 * Get a detailed array of data about the last request made through the API
		 *
		 * @return array Returns request information array
		 */
		public function getRequestInformation(){
			return $this->arrRequestInfo;
		}

		/**
		 * Get an array of error data relating to the last request, if no error occurred the array will be empty
		 *
		 * @return array Returns request error array
		 */
		public function getRequestError(){
			return $this->arrRequestError;
		}
	}