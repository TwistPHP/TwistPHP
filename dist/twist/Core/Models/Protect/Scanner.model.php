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

class Scanner{

	protected $intFiles = 0;
	protected $intIgnored = 0;
	protected $intDirs = 0;
	protected $arrInfected = array();
	protected $intScanLife = 604800;
	protected $bScanRun = false;
	protected $arrFiles = array();
	protected $arrNewFiles = array();
	protected $arrChangedFiles = array();
	protected $blFirstReport = false;

	public function scan($dirPath,$blReport = false){

		//Load in the last scan of the directory ready for the comparison
		$this->getLastScan($dirPath);

		$strPathKey = sha1(str_replace('/','_',$dirPath));

		$this->scanDirectory($dirPath);
		$this->bScanRun = true;

		\Twist::Cache('twist/protect')->write(sprintf('scan-%s',$strPathKey),$this->summary(),$this->intScanLife);
		\Twist::Cache('twist/protect')->write(sprintf('scan-hashes-%s',$strPathKey),$this->arrFiles,$this->intScanLife);

		if($blReport){
			$this->generateReport();
		}

		return $this->summary();
	}

	public function getLastScan($dirPath){

		$strPathKey = sha1(str_replace('/','_',$dirPath));

		$arrLastScan = \Twist::Cache('twist/protect')->read(sprintf('scan-%s',$strPathKey));
		$this->arrFiles = \Twist::Cache('twist/protect')->read(sprintf('scan-hashes-%s',$strPathKey));

		if(!is_array($this->arrFiles)){
			$this->arrFiles = array();
			$this->blFirstReport = true;
		}

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

					if(preg_match("#\.(php|phtml|html|htm|tpl|js|css|sh|htaccess)$#",$strFullPath,$arrResults)){

						$strFileSHA1 = sha1_file($strFullPath);

						if(!array_key_exists($strFullPath,$this->arrFiles)){
							$this->arrNewFiles[$strFullPath] = $strFileSHA1;
							$this->arrFiles[$strFullPath] = $strFileSHA1;

						}elseif($this->arrFiles[$strFullPath] != $strFileSHA1){
							$this->arrChangedFiles[$strFullPath] = $strFileSHA1;
							$this->arrFiles[$strFullPath] = $strFileSHA1;
						}

						if(preg_match("#\.(php|phtml)$#",$strFullPath,$arrResults)){

							$strFirstLine = fgets(fopen($strFullPath, 'r'));

							if(stristr($strFirstLine,'eval(') || stristr($strFirstLine,'base64') || stristr($strFirstLine,'%x') || stristr($strFirstLine,'\x')){
								$this->arrInfected[] = array('file' => $strFullPath,'code' => $strFirstLine);
							}
						}

						//Update the SHA1 value of the file
						$this->intFiles++;
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
			),
			'changed' => array(
				'count' => count($this->arrChangedFiles),
				'files' => $this->arrChangedFiles
			),
			'new' => array(
				'count' => count($this->arrNewFiles),
				'files' => $this->arrNewFiles
			)
		);

		return $arrSummary;
	}

	protected function generateReport(){

		//Determine if we need to send the report or not
		//Send if this is the first run, if there are infected, new or updated files
		if(!$this->blFirstReport && (count($this->arrInfected) || count($this->arrChangedFiles) || count($this->arrNewFiles))){

			$strToEmail = \Twist::framework()->setting('TWISTPROTECT_SCANNER_REPORT_EMAIL');

			//Only send the report if a valid email address has been entered
			if(\Twist::Validate()->email($strToEmail)){

				$arrTags = array(
					'site_name' => \Twist::framework()->setting('SITE_NAME'),
					'site_host' => \Twist::framework()->setting('SITE_HOST'),
					'subject' => 'TwistProtect: Scan Report',
					'firstname' => 'Admin',
					'url' => \Twist::framework()->setting('SITE_PROTOCOL').'://'.\Twist::framework()->setting('SITE_HOST'),
					'infected_files' => '',
					'changed_files' => '',
					'new_files' => ''
				);

				if(count($this->arrInfected)){

					$arrTags['infected_files'] = "\n<h2>Infected Files (".count($this->arrChangedFiles).")</h2>\n<ul>\n";
					foreach($this->arrInfected as $strPath => $strKey){
						$arrTags['infected_files'] .= "<li><strong>".$strPath."</strong> (".$strKey.")</li>\n";
					}
					$arrTags['infected_files'] .= "</ul>";
				}

				if(count($this->arrChangedFiles)){

					$arrTags['changed_files'] = "\n<h2>Changed Files (".count($this->arrChangedFiles).")</h2>\n<ul>\n";
					foreach($this->arrChangedFiles as $strPath => $strKey){
						$arrTags['changed_files'] .= "<li><strong>".$strPath."</strong> (".$strKey.")</li>\n";
					}
					$arrTags['changed_files'] .= "</ul>";
				}

				if(count($this->arrNewFiles)){

					$arrTags['new_files'] = "\n<h2>New Files (".count($this->arrNewFiles).")</h2>\n<ul>\n";
					foreach($this->arrNewFiles as $strPath => $strKey){
						$arrTags['new_files'] .= "<li><strong>".$strPath."</strong> (".$strKey.")</li>\n";
					}
					$arrTags['new_files'] .= "</ul>";
				}

				$resEmail = \Twist::Email()->create();

				$resEmail->addTo($strToEmail);
				$resEmail->setFrom(sprintf('twistprotect@%s',str_replace('www.','',$arrTags['site_host'])));
				$resEmail->setReplyTo(sprintf('twistprotect@%s',str_replace('www.','',$arrTags['site_host'])));
				$resEmail->setSubject($arrTags['subject']);

				$resEmail->setBodyHTML(\Twist::View()->build(sprintf('%sprotect/scan-report-email.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags));

				$resEmail->send();
			}
		}
	}
}