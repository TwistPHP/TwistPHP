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

	class ArchivePclZip{

		protected $resZip = null;

		public function __construct(){
			require_once sprintf('%s/libraries/Archive/PclZip.class.php',DIR_FRAMEWORK_PACKAGES);
		}

		public function create($strZipArchive){
			$this->resZip = new \PclZip($strZipArchive);
		}

		public function load($strZipArchive){
			$this->resZip = new \PclZip($strZipArchive);
		}

		public function addFile($strLocalFile,$strZipPath){

			//PCLZIP_OPT_REMOVE_PATH - Remove from the local file path
			//PCLZIP_OPT_ADD_PATH - virtual folder to add the file too

			$this->resZip->add($strLocalFile, PCLZIP_OPT_ADD_PATH,'install',PCLZIP_OPT_REMOVE_PATH,'dev');
		}

		public function extract($strExtractPath){
			return ($this->resZip->extract(PCLZIP_OPT_PATH, $strExtractPath) == 0) ? false : true;
		}

		public function addEmptyDir($strDirectoryPath){
			$this->resZip->addEmptyDir($strDirectoryPath);
		}

		public function setArchiveComment($strComment){
			$this->resZip->setArchiveComment($strComment);
		}

		public function deleteName($strDirectoryPath){
			$this->resZip->deleteName($strDirectoryPath);
		}

		public function close(){
			//$this->resZip->close();
			$this->resZip = null;
		}
	}