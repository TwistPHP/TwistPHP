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

	namespace Twist\Core\Models\Archive;

	class Native{

		protected $resZip = null;

		/**
		 * Create an archive resource ready to accept files and folders
		 * @param string $strZipArchive
		 * @return boolean
		 */
		public function create($strZipArchive){
			$this->resZip = new \ZipArchive();
			$blStatus = $this->resZip->open($strZipArchive, \ZipArchive::CREATE);
			return $blStatus;
		}

		/**
		 * Load in an existing archive and store it as a resource ready to be manipulated/extracted.
		 * @param string $strZipArchive
		 * @return boolean
		 */
		public function load($strZipArchive){
			$this->resZip = new \ZipArchive();
			$blStatus = $this->resZip->open($strZipArchive);
			return $blStatus;
		}

		/**
		 * Add a file to the archive resource
		 * @param string $strLocalFile
		 * @param string $strZipPath
		 */
		public function addFile($strLocalFile,$strZipPath){
			$this->resZip->addFile($strLocalFile,$strZipPath);
		}

		/**
		 * Extract the files from the archive resource
		 * @param string $strExtractPath
		 */
		public function extract($strExtractPath){
			$this->resZip->extractTo($strExtractPath);
		}

		/**
		 * Add an empty folder to the archive resource
		 * @param string $strDirectoryPath
		 */
		public function addEmptyDir($strDirectoryPath){
			$this->resZip->addEmptyDir($strDirectoryPath);
		}

		/**
		 * Set a comment in the archive comment field, the comment can be seen when extracting the archive on commandline or using certain GUI tools.
		 * @param string $strComment
		 */
		public function setArchiveComment($strComment){
			$this->resZip->setArchiveComment($strComment);
		}

		/**
		 * Delete a file or folder form the archive by its path.
		 * @param string $strDirectoryPath
		 */
		public function deleteName($strDirectoryPath){
			$this->resZip->deleteName($strDirectoryPath);
		}

		/**
		 * Close the resource handler, this will save the resource to disk where appropriate
		 */
		public function close(){
			$this->resZip->close();
			$this->resZip = null;
		}
	}