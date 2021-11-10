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

	namespace Twist\Core\Models\FTP;

	class Socket{

		protected $resConnection = null;
		protected $strLastMessage = '';
		protected $intTimeout = 90;
		protected $blPassiveMode = true;
		protected $strError = '';

		public function getMessage(){
			return $this->strError;
		}

		public function setTimeout($intTimeout = 90){
			$this->intTimeout = (is_null($intTimeout)) ? 90 : $intTimeout;
		}

		/**
		 * Open a new FTP connection
		 * @param string $strHost
		 * @param integer $intPort
		 * @throws \Exception
		 */
		public function connect($strHost,$intPort){

			/**
			 * May be a valid way to specify the IP address of the server when on a server with multiple IP addresses
			 */
			//$strInterfaceIP = '192.168.1.100'
			//$this->resConnection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			//socket_bind($this->resConnection, $strInterfaceIP);
			//socket_connect($this->resConnection, $strHost, $intPort);

			$this->resConnection = fsockopen($strHost, $intPort);

			stream_set_blocking($this->resConnection, true);
			stream_set_timeout($this->resConnection, $this->intTimeout);

			if($this->communicate() !== 220){
				$this->strError = 'Failed to connect to the FTP Server.';
			}

			return (!empty($this->resConnection));
		}

		/**
		 * Disconnect the current session (connection)
		 */
		public function disconnect(){
			$this->communicate('QUIT');
			fclose($this->resConnection);
			$this->resConnection = null;
		}

		/**
		 * Login to the open FTP connection
		 * @param string $strUsername
		 * @param string $strPassword
		 * @return bool
		 */
		public function login($strUsername,$strPassword){

			$blStatus = false;
			if(!empty($this->resConnection)){
				if(($this->communicate(sprintf('USER %s',$strUsername)) === 331 && $this->communicate(sprintf('PASS %s',$strPassword)) === 230)){
					$blStatus = true;
				}else{
					$this->strError = 'Invalid FTP login credentials provided';
				}
			}else{
				$this->strError = 'No FTP connection has been established yet';
			}

			return $blStatus;
		}

		public function pasv($blEnable = true){
			$this->blPassiveMode = $blEnable;
		}

		/**
		 * Get the system name for the FTP connection
		 * @return bool
		 */
		public function systype(){

			$mxdOut = false;

			if($this->communicate('SYST') === 215){
				$strParts = explode(' ',$this->strLastMessage);
				$mxdOut = $strParts[1];
			}

			return $mxdOut;
		}

		/**
		 * Get an array of supported features for the current FTP server connection
		 * @return array|bool
		 */
		public function feat(){

			$mxdOut = false;

			if($this->communicate('FEAT') === 211){

				$arrLines = explode("\n",$this->strLastMessage);
				$arrLines = array_map('trim', $arrLines);
				$arrLines = array_filter($arrLines);
				array_shift($arrLines);

				if(count($arrLines)){
					$mxdOut = array();
					foreach($arrLines as $strEachLine){
						$arrParts = explode(' ',$strEachLine);
						$mxdOut[$arrParts[0]] = $strEachLine;
					}
				}
			}

			return $mxdOut;
		}

		/**
		 * Make Directory
		 * @param string $strDirectory
		 * @return bool
		 */
		public function mkd($strDirectory){
			return ($this->communicate(sprintf('MKD %s', $strDirectory)) === 257);
		}

		/**
		 * Remove Directory
		 * @param string $strDirectory
		 * @return bool
		 */
		public function rmd($strDirectory){
			return ($this->communicate(sprintf('RMD %s', $strDirectory)) === 250);
		}

		/**
		 * Print current working directory
		 * @return bool|string
		 */
		public function pwd(){

			$mxdOut = false;

			if($this->communicate('PWD') === 257){
				preg_match('#\"(.*)\"#',$this->strLastMessage,$arrMatches);
				$mxdOut = $arrMatches[0];
			}

			return $mxdOut;
		}

		/**
		 * Change working directory
		 * @param string $strDirectory
		 * @return bool
		 */
		public function cwd($strDirectory){
			return ($this->communicate(sprintf('CWD %s', $strDirectory)) === 250);
		}

		/**
		 * Rename a directory or file to a new name
		 * @param string $strFilename
		 * @param string $strNewFilename
		 * @return bool
		 */
		public function rename($strFilename, $strNewFilename){
			return ($this->communicate(sprintf('RNFR %s', $strFilename)) === 350 && $this->communicate(sprintf('RNTO %s', $strNewFilename)) === 250);
		}

		/**
		 * Remove the file from the server
		 * @param string $strFilename
		 * @return bool
		 */
		public function delete($strFilename){
			return ($this->communicate(sprintf('DELE %s', $strFilename)) === 250);
		}

		/**
		 * CHMOD the files permissions
		 * @param string $strFilename
		 * @param integer $intMode
		 * @return bool
		 */
		public function chmod($strFilename, $intMode){
			return ($this->communicate(sprintf('SITE CHMOD %o %s', $intMode, $strFilename)) === 200);
		}

		/**
		 * Download a file from the remote FTP server
		 * @param string $strRemoteFilename
		 * @param string $strLocalFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function download($strRemoteFilename, $strLocalFilename, $strMode = 'A'){

			//$modes = array(
			//	self::MODE_ASCII => 'A',
			//	self::MODE_BINARY => 'I',
			//);

			//if ( array_key_exists($mode, $modes) === false )
			//{
			//	throw new InvalidArgumentException(sprintf('Invalid mode "%s" was given', $mode));
			//}

			$blOut = false;
			$resLocalFile = fopen($strLocalFilename, 'wb');

			if(is_resource($resLocalFile)){
				if($this->communicate(sprintf('TYPE %s',$strMode)) === 200){

					$resPassiveConnection = $this->openPassiveConnection();

					if(is_resource($resPassiveConnection) && $this->communicate(sprintf('RETR %s',$strRemoteFilename)) === 150){

						while(feof($resPassiveConnection) === false){
							fwrite($resLocalFile,fread($resPassiveConnection,10240),10240);
						}

						$blOut = true;
						$this->closePassiveConnection($resPassiveConnection);
					}
				}

				fclose($resLocalFile);
			}

			return $blOut;
		}

		/**
		 * Upload a file to the remote FTP server
		 * @param string $strLocalFilename
		 * @param string $strRemoteFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function upload($strLocalFilename, $strRemoteFilename, $strMode = 'A'){

			$blOut = false;
			$resLocalFile = fopen($strLocalFilename, 'rb');

			if(is_resource($resLocalFile)){
				if($this->communicate(sprintf('TYPE %s',$strMode)) === 200){

					$resPassiveConnection = $this->openPassiveConnection();

					if(is_resource($resPassiveConnection) && $this->communicate(sprintf('STOR %s',$strRemoteFilename)) === 150){

						while(feof($resLocalFile) === false){
							fwrite($resPassiveConnection,fread($resLocalFile,10240),10240);
						}

						$blOut = true;
						$this->closePassiveConnection($resPassiveConnection);
					}
				}

				fclose($resLocalFile);
			}

			return $blOut;
		}

		/**
		 * List the provided directory and return as an array
		 * @param string $strDirectory
		 * @return array|bool
		 */
		public function nlist($strDirectory){

			$mxdOut = false;
			$resPassiveConnection = $this->openPassiveConnection();

			if(is_resource($resPassiveConnection) && $this->communicate(sprintf('NLST %s', $strDirectory)) === 150){

				$mxdData = '';
				while(feof($resPassiveConnection) === false){
					$mxdData .= fread($resPassiveConnection, 1024);
				}

				$mxdOut = preg_split("#[\n\r]+#", trim($mxdData));
				$this->closePassiveConnection($resPassiveConnection);
			}

			return $mxdOut;
		}

		/**
		 * Get the size of any given file on the remote FTP server
		 * @param string $strFilename
		 * @return bool|int
		 */
		public function size($strFilename){

			$mxdOut = false;

			if($this->communicate(sprintf('SIZE %s', $strFilename)) === 213){

				if(preg_match('#^[0-9]{3} ([0-9]+)$#',$this->strLastMessage,$arrMatches)){
					$mxdOut = intval($arrMatches[0]);
				}
			}

			return $mxdOut;
		}

		/**
		 * Get the last modified time of any given file on the remote FTP server
		 * @param string $strFilename
		 * @return bool|int
		 */
		public function getModifiedDateTime($strFilename){

			$mxdOut = false;

			if($this->communicate(sprintf('MDTM %s', $strFilename)) === 213){

				if(preg_match('#^[0-9]{3} ([0-9]{14})$#', $this->strLastMessage, $arrMatches)){
					$mxdOut = strtotime($arrMatches[0].' UTC');
				}
			}

			return $mxdOut;
		}

		/**
		 * Open up a passive connection to be used for the next FTP communication
		 * @return bool|resource
		 */
		protected function openPassiveConnection(){

			$resPassiveConnection = false;

			if($this->communicate('PASV') === 227){

				if(preg_match("#\(([0-9,]+),([0-9]+),([0-9]+)\)#",$this->strLastMessage,$arrMatches)){

					$strPassiveHost = str_replace(',','.',$arrMatches[0]);
					$intPassivePort = ($arrMatches[1] * 256) + $arrMatches[2];

					$resPassiveConnection = fsockopen($strPassiveHost,$intPassivePort);

					if(is_resource($resPassiveConnection)){
						stream_set_blocking($resPassiveConnection, true);
						stream_set_timeout($resPassiveConnection, $this->intTimeout);
					}
				}
			}

			return $resPassiveConnection;
		}

		/**
		 * Pass in a passive connection resource to be correctly closed
		 * @param $resPassiveConnection
		 */
		protected function closePassiveConnection($resPassiveConnection){
			fclose($resPassiveConnection);
		}

		/**
		 * Send and read data from the FTP connection, this function is used to send and receive all comms with the FTP server.
		 * @param null $strRequestString
		 * @return int
		 */
		protected function communicate($strRequestString = null){

			$intResponseCode = 0;
			$this->strLastMessage = '';

			//Post the request data if required, otherwise get the response data only
			if(!is_null($strRequestString)){
				fputs($this->resConnection, $strRequestString);
			}

			while(true){
				$mxdLine = fgets($this->resConnection, 8129);
				$this->strLastMessage .= $mxdLine;

				if(preg_match('#^([0-9]{3})\s#',$mxdLine,$arrMatches)){
					$intResponseCode = intval($arrMatches[0]);
					break;
				}
			}

			//Clean the message string
			$this->strLastMessage = trim($this->strLastMessage);

			return $intResponseCode;
		}
	}