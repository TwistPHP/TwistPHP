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

namespace Twist\Core\Models\Protect;

set_time_limit(0);

class CodeScanner{

	protected $intFiles = 0;
	protected $intIgnored = 0;
	protected $intDirs = 0;
	protected $arrInfected = array();
	protected $intScanLife = 604800;
	protected $bScanRun = false;

	public function scan($dirPath){

		$this->scanDirectory($dirPath);
		$this->bScanRun = true;

		\Twist::Cache('twist/security')->write(sprintf('scan-%s',str_replace('/','_',$dirPath)),$this->summary(),$this->intScanLife);
	}

	public function getLastScan($dirPath){

		$arrLastScan = \Twist::Cache('twist/security')->read(sprintf('scan-%s',str_replace('/','_',$dirPath)));

		return (is_null($arrLastScan)) ? $this->summary() : $arrLastScan;
	}

	public function reset(){
		$this->intFiles = 0;
		$this->intIgnored = 0;
		$this->intDirs = 0;
		$this->arrInfected = array();
		$this->bScanRun = false;
	}

	protected function scanDirectory($dirPath){

		$this->intDirs++;

		foreach(scandir($dirPath) as $strEachFile){
			if(!in_array($strEachFile,array('.','..'))){

				$strFullPath = $dirPath.'/'.$strEachFile;

				if(is_dir($strFullPath)){
					$this->scanDirectory($strFullPath);
				}else{

					if(substr($strFullPath,-3) == 'php' || substr($strFullPath,-4) == 'phtml'){

						//Scan the file for infections
						$this->intFiles++;

						$strFirstLine = fgets(fopen($strFullPath, 'r'));

						if(stristr($strFirstLine,'eval(') || stristr($strFirstLine,'base64') || stristr($strFirstLine,'%x') || stristr($strFirstLine,'\x')){
							$this->arrInfected[] = array('file' => $strFullPath,'code' => $strFirstLine);
						}
					}else{
						$this->intIgnored++;
					}
				}
			}
		}
	}

	public function summary(){

		$arrSummary = array(
			'scanned' => ($this->bScanRun) ? date('Y-m-d H:i:s') : '',
			'files' => $this->intFiles,
			'skipped' => $this->intIgnored,
			'dirs' => $this->intDirs,
			'infected' => array(
				'count' => count($this->arrInfected),
				'files' => $this->arrInfected
			)
		);

		return $arrSummary;
	}
}