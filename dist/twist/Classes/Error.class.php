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

	namespace Twist\Classes;
	use Twist\Core\Models\String\SyntaxHighlight;

	/**
	 * A custom error handler that handles all of TwistPHPs Errors, Exceptions and Fatal Errors, it also handles errors produced in your PHP code and outputs all HTTP status pages.
	 * @package Twist\Classes
	 */
	class Error{

		public static $arrErrorLog = array();

		function __construct(){

		}

		/**
		 * Get an array of the Apache request headers.
		 *
		 * @return array
		 */
		public static function apacheRequestHeaders(){ //TODO: Needs to be re-written/optimised

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
		 * Handle a PHP Exceptions and PHP7 Throwable Fatal Errors and output the twist exception page. The exception page will be more detailed when the framework is set in development mode (see the TwistPHP setting DEVELOPMENT_MODE).
		 * @param \Exception|\Throwable $resException
		 * @param array $arrError
		 */
		public static function handleException($resException,$arrError = array()){

			//Capture PHP7 throwable errors and turn then into a regular PHP Exception
			if(!$resException instanceof \Exception) {
				$resException = new Throwable($resException);
			}

            $strExceptionTemplate = sprintf("%s/system/exception-user.tpl",TWIST_FRAMEWORK_VIEWS);

            //Clean the screen output ready for an exception
            if(in_array('ob_gzhandler', ob_list_handlers())){
                ob_clean();
            }

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

				$arrTags['php_code'] = SyntaxHighlight::file($resException->getFile(),'em',$resException->getLine(),5);

				$arrTags['trace'] = '';
				if(count($resException->getTrace())){
					$arrTags['trace'] = '<h3>Backtrace</h3>';
					foreach($resException->getTrace() as $arrEachCall){
						if(array_key_exists('file',$arrEachCall)){
							$arrTags['trace'] .= sprintf('<pre class="code" lang="php" title="%s">%s</pre>',$arrEachCall['file'],SyntaxHighlight::file($arrEachCall['file'],'em',$arrEachCall['line'],2));
						}
					}
				}

				self::handleError($arrTags['type_code'],$arrTags['message'],$arrTags['file'],$arrTags['line']);
			}

			//Output the correct
			$strHttpProtocol = ("HTTP/1.1" === $_SERVER["SERVER_PROTOCOL"]) ? 'HTTP/1.1' : 'HTTP/1.0';

			//Output a 500 Error response for an exception page (this page should not have a 200 status code)
			header(sprintf('%s %d Internal Server Error',$strHttpProtocol,500),true,500);

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

		protected static function debugDataOutput($arrData){

			$strOut = '';

			if(is_array($arrData) && count($arrData)){
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
		 * @param integer $intErrorNo
		 * @param string $strError
		 * @param string $strErrorFile
		 * @param integer $intErrorLine
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
					'code' => SyntaxHighlight::file($strErrorFile,'em',$intErrorLine,3)
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
			if(self::getType($arrLastError['type']) === "Fatal Error"){

				//throw new Exception($arrLastError['message']);
				//A fatal error has occurred, throw and exception instead.
				$objException = new Exception($arrLastError['message'],1,$arrLastError['file'],$arrLastError['line']);

				self::handleException($objException,$arrLastError);
			}
		}

		/**
		 * Output a 404 page to the user
		 */
		public static function handle404(){
			self::response(404);
		}

		/**
		 * Output HTTP error response code, This function has been deprecated in favour of the response() method
		 * @param int $intErrorCode
		 * @param null|string $strCustomDescription
		 * @param boolean $blExitOnComplete Set false will output error and continue (Used for testing)
		 * @alias response
		 * @deprecated
		 */
		public static function errorPage($intErrorCode,$strCustomDescription = null,$blExitOnComplete = true){
			self::response($intErrorCode,$strCustomDescription,$blExitOnComplete);
		}

		public static function responseInfo($intErrorCode){
			
			$arrOut = array(
				'code' => $intErrorCode,
				'return' => 'Unknown',
				'description' => ''
			);

			$jsonResponses = file_get_contents(sprintf('%sCore/Data/http/response-codes.json',TWIST_FRAMEWORK));
			$arrResponses = json_decode($jsonResponses,true);

			if(array_key_exists($intErrorCode,$arrResponses)){
				$arrOut['return'] = $arrResponses[$intErrorCode]['return'];
				$arrOut['description'] = $arrResponses[$intErrorCode]['description'];
			}

			return $arrOut;
		}
		
		/**
		 * Output HTTP error response code and a custom message if required to the user, this function handles all HTTP response codes.
		 * @param int $intErrorCode
		 * @param null|string $strCustomDescription
		 * @param boolean $blExitOnComplete Set false will output page and continue (Used for testing)
		 */
		public static function response($intErrorCode,$strCustomDescription = null,$blExitOnComplete = true){

			$arrResponse = self::responseInfo($intErrorCode);
			
			//Output the correct
			$strHttpProtocol = ("HTTP/1.1" === $_SERVER["SERVER_PROTOCOL"]) ? 'HTTP/1.1' : 'HTTP/1.0';

			header(sprintf('%s %d %s',$strHttpProtocol,$intErrorCode,$arrResponse['return']),true,$intErrorCode);

			if($intErrorCode == 503){
				header("Retry-After: 3600");
			}

			//Clean the screen output ready for an exception
			ob_clean();

			$arrTags = array(
				'code' => $intErrorCode,
				'title' => $arrResponse['return'],
				'description' => (is_null($strCustomDescription)) ? $arrResponse['description'] : $strCustomDescription,
				'name' => \Twist::framework() -> setting('SITE_NAME'),
				'domain' => \Twist::framework() -> setting('SITE_HOST')
			);

			if($blExitOnComplete){
				die(\Twist::View('Exception')->build(sprintf("%s/system/error-page.tpl",TWIST_FRAMEWORK_VIEWS),$arrTags));
			}else{
				echo \Twist::View('Exception')->build(sprintf("%s/system/error-page.tpl",TWIST_FRAMEWORK_VIEWS),$arrTags);
			}
		}

		/**
		 * Output the PHP error log to a file, this function is called automatically upon shutdown for the framework.
		 */
		public static function outputLog(){

			if(count(self::$arrErrorLog)){

				//Get the PHP Error Log setting
				$strErrorLogType = strtoupper(\Twist::framework() -> setting('ERROR_LOG'));
				if($strErrorLogType != 'OFF'){

					$intMaxErrorLogs = \Twist::framework() -> setting('ERROR_LOGS_MAX');

					switch($strErrorLogType){
						case'DAILY':
							$strLogFile = sprintf('%s/Logs/php-errors_%s.log',TWIST_APP,date('Y-m-d'));
							break;

						case'WEEKLY':
							$strLogFile = sprintf('%s/Logs/php-errors_%s.log',TWIST_APP,date('Y').'-week'.str_pad(date('W'), 2, "0", STR_PAD_LEFT));
							break;

						case'MONTHLY':
							$strLogFile = sprintf('%s/Logs/php-errors_%s.log',TWIST_APP,date('Y-m'));
							break;

						case'SINGLE':
						default:
							$strLogFile = sprintf('%s/Logs/php-errors.log',TWIST_APP);

							//If a single log file has reached the split level (5MB) then delete it of split it
							if(filesize($strLogFile) >= 5242880){
								if($intMaxErrorLogs > 1){
									\Twist::File()->move($strLogFile,sprintf('%s/Logs/php-errors_%s.log',TWIST_APP,date('Y-m-d')));
								}else{
									\Twist::File()->delete($strLogFile);
								}
							}

							break;
					}

					$strErrorLog = "";
					foreach(self::$arrErrorLog as $arrEachItem){
						if($arrEachItem['type'] != 'TWIST'){
							$strErrorLog .= sprintf("[%s] %s: [%s] %s - %s [line %s]\n",date('Y-m-d H:i:s'),$arrEachItem['type'],$arrEachItem['number'],$arrEachItem['message'],$arrEachItem['file'],$arrEachItem['code_line']);
						}else{
							$strErrorLog .= sprintf("[%s] %s: %s - %s [line %s]\n",date('Y-m-d H:i:s'),$arrEachItem['type'],$arrEachItem['message'],$arrEachItem['file'],$arrEachItem['code_line']);
						}
					}

					//Output the errors to the error log
					if(defined('TWIST_APP')){
						file_put_contents($strLogFile,$strErrorLog,FILE_APPEND);
					}

					//Get the Error Log storage age
					$arrErrorLogs = array();
					$strLogFolder = sprintf('%s/Logs/',TWIST_APP);

					foreach(scandir($strLogFolder) as $strEachFile){

						//Check to see we are looking at an php error file
						if($strErrorLogType == 'SINGLE' && $strEachFile == 'php-errors.log'){
							$arrErrorLogs['zzz-'.$strEachFile] = sprintf('%s%s',$strLogFolder,$strEachFile);
						}elseif(strstr($strEachFile,'php-errors')){
							$arrErrorLogs[$strEachFile] = sprintf('%s%s',$strLogFolder,$strEachFile);
						}
					}

					ksort($arrErrorLogs);
					while(count($arrErrorLogs) > $intMaxErrorLogs){
						\Twist::File()->delete(array_shift($arrErrorLogs),true);
					}
				}

				if(\Twist::framework() -> setting('ERROR_SCREEN')){
					echo "<hr/><h1>Twist Error Handler</h1><pre>".print_r(self::$arrErrorLog,true)."</pre>";
				}
			}
		}

		/**
		 * Detect the error type as a string based on an error number.
		 * @param integer $intErrorNo
		 * @return string
		 */
		public static function getType($intErrorNo){

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