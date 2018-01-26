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

namespace Twist\Core\Helpers;

use Twist\Classes\Error;

/**
 * Additional file system support that you have always needed but never had. Convert bytes into human readable format, sanitize file names, hash a directory or get its total size in bytes.
 * Recursive file and folder manipulation and the ability to serve files even apply download speed restrictions when running a high traffic site and bandwidth is precious.
 */
class File extends Base{

	protected $strAssetDirectory = null;
	protected $arrDelayedFileStorage = array();
	protected $arrContentTypes = array();

	/**
	 * Load up an instance of the template class for when it is required
	 */
	public function __construct(){
		$jsonContentTypes = file_get_contents(sprintf('%sCore/Data/file/content-types.json',TWIST_FRAMEWORK));
		$this->arrContentTypes = json_decode($jsonContentTypes,true);

		//Register the delayed write shutdown function
		\Twist::framework()->register()->shutdownEvent('delayed-file-write','Twist::File','writeDelayedFiles');
	}

	public function writeDelayedFiles(){

		if(count($this->arrDelayedFileStorage)){

			//Write each file to disk one by one
			foreach($this->arrDelayedFileStorage as $dirFilePath => $mxdData){
				$this->write($dirFilePath,$mxdData);
				unset($this->arrDelayedFileStorage[$dirFilePath]);
			}
		}
	}

	/**
	 * Convert bytes to a human readable size for example 1536 would be converted to 1.5KB
	 *
	 * @param integer $intBytes Size in bytes
	 * @return mixed Returns a human readable data size
	 */
	public function bytesToSize($intBytes){

		$intKilobyte = 1024;
		$intMegabyte = $intKilobyte * 1024;
		$intGigabyte = $intMegabyte * 1024;
		$intTerabyte = $intGigabyte * 1024;

		if(($intBytes >= 0) && ($intBytes < $intKilobyte)){
			$strOut = sprintf('%sB',$intBytes);
		}elseif(($intBytes >= $intKilobyte) && ($intBytes < $intMegabyte)){
			//For Kilobytes make sure to remove odd bits as it is not necessary to see all decimals at this level
			$intKilobytesFull = round($intBytes / $intKilobyte, 2);
			switch(strlen($intKilobytesFull)){
				case 7:
				case 6:
					$strOut = sprintf('%skB',round($intBytes / $intKilobyte, 0));
					break;
				case 5:
					$strOut = sprintf('%skB',round($intBytes / $intKilobyte, 1));
					break;
				case 4:
				default:
					$strOut = sprintf('%skB',round($intBytes / $intKilobyte, 2));
					break;
			}
		}elseif(($intBytes >= $intMegabyte) && ($intBytes < $intGigabyte)){
			$strOut = sprintf('%sMB',round($intBytes / $intMegabyte, 2));
		}elseif(($intBytes >= $intGigabyte) && ($intBytes < $intTerabyte)){
			$strOut = sprintf('%sGB',round($intBytes / $intGigabyte, 2));
		}elseif($intBytes >= $intTerabyte) {
			$strOut = sprintf('%sTB',round($intBytes / $intTerabyte, 2));
		}else{
			$strOut = $intBytes . 'B';
		}
		return str_replace('.00','',$strOut);
	}

	/**
	 * Sanitize a file name to make it more user friendly. Also helps to prevent errors and make a much cleaner file system.
	 *
	 * @param string $strFilename Name to be sanitized
	 * @param bool $blIsFilename Set to true will allow '~' and '.' in file names
	 * @return string Returns the sanitized file name
	 */
	public function sanitizeName($strFilename, $blIsFilename = true){

		//Trim out the non required items
		$strFilename = trim($strFilename," -");

		$strFilename = preg_replace('/[^a-z0-9\s\-_'. ($blIsFilename ? '~\.' : '').']/i','',$strFilename);
		$strFilename = preg_replace('/\s{2,}/',' ',$strFilename);
		$strFilename = preg_replace('/\s/','-',$strFilename);
		$strFilename = preg_replace('/\-{2,}/','-',$strFilename);

		// Only allow one dash separator at a time (and make string lowercase) - only user mb_ if mbstring is enabled in PHP
		return function_exists('mb_strtolower') ? mb_strtolower(preg_replace('/--+/u', '-', $strFilename), 'UTF-8') : strtolower(preg_replace('/--+/u', '-', $strFilename));
	}


	/**
	 * Get the file extension of any file, provide the file or its full path.
	 *
	 * @param string $strFilePath File name/path
	 * @return string Returns the file extension
	 */
	public function extension($strFilePath){

		if(function_exists('pathinfo')){
			$arrData = pathinfo($strFilePath);
			$strExtension = $arrData['extension'];
		}else{
			$strExtension = ltrim(substr(strrchr($strFilePath,'.'),1),'.');
		}

		return $strExtension;
	}

