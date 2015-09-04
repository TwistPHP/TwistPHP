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
	 * @author    Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license   https://www.gnu.org/licenses/gpl.html LGPL License
	 * @link      http://twistphp.com/
	*
	*/

	namespace Twist\Core\Packages;

	/**
	 * Connect to an FTP server via PHP with the ability to browse and create directories, upload, download and delete files.
	 * The ability to choose Passive and Active connection mode is also present if using Native support.
	 */
	class FTP extends Base{

		protected $resLibrary = null;
		protected $intTimeout = 90;
		protected $arrFeatures = array();

		public function __construct(){

			$strLibraryClass = sprintf('\Twist\Core\Models\FTP\%s',lcfirst(\Twist::framework()->setting('FTP_LIBRARY'))); //Can be set to either 'ftpnative' or 'ftpsocket'

			if(!class_exists($strLibraryClass)){
				throw new \Exception(sprintf("Error, FTP protocol library '%s' is not installed or supported",\Twist::framework()->setting('FTP_LIBRARY')));
			}

			$this->resLibrary = new $strLibraryClass();
		}

		/**
		 * Connect to the remote FTP server
		 * @param $strHost
		 * @param int $intPort
		 * @param null|int $intConnectionTimeout
		 */
		public function connect($strHost,$intPort = 21,$intConnectionTimeout = null){

			//Set the connection timeout
			$this->resLibrary->setTimeout($intConnectionTimeout);
			$this->resLibrary->connect($strHost,$intPort);
		}

		/**
		 * Disconnect from the remote FTP server
		 */
		public function disconnect(){
			$this->resLibrary->disconnect();
			$this->arrFeatures = array();
		}

		/**
		 * Login to the open FTP connection
		 * @param $strUsername
		 * @param $strPassword
		 * @return bool
		 */
		public function login($strUsername,$strPassword){
			$this->resLibrary->login($strUsername,$strPassword);
		}

		/**
		 * Enable/Disable passive mode globally for this connection
		 * @param bool $blEnable
		 */
		public function passiveMode($blEnable = true){
			$this->resLibrary->pasv($blEnable);
		}

		/**
		 * Get the system name for the FTP connection
		 * @return bool
		 */
		public function systemName(){
			return $this->resLibrary->systype();
		}

		/**
		 * Get an array of supported features for the current FTP server connection
		 * @return array|bool
		 */
		public function featureList(){
			return (count($this->arrFeatures)) ? $this->arrFeatures : $this->arrFeatures = $this->resLibrary->feat();
		}

		/**
		 * Detect if the connected FTP server supports this feature
		 * @param $strFeature Name of feature to check
		 * @return bool
		 */
		public function featureSupported($strFeature){
			$arrFeatures = $this->featureList();
			return array_key_exists($strFeature,$arrFeatures);
		}

		/**
		 * Get path of the current working directory on the remote FTP server
		 * @return string Returns the directory path
		 */
		public function getCurrentDirectory(){
			return $this->resLibrary->pwd();
		}

		/**
		 * Change the current working directory to a new location
		 *
		 * @param $strDirectory Path of new working directory
		 * @return bool Returns the status of change
		 */
		public function changeDirectory($strDirectory){
			return $this->resLibrary->cwd($strDirectory);
		}

		/**
		 * Detect if the directory exists and is a directory
		 *
		 * @param $strDirectory Path of directory
		 * @return bool Returns the status of directory
		 */
		public function isDirectory($strDirectory){
			return count($this->resLibrary->nlist($strDirectory));
		}

		/**
		 * Make a new directory on the remote FTP server
		 *
		 * @param $strDirectory Path for the new directory
		 * @return bool Returns the status of directory creation
		 */
		public function makeDirectory($strDirectory,$blRecursive = false){

			if($blRecursive == true && strstr($strDirectory,'/')){
				$arrDirectoryParts = explode('/',trim($strDirectory,'/'));
				$strCurrentPath = '';

				//Run through all directories until get to last one
				while(count($arrDirectoryParts) > 1){
					$strCurrentPath .= array_shift($arrDirectoryParts);
					if(!$this->isDirectory($strCurrentPath)){
						$this->resLibrary->mkd($strCurrentPath);
					}
					$strCurrentPath .= '/';
				}

				//Allow the main function to make the new directory
				$strDirectory = $strCurrentPath.array_shift($arrDirectoryParts);
			}

			return $this->resLibrary->mkd($strDirectory);
		}

		/**
		 * Remove a directory on the remote FTP server
		 *
		 * @param $strDirectory Path of the directory to remove
		 * @return bool Returns the status of the removal
		 */
		public function removeDirectory($strDirectory,$blRecursive = false){

			if($blRecursive == true){
				$arrFiles = $this->resLibrary->nlist($strDirectory);

				if(count($arrFiles) > 2){

					//Go through all files and folders in this directory removing them before removing the directory concerned
					foreach($arrFiles as $strFile){

						if(!in_array($strFile,array('.','..'))){
							$strFilePath = sprintf('%s/%s',rtrim($strDirectory,'/'),$strFile);

							if($this->isDirectory($strFilePath)){
								$this->removeDirectory($strFilePath,true);
							}else{
								$this->delete($strFilePath);
							}
						}
					}
				}
			}

			return $this->resLibrary->rmd($strDirectory);
		}

		/**
		 * List the provided directory and return as an array
		 *
		 * @param $strDirectory
		 * @return array|bool
		 */
		public function listDirectory($strDirectory){
			return $this->resLibrary->nlist($strDirectory);
		}

		/**
		 * Rename either a file or directory to a new name
		 *
		 * @param $strFilename
		 * @param $strNewFilename
		 * @return bool
		 */
		public function rename($strFilename, $strNewFilename){
			return $this->resLibrary->rename($strFilename,$strNewFilename);
		}

		/**
		 * Upload a file to the remote FTP server
		 *
		 * @param $strLocalFilename
		 * @param $strRemoteFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function upload($strLocalFilename, $strRemoteFilename, $strMode = 'A'){
			return $this->resLibrary->upload($strLocalFilename,$strRemoteFilename,$strMode);
		}

		/**
		 * Download a file from the remote FTP server
		 *
		 * @param $strRemoteFilename
		 * @param $strLocalFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function download($strRemoteFilename, $strLocalFilename, $strMode = 'A'){
			return $this->resLibrary->download($strRemoteFilename,$strLocalFilename,$strMode);
		}

		/**
		 * Remove the file from the server
		 *
		 * @param $strFilename
		 * @return bool
		 */
		public function delete($strFilename){
			return $this->resLibrary->delete($strFilename);
		}

		/**
		 * CHMOD the files permissions
		 *
		 * @param $strFilename
		 * @param $intMode
		 * @return bool
		 */
		public function chmod($strFilename,$intMode){
			return $this->resLibrary->chmod($strFilename,$intMode);
		}

		/**
		 * Get the size of any given file on the remote FTP server
		 *
		 * @param $strFilename
		 * @return bool|int
		 */
		public function size($strFilename){
			return ($this->featureSupported('SIZE')) ? $this->resLibrary->size($strFilename) : false;
		}

		/**
		 * Get the last modified time of any given file on the remote FTP server
		 *
		 * @param $strFilename
		 * @return bool|int
		 */
		public function modified($strFilename){
			return ($this->featureSupported('MDTM')) ? $this->resLibrary->mdtm($strFilename) : false;
		}
	}