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

	namespace Twist\Classes;

	class Error{

		public static $arrErrorLog = array();

		function __construct(){

		}

		/**
		 * Get an array of the Apache request headers.
		 * @todo Needs to be re-written/optimised
		 * @return array
		 */
		public static function apacheRequestHeaders(){

            $arrOut = array();
            $regxHTTP = '/\AHTTP_/';

            foreach($_SERVER as $strKey => $mxdValue){

                if(preg_match($regxHTTP, $strKey)){

                    $strApacheRequestKey = preg_replace($regxHTTP, '', $strKey);

	                //Restore key case to its original state
                    $arrMatches = explode('_', $strApacheRequestKey);
	                if(count($arrMatches) > 0 && strlen($strApacheRequestKey) > 2){
                        foreach($arrMatches as $strApacheKey => $mxdApacheKeyValue){
	                        $arrMatches[$strApacheKey] = ucfirst($mxdApacheKeyValue);
                        }
		                $strApacheRequestKey = implode('-', $arrMatches);
                    }

	                $arrOut[$strApacheRequestKey] = $mxdValue;
                }
            }

            return $arrOut;
        }

		/**
		 * Get an array of server related information
		 * @return array
		 */
		public static function serverInformation(){

            $arrOut = array();

            foreach($_SERVER as $strKey => $mxdValue){
                if(strstr($strKey,'SERVER_') || in_array($strKey,array('','','','',''))){
                    $strKey = str_replace('SERVER_','',$strKey);
                    $arrOut[$strKey] = $mxdValue;
                }
            }

            return $arrOut;
        }

		/**
		 * Handle a PHP Exception and output the twist exception page. The exception page will be more detailed when the framework is set in development mode (see the TwistPHP setting DEVELOPMENT_MODE).
		 * @param \Exception $resException
		 * @param array $arrError
		 */
		public static function handleException(\Exception $resException,$arrError = array()){

            $strExceptionTemplate = sprintf("%s/system/exception-user.tpl",TWIST_FRAMEWORK_VIEWS);

            //Clean the screen output ready for an exception
            ob_clean();

            try{
                $strName = \Twist::framework() -> setting('SITE_NAME');
                $strHost = \Twist::framework() -> setting('SITE_HOST');
            }catch(\Exception $resException){
                $strName = 'Twist Framework';
                $strHost = $_SERVER['HTTP_HOST'];
            }

            $arrTags = array(
                'name' => $strName,
                'code' => $resException->getCode(),
                'php_code' => 'No debug found',
                'server' => self::debugDataOutput($_SERVER),
                'post' => self::debugDataOutput($_POST),
                'get' => self::debugDataOutput($_GET),
                'cookie' => self::debugDataOutput($_COOKIE),
                'session' => self::debugDataOutput($_SESSION),
                'message' => '',
                'domain' => $strHost,
                'dump_data' => '',
                'request_headers' => '',
                'server_vars' => ''
            );

            if(is_array($arrError) && count($arrError) > 0){
                $arrTags['type'] = 'PHP Fatal Error';
                $arrTags['line'] = $arrError['line'];
                $arrTags['file'] = $arrError['file'];
                $arrTags['type_code'] = E_ERROR;
            }else{
                $arrTags['type'] = 'PHP Exception';
                $arrTags['line'] = $resException->getLine();
                $arrTags['file'] = $resException->getFile();
                $arrTags['type_code'] = 'php-exception';
            }

			if(\Twist::framework()->setting('DEVELOPMENT_MODE')){

                $arrRequestHeaders = self::apacheRequestHeaders();
                $arrServerVars = self::serverInformation();

                if(TWIST_AJAX_REQUEST){
                    $arrTags['request_headers'] = $arrRequestHeaders;
                    $arrTags['server_vars'] = $arrServerVars;
                }else{
                    $arrTags['request_headers'] = '';
                    foreach($arrRequestHeaders as $strKey => $mxdValue){
                        $arrTags['request_headers'] .= sprintf('<dt>%s</dt><dd>%s</dd>',$strKey,$mxdValue);
                    }

                    $arrTags['server_vars'] = '';
                    foreach($arrServerVars as $strKey => $mxdValue){
                        $arrTags['server_vars'] .= sprintf('<dt>%s</dt><dd>%s</dd>',$strKey,$mxdValue);
                    }
                }

				if($resException->getCode() === 1200){
					//Dump Data call from core
					$arrTags['code'] = '';
					$arrTags['type'] = 'Inspector';
					$arrTags['message'] = 'System Process Dump';

					$mxdData = json_decode($resException->getMessage(),true);

					if(is_object($mxdData) || is_resource($mxdData)){
						$arrTags['var_dump'] = \Twist::framework()->tools()->varDump($mxdData);
					}else{
						$arrTags['var_dump'] = print_r($mxdData,true);
					}

					if(is_null($mxdData)){
						$mxdData = array();
					}elseif(!is_array($mxdData)){
						$mxdData = array('n/a' => $mxdData);
					}

					$arrTags['dump_data'] = self::debugDataOutput($mxdData);
					$strExceptionTemplate = sprintf("%s/system/dump.tpl",TWIST_FRAMEWORK_VIEWS);
				}else{
					$arrTags['message'] = $resException->getMessage();
                    $strExceptionTemplate = sprintf("%s/system/exception.tpl",TWIST_FRAMEWORK_VIEWS);
					$arrTags['dump_data'] = '';
				}

				$arrTags['php_code'] = self::codeOutput($resException->getFile(),$resException->getLine(),5);

				$arrTags['trace'] = '';
				if(count($resException->getTrace())){
					$arrTags['trace'] = '<h3>Backtrace</h3>';
					foreach($resException->getTrace() as $arrEachCall){
						if(array_key_exists('file',$arrEachCall)){
							$arrTags['trace'] .= sprintf('<pre class="code" lang="php" title="%s">%s</pre>',$arrEachCall['file'],self::codeOutput($arrEachCall['file'],$arrEachCall['line'],2));
						}
					}
				}

				self::handleError($arrTags['type_code'],$arrTags['message'],$arrTags['file'],$arrTags['line']);
			}

            if(TWIST_AJAX_REQUEST){

                header( 'Cache-Control: no-cache, must-revalidate' );
                header( 'Expires: Wed, 24 Sep 1986 14:20:00 GMT' );
                header( 'Content-type: application/json' );
                header( sprintf('Content-length: %d', function_exists('mb_strlen') ? mb_strlen(json_encode($arrTags)) : strlen(json_encode($arrTags))) );

                die(json_encode($arrTags));
            }else{
                die(\Twist::View('Exception')->build($strExceptionTemplate,$arrTags));
            }
		}

		/**
		 * Grab some code from a file and line number, highlight the code and return and a HTML string to be used within the exception page and debug window.
		 * @param $strFile
		 * @param $strLine
		 * @param int $intLinesAboveBelow
		 * @return string
		 */
		protected static function codeOutput($strFile,$strLine,$intLinesAboveBelow = 3){

			$strOut = '';

			//Grab the offending lines of code and then unset the var
			$arrFileCode = (file_exists($strFile)) ? file($strFile) : array();
			$intErrorLine = ($strLine-1);
			if(is_array($arrFileCode) && count($arrFileCode) >= $intErrorLine){
				if(array_key_exists($intErrorLine,$arrFileCode)){
					$intMaxChar = strlen($intErrorLine+$intLinesAboveBelow);
					$arrTags['php_code'] = '';
					for($intLine=-$intLinesAboveBelow; $intLine<=$intLinesAboveBelow; $intLine++){

						if(array_key_exists($intErrorLine+$intLine,$arrFileCode)){

							$strCode = highlight_string('<?php '.str_replace("\t","    ",$arrFileCode[$intErrorLine+$intLine]),true);
							$strCode = str_replace('&lt;?php&nbsp;','',$strCode);
							$strCode = str_replace("\n",'',$strCode);
							$strCode = str_replace(array("<br>",'<br >','<br />','<br/>'),'',$strCode);
							$strCode = str_replace(array('<code>','</code>'),'',$strCode);
							$strCode .= "\n";

							if($intLine === 0){
								$strOut .= '<em>'.str_pad(($intErrorLine+$intLine)+1,$intMaxChar,' ',STR_PAD_LEFT).' | '.$strCode.'</em>';
							}else{
								$strOut .= (array_key_exists($intErrorLine+$intLine,$arrFileCode)) ? str_pad(($intErrorLine+$intLine)+1,$intMaxChar,' ',STR_PAD_LEFT).' | '.$strCode : '';
							}
						}
					}
					unset($arrFileCode);
				}
			}

			return $strOut;
		}

		protected static function debugDataOutput($arrData){

			$strOut = '';

			if(count($arrData)){
				foreach($arrData as $strKey => $mxdValue){
					$strOut .= sprintf('<tr><th>%s</th><td>%s</td><td>%s</td><td>%s</td></tr>',
						$strKey,
						(is_array($mxdValue)) ? print_r($mxdValue,true) : $mxdValue,
						gettype($mxdValue),
						(is_array($mxdValue)) ? count($mxdValue) : strlen($mxdValue)
					);
				}
			}else{
				$strOut .= '<tr><td colspan="4">No data to be displayed</td></tr>';
			}
			return $strOut;
		}

		/**
		 * PHP Error handler to capture all PHP errors so that they can be logged to a file or output into the debug window later.
		 * @param $intErrorNo
		 * @param $strError
		 * @param $strErrorFile
		 * @param $intErrorLine
		 */
		public static function handleError($intErrorNo, $strError, $strErrorFile, $intErrorLine){

			$strErrorType = self::getType($intErrorNo);

			if($strErrorType != "User Error"){

				$arrError = array(
					'type' => $strErrorType,
					'number' => $intErrorNo,
					'message' => $strError,
					'file' => $strErrorFile,
					'file_size' => (file_exists($strErrorFile)) ? filesize($strErrorFile) : 0,
					'code_line' => $intErrorLine,
					'code' => self::codeOutput($strErrorFile,$intErrorLine,3)
				);

				\Twist::framework()->debug()->log('Error','php',$arrError);
				self::$arrErrorLog[] = $arrError;
			}
		}

		/**
		 * Handler to capture most PHP fatal errors and output them to the screen as a nicely formatted exception page.
		 */
		public static function handleFatal(){

			$arrLastError = error_get_last();

			//Check if the last error was fatal (INSTANT DEATH)
			if(self::getType($arrLastError['type'],true) === "Fatal Error"){

				//throw new Exception($arrLastError['message']);
				//A fatal error has occured, throw and exception instead.
				$objException = new Exception($arrLastError['message'],1,$arrLastError['file'],$arrLastError['line']);

				self::handleException($objException,$arrLastError);
			}
		}

		/**
		 * Output a 404 page to the user
		 */
		public static function handle404(){
			self::errorPage(404);
		}

		/**
		 * Output a response code and a custom message if required to the user, this function handles all HTTP response codes.
		 * @param $intErrorCode
		 * @param null $strCustomDescription
		 */
		public static function errorPage($intErrorCode,$strCustomDescription = null){

			$strReturn = 'Unknown';
			$strDescription = '';

			switch( $intErrorCode ) {
				case 100:
					$strReturn = 'Continue';
					break;

				case 101:
					$strReturn = 'Switching Protocols';
					break;

				case 102:
					$strReturn = 'Processing (WebDAV; RFC 2518)';
					break;

				case 200:
					$strReturn = 'OK';
					break;

				case 201:
					$strReturn = 'Created';
					break;

				case 202:
					$strReturn = 'Accepted';
					break;

				case 203:
					$strReturn = 'Non-Authoritative Information (since HTTP/1.1)';
					break;

				case 204:
					$strReturn = 'No Content';
					break;

				case 205:
					$strReturn = 'Reset Content';
					break;

				case 206:
					$strReturn = 'Partial Content';
					break;

				case 207:
					$strReturn = 'Multi-Status (WebDAV; RFC 4918)';
					break;

				case 208:
					$strReturn = 'Already Reported (WebDAV; RFC 5842)';
					break;

				case 226:
					$strReturn = 'IM Used (RFC 3229)';
					break;

				case 300:
					$strReturn = 'Redirected';
					break;

				case 301:
					$strReturn = 'Moved Permanently';
					break;

				case 302:
					$strReturn = 'Found';
					break;

				case 303:
					$strReturn = 'See Other (since HTTP/1.1)';
					break;

				case 304:
					$strReturn = 'Not Modified';
					break;

				case 305:
					$strReturn = 'Use Proxy (since HTTP/1.1)';
					break;

				case 306:
					$strReturn = 'Switch Proxy';
					break;

				case 307:
					$strReturn = 'Temporary Redirect (since HTTP/1.1)';
					break;

				case 308:
					$strReturn = 'Permanent Redirect (approved as experimental RFC)';
					break;

				case 400:
					$strReturn = 'Bad Request';
					break;

				case 401:
					$strReturn = 'Unauthorized';
					break;

				case 402:
					$strReturn = 'Payment Required';
					break;

				case 403:
					$strReturn = 'Forbidden';
					break;

				case 404:
					$strReturn = 'Not Found';
					$strDescription = 'The requested file was not found';
					break;

				case 405:
					$strReturn = 'Method Not Allowed';
					break;

				case 406:
					$strReturn = 'Not Acceptable';
					break;

				case 407:
					$strReturn = 'Proxy Authentication Required';
					break;

				case 408:
					$strReturn = 'Request Timeout';
					break;

				case 409:
					$strReturn = 'Conflict';
					break;

				case 410:
					$strReturn = 'Gone';
					break;

				case 411:
					$strReturn = 'Length Required';
					break;

				case 412:
					$strReturn = 'Precondition Failed';
					break;

				case 413:
					$strReturn = 'Request Entity Too Large';
					break;

				case 414:
					$strReturn = 'Request-URI Too Long';
					break;

				case 415:
					$strReturn = 'Unsupported media platform';
					break;

				case 416:
					$strReturn = 'Requested Range Not Satisfiable';
					break;

				case 417:
					$strReturn = 'Expectation Failed';
					break;

				case 418:
					$strReturn = 'I\'m a teapot (RFC 2324)';
					break;

				case 419:
					$strReturn = 'Authentication Timeout';
					break;

				case 420:
					$strReturn = 'Enhance Your Calm (Twitter)';
					break;

				case 422:
					$strReturn = 'Unprocessable Entity (WebDAV; RFC 4918)';
					break;

				case 423:
					$strReturn = 'Locked (WebDAV; RFC 4918)';
					break;

				case 424:
					$strReturn = 'Failed Dependency (WebDAV; RFC 4918)/Method Failure (WebDAV)';
					break;

				case 425:
					$strReturn = 'Unordered Collection (Internet draft)';
					break;

				case 426:
					$strReturn = 'Upgrade Required (RFC 2817)';
					break;

				case 428:
					$strReturn = 'Precondition Required (RFC 6585)';
					break;

				case 429:
					$strReturn = 'Too Many Requests (RFC 6585)';
					break;

				case 431:
					$strReturn = 'Request Header Fields Too Large (RFC 6585)';
					break;

				case 444:
					$strReturn = 'No Response (Nginx)';
					break;

				case 449:
					$strReturn = 'Retry With (Microsoft)';
					break;

				case 450:
					$strReturn = 'Blocked by Windows Parental Controls (Microsoft)';
					break;

				case 451:
					$strReturn = 'Unavailable For Legal Reasons (Internet draft)/Redirect (Microsoft)';
					break;

				case 494:
					$strReturn = 'Request Header Too Large (Nginx)';
					break;

				case 495:
					$strReturn = 'Cert Error (Nginx)';
					break;

				case 496:
					$strReturn = 'No Cert (Nginx)';
					break;

				case 497:
					$strReturn = 'HTTP to HTTPS (Nginx)';
					break;

				case 499:
					$strReturn = 'Client Closed Request (Nginx)';
					break;

				case 500:
					$strReturn = 'Internal Server Error';
					break;

				case 501:
					$strReturn = 'Not Implemented';
					break;

				case 502:
					$strReturn = 'Bad Gateway';
					break;

				case 503:
					$strReturn = 'Service Unavailable';
					$strDescription = 'The requested site is currently in maintenance mode, please check back shortly';
					break;

				case 504:
					$strReturn = 'Gateway Timeout';
					break;

				case 505:
					$strReturn = 'HTTP Version Not Supported';
					break;

				case 506:
					$strReturn = 'Variant Also Negotiates (RFC 2295)';
					break;

				case 507:
					$strReturn = 'Insufficient Storage (WebDAV; RFC 4918)';
					break;

				case 508:
					$strReturn = 'Loop Detected (WebDAV; RFC 5842)';
					break;

				case 509:
					$strReturn = 'Bandwidth Limit Exceeded (Apache bw/limited extension)';
					break;

				case 510:
					$strReturn = 'Not Extended (RFC 2774)';
					break;

				case 511:
					$strReturn = 'Network Authentication Required (RFC 6585)';
					break;

				case 598:
					$strReturn = 'Network read timeout error (Unknown)';
					break;

				case 599:
					$strReturn = 'Network connect timeout error (Unknown)';
					break;
			}

			//Output the correct
			$strHttpProtocol = ("HTTP/1.1" === $_SERVER["SERVER_PROTOCOL"]) ? 'HTTP/1.1' : 'HTTP/1.0';

			header(sprintf('%s %d %s',$strHttpProtocol,$intErrorCode,$strReturn),true,$intErrorCode);

			if($intErrorCode == 503){
				header("Retry-After: 3600");
			}

			//Clean the screen output ready for an exception
			ob_clean();

			$arrTags = array(
				'code' => $intErrorCode,
				'title' => $strReturn,
				'description' => (is_null($strCustomDescription)) ? $strDescription : $strCustomDescription,
				'name' => \Twist::framework() -> setting('SITE_NAME'),
				'domain' => \Twist::framework() -> setting('SITE_HOST')
			);

            die(\Twist::View('Exception')->build(sprintf("%s/system/error-page.tpl",TWIST_FRAMEWORK_VIEWS),$arrTags));
		}

		/**
		 * Output the PHP error log to a file, this function is called automatically upon shutdown for the framework.
		 */
		public static function outputLog(){

			if(count(self::$arrErrorLog)){

				if(TWIST_ERROR_LOG){

					$strLog = "";
					foreach(self::$arrErrorLog as $arrEachItem){
						if($arrEachItem['type'] != 'TWIST'){
							$strLog .= sprintf("[%s] %s: [%s] %s - %s [line %s]\n",date('Y-m-d H:i:s'),$arrEachItem['type'],$arrEachItem['number'],$arrEachItem['message'],$arrEachItem['file'],$arrEachItem['code_line']);
						}else{
							$strLog .= sprintf("[%s] %s: %s - %s [line %s]\n",date('Y-m-d H:i:s'),$arrEachItem['type'],$arrEachItem['message'],$arrEachItem['file'],$arrEachItem['code_line']);
						}
					}

					if(defined('TWIST_APP')){

						if(!is_dir(sprintf('%s/Logs',TWIST_APP))){
							mkdir(sprintf('%s/Logs',TWIST_APP),0777,true);
						}

						file_put_contents(sprintf('%s/Logs/php-errors.log',TWIST_APP),$strLog);
					}
				}

				if(TWIST_ERROR_SCREEN){
					echo "<hr/><h1>Twist Error Handler</h1><pre>".print_r(self::$arrErrorLog,true)."</pre>";
				}
			}
		}

		/**
		 * Detect the error type as a string based on an error number.
		 * @param $intErrorNo
		 * @return string
		 */
		public static function getType($intErrorNo){

			$strErrorType = '';
			$strDeprecated = $strDeprecatedUser = 'Unknown';

			if(defined('E_DEPRECATED')){
				$strDeprecated = E_USER_DEPRECATED;
			}

			if(defined('E_USER_DEPRECATED')){
				$strDeprecatedUser = E_USER_DEPRECATED;
			}

			switch($intErrorNo){
				case 'php-exception':
					$strErrorType = "Exception";
					break;

				case E_NOTICE:
				case E_USER_NOTICE:
					$strErrorType = "Notice";
					break;

				case E_WARNING:
				case E_USER_WARNING:
				case E_COMPILE_WARNING:
				case E_CORE_WARNING:
					$strErrorType = "Warning";
					break;

				case $strDeprecated:
				case $strDeprecatedUser:
					$strErrorType = "Deprecated";
					break;

				case E_ERROR:
				case E_USER_ERROR:
				case E_COMPILE_ERROR:
				case E_CORE_ERROR:
				case E_PARSE:
				case E_RECOVERABLE_ERROR:
					$strErrorType = "Fatal Error";
					break;

				default:
					$strErrorType = "Unknown";
					break;
			}

			return $strErrorType;
		}
	}