	/**
	 * Get the filename, trim off any path information that is not required.
	 *
	 * @param string $strFile Full path to file including file name
	 * @return string Returns the file name only
	 */
	public function name($strFile){

		if(strstr($strFile,'/')){
			$arrParts = explode('/',$strFile);
			$strFile = array_pop($arrParts);
		}

		return $strFile;
	}

	/**
	 * Get the mime type of a file by its file extension.
	 *
	 * @param string $dirFile Full path to file including file name
	 * @param bool $blReturnDefaultOnly If multiple types available return as array
	 * @return array|mixed|null|string Returns the content type
	 */
	public function mimeType($dirFile,$blReturnDefaultOnly = true){

		$mxdOut = null;
		$strFileExtension = strtolower((!strstr($dirFile,'.')) ? $dirFile : $this->extension($dirFile));

		foreach($this->arrContentTypes as $strType => $mxdData){
			if(array_key_exists($strFileExtension,$mxdData['extensions'])){
				$mxdOut = array($mxdData['extensions'][$strFileExtension]['mine']);
				break;
			}
		}

		if(!is_null($mxdOut) && strstr($mxdOut[0],',')){
			$mxdOut = explode(',',$mxdOut[0]);
		}

		return (is_null($mxdOut)) ? 'application/octet-stream' : ($blReturnDefaultOnly) ? $mxdOut[0] : $mxdOut;
	}

	/**
	 * Get file details by path or extension
	 * @param string $dirFile
	 * @return array
	 */
	public function mimeTypeInfo($dirFile){

		$arrOut = array();
		$strFileExtension = strtolower((!strstr($dirFile,'.')) ? $dirFile : $this->extension($dirFile));

		foreach($this->arrContentTypes as $strType => $mxdData){
			if(array_key_exists($strFileExtension,$mxdData['extensions'])){

				$arrOut = $mxdData['extensions'][$strFileExtension];
				$arrOut['icon'] = sprintf('/%sCore/Resources/%s',ltrim(TWIST_FRAMEWORK_URI,'/'),$mxdData['icon']);
				$arrOut['name'] = $mxdData['name'];
				break;
			}
		}

		return $arrOut;
	}

	/**
	 * Get all mime type information or mime type for a selected type i.e document, spreadsheet, archive
	 * @param null $strType
	 * @return array|mixed
	 */
	public function mimeTypes($strType = null){
		return (!is_null($strType)) ? $this->arrContentTypes[$strType] : $this->arrContentTypes;
	}

	/**
	 * Find a file in a directory when there is multiple of the same file with many different version numbers.
	 *
	 * @param string $strDirectory Path of directory to search
	 * @param string $strFilePrefix File prefix to help filter correct files
	 * @param string $strVersion Version of file to find
	 * @return string Returns file name of verion file
	 */
	public function findVersion($strDirectory,$strFilePrefix,$strVersion = null){

		$strOut = null;
		$arrFiles = scandir(TWIST_DOCUMENT_ROOT.$strDirectory);

		if(is_null($strVersion) || $strVersion === '' || $strVersion === 'latest'){

			$arrOptions = array();

			//Run through and create the version array
			foreach($arrFiles as $strEachFile){

				//Only pick out the correct files that contain the prefix
				if(strstr($strEachFile,$strFilePrefix) && !in_array($strEachFile,array('.','..'))){

					$arrParts = explode('-',$strEachFile);
					$arrVersionNos = explode('.',array_pop($arrParts));

					//Only log files with 4 parts
					if(count($arrVersionNos) === 4){
						$arrOptions[$arrVersionNos[0]][$arrVersionNos[1]][$arrVersionNos[2]] = $strEachFile;
					}
				}
			}

			//Boil down to the latest version
			ksort($arrOptions);
			$arrTopVersion = array_pop($arrOptions);
			ksort($arrTopVersion);
			$arrTopMajor = array_pop($arrTopVersion);
			ksort($arrTopMajor);
			$strOut = array_pop($arrTopMajor);

		}else{

			foreach($arrFiles as $strEachFile){
				if(strstr($strEachFile,sprintf('%s-%s',$strEachFile,$strVersion))){
					$strOut = $strEachFile;
				}
			}
		}

		if(!is_null($strOut)){
			$strOut = sprintf('%s%s',$strDirectory,$strOut);
		}

		return $strOut;
	}

