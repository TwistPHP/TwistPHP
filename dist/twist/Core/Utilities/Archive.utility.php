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

	use \Twist\Core\Models\Archive\PclZip;
	use \Twist\Core\Models\Archive\Native;

	/**
	 * Create ZIP archives of compressed files, easily zip up whole directories and single files. Default handler is PHP's native ZipArchive, the option to use the third party class PclZip can be selected in the framework settings.
	 *
	 * @package Twist\Core\Utilities
	 * @reference http://www.phpconcept.net/pclzip/ PclZip Package included as fallback option
	 */
	class Archive extends Base{

		var $resZip = null;
		var $root = null;
		var $ignored_names = null;

		protected $blTempFile = false;
		protected $strHandler = 'native';
		protected $resHandler = null;
		protected $dirZipFile = null;

		/**
		 * Determine that Zip Archive library to be used when creating and manipulating archives
		 */
		public function __construct(){

			$this->strHandler = \Twist::framework()->setting('ARCHIVE_HANDLER');
			switch($this->strHandler){

				case'pclzip';
					$this->resHandler = new PclZip();
					break;

				case'native';
				default:
					$this->resHandler = new Native();
					break;
			}
		}

		/**
		 * Create a new empty archive ready to have files and directories added
		 * @param string $dirZipArchive Full path for the new Zip archive, the Archive will be created here
		 */
		public function create($dirZipArchive = null){

			if(is_null($dirZipArchive)){
				$this->dirZipFile = sprintf('%s/twist-temp-archive-%s.zip',sys_get_temp_dir(),time());
				$this->blTempFile = true;
			}else{
				$this->dirZipFile = $dirZipArchive;
			}

			$this->resHandler->create($this->dirZipFile);
		}

		/**
		 * Load in an existing archive to be modified or added to
		 * @param string $dirZipArchive Full path to an existing Zip archive (on the server)
		 */
		public function load($dirZipArchive){
			$this->dirZipFile = $dirZipArchive;
			$this->resHandler->load($this->dirZipFile);
		}

		/**
		 * Set the main comment to display in the archive
		 * @param string $strComment
		 */
		public function setComment($strComment){
			$this->resHandler->setArchiveComment($strComment);
		}

		/**
		 * Add a file or directory to the current Zip Archive, the archive must be loaded or created using the 'load' or 'create' functions
		 * @param string $dirLocalFile Full path to the local file that will be added to the Zip Archive
		 * @param string $dirZipBasePath Base path to place the file within the zip, leave blank for the zip root
		 */
		public function addFile($dirLocalFile,$dirZipBasePath = ''){
			$this->addItem($dirLocalFile,$dirZipBasePath);
		}

		/**
		 * Add an empty directory to the zip, the directory path must be set from the root of the zip
		 * @param string $dirZipDirectoryPath Path of the empty directory to create
		 */
		public function addEmptyDirectory($dirZipDirectoryPath = ''){

			$dirZipDirectoryPath = trim($dirZipDirectoryPath,'/');

			if($dirZipDirectoryPath != ''){
				$this->resHandler->addEmptyDir($dirZipDirectoryPath);
			}
		}

		/**
		 * Delete a file or directory from the current ZIP file
		 * @param string $dirZipFilePath Path of the file to be deleted
		 */
		public function deleteFile($dirZipFilePath = ''){

			$dirZipFilePath = trim($dirZipFilePath,'/');

			if($dirZipFilePath != ''){
				$this->resHandler->deleteName($dirZipFilePath);
			}
		}

		/**
		 * Decides how to deal with the path being entered into the zip
		 * @param string $dirLocalPath Local path of hte item to be added
		 * @param string $strCurrentPath Base path within the zip where the item will be addded
		 */
		protected function addItem($dirLocalPath,$strCurrentPath = ''){

			//Clean up current path
			$strCurrentPath = trim($strCurrentPath,'/');

			if(file_exists($dirLocalPath)){

				if(is_dir($dirLocalPath)){

					$dirLocalPath = rtrim($dirLocalPath,'/');
					$arrFiles = scandir($dirLocalPath);

					foreach($arrFiles as $strEachFile){

						if(!in_array($strEachFile,array('.','..'))){

							$dirNextAdd = sprintf('%s/%s',$dirLocalPath,$strEachFile);
							$dirNextPath = (is_dir($dirNextAdd)) ? sprintf('%s/%s',$strCurrentPath,$strEachFile) : $strCurrentPath;

							$this->addItem($dirNextAdd,$dirNextPath);
						}
					}
				}else{

					$strFileName = \Twist::File()->name($dirLocalPath);
					$dirZipPath = ($strCurrentPath == '') ? $strFileName : sprintf('%s/%s',$strCurrentPath,$strFileName);

					$this->resHandler->addFile($dirLocalPath,$dirZipPath);
				}
			}
		}

		/**
		 * Finish and save the archive
		 * If a temp file a new save file must be passed in otherwise the temp file will be deleted
		 * @param string $dirSaveFile
		 */
		public function save($dirSaveFile = null){

			$this->resHandler->close();

			if($this->blTempFile){
				if(!is_null($dirSaveFile)){
					\Twist::File()->move($this->dirZipFile,$dirSaveFile);
				}else{
					unlink($this->dirZipFile);
				}
			}
		}

		/**
		 * Serve the newly created archive to the browser, this will allow the user to download the Archive to there computer
		 * Temp files are deleted upon serve
		 * @param string $strFileName
		 */
		public function serve($strFileName = null){

			//Save and close
			$this->resHandler->close();

			\Twist::File()->serve($this->dirZipFile,$strFileName,null,null,null,$this->blTempFile);
		}

		/**
		 * Extract the loaded Zip Archive to a given folder on the local server
		 * @param string $dirExtractPath Full path to the local directory in which to extract the archive
		 */
		public function extract($dirExtractPath){
			$this->resHandler->extract($dirExtractPath);
		}
	}