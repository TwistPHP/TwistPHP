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
	 * @link       http://twistphp.com
	 *
	 */

	namespace TwistPHP\Packages;

	class FTPNative{

		protected $resConnection = null;
		protected $intTimeout = 90;
		protected $blPassiveMode = false;

		public function setTimeout($intTimeout = null){
			$this->intTimeout = (is_null($intTimeout)) ? 90 : $intTimeout;
		}

		/**
		 * Open a new FTP connection
		 * @param $strHost
		 * @param $intPort
		 * @throws RuntimeException
		 */
		public function connect($strHost,$intPort = 21){
			$this->resConnection = ftp_connect($strHost,$intPort,$this->intTimeout);
		}

		/**
		 * Disconnect the current session (connection)
		 */
		public function disconnect(){
			return ftp_close($this->resConnection);
		}

		/**
		 * Login to the open FTP connection
		 * @param $strUsername
		 * @param $strPassword
		 * @return bool
		 */
		public function login($strUsername,$strPassword){
			return @ftp_login($this->resConnection, $strUsername, $strPassword);
		}

		public function pasv($blEnable = true){
			$this->blPassiveMode = $blEnable;
			return ftp_pasv($this->resConnection,$blEnable);
		}

		/**
		 * Get the system name for the FTP connection
		 * @return bool
		 */
		public function systype(){
			return ftp_systype($this->resConnection);
		}

		/**
		 * Get an array of supported features for the current FTP server connection
		 * @return array|bool
		 */
		public function feat(){

			//Custom built as dosnt appear to be a natively supported command
			$strResponse = ftp_raw($this->resConnection,"FEAT");

			if(preg_match('#^([0-9]{3})\s#',$strResponse,$arrMatches) && $arrMatches[0] == 211){

				$arrLines = explode("\n",$strResponse);
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
		}

		/**
		 * Make Directory
		 * @param $strDirectory
		 * @return bool
		 */
		public function mkd($strDirectory){
			return ftp_mkdir($this->resConnection, $strDirectory);
		}

		/**
		 * Remove Directory
		 * @param $strDirectory
		 * @return bool
		 */
		public function rmd($strDirectory){
			return ftp_rmdir($this->resConnection, $strDirectory);
		}

		/**
		 * Print current working directory
		 * @return bool|string
		 */
		public function pwd(){
			return ftp_pwd($this->resConnection);
		}

		/**
		 * Change working directory
		 * @param $strDirectory
		 * @return bool
		 */
		public function cwd($strDirectory){
			return ftp_chdir($this->resConnection,$strDirectory);
		}

		/**
		 * Rename a directory or file to a new name
		 * @param $strFilename
		 * @param $strNewFilename
		 * @return bool
		 */
		public function rename($strFilename, $strNewFilename){
			return ftp_rename($this->resConnection,$strFilename,$strNewFilename);
		}

		/**
		 * Remove the file from the server
		 * @param $strFilename
		 * @return bool
		 */
		public function delete($strFilename){
			return ftp_delete($this->resConnection,$strFilename);
		}

		/**
		 * CHMOD the files permissions
		 * @param $strFilename
		 * @param $intMode
		 * @return bool
		 */
		public function chmod($strFilename,$intMode){
			return ftp_chmod($this->resConnection,$intMode,$strFilename);
		}

		/**
		 * Download a file from the remote FTP server
		 * @param $strRemoteFilename
		 * @param $strLocalFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function download($strRemoteFilename, $strLocalFilename, $strMode = 'A'){
			$arrMode = array('I' => FTP_BINARY, 'A' => FTP_ASCII);
			return ftp_get($this->resConnection,$strLocalFilename,$strRemoteFilename,$arrMode[$strMode]);
		}

		/**
		 * Upload a file to the remote FTP server
		 * @param $strLocalFilename
		 * @param $strRemoteFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function upload($strLocalFilename, $strRemoteFilename, $strMode = 'A'){
			$arrMode = array('I' => FTP_BINARY, 'A' => FTP_ASCII);
			return ftp_put($this->resConnection,$strRemoteFilename,$strLocalFilename,$arrMode[$strMode]);
		}

		/**
		 * List the provided directory and return as an array
		 * @param $strDirectory
		 * @return array|bool
		 */
		public function nlist($strDirectory){
			return ftp_nlist($this->resConnection,$strDirectory);
		}

		/**
		 * Get the size of any given file on the remote FTP server
		 * @param $strFilename
		 * @return bool|int
		 */
		public function size($strFilename){
			return ftp_size($this->resConnection,$strFilename);
		}

		/**
		 * Get the last modified time of any given file on the remote FTP server
		 * @param $strFilename
		 * @return bool|int
		 */
		public function mdtm($strFilename){
			return ftp_mdtm($this->resConnection,$strFilename);
		}
	}