	/**
	 * Serve any local file to the user to be downloaded. Mime type, Max Cache Time and Restricted Download Speed in KB are all optional
	 *
	 * @param string $strFile Full path of file to be served
	 * @param string $strServeAsName Serve the file as the name provided
	 * @param string $strMimeType Mime type to serve file as
	 * @param integer $intMaxCacheTime Set to the max cache time in seconds
	 * @param integer $intMaxTransferRate Set to the Max transfer rate in kb/s
	 * @param bool $blDeleteFile Remove the file after serve, use this when serving a temp file
	 */
	public function serve($strFile,$strServeAsName=null,$strMimeType=null,$intMaxCacheTime=null,$intMaxTransferRate=null,$blDeleteFile=false){

		if(file_exists($strFile)){

			$intFileModifiedTime=filemtime($strFile);

			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $intFileModifiedTime)){
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', $intFileModifiedTime).' GMT', true, 304);
			}else{

				//Fix the IE Shite Browser Download
				if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}

				$strMimeType = (is_null($strMimeType)) ? 'application/force-download' : $strMimeType;
				header(sprintf("Content-Type: %s",$strMimeType));

				if(!is_null($strServeAsName)){
					$strFileBase = $this->sanitizeName($strServeAsName);
				}else{
					$strFileBase = basename($strFile);
					$strFileBase = $this->sanitizeName($strFileBase);
				}

                if(is_null($strMimeType)){
                    //Only set these headers if we are forcing a download
                    header("Content-Transfer-Encoding: binary");
                    header('Content-Description: File Transfer');
                    header('Content-Disposition: attachment; filename='.$strFileBase);
                }else{
                    header('Content-Disposition: inline; filename='.$strFileBase);
                }

				header("Content-Length: ".filesize($strFile));

				//Fix for IE6, IE7 and IE8 browsers when using HTTPS that do not have the following HotFix: http://support.microsoft.com/kb/323308/en-us
				if(preg_match('/MSIE ([6-8]{1}[^;]*);/',$_SERVER['HTTP_USER_AGENT'],$arrMatches) && !empty($_SERVER['HTTPS'])){
					header("Cache-Control: private");
					header("Pragma: private");
				}else{

					if(is_null($intMaxCacheTime)){
						//If no cache time set don't allow caching
						header('Pragma: no-cache');
						header('Expires: 0');
					}else{
						//Server all the cache headers for the file
						header('Pragma: public');
						header('Cache-Control: public, maxage='.$intMaxCacheTime);
						header('Expires: ' . gmdate('D, d M Y H:i:s', \Twist::DateTime()->time()+$intMaxCacheTime) . ' GMT');
					}
				}

				header('Last-Modified: '.gmdate('D, d M Y H:i:s', $intFileModifiedTime).' GMT', true, 200);

				//flush content
				flush();

