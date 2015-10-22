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

	namespace Twist\Core\Models\Archive;

	class PclZip{

		protected $resZip = null;

		/**
		 * Initialise the PclZip third party package ready for use within the framework.
		 */
		public function __construct(){
			require_once sprintf('%s/Archive/PclZip.class.php',TWIST_FRAMEWORK_MODELS);
		}

		/**
		 * Create an archive resource using the PclZip Third party package ready to accept files and folders
		 * @param $strZipArchive
		 * @return mixed
		 */
		public function create($strZipArchive){
			$this->resZip = new \PclZip($strZipArchive);
		}

		/**
		 * Load in an existing archive using the PclZip Third party package and store it as a resource ready to be manipulated/extracted.
		 * @param $strZipArchive
		 * @return boolean
		 */
		public function load($strZipArchive){
			$this->resZip = new \PclZip($strZipArchive);
		}

		/**
		 * Add a file to the archive resource using the PclZip Third party package.
		 * @param $strLocalFile
		 * @param $strZipPath
		 */
		public function addFile($strLocalFile,$strZipPath){

			//PCLZIP_OPT_REMOVE_PATH - Remove from the local file path
			//PCLZIP_OPT_ADD_PATH - virtual folder to add the file too

			$this->resZip->add($strLocalFile, PCLZIP_OPT_ADD_PATH,'install',PCLZIP_OPT_REMOVE_PATH,'dev');
		}

		/**
		 * Extract the files from the archive resource using the PclZip Third party package.
		 * @param $strExtractPath
		 */
		public function extract($strExtractPath){
			return ($this->resZip->extract(PCLZIP_OPT_PATH, $strExtractPath) == 0) ? false : true;
		}

		/**
		 * Add an empty folder to the archive resource using the PclZip Third party package.
		 * @param $strDirectoryPath
		 */
		public function addEmptyDir($strDirectoryPath){
			$this->resZip->addEmptyDir($strDirectoryPath);
		}

		/**
		 * Set a comment in the archive comment field using the PclZip Third party package, the comment can be seen when extracting the archive on commandline or using certain GUI tools.
		 * @param $strComment
		 */
		public function setArchiveComment($strComment){
			$this->resZip->setArchiveComment($strComment);
		}

		/**
		 * Delete a file or folder form the archive by its path using the PclZip Third party package.
		 * @param $strDirectoryPath
		 */
		public function deleteName($strDirectoryPath){
			$this->resZip->deleteName($strDirectoryPath);
		}

		/**
		 * Close the resource handler, this will save the resource to disk where appropriate. Uses the PclZip Third party package.
		 */
		public function close(){
			//$this->resZip->close();
			$this->resZip = null;
		}
	}