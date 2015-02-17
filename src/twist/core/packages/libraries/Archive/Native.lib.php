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

	namespace TwistPHP\Packages;

	class ArchiveNative{

		protected $resZip = null;

		public function create($strZipArchive){
			$this->resZip = new \ZipArchive();
			$blStatus = $this->resZip->open($strZipArchive, \ZipArchive::CREATE);
			return $blStatus;
		}

		public function load($strZipArchive){
			$this->resZip = new \ZipArchive();
			$blStatus = $this->resZip->open($strZipArchive);
			return $blStatus;
		}

		public function addFile($strLocalFile,$strZipPath){
			$this->resZip->addFile($strLocalFile,$strZipPath);
		}

		public function extract($strExtractPath){
			$this->resZip->extractTo($strExtractPath);
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
			$this->resZip->close();
			$this->resZip = null;
		}
	}