				if(is_null($intMaxTransferRate)){
					//Read the file to the user as fast as possible
					readfile($strFile);

					if($blDeleteFile){
						unlink($strFile);
					}
				}else{

					// open file stream
					$resFile = fopen($strFile, "r");

					if(is_resource($resFile)){

						while(!feof($resFile)){

							// send the current file part to the browser
							print fread($resFile, round(($intMaxTransferRate / 4) * 1024));

							// flush the content to the browser
							flush();

							// sleep one second (1000000 == 1 second)
							usleep(250000);
							// -- sleep was abit jerky in the browser side sleep(1);
						}

						// close file stream
						fclose($resFile);

						if($blDeleteFile){
							unlink($strFile);
						}
					}else{
						//header("HTTP/1.1 500 Internal Server Error");
						Error::response(500);
					}
				}
			}
		}else{
			//header("HTTP/1.1 404 Not Found");
			Error::response(404);
		}

		die();
	}

	/**
	 * Handle uploaded files, call the function and pass in the html file input name. The file will then be uploaded to the system ready to be processed.
	 * Optionally pass in a UID so that you can reference the temp file to further process the file, can be useful if uploading a file before the user has submitted the form.
	 *
	 * @param string $strFileKey Key for the file in the $_FILES array
	 * @param string $strUID Unique ID used to reference the file after upload
	 * @return array Returns an array of information for the uploaded file
	 */
	public function upload($strFileKey,$strUID = null){

		if(is_null($strUID)){
			$strUID = uniqid();
		}

		$arrOut = array(
			'status' => false,
			'error' => '',
			'file' => array(
				'uid' => $strUID,
				'name' => '',
				'size' => '',
				'path' => '',
			)
		);

		//Create an upload directory for uploaded files
		$strUploadFolder = TWIST_UPLOADS;

		if(!is_dir($strUploadFolder)){
			$this->recursiveCreate($strUploadFolder);
		}

		if(!is_null($strUID) && $strUID != ''){
			if(is_array($_FILES) && array_key_exists($strFileKey,$_FILES)){

				$arrFileData = $_FILES[$strFileKey];

				if($arrFileData['error'] == 0){

					$intMegabytes = str_replace('M','',\Twist::framework()->setting('FILE_MAX_UPLOAD_SIZE'));
					$intMaxSizeInBytes = (($intMegabytes*1024)*1024);

					if($arrFileData['size'] <= $intMaxSizeInBytes){

						$strExtension = $this->extension($arrFileData['name']);

						$strNewFilename = $this->sanitizeName($arrFileData['name']);
						$strNewFilename = str_replace('.'.strtolower($strExtension),'',$strNewFilename);

						$strFile = sprintf('uid%s_%s-%s.%s',$strUID,$strNewFilename,\Twist::DateTime()->time(),strtolower($strExtension));
						move_uploaded_file($arrFileData['tmp_name'],sprintf('%s/%s',$strUploadFolder,$strFile));

						//Set the output data of the uploaded file
						$arrOut['file']['size'] = $arrFileData['size'];
						$arrOut['file']['original_name'] = $strNewFilename;
						$arrOut['file']['name'] = $strFile;
						$arrOut['file']['path'] = sprintf('%s/%s',$strUploadFolder,$strFile);
						$arrOut['status'] = true;
					}else{
						$arrOut['error'] = sprintf("Uploaded file size to large, max file size of %s",\Twist::framework()->setting('FILE_MAX_UPLOAD_SIZE'));
					}
				}else{
					$arrOut['error'] = $_FILES[$strFileKey]["error"];
				}
			}else{
				$arrOut['error'] = sprintf("File key '%s' has not been posted",$strFileKey);
			}
		}else{
			$arrOut['error'] = sprintf("UID has not been set correctly",$strFileKey);
		}

		if($arrOut['error'] != ''){
			trigger_error(sprintf("Twist File Handler: %s",$arrOut['error']));
		}

		return $arrOut;
	}

	/**
	 * Upload a file the easy way with the new AJAX PUT upload function by TwistPHP. Use the template tags and provided JS files to achieve simple file uploads.
	 *
	 * @related upload
	 * @return array Returns an array of information for the uploaded file
	 */
	public function uploadPUT(){

		$arrOut = array(
			'status' => false,
			'error' => '',
			'file' => array(
				'uid' => '',
				'name' => '',
				'size' => 0,
				'path' => '',
			)
		);

		//Create an upload directory for uploaded files
		$strUploadFolder = TWIST_UPLOADS;

		if(!is_dir($strUploadFolder)){
			$this->recursiveCreate($strUploadFolder);
		}

		$strFileData = file_get_contents("php://input");

		if(strlen($strFileData) > 0){

			if(array_key_exists('HTTP_TWIST_FILE',$_SERVER) && array_key_exists('HTTP_TWIST_UID',$_SERVER)){
				$arrOut['file']['uid'] = $_SERVER['HTTP_TWIST_UID'];
				$arrOut['file']['name'] = $_SERVER['HTTP_TWIST_FILE'];
				$arrOut['file']['size'] = strlen($strFileData);
				$arrOut['file']['path'] = sprintf('%s/%s',$strUploadFolder,$_SERVER['HTTP_TWIST_FILE']);

				file_put_contents($arrOut['file']['path'],$strFileData);
				$arrOut['status'] = true;
			}else{
				$arrOut['error'] = 'Incorrect file headers provided';
			}
		}else{
			$arrOut['error'] = 'No file data has been uploaded';
		}

		return $arrOut;
	}

	/**
	 * Download/Copy a remote file over HTTP in chunks, outputting the chunks directly to a local file handler. Download large files to the server without running out of system memory.
	 * @param string $strRemoteFile Full URL to the remote file
	 * @param string $strLocalFile Local path where the file is to be saved
	 * @param int $intMaxRedirects Set to the maximum redirects you will allow
	 * @return array Returns and array of information about the request
	 */
	public function download($strRemoteFile, $strLocalFile,$intMaxRedirects = 0){

		set_time_limit(0);
		$intChunkSize = 1 * (1024 * 1024); // 11 Megabyte
		$intContentLength = 0;
		$intCurrentContentLength = 0;

		$blRedirect = false;
		$arrRedirectData = array();

		//Break the request URL up into usable parts
		$arrURLParts = parse_url($strRemoteFile);
		$resRemoteHandle = fsockopen($arrURLParts['host'], 80, $intErrorCode, $strError, 5);
		$resLocalHandle = fopen($strLocalFile, 'wb');

		if($resRemoteHandle == false || $resLocalHandle == false){
			return array(
				'status' => false,
				'error_message' => "Twist failed to open remote or local resource",
				'redirects' => array()
			);
		}

		if(!empty($arrURLParts['query'])){
			$arrURLParts['path'] = sprintf('%s?%s',$arrURLParts['path'],$arrURLParts['query']);
		}

		//Request the file from the remote server, send the request headers
		$strRequest = sprintf("GET %s HTTP/1.1\r\n",$arrURLParts['path']);
		$strRequest .= sprintf("Host: %s\r\n",$arrURLParts['host']);
		$strRequest .= "User-Agent: TwistPHP/".\Twist::version()."\r\n\r\n";
		//$strRequest .= "Keep-Alive: 115\r\n";
		//$strRequest .= "Connection: keep-alive\r\n\r\n";
		fwrite($resRemoteHandle, $strRequest);

		//Read the headers from the remote server and find the content length
		$arrResponseHeaders = array();
		while(!feof($resRemoteHandle)){

			$strLine = fgets($resRemoteHandle);
			if($strLine == "\r\n"){
				break;
			}

			$arrHeaderParts = explode(':',$strLine);
			$strKey = $arrHeaderParts[0];
			unset($arrHeaderParts[0]);

			if(strstr(strtolower(trim($strKey)),'302 moved')){
				$blRedirect = true;
			}

			$arrResponseHeaders[strtolower(trim($strKey))] = trim(implode(':',$arrHeaderParts));
		}

		if($blRedirect){

			//Close all open file handlers
			fclose($resRemoteHandle);
			fclose($resLocalHandle);

			//Follow the redirects, decrease the count of how many to follow on each iteration
			if(array_key_exists('location',$arrResponseHeaders) && $intMaxRedirects > 0){
				$intMaxRedirects--;
				$arrOut = $this->download($arrResponseHeaders['location'],$strLocalFile,$intMaxRedirects);
				$arrOut['redirects'][] = $arrResponseHeaders['location'];
			}else{
				return array(
					'status' => false,
					'error_message' => "Max redirects exceeded",
					'redirects' => array()
				);
			}

		}else{

			//Find the content length of the data to be downloaded
			if(array_key_exists('content-length',$arrResponseHeaders)){
				$intContentLength = (int)$arrResponseHeaders['content-length'];
			}

			//Read the remote file and write directly into the local file handler
			while(!feof($resRemoteHandle)){
				$intBytes = fwrite($resLocalHandle, fread($resRemoteHandle, $intChunkSize));

				if($intBytes == false){
					return array(
						'status' => false,
						'error_message' => "Twist failed to write data to the local resource",
						'redirects' => array()
					);
				}

				$intCurrentContentLength += $intBytes;

				if($intCurrentContentLength >= $intContentLength){
					//All data required has been read, stop here
					break;
				}
			}

			//Close all open file handlers
			fclose($resRemoteHandle);
			fclose($resLocalHandle);

			$arrOut = array(
				'status' => true,
				'response-headers' => $arrResponseHeaders,
				'content-length' => $intCurrentContentLength,
				'error_no' => $intErrorCode,
				'error_message' => $strError,
				'redirects' => array()
			);
		}

		return $arrOut;
	}

	/**
	 * Basic alias function of PHP's hash_file, hash a file on the local server
	 *
	 * @reference http://php.net/manual/en/function.hash-file.php
	 * @param string $strFilePath Path to the file
	 * @param string $strHashAlgorithm Set the hash algorithm 'md5' or 'sha1'
	 * @return string Returns a hash of the file
	 */
	public function hash($strFilePath, $strHashAlgorithm='md5'){
		return hash_file($strHashAlgorithm,$strFilePath);
	}

	/**
	 * Get a unique Hash of a directory in MD5 or SHA1. If any single item within the directory or sub-directories changes the unique hash will change as well.
	 *
	 * @related hash
	 * @param string $dirPath Path of the directory
	 * @param string $strHashAlgorithm Set the hash algorithm 'md5' or 'sha1'
	 * @return bool|string
	 */
	public function directoryHash($dirPath, $strHashAlgorithm='md5'){

		$arrHashes = array();

		if(is_dir($dirPath)){

			$arrHashes = array();
			$resDir = dir($dirPath);

			while(false !== ($strEntry = $resDir->read())){
				if($strEntry != '.' && $strEntry != '..'){
					if(is_dir($dirPath.'/'.$strEntry)){
						$arrHashes[] = $this->directoryHash($dirPath.'/'.$strEntry,$strHashAlgorithm);
					}else{
						$arrHashes[] = $this->hash($dirPath.'/'.$strEntry,$strHashAlgorithm);
					}
				}
			}

			$resDir->close();
		}

		return ($strHashAlgorithm === 'md5') ? md5(implode('', $arrHashes)) : sha1(implode('', $arrHashes));
	}

	/**
	 * Get the full size in bytes of any directory by providing its full path. Optional parameter to format the return data in a human readable format.
	 *
	 * @param string $dirPath Path of the directory
	 * @param bool $blFormatOutput Set 'true' to format output
	 * @return mixed Returns the size in bytes or a human readable format
	 */
	public function directorySize($dirPath, $blFormatOutput=false){

		$intSizeBytes = 0;

		if(is_dir($dirPath)){

			$resDir = dir($dirPath);

			while(false !== ($strEntry = $resDir->read())){
				if ($strEntry != '.' && $strEntry != '..'){
					if (is_dir($dirPath.'/'.$strEntry)){
						$intSizeBytes += $this->directorySize($dirPath.'/'.$strEntry);
					}else{
						$intSizeBytes += filesize($dirPath.'/'.$strEntry);
					}
				}
			}
			$resDir->close();
		}

		return ($blFormatOutput) ? $this->bytesToSize($intSizeBytes) : $intSizeBytes;
	}

	/**
	 * Check to see if a file exists, this also checks delayed files by default
	 * @param string $dirFilePath
	 * @param bool $blCheckDelayedFiles
	 * @return bool
	 */
	public function exists($dirFilePath,$blCheckDelayedFiles = true){
		return (file_exists($dirFilePath) || $blCheckDelayedFiles && array_key_exists($dirFilePath,$this->arrDelayedFileStorage));
	}

	/**
	 * Read a file from the server, applies the appropriate file locks when reading from the file
	 * @param string $dirFilePath Full path to the file to be read
	 * @param int $intBytesStart
	 * @param null $intBytesEnd
	 * @param bool $blCheckDelayedFiles
	 * @return string
	 * @throws \Exception
	 */
	public function read($dirFilePath,$intBytesStart = 0,$intBytesEnd = null,$blCheckDelayedFiles = true){

		$strOut = '';

		if($blCheckDelayedFiles && array_key_exists($dirFilePath,$this->arrDelayedFileStorage)){
			if(($intBytesStart === 0 && is_null($intBytesEnd)) || (is_null($intBytesStart) && is_null($intBytesEnd))){
				return $this->arrDelayedFileStorage[$dirFilePath];
			}else{
				return substr($this->arrDelayedFileStorage[$dirFilePath], $intBytesStart, $intBytesEnd);
			}
		}else{

			// Get the data from the file
			try{

				$resFileHandler = fopen($dirFilePath, "r");

				if(flock($resFileHandler, LOCK_SH)){

					if(($intBytesStart === 0 && is_null($intBytesEnd)) || (is_null($intBytesStart) && is_null($intBytesEnd))){
						$strOut = file_get_contents($dirFilePath);
					}else{

						if($intBytesStart != 0 && !is_null($intBytesStart)){
							fseek($resFileHandler, $intBytesStart);
						}else{
							$intBytesStart = 0;
						}

						$strOut = fread($resFileHandler, ($intBytesEnd != 0 && !is_null($intBytesEnd) ? $intBytesEnd - $intBytesStart : filesize($dirFilePath)));
					}
				}

				fclose($resFileHandler);
				return $strOut;

			}catch(\Exception $resException){
				throw new \Exception('TwistPHP File::read() - '.$resException->getMessage());
			}
		}
	}

	/**
	 * Write a file to disk and apply the appropriate file locks, delayed file writing is also available to store the file upon shutdown of the PHP process (after the user data has been served)
	 * @param string $dirFilePath Full path to the file to be created/stored
	 * @param mixed $mxdData Data to be stored in the file
	 * @param null $strOptions pass in either null, prefix or suffix
	 * @param bool $blDelayedWrite Store file in memory and write to disk after script has finished
	 * @throws \Exception
	 */
	public function write($dirFilePath,$mxdData,$strOptions = null,$blDelayedWrite = false){

		if($blDelayedWrite){
			$this->arrDelayedFileStorage[$dirFilePath] = $mxdData;
		}else{
			try{

				//Create the required path if not currently existing
				if(!is_dir(dirname($dirFilePath))){
					$this->recursiveCreate(dirname($dirFilePath));
				}

				$resFileHandler = fopen($dirFilePath, "w+");

				if(flock($resFileHandler, LOCK_EX)){

					//Allow for the writing of file data to the beginning/end of a file (default Replaces all data)
					if($strOptions === 'prefix'){
						fseek($resFileHandler, 0);
					}elseif($strOptions === 'suffix'){
						fseek($resFileHandler, filesize($dirFilePath));
					}else{
						ftruncate($resFileHandler, 0);
					}

					fwrite($resFileHandler, $mxdData);
					flock($resFileHandler, LOCK_UN);
				}

				fclose($resFileHandler);
				chmod($dirFilePath, 0755);

			}catch(\Exception $resException){
				throw new \Exception('TwistPHP File::write() - '.$resException->getMessage());
			}
		}
	}

	/**
	 * Basic alias function of PHP's unlink, removes a file or symlink from the local server
	 *
	 * @reference http://php.net/manual/en/function.unlink.php
	 * @param string $dirFilePath Path of the file to be removed
	 * @param bool $blIncludeDelayedFiles
	 * @return bool Return the status of the removal
	 */
	public function remove($dirFilePath,$blIncludeDelayedFiles = true){

		if($blIncludeDelayedFiles && array_key_exists($dirFilePath,$this->arrDelayedFileStorage)){
			unset($this->arrDelayedFileStorage[$dirFilePath]);
		}

		return (file_exists($dirFilePath) || is_link($dirFilePath)) ? unlink($dirFilePath) : false;
	}

	/**
	 * @alias remove
	 * @param string $strFilePath
	 * @param bool $blIncludeDelayedFiles
	 * @return bool
	 */
	public function delete($strFilePath,$blIncludeDelayedFiles = true){ return $this->remove($strFilePath,$blIncludeDelayedFiles); }

	/**
	 * Recursively remove a directory and all its files and sub directories on the local server
	 *
	 * @related remove
	 * @param string $strDirectory Path of the directory to be removed
	 */
	public function recursiveRemove($strDirectory){

		if(file_exists($strDirectory) && is_dir($strDirectory)){

			$arrFiles = scandir($strDirectory);

			foreach ($arrFiles as $strEachFile){
				($strEachFile != "." && $strEachFile != "..") ? $this->recursiveRemove(sprintf('%s/%s',$strDirectory,$strEachFile)) : null;
			}

			if(file_exists($strDirectory) && is_dir($strDirectory)){
				rmdir($strDirectory);
			}

		}elseif(file_exists($strDirectory) || is_link($strDirectory)){
			$this->remove($strDirectory);
		}
	}

	/**
	 * Basic alias function of PHP's rename, move/rename a file on the local server
	 *
	 * @reference http://php.net/manual/en/function.rename.php
	 * @param string $strSourcePath Path of file to be moved
	 * @param string $strDestinationPath Destination path and name for moved file
	 * @return bool Returns the status of the move
	 */
	public function move($strSourcePath, $strDestinationPath){
		return rename($strSourcePath, $strDestinationPath);
	}

	/**
	 * Basic alias function of PHP's copy, copy a file on the local server
	 *
	 * @reference http://php.net/manual/en/function.copy.php
	 * @param string $strSourcePath Path of file to be copied
	 * @param string $strDestinationPath Destination path and name for copied file
	 * @return bool Returns the status of the copied
	 */
	public function copy($strSourcePath,$strDestinationPath){
		return copy($strSourcePath, $strDestinationPath);
	}

	/**
	 * Recursively copy a directory and all its files and sub-directories to a new location on the local server
	 *
	 * @related copy
	 * @param string $strSourcePath
	 * @param string $strDestinationPath
	 */
	public function recursiveCopy($strSourcePath,$strDestinationPath){

		//Remove data from the destination if already exists
		(file_exists($strDestinationPath)) ? $this->recursiveRemove($strDestinationPath) : null;

		if(is_dir($strSourcePath)){

			$this->recursiveCreate($strDestinationPath);
			$arrFiles = scandir($strSourcePath);

			//For each file and folder in the array recursively copy it
			foreach($arrFiles as $strEachFile){
				if($strEachFile != "." && $strEachFile != ".."){
					//Recursively copy as this is another directory
					$this->recursiveCopy("$strSourcePath/$strEachFile", sprintf('%s/%s',$strDestinationPath,$strEachFile));
				}
			}
		}elseif(file_exists($strSourcePath)){
			//If the source is a file copy to the destination
			$this->copy($strSourcePath, $strDestinationPath);
		}
	}

	/**
	 * Recursively create a directory on the local server
	 *
	 * @param string $strDirectoryPath New directory path
	 * @param int $intMode CHMOD creation mode
	 * @return boolean Returns that status of the new directory
	 */
	public function recursiveCreate($strDirectoryPath,$intMode = 0777){

		if(!file_exists($strDirectoryPath) && mkdir($strDirectoryPath,$intMode,true) == false){
			trigger_error(sprintf("Twist File Handler: Failed to create directory %s",$strDirectoryPath));
			return false;
		}

		return true;
	}

	/**
	 * Output the HTML upload form and relevant javascript
	 *
	 * Template Tags:
	 * upload
	 * upload-html
	 * upload-init
	 * upload-js
	 *
	 * Parameters:
	 * name      = string (default: 'file')
	 * multiple  = 0 or 1 (default: 0)
	 * accept    = pipe separated string of file extensions and/or MIME types
	 *
	 * There is no required order for the parameters, examples include:
	 * {file:upload}
	 * {file:upload,multiple=1}
	 * {file:upload,name=foo}
	 * {file:upload,accept=png}
	 * {file:upload,accept=.png|.mov}
	 * {file:upload,accept=video/*|.png,multiple=1}
	 * {asset:upload}
	 *
	 * @param string $strReference
	 * @param array $arrParameters
	 * @return string
	 */
	public function viewExtension($strReference,$arrParameters = array()){

		$strOut = $strAccept = '';

		$arrDefaultParams = array(
			'uri' => str_replace('%','file',(defined('UPLOAD_ROUTE_URI')) ? UPLOAD_ROUTE_URI : '/upload/%'),
			'name' => 'file',
			'id' => uniqid(),
			'multiple' => 0,
			'accept' => '',
			'value' => ''
		);

		if(array_key_exists('multiple', $arrParameters) && $arrParameters['multiple'] !== false) {
			$arrParameters['multiple'] = '1';
		}

		$arrParameters = \Twist::framework()->tools()->arrayMergeRecursive($arrDefaultParams,$arrParameters);

		//Now update the URI if only relative is passed in
		if(substr($arrParameters['uri'],0,1) != '/'){
			$arrParameters['uri'] = str_replace('%',$arrParameters['uri'],(defined('UPLOAD_ROUTE_URI')) ? UPLOAD_ROUTE_URI : '/upload/%');
		}

		$strAcceptExtensions = array();
		$strAcceptTypes = array();
		//Get the mime types of the
		if((is_array($arrParameters['accept']) && count($arrParameters['accept'])) || $arrParameters['accept'] != ''){

			if(!is_array($arrParameters['accept'])){
				$arrParameters['accept'] = array($arrParameters['accept']);
			}

			$arrTypes = array();

			foreach($arrParameters['accept'] as $strAccept){

				//Use as key to avoid duplication
				if(substr($strAccept,0,1) === '.'){
					$strAcceptExtensions[] = $strAccept;
				}else if(strpos($strAccept,'/') === false){
					$strMimeType = implode(',',$this->mimeType($strAccept,false));
					$strAcceptTypes[] = $strMimeType;
				} else {
					$strAcceptTypes[] = $strAccept;
				}
			}
		}

		switch($strReference){

			case 'upload':
			case 'asset-upload':

				$arrTags = array(
					'uniqid' => $arrParameters['id'],
					'name' => $arrParameters['name'],
					'uri' => $arrParameters['uri'],
					'include-js' => (is_null(\Twist::Cache()->read('asset-js-include'))) ? 1 : 0,
					'multiple' => (array_key_exists('multiple', $arrParameters) && $arrParameters['multiple'] != '0') ? 1 : 0,
					'accept' => $strAccept,
					'acceptTypes' => json_encode($strAcceptTypes),
					'acceptExtensions' => json_encode($strAcceptExtensions),
					'value' => $arrParameters['value']
				);

				//Store a temp session for js output
				\Twist::Cache()->write('asset-js-include',1,0);

				$strOut = \Twist::View()->build(sprintf('%s/file/upload.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags);
				break;

			case 'upload-html':
			case 'asset-upload-html':

				$arrTags = array(
					'uniqid' => $arrParameters['id'],
					'name' => $arrParameters['name'],
					'uri' => $arrParameters['uri'],
					'include-js' => (is_null(\Twist::Cache()->read('asset-js-include'))) ? 1 : 0,
					'multiple' => (array_key_exists('multiple', $arrParameters) && $arrParameters['multiple'] != '0') ? 1 : 0,
					'accept' => $strAccept,
					'acceptTypes' => json_encode($strAcceptTypes),
					'acceptExtensions' => json_encode($strAcceptExtensions),
					'value' => $arrParameters['value']
				);

				//Store a temp session for js output
				\Twist::Cache()->write('asset-js-include',1,0);

				$strOut = \Twist::View()->build(sprintf('%s/file/upload-html.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags);
				break;

			case 'upload-init':
			case 'asset-upload-init':

				$arrTags = array(
					'uniqid' => $arrParameters['id'],
					'name' => $arrParameters['name'],
					'uri' => $arrParameters['uri'],
					'function' => ($strReference === 'asset-upload-init') ? 'asset' : 'file'
				);

				$strOut = \Twist::View()->build(sprintf('%s/file/upload-init.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags);
				break;

			case 'upload-js':
			case 'asset-upload-js':

				$strOut = '';

				if(is_null(\Twist::Cache()->read('asset-js-include'))){
					$strOut = \Twist::View()->build(sprintf('%s/file/upload-js.tpl',TWIST_FRAMEWORK_VIEWS));

					//Store a temp session for js output
					\Twist::Cache()->write('asset-js-include',1,0);
				}

				break;
		}

		return $strOut;
	}
}