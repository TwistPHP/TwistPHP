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

	namespace Twist\Core\Utilities;

	/**
	 * Simply make GET, POST, PUT and DELETE CURL requests, set custom headers, decode JSON responses. Transferring data via many different protocols including HTTP, HTTPS, FTP, FTPS, TELNET, LDAP and many more.
	 */
	class Curl extends Base{

		protected $blResponseJSON = false;
		protected $blDisableUrlEncoding = false;
		protected $blVerifySSLRequest = false;
        protected $blNoBody = false;
        protected $blFollowRedirects = false;
        protected $blFailOnError = false;
		protected $arrSSLCertificate = array();
		protected $strDefaultUserAgent = 'TwistPHP Curl';
		protected $strCookies = '';
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
		 * @param bool $blEnable Determines if functionality should be used
		 */
		public function decodeResponseJSON($blEnable = false){
			$this->blResponseJSON = $blEnable;
		}

		/**
		 * Stop the system from URL encoding all parameters before they are sent
		 *
		 * @param bool $blEnable Determines if functionality should be used
		 */
		public function disableUrlEncoding($blEnable = true){
			$this->blDisableUrlEncoding = $blEnable;
		}

		/**
		 * Tell Curl whether to verify the Host and Peer when making requests to HTTPS urls
		 *
		 * @param bool $blEnable Determines if functionality should be enabled (Default setting: disabled)
		 */
		public function verifySSLRequest($blEnable = true){
			$this->blVerifySSLRequest = $blEnable;
		}

		/**
		 * Set the max timeout for the requests to be made
		 *
		 * @param integer $intTimeout Time in seconds
		 */
		public function setTimeout($intTimeout = 5){
			$this->intTimeout = $intTimeout;
		}

        /**
         * Return only the response headers, no body
         *
         * @param boolean $blEnable Determines if functionality should be used
         */
        public function setNoBody($blEnable = true){
            $this->blNoBody = $blEnable;
        }

        /**
         * Follow 301 and 302 redirects when enabled
         *
         * @param boolean $blEnable Determines if functionality should be used
         */
        public function setFollowRedirects($blEnable = true){
            $this->blFollowRedirects = $blEnable;
        }

        /**
         * Fail on error, Error HTTP response codes will cause a fail and will not return a response body
         *
         * @param boolean $blEnable Determines if functionality should be used
         */
        public function setFailOnError($blEnable = true){
            $this->blFailOnError = $blEnable;
        }

		/**
		 * Set a custom user agent header to be used when making the request, pass in null to use default user agent
		 *
		 * @param string $strUserAgent Custom User Agent Header
		 */
		public function setUserAgent($strUserAgent = null){
			$this->strUserAgent = (is_null($strUserAgent)) ? $this->strDefaultUserAgent : $strUserAgent;
		}

		/**
		 * Set a username and password, this will log you into any request that may have HTTP User Restriction in place
		 *
		 * @param string $strUsername Username required for the request
		 * @param string $strPassword Password required for the request
		 */
		public function setUserPass($strUsername,$strPassword){
			$this->strUserPassword = sprintf("%s:%s",$strUsername,$strPassword);
		}

		/**
		 * Encrypt the Curl request using a SSL certificate and key pair
		 *
		 * @param string $dirSSLCertificate The path of a file containing a PEM formatted certificate.
		 * @param string $dirSSLKey The path of a file containing a private SSL key.
		 * @param string $strCertificateType The format of the certificate. Supported formats are "PEM" (default), "DER", and "ENG".
		 * @param string $strKeyType The key type of the private SSL key. Supported key types are "PEM" (default), "DER", and "ENG".
		 */
		public function setSSLCertificate($dirSSLCertificate,$dirSSLKey,$strCertificateType = 'PEM',$strKeyType = 'PEM'){
			$this->arrSSLCertificate = array(
				'certificate' => $dirSSLCertificate,
				'key' => $dirSSLKey,
				'certificate_type' => $strCertificateType,
				'key_type' => $strKeyType
			);
		}

		/**
		 * Set the content type of the request, this will be merged with the headers array if required
		 *
		 * @param string $strContentType Content type of the body data (if required)
		 */
		public function setContentType($strContentType){
			$this->arrHeaders[] = sprintf("Content-Type: %s",trim($strContentType));
		}

		/**
		 * The contents of the "Cookie: " header to be used in the HTTP request. Note that multiple cookies are separated with a semicolon followed by a space (e.g., "fruit=apple; colour=red")
		 *
		 * @param string $strCookies The cookie content to be, separated multiple cookies with a semicolon followed by a space
		 */
		public function setCookies($strCookies){
			$this->strCookies = $strCookies;
		}

		/**
		 * Make a GET request to the provided URL, set the User Agent header when required
		 *
		 * @param string $strURL Full URL for the request
		 * @param array $arrRequestData Array of get parameters
		 * @param array $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function get($strURL,$arrRequestData = array(),$arrHeaders = array()){
			return $this->makeRequest($strURL,$arrRequestData,'get',$arrHeaders);
		}

		/**
		 * Make a POST request to the provided URL, set the User Agent header when required. The post data can either be an array of parameters or raw body data e.g. XML/JSON.
		 * If Raw data is used you must specify a content type for the request.
		 *
		 * @related get
		 *
		 * @param string $strURL Full URL for the request
		 * @param mixed $mxdRequestData Pass in an array of post parameters or raw body data of e.g. XML,JSON to be posted
		 * @param array $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function post($strURL,$mxdRequestData = array(),$arrHeaders = array()){
			return $this->makePostRequest($strURL,$mxdRequestData,$arrHeaders,'post');
		}

		/**
		 * Make a PUT request to the provided URL, set the User Agent header when required, put request can contain get parameters and file data should be passed in
		 *
		 * @related get
		 *
		 * @param string $strURL Full URL for the request
		 * @param string $strRawData Raw data to be posted
		 * @param array $arrRequestData Array of get parameters
		 * @param array $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function put($strURL,$strRawData,$arrRequestData = array(),$arrHeaders = array()){
			return $this->makeRequest($strURL,$arrRequestData,'put',$arrHeaders,$strRawData);
		}

		/**
		 * Make a PATCH request to the provided URL, set the User Agent header when required. The post data can either be an array of parameters or raw body data e.g. XML/JSON.
		 * If Raw data is used you must specify a content type for the request.
		 *
		 * @related get
		 *
		 * @param string $strURL Full URL for the request
		 * @param mixed $mxdRequestData Pass in an array of post parameters or raw body data of e.g. XML,JSON to be posted
		 * @param array $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function patch($strURL,$mxdRequestData = array(),$arrHeaders = array()){
			return $this->makePostRequest($strURL,$mxdRequestData,$arrHeaders,'patch');
		}

		/**
		 * Make a DELETE request to the provided URL, set the User Agent header when required. The post data can either be an array of parameters or raw body data e.g. XML/JSON.
		 * If Raw data is used you must specify a content type for the request.
		 *
		 * @related get
		 *
		 * @param string $strURL Full URL for the request
		 * @param mixed $mxdRequestData Pass in an array of post parameters or raw body data of e.g. XML,JSON to be posted
		 * @param array $arrHeaders Array of additional headers to be sent
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		public function delete($strURL,$mxdRequestData = array(),$arrHeaders = array()){
			return $this->makePostRequest($strURL,$mxdRequestData,$arrHeaders,'delete');
		}

		/**
		 * A method used but POST, PATCH and DELETE as they are all similar requests with a different method name
		 *
		 * @param string $strURL Full URL for the request
		 * @param mixed $mxdRequestData Pass in an array of post parameters or raw body data of e.g. XML,JSON to be posted
		 * @param array $arrHeaders Array of additional headers to be sent
		 * @param string $strMethod
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		protected function makePostRequest($strURL,$mxdRequestData = array(),$arrHeaders = array(),$strMethod = 'post'){

			$arrRequestData = array();
			$strRawData = '';

			//Decide if the request is post fields or body data
			if(is_array($mxdRequestData)){
				$arrRequestData = $mxdRequestData;
			}else{
				$strRawData = $mxdRequestData;
			}

			return $this->makeRequest($strURL,$arrRequestData,$strMethod,$arrHeaders,$strRawData);
		}

		/**
		 * The function that makes the CURL requests, all data is passed in and the response is returned
		 *
		 * @param string $strURL Full URL for the request
		 * @param array $arrRequestData Array of post/get parameters
		 * @param string $strType HTTP protocol of the request
		 * @param array $arrHeaders Array of additional headers to be sent
		 * @param string $strRawData Raw data to be posted
		 * @return mixed Returns the results of the request, will be an array if 'decodeResponseJSON' is enabled
		 */
		protected function makeRequest($strURL,$arrRequestData = array(),$strType = 'get',$arrHeaders=array(),$strRawData = ''){

			$strData = null;

			//Set the all the parameters
			if(is_array($arrRequestData) && count($arrRequestData)){
				$strData = '';
				foreach($arrRequestData as $mxdKey => $mxdData){
					$strParamData = ($this->blDisableUrlEncoding) ? $mxdData : urlencode($mxdData);
					$strData .= sprintf("%s=%s&",$mxdKey,$strParamData);
				}
			}

			//open connection
			$resCurl = curl_init();

			switch($strType){

				case 'get':
					$strURL = (!is_null($strData)) ? sprintf("%s?%s",$strURL,$strData) : $strURL;
					break;

				case 'patch':
				case 'delete':
				case 'post':

					//Set the custom request method name
					if($strType != 'post'){
						curl_setopt($resCurl, CURLOPT_CUSTOMREQUEST, strtoupper($strType));
					}

					//Set the post vars if a post request or Raw body data if required
					if(!is_null($strData)){
						curl_setopt($resCurl, CURLOPT_POST, count($arrRequestData)+1);
						curl_setopt($resCurl, CURLOPT_POSTFIELDS, $strData);
					}elseif($strRawData != ''){
						curl_setopt($resCurl, CURLOPT_POST, 1);
						curl_setopt($resCurl, CURLOPT_POSTFIELDS, $strRawData);
					}

					break;

				case 'put':

					if(!is_null($strData)){
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
			}

			//set the url
			curl_setopt($resCurl, CURLOPT_URL,$strURL);

			//Set the username and password when required
			if(!is_null($this->strUserPassword)){
				curl_setopt($resCurl, CURLOPT_USERPWD, $this->strUserPassword);
			}

			//Set the SSL encryption certificate and key
			if(count($this->arrSSLCertificate)){
				curl_setopt($resCurl, CURLOPT_SSLCERT, $this->arrSSLCertificate['certificate']);
				curl_setopt($resCurl, CURLOPT_SSLCERTTYPE, $this->arrSSLCertificate['certificate_type']);
				curl_setopt($resCurl, CURLOPT_SSLKEY, $this->arrSSLCertificate['key']);
				curl_setopt($resCurl, CURLOPT_SSLKEYTYPE, $this->arrSSLCertificate['key_type']);
			}

			curl_setopt($resCurl, CURLOPT_SSL_VERIFYHOST, ($this->blVerifySSLRequest) ? 1 : 0);
			curl_setopt($resCurl, CURLOPT_SSL_VERIFYPEER, ($this->blVerifySSLRequest) ? 1 : 0);

            curl_setopt($resCurl, CURLOPT_NOBODY, ($this->blNoBody) ? 1 : 0);
            curl_setopt($resCurl, CURLOPT_FOLLOWLOCATION, ($this->blFollowRedirects) ? 1 : 0);
			curl_setopt($resCurl, CURLOPT_FAILONERROR, ($this->blFailOnError) ? 1 : 0);

			//Set the other data
			curl_setopt($resCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($resCurl, CURLOPT_VERBOSE, 1);
			curl_setopt($resCurl, CURLOPT_HEADER, 1);
			curl_setopt($resCurl, CURLOPT_TIMEOUT, $this->intTimeout);
			curl_setopt($resCurl, CURLOPT_REFERER, sprintf('%s://%s/',
					\Twist::framework()->setting('SITE_PROTOCOL'),
					\Twist::framework()->setting('SITE_HOST'))
			);

			//Set the custom headers
			if(is_array($arrHeaders) && count($arrHeaders) > 0){

                if(!array_key_exists(0,$arrHeaders)){

                    //Convert key pair headers into header strings
                    $arrHeadersNew = array();

                    foreach($arrHeaders as $strHeaderKey => $strHeaderValue){
                        $arrHeadersNew[] = $strHeaderKey.': '.$strHeaderValue;
                    }

                    $arrHeaders = $arrHeadersNew;
                }

				curl_setopt($resCurl, CURLOPT_HTTPHEADER,$arrHeaders);
			}

			//Set some cookies for the request if required
			if($this->strCookies != ''){
				curl_setopt($resCurl, CURLOPT_COOKIE, $this->strCookies);
			}

			//Set the custom User Agent Header
			if(!is_null($this->strUserAgent) && $this->strUserAgent != ''){
				curl_setopt($resCurl, CURLOPT_USERAGENT, $this->strUserAgent);
			}

			//Enable the request headers "request_header" to be added into the debug as well as the response headers 
			curl_setopt($resCurl, CURLINFO_HEADER_OUT, true);
			
			//execute post
			$mxdResponse = curl_exec($resCurl);
			$this->arrRequestInfo = curl_getinfo($resCurl);

			$this->arrRequestInfo['headers'] = $this->httpParseHeaders(substr($mxdResponse, 0, $this->arrRequestInfo['header_size']));
			$mxdResponse = substr($mxdResponse, $this->arrRequestInfo['header_size']);

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

		/**
		 * Parse the response headers and output them as an array of key value pairs
		 * @param mixed $mxdRawHeaders Raw response headers to be parsed
		 * @return array Key Value pare of all response headers
		 */
		protected function httpParseHeaders($mxdRawHeaders){

			$arrHeaders = array();
			$mxdKey = '';

			foreach(explode("\n", $mxdRawHeaders) as $mxdRawHeaderLine){

				$arrHeaderParts = explode(':', $mxdRawHeaderLine, 2);

				if(isset($arrHeaderParts[1])){

					if(!isset($arrHeaders[$arrHeaderParts[0]])){
						$arrHeaders[$arrHeaderParts[0]] = trim($arrHeaderParts[1]);

					}elseif(is_array($arrHeaders[$arrHeaderParts[0]])){
						$arrHeaders[$arrHeaderParts[0]] = array_merge($arrHeaders[$arrHeaderParts[0]], array(trim($arrHeaderParts[1])));

					}else{
						$arrHeaders[$arrHeaderParts[0]] = array_merge(array($arrHeaders[$arrHeaderParts[0]]), array(trim($arrHeaderParts[1])));
					}

					$mxdKey = $arrHeaderParts[0];

				}else{

					if(substr($arrHeaderParts[0], 0, 1) == "\t"){
						$arrHeaders[$mxdKey] .= "\r\n\t" . trim($arrHeaderParts[0]);

					}elseif(!$mxdKey){
						$arrHeaders[0] = trim($arrHeaderParts[0]);
					}
				}
			}

			return $arrHeaders;
		}
	}
