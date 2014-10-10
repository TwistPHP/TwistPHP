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
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP\Packages;
	use TwistPHP\ModuleBase;

	class Archive extends ModuleBase{

		var $resZip = null;
		var $root = null;
		var $ignored_names = null;

		protected $strHandler = 'native';
		protected $resHandler = null;

		public function __construct(){

			$this->strHandler = \Twist::framework() -> setting('ARCHIVE_HANDLER');
			switch($this->strHandler){

				case'pclzip';
					require_once sprintf('%s/libraries/Archive/PclZip.lib.php',DIR_FRAMEWORK_PACKAGES);
					$this->resHandler = new ArchivePclZip();
					break;

				case'native';
				default:
					require_once sprintf('%s/libraries/Archive/Native.lib.php',DIR_FRAMEWORK_PACKAGES);
					$this->resHandler = new ArchiveNative();
					break;
			}
		}

		public function create($strZipArchive){
			$this->resHandler->create($strZipArchive);
		}

		public function load($strZipArchive){
			$this->resHandler->load($strZipArchive);
		}

		public function addFile($strLocalFile){

			$strZipPath = '';

			$this->resHandler->addFile($strLocalFile,$strZipPath);
		}

		public function addDirectory($strLocalDirectory){
			$arrFiles = scandir($strLocalDirectory);

			foreach($arrFiles as $strEachFile){
				$this->addFile($strEachFile);
			}
		}

		public function save(){

		}

		public function serve(){
			$strTempFile = '';
			\Twist::File()->serve($strTempFile);
		}

		public function extract($strExtractPath){
			$this->resHandler->extract($strExtractPath);
		}

		/**
		 * Pass in an array or a single file to be zipped in the designated zip file
		 * @param $mxdFilesToZip
		 * @param $strZipFile
		 * @return int
		 */
		public function zip($file, $folder, $ignored=null){

			$this->resZip = new ZipArchive();

			$this->ignored_names = is_array($ignored) ? $ignored : ($ignored ? array($ignored) : array());

			if ($this->resZip->open($file, ZIPARCHIVE::CREATE)!==TRUE){
				throw new Exception("cannot open <$file>\n");
			}

			$folder = substr($folder, -1) == '/' ? substr($folder, 0, strlen($folder)-1) : $folder;

			if(strstr($folder, '/')) {
				$this->root = substr($folder, 0, strrpos($folder, '/')+1);
				$folder = substr($folder, strrpos($folder, '/')+1);
			}

			$this->zipDirectory($folder);
			$this->resZip->close();
		}

		protected function zipDirectory($folder, $parent=null) {

			$full_path = $this->root.$parent.$folder;
			$zip_path = $parent.$folder;

			$this->resZip->addEmptyDir($zip_path);
			$dir = new DirectoryIterator($full_path);

			foreach($dir as $file){

				if(!$file->isDot()){

					$filename = $file->getFilename();

					if(!in_array($filename, $this->ignored_names)){
						if($file->isDir()){
							$this->zipDirectory($filename, $zip_path.'/');
						}else{
							$this->resZip->addFile($full_path.'/'.$filename, $zip_path.'/'.$filename);
						}
					}
				}
			}
		}

		public function extractZIP($strZipFile,$strExtractLocation){

			$resZip = new ZipArchive;
			$blStatus = $resZip->open($strZipFile);

			if($blStatus === true){
				$resZip->extractTo($strExtractLocation);
				$resZip->close();
			}

			return $blStatus;
		}

		/**
		 * Pass in an array of files to be combined in a single ZIP
		 * @param $mxdFilesToZip
		 * @param $strZipFile
		 * @return int
		 */
		public function zipFiles($mxdFilesToZip,$strZipFile){

			$resZip = new ZipArchive;
			$resZip->open($strZipFile, ZipArchive::CREATE);

			$arrBackupFiles = array();

			if(is_array($mxdFilesToZip)){

				//Will later to be used also to add directories
				foreach($mxdFilesToZip as $strEachFile){

					if(is_file($strEachFile) && !is_dir($strEachFile)){
						$arrBackupFiles[] = $strEachFile;
					}
				}
			}else{

				//Backup a single file
				$arrBackupFiles[] = $mxdFilesToZip;
			}

			foreach($arrBackupFiles as $strEachFile){
				$resZip->addFile($strEachFile, basename($strEachFile));
			}

			$resZip->close();

			return filesize($strZipFile);
		}


		public function extractZIPs($strZipFile,$strExtractLocation){

			if(class_exists('ZipArchive')){

				$resZip = new ZipArchive;
				$blStatus = $resZip->open($strZipFile);

				if($blStatus === true){
					$resZip->extractTo($strExtractLocation);
					$resZip->close();
				}

			}else{

				require_once sprintf('%s/../../support/PclZip.lib.php',dirname(__FILE__));
				$resArchive = new PclZip($strZipFile);
				if($resArchive->extract(PCLZIP_OPT_PATH, $strExtractLocation) == 0){
					$blStatus = false;
				}else{
					$blStatus = true;
				}
			}

			return $blStatus;
		}

	}