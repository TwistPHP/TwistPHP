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

	namespace TwistPHP;

	//Called only when using this class standalone (to help make define work)
	if(!class_exists('BaseModules') && !class_exists('\Twist')){
		require_once sprintf('%s/Base.twist.php',dirname(__FILE__));
		require_once sprintf('%s/BaseModules.twist.php',dirname(__FILE__));
		require_once sprintf('%s/Core.twist.php',dirname(__FILE__));
	}

	class Upgrade{

		protected $strReleaseChannel = 'stable';

		protected $strRepoPath = null;
		protected $strModulePath = null;
		protected $strInterfacePath = null;

		protected $arrRepositories = array();
		protected $arrModules = array();
		protected $arrInterfaces = array();

		protected $arrErrors = array();
		protected $intDebugCharWidth = 65;
		protected $blDebugOutput = false;
		protected $strDebugFile = null;
		protected $arrDebugData = array();

		protected $strHexGreen = '#0E0';
		protected $strHexRed = '#C00';

		private $resDatabase = null;

		public function __construct(){

			$this->strFrameworkPath = realpath(sprintf('%s/../../',dirname(__FILE__)));
			$this->strRepoPath = realpath(sprintf('%s/../../config/repo/',dirname(__FILE__)));
			$this->strModulePath = realpath(sprintf('%s/../../modules/',dirname(__FILE__)));
			$this->strInterfacePath = realpath(sprintf('%s/../../interfaces/',dirname(__FILE__)));

			if(!file_exists($this->strRepoPath)){
				mkdir($this->strRepoPath);
			}

			//Load in all the repositories
			$this->_loadRepositories();
		}

		public function channel($strReleaseChannel = 'stable'){
			$this->strReleaseChannel = strtolower($strReleaseChannel);
		}

		public function debugOutput($blEnable = true){
			$this->blDebugOutput = $blEnable;
		}

		public function debugFile($strFilePath = null){
			$this->strDebugFile = $strFilePath;
		}

		public function debug(){
			return $this->arrDebugData;
		}

		private function _databaseConnect(){
			require_once sprintf('%s/../../config/config.php',dirname(__FILE__));

			if(DATABASE_PROTOCOL != 'none'){

				$this->resDatabase = mysqli_connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
				mysqli_select_db($this->resDatabase,DATABASE_NAME);
			}
		}

		private function _databaseQuery($strSQL){
			$blOut = (is_resource($this->resDatabase) || is_object($this->resDatabase)) ? mysqli_query($this->resDatabase,$strSQL) : false;
			//$this->_debug(sprintf("- Run Query: %s",$strSQL));
			//$this->_debug(sprintf("- Error: %s",mysqli_error($this->resDatabase)));
			return $blOut;
		}

		private function _databaseEscape($strValue){
			return (is_resource($this->resDatabase) || is_object($this->resDatabase)) ? mysqli_real_escape_string($this->resDatabase,$strValue) : null;
		}

		protected function _loadRepositories(){

			$this->arrRepositories = array();
			$arrFiles = scandir($this->strRepoPath);

			foreach($arrFiles as $strEachFile){
				if(!in_array($strEachFile,array('.','..')) && substr($strEachFile,-4) == 'json'){
					$jsonRaw = file_get_contents(sprintf('%s/%s',$this->strRepoPath,$strEachFile));
					$strRepoKey = str_replace('.json','',$strEachFile);

					$this->arrRepositories[$strRepoKey] = json_decode($jsonRaw,true);
				}
			}
		}

		protected function _blankItem($strRepository,$strName,$strDescription,$strAuthor,$strBugs,$strHomepage,$blLicenced=false){
			return array(
				'key' => strtolower(sprintf('%s-%s',$strRepository,$strName)),
				'name' => $strName,
				'description' => $strDescription,
				'author' => $strAuthor,
				'bugs' => $strBugs,
				'homepage' => $strHomepage,
				'repository' => $strRepository,
				'licenced' => ($blLicenced == true || $blLicenced == 1) ? '1' : '0',
				'installed' => '0',
				'current' => array(/*
					'version' => '1.0.0',
					'release' => '2014-01-01 00:00:00'
				*/),
				'available' => array(/*
					'version' => '1.2.0',
					'release' => '2014-01-01 00:00:00',
					'change_log' => ''
				*/),
				'update' => '0'
			);
		}

		protected function _request($strRepoKey,$strType,$strChannel,$arrPostParameters = array()){

			if(array_key_exists($strRepoKey,$this->arrRepositories)){

				$arrOut = null;
				$strCacheKey = sprintf('%s-%s-%s-%s',$strRepoKey,$strType,$strChannel,sha1(serialize($arrPostParameters)));
				$blUseCache = (!in_array($strType,array('download','authenticate','connect'))) ? true : false;

				$arrOut = null;
				if($blUseCache){
					$arrOut = \Twist::Cache('twistUpgrade')->retrieve($strCacheKey);
				}

				if(is_null($arrOut)){

					require_once sprintf('%s/RC4.twist.php',dirname(__FILE__));
					$objTwistRC4 = new \Crypt_RC4();
					$objTwistRC4->setKey($this->arrRepositories[$strRepoKey]['token']);

					$strRepoURL = $this->arrRepositories[$strRepoKey]['url'];

					$resCurl = curl_init();
					curl_setopt($resCurl, CURLOPT_URL, sprintf('%s?type=%s&channel=%s',$strRepoURL,$strType,$strChannel));

					if(count($arrPostParameters)){

						$strPostData = '';
						if(is_array($arrPostParameters) && count($arrPostParameters)){
							foreach($arrPostParameters as $mxdKey => $mxdData){
								$strPostData .= sprintf("%s=%s&",$mxdKey,$mxdData);
							}
						}
						$strPostData = rtrim($strPostData,'&');

						curl_setopt($resCurl, CURLOPT_POST, count($arrPostParameters));
						curl_setopt($resCurl, CURLOPT_POSTFIELDS, $strPostData);
					}

					curl_setopt($resCurl, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($resCurl, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($resCurl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($resCurl, CURLOPT_TIMEOUT, 5);

					if($this->arrRepositories[$strRepoKey]['licence'] != ''){
						curl_setopt($resCurl, CURLOPT_HTTPHEADER, array('Request-Key: '.$this->arrRepositories[$strRepoKey]['licence']));
					}

					curl_setopt($resCurl, CURLOPT_USERAGENT, 'Twist Framework Manager');
					$mxdResponse = curl_exec($resCurl);
					$arrRequestInfo = curl_getinfo($resCurl);

					if(!curl_errno($resCurl)){

						if($arrRequestInfo['http_code'] == 200){

							if($strType == 'authenticate' && $this->arrRepositories[$strRepoKey]['token'] != ''){
								$mxdResponse = $objTwistRC4->decrypt($mxdResponse);
								$arrOut = (strstr($mxdResponse,'{')) ? json_decode($mxdResponse,true) : array('error' => 'Licence Key Error');
							}else{
								$arrOut = (strstr($mxdResponse,'{')) ? json_decode($mxdResponse,true) : array('error' => 'Communication Error');
							}

							if($strType == 'download' && !is_array($arrOut)){
								$arrOut = array('file' => $mxdResponse);
							}

							if($blUseCache){
								\Twist::Cache('twistUpgrade')->store($strCacheKey,$arrOut,86400);
							}

						}else{
							$arrOut = array('error' => sprintf('Communication Error: [%s] Forbidden access',$arrRequestInfo['http_code']));
						}

					}else{
						$arrOut = array('error' => sprintf('Communication Error: [%s] %s',curl_errno($resCurl),curl_error($resCurl)));
					}

					curl_close($resCurl);
				}
			}else{
				$arrOut = array('error' => sprintf('Invalid repository: %s',$strRepoKey));
			}

			if(array_key_exists('error',$arrOut)){
				$this->arrErrors[] = $arrOut['error'];
				$arrOut = array();
			}

			return $arrOut;
		}

		protected function _install($strRepositoryURL){

			$newRepo = 'temp'.rand(1000,9999);
			$this->arrRepositories[$newRepo] = array(
				'name' => 'install-new-repo',
				'key' => 'install-new-repo',
				'url' => trim($strRepositoryURL),
				'enabled' => '0',
				'licence' => '',
				'token' => ''
			);

			$arrOut = $this->_request($newRepo,'connect',$this->strReleaseChannel,array('request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],'package-type' => 'core'));

			if(array_key_exists('name',$arrOut) && array_key_exists('key',$arrOut)){
				$strRepoFile = sprintf('%s/%s.json',$this->strRepoPath,$arrOut['key']);
				file_put_contents($strRepoFile,json_encode($arrOut));

				//Now reload the repository list
				$this->_loadRepositories();
			}

			return $arrOut;
		}

		protected function _authenticate($strRepoKey){

			$arrOut = $this->_request($strRepoKey,'authenticate',$this->strReleaseChannel,array('request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']));

			if(count($arrOut)){
				$arrOut['licence_key'] = $this->arrRepositories[$strRepoKey]['licence'];
				$arrOut['account_token'] = $this->arrRepositories[$strRepoKey]['token'];
				$arrOut['status'] = true;
			}else{
				$arrOut['status'] = false;
			}

			return $arrOut;
		}

		protected function _core(){
			$arrOut = $this->_request('twistphp','packages',$this->strReleaseChannel,array('request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],'package-type' => 'core'));
			return $arrOut;
		}

		protected function _modules($strRepoKey){
			$arrOut = $this->_request($strRepoKey,'packages',$this->strReleaseChannel,array('request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],'package-type' => 'modules'));
			return $arrOut;
		}

		protected function _interfaces($strRepoKey){
			$arrOut = $this->_request($strRepoKey,'packages',$this->strReleaseChannel,array('request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],'package-type' => 'interfaces'));
			return $arrOut;
		}

		protected function _downloadCore($strVersion){

			$this->_debug(sprintf("Downloading TwistPHP core v%s",$strVersion));

			$mxdRequestData = array(
				'request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
				'package' => 'twist',
				'package-type' => 'core',
				'package-version' => $strVersion,
				'php' => phpversion()
			);

			$arrOut = $this->_request('twistphp','download',$this->strReleaseChannel,$mxdRequestData);
			$arrOut = $this->_processTempFile($mxdRequestData,$arrOut);

			return $arrOut;
		}

		protected function _downloadModule($strRepoKey,$strModule,$strVersion){

			$this->_debug(sprintf("Downloading module %s v%s from %s",$strModule,$strVersion,$strRepoKey));

			$mxdRequestData = array(
				'request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
				'package' => $strModule,
				'package-type' => 'modules',
				'package-version' => $strVersion,
				'php' => phpversion()
			);

			$arrOut = $this->_request($strRepoKey,'download',$this->strReleaseChannel,$mxdRequestData);
			$arrOut = $this->_processTempFile($mxdRequestData,$arrOut);

			return $arrOut;
		}

		protected function _downloadInterface($strRepoKey,$strInterface,$strVersion){

			$this->_debug(sprintf("Downloading interface %s v%s from %s",$strInterface,$strVersion,$strRepoKey));

			$mxdRequestData = array(
				'request-host' => (array_key_exists('HTTP_HOST',$_SERVER) && $_SERVER['HTTP_HOST'] != '') ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
				'package' => $strInterface,
				'package-type' => 'interfaces',
				'package-version' => $strVersion,
				'php' => phpversion()
			);

			$arrOut = $this->_request($strRepoKey,'download',$this->strReleaseChannel,$mxdRequestData);
			$arrOut = $this->_processTempFile($mxdRequestData,$arrOut);

			return $arrOut;
		}

		protected function _processTempFile($arrRequest,$arrResponse){

			if(count($arrResponse)){

				$strTempFile = tempnam(sys_get_temp_dir(), sprintf('%s-%s_',$arrRequest['package-type'],$arrRequest['package']));

				if($strTempFile !== false){
					$resTempFile = fopen($strTempFile, "w");
					fwrite($resTempFile,$arrResponse['file']);
					fclose($resTempFile);

					$arrResponse = $arrRequest;
					$arrResponse['package-file'] = $strTempFile;
					$arrResponse['package-size'] = filesize($strTempFile);

					$this->_debug("OK",$this->strHexGreen);
					return $arrResponse;
				}
			}

			$this->_debug("FAIL",$this->strHexRed);
			return $arrResponse;
		}

		protected function _debugReset(){
			$this->arrDebugData = array('complete' => false,'success' => false, 'progress' => 0, 'html' => "", 'plain' => "", 'manager_url' => '');
			$this->_debugUpdateFile();
		}

		protected function _debugComplete($blStatus = false){

            $arrParts = explode($_SERVER['REQUEST_URI'],'/');
            array_pop($arrParts);
            $arrParts[] = 'modules';

			$this->arrDebugData['manager_url'] = implode($arrParts,'/');
			$this->arrDebugData['complete'] = $blStatus;

            $this->_debugUpdateFile();
		}

		protected function _debugSuccess($blStatus = false){
			$this->arrDebugData['success'] = $blStatus;
			$this->_debugUpdateFile();
		}

		protected function _debugProgress($intProgress = 0){
			$this->arrDebugData['progress'] = round($intProgress);
			$this->_debugUpdateFile();
		}

		protected function _debugUpdateFile(){
			if(!is_null($this->strDebugFile)){
				file_put_contents($this->strDebugFile,json_encode($this->arrDebugData));
			}
		}

		protected function _debug($strInformation,$strStatus = null){

			if(!is_null($strStatus)){
				$strHTMLInformation = ($strInformation == 'OK') ? sprintf("&nbsp;[ <span style=\"color: %s;\">%s</span> ]",$strStatus,$strInformation) : sprintf("&nbsp;[<span style=\"color: %s;\">%s</span>]",$strStatus,$strInformation);
				$strInformation = ($strInformation == 'OK') ? sprintf(" [ %s ]",$strInformation) : sprintf(" [%s]",$strInformation);
			}else{

				$strHTMLInformation = $strInformation;

				if(strlen($strInformation) < $this->intDebugCharWidth){
					$strHTMLInformation .= '&nbsp;';
					for($intCount = strlen($strInformation); $intCount < ($this->intDebugCharWidth-1); $intCount++){
						$strHTMLInformation .= '&nbsp;';
					}
				}

				$strHTMLInformation = sprintf("<br>&gt; %s",$strHTMLInformation);
				$strInformation = sprintf("\n%s",$strInformation);
			}

            if($this->arrDebugData['html'] == ''){
                $strHTMLInformation = str_replace("<br>","",$strHTMLInformation);
                $strInformation = str_replace("\n","",$strInformation);
            }

			$this->arrDebugData['html'] .= $strHTMLInformation;
			$this->arrDebugData['plain'] .= $strInformation;
			$this->_debugUpdateFile();

			if($this->blDebugOutput){
				echo $strInformation;
			}

			//Micro sleep for 1/4 of a second to allow the repository servers time to breath when multiple requests are made
			usleep(250000);
		}

		/**
		 * A Standalone zip extract function that can be used as the main framework may not be active whilst running the upgrade script
		 * @param $strZipFile
		 * @param $strExtractLocation
		 * @return bool
		 */
		protected function _extract($strZipFile,$strExtractLocation){

			if(class_exists('ZipArchive')){

				$resZip = new \ZipArchive;

				if($resZip->open($strZipFile) === true){
					$blStatus = $resZip->extractTo($strExtractLocation);
					$resZip->close();
				}else{
					$blStatus = false;
				}

			}else{

				require_once sprintf('%s/../packages/libraries/Archive/PclZip.class.php',dirname(__FILE__));
				$resArchive = new \PclZip($strZipFile);

				if($resArchive->extract(PCLZIP_OPT_PATH, $strExtractLocation) == 0){
					$blStatus = false;
				}else{
					$blStatus = true;
				}
			}

			return $blStatus;
		}

		protected function _remove($strDirectory){

			//$this->_debug($strDirectory);

			if(file_exists($strDirectory) && is_dir($strDirectory)){

				$arrFiles = scandir($strDirectory);

				foreach($arrFiles as $strEachFile){
					($strEachFile != "." && $strEachFile != "..") ? $this->_remove("$strDirectory/$strEachFile") : null;
				}

				if(file_exists($strDirectory) && is_dir($strDirectory)){
					rmdir($strDirectory);
				}

			}elseif(file_exists($strDirectory)){
				unlink($strDirectory);
			}
		}

		protected function _copy($strSource,$strDestination){

			//$this->_debug($strDestination);

			//Remove data from the destination if already exists
			(file_exists($strDestination)) ? $this->_remove($strDestination) : null;

			if(is_dir($strSource)){

				mkdir($strDestination);
				$arrFiles = scandir($strSource);

				//For each file and folder in the array recursively copy it
				foreach($arrFiles as $strEachFile){
					if($strEachFile != "." && $strEachFile != ".."){
						//Recursively copy as this is another directory
						$this->_copy("$strSource/$strEachFile", "$strDestination/$strEachFile");
					}
				}
			}elseif(file_exists($strSource)){

				//If the source is a file copy to the destination
				copy($strSource, $strDestination);
			}
		}

		protected function _installFiles($arrData){

			$blInstalled = false;

			if(array_key_exists('package-file',$arrData)){

				$this->_debug("Extracting archive");
				$strPackageFolder = sprintf('%s-extracted',$arrData['package-file']);
				$strFrameworkDirectory = realpath(sprintf('%s/../../',dirname(__FILE__)));

				if($this->_extract($arrData['package-file'],$strPackageFolder)){

					$this->_debug("OK",$this->strHexGreen);

					//Previous version is always null until looked up
					$strPreviousVersion = null;

					switch($arrData['package-type']){
						case'modules':
						case'interfaces':

							$strInstallFolder = sprintf('%s/%s/%s',$strFrameworkDirectory,$arrData['package-type'],$arrData['package']);
							if(file_exists($strInstallFolder)){

								$rawJSON = file_get_contents(sprintf('%s/info.json',$strInstallFolder));
								$arrCurrentInfo = json_decode($rawJSON,true);

								//Find the current installed version number
								$strPreviousVersion = $arrCurrentInfo['version'];

								$this->_debug("Remove existing files");
								$this->_remove($strInstallFolder);
							}

							$this->_debug("Installing new files");
							$this->_copy(sprintf('%s/%s',$strPackageFolder,$arrData['package']),$strInstallFolder);

							//This package is new of the previous version is null
							if(is_null($strPreviousVersion)){
								$this->installPackageTables($arrData['package-type'],$arrData['package']);
							}else{
								$this->updatePackageTables($arrData['package-type'],$arrData['package'],$strPreviousVersion);
							}

							//Update all the settings in the settings table for this package
							$this->updatePackageSettings($arrData['package-type'],$arrData['package']);

							break;
						case'core':

							//Only update the setup interface if it has not been deleted
							$blSetupInstalled = (file_exists(sprintf('%s/interfaces/Setup',$strFrameworkDirectory))) ? true : false;

							$strInstallFolder = sprintf('%s/core',$strFrameworkDirectory);
							if(file_exists($strInstallFolder)){

								$rawJSON = file_get_contents(sprintf('%s/info.json',$strFrameworkDirectory));
								$arrCurrentInfo = json_decode($rawJSON,true);

								//Find the current installed version number
								$strPreviousVersion = $arrCurrentInfo['version'];

								$this->_debug("Remove existing files");

								$this->_remove($strInstallFolder);
								$this->_remove(sprintf('%s/.htaccess',$strFrameworkDirectory));
								$this->_remove(sprintf('%s/framework.php',$strFrameworkDirectory));
								$this->_remove(sprintf('%s/info.json',$strFrameworkDirectory));
								$this->_remove(sprintf('%s/LICENCE.html',$strFrameworkDirectory));
								$this->_remove(sprintf('%s/LICENCE.txt',$strFrameworkDirectory));
								$this->_remove(sprintf('%s/interfaces/Setup',$strFrameworkDirectory));
							}

							$this->_copy(sprintf('%s/twist/core',$strPackageFolder),$strInstallFolder);
							$this->_copy(sprintf('%s/twist/.htaccess',$strPackageFolder),sprintf('%s/.htaccess',$strFrameworkDirectory));
							$this->_copy(sprintf('%s/twist/framework.php',$strPackageFolder),sprintf('%s/framework.php',$strFrameworkDirectory));
							$this->_copy(sprintf('%s/twist/info.json',$strPackageFolder),sprintf('%s/info.json',$strFrameworkDirectory));
							$this->_copy(sprintf('%s/twist/LICENCE.html',$strPackageFolder),sprintf('%s/LICENCE.html',$strFrameworkDirectory));
							$this->_copy(sprintf('%s/twist/LICENCE.txt',$strPackageFolder),sprintf('%s/LICENCE.txt',$strFrameworkDirectory));

							if($blSetupInstalled){
								$this->_copy(sprintf('%s/twist/interfaces/Setup',$strPackageFolder),sprintf('%s/interfaces/Setup',$strFrameworkDirectory));
							}

							//This package is new of the previous version is null
							if(is_null($strPreviousVersion)){
								$this->installCoreTables();
							}else{
								$this->updateCoreTables($strPreviousVersion);
							}

							//Update all the settings in the settings table for the core
							$this->updateCoreSettings();

							break;
					}

					$blInstalled = true;
				}else{
					$this->_debug("FAIL",$this->strHexRed);
				}

				//Clean up files
				$this->_debug("Cleanup temporary installation files");
				$this->_remove($arrData['package-file']);
				$this->_remove($strPackageFolder);

				$this->_debug(sprintf("Finished installing %s v%s",$arrData['package'],$arrData['package-version']));

				if($blInstalled){
					$this->_debug("OK",$this->strHexGreen);
				}else{
					$this->_debug("FAIL",$this->strHexRed);
				}
			}

			return $blInstalled;
		}

		protected function _removeFiles($arrData){

			$blRemoved = false;
			$strFrameworkDirectory = realpath(sprintf('%s/../../',dirname(__FILE__)));
			$strInstallationFolder = sprintf('%s/%s/%s',$strFrameworkDirectory,$arrData['package-type'],$arrData['package']);

			$this->_debug(sprintf("Removing %s",$arrData['package']));

			if(file_exists($strInstallationFolder)){
				$this->_remove($strInstallationFolder);

				if(!file_exists($strInstallationFolder)){
					$this->_debug("OK",$this->strHexGreen);
					$blRemoved = true;
				}else{
					$this->_debug("FAIL",$this->strHexRed);
				}
			}else{
				$this->_debug("FAIL",$this->strHexRed);
			}

			return $blRemoved;
		}

		protected function _installedModules(){

			$arrOut = array();
			$arrFiles = scandir($this->strModulePath);

			foreach($arrFiles as $strEachFolder){

				$strJsonFile = sprintf('%s/%s/info.json',$this->strModulePath,$strEachFolder);
				if(!in_array($strEachFolder,array('.','..')) && file_exists($strJsonFile)){
					$jsonRaw = file_get_contents($strJsonFile);
					$arrModuleInfo = json_decode($jsonRaw,true);

					$arrNewItem = $this->_blankItem($arrModuleInfo['repository'],$arrModuleInfo['name'],$arrModuleInfo['description'],$arrModuleInfo['author'],$arrModuleInfo['bugs'],$arrModuleInfo['homepage'],$arrModuleInfo['licenced']);
					$arrNewItem['installed'] = '1';
					$arrNewItem['current'] = array(
						'version' => $arrModuleInfo['version'],
						'release' => $arrModuleInfo['release'],
						'size' => 0,
						'channel' => (substr_count($arrModuleInfo['version'],'.') == 2) ? 'stable' : 'development'
					);

					$arrOut[$arrNewItem['key']] = $arrNewItem;
				}
			}

			return $arrOut;
		}

		protected function _installedInterfaces(){

			$arrOut = array();
			$arrFiles = scandir($this->strInterfacePath);

			foreach($arrFiles as $strEachFolder){

				$strJsonFile = sprintf('%s/%s/info.json',$this->strInterfacePath,$strEachFolder);
				if(!in_array($strEachFolder,array('.','..')) && file_exists($strJsonFile)){
					$jsonRaw = file_get_contents($strJsonFile);
					$arrInterfaceInfo = json_decode($jsonRaw,true);

					$arrNewItem = $this->_blankItem($arrInterfaceInfo['repository'],$arrInterfaceInfo['name'],$arrInterfaceInfo['description'],$arrInterfaceInfo['author'],$arrInterfaceInfo['bugs'],$arrInterfaceInfo['homepage'],$arrInterfaceInfo['licenced']);
					$arrNewItem['installed'] = '1';
					$arrNewItem['current'] = array(
						'version' => $arrInterfaceInfo['version'],
						'release' => $arrInterfaceInfo['release'],
						'size' => 0,
						'channel' => (substr_count($arrInterfaceInfo['version'],'.') == 2) ? 'stable' : 'development'
					);

					$arrOut[$arrNewItem['key']] = $arrNewItem;
				}
			}

			return $arrOut;
		}

		protected function _installedCore(){

			$strCoreInfoFile = sprintf('%s/info.json',$this->strFrameworkPath);
			$jsonRaw = file_get_contents($strCoreInfoFile);
			$arrCoreInfo = json_decode($jsonRaw,true);

			$arrOut = $this->_blankItem($arrCoreInfo['repository'],$arrCoreInfo['name'],$arrCoreInfo['description'],$arrCoreInfo['author'],$arrCoreInfo['bugs'],$arrCoreInfo['homepage']);
			$arrOut['installed'] = '1';
			$arrOut['current'] = array(
				'version' => $arrCoreInfo['version'],
				'release' => $arrCoreInfo['release'],
				'size' => 0,
				'channel' => (substr_count($arrCoreInfo['version'],'.') == 2) ? 'stable' : 'development'
			);

			return $arrOut;
		}

		public function installCoreTables(){

			$blOut = true;

			$strInstallFile = sprintf('%s/../install/install.sql',dirname(__FILE__));
			if(file_exists($strInstallFile)){

				$this->_debug("Installing core database tables");
				$this->_databaseConnect();

				//Install the user database
				$strInstallSQL = file_get_contents( $strInstallFile );
				$strInstallSQL = preg_replace("#--[^\n]*\n#i","",$strInstallSQL);
				$arrQueries = explode(";",$strInstallSQL);

				foreach($arrQueries as $strQuery){
					if(trim($strQuery) != ''){
						$strQuery = str_replace('/*TABLE_PREFIX*/`','`'.DATABASE_TABLE_PREFIX,$strQuery);

						if(!$this->_databaseQuery($strQuery)){
							$blOut = false;
						}
					}
				}

				if($blOut){
					$this->_debug("OK",$this->strHexGreen);
				}else{
					$this->_debug("FAIL",$this->strHexRed);
				}
			}else{
				$this->_debug("No tables to be installed");
			}

			return $blOut;
		}

		public function updateCoreTables($strPreviousVersion){

			$blOut = true;
			$strUpdateDir = sprintf('%s/../install/updates',dirname(__FILE__));

			if(file_exists($strUpdateDir)){

				$arrUpdateDirFiles = scandir($strUpdateDir);
				if(count($arrUpdateDirFiles) > 2){

					$this->_debug(sprintf("Updating core database tables from %s",$strPreviousVersion));
					$this->_databaseConnect();

					$arrUpdates = array();

					//Get a list of all the update files
					foreach($arrUpdateDirFiles as $strFile){
						if(!in_array($strFile,array('.','..')) && strstr($strFile,'.sql')){
							$strVersion = str_replace('.sql','',$strFile);
							$arrUpdates[$strVersion] = $strFile;
						}
					}

					ksort($arrUpdates);

					foreach($arrUpdates as $strVersion => $strFile){

						//If hte previous version is older than the version to be installed then install it
						if(version_compare($strPreviousVersion,$strVersion,'lt')){

							$strInstallFile = sprintf('%s/%s',$strUpdateDir,$strFile);
							if(file_exists($strInstallFile)){

								$this->_debug(sprintf("- updating SQL to version %s",$strVersion));

								//Install the user database
								$strInstallSQL = file_get_contents( $strInstallFile );
								$strInstallSQL = preg_replace("#--[^\n]*\n#i","",$strInstallSQL);
								$arrQueries = explode(";",$strInstallSQL);

								foreach($arrQueries as $strQuery){
									if(trim($strQuery) != ''){
										$strQuery = str_replace('/*TABLE_PREFIX*/`','`'.DATABASE_TABLE_PREFIX,$strQuery);

										if(!$this->_databaseQuery($strQuery)){
											$blOut = false;
										}
									}
								}
							}
						}
					}

					if($blOut){
						$this->_debug("OK",$this->strHexGreen);
					}else{
						$this->_debug("FAIL",$this->strHexRed);
					}
				}else{
					$this->_debug("No tables to be updated");
				}
			}else{
				$this->_debug("No tables to be updated");
			}

			return $blOut;
		}

		public function updateCoreSettings(){

			//Process the core settings of the framework
			$strCoreJSON = sprintf('%s/../install/settings.json',dirname(__FILE__));
			if(file_exists($strCoreJSON)){

				$this->_debug("Updating core settings");
				$this->_databaseConnect();

				$jsonData = file_get_contents($strCoreJSON);
				$arrSettings = json_decode($jsonData,true);

				//Add each settings into the settings table
				foreach($arrSettings as $strKey => $arrOptions){
					$this->storeSetting(
						'core',
						'core',
						$strKey,
						$arrOptions['default'],
						$arrOptions['title'],
						$arrOptions['description'],
						$arrOptions['default'],
						$arrOptions['type'],
						$arrOptions['options'],
						$arrOptions['null']
					);
				}
			}else{
				$this->_debug("No settings to be updated");
			}
		}

		public function installPackageTables($strType,$strPackage){

			$blOut = true;
			$strPackageDir = sprintf('%s/../../%s/%s',dirname(__FILE__),$strType,$strPackage);

			$strInstallFile = sprintf('%s/install/install.sql',$strPackageDir);
			if(file_exists($strInstallFile)){

				$this->_debug("Installing package database tables");
				$this->_databaseConnect();

				//Install the user database
				$strInstallSQL = file_get_contents( $strInstallFile );
				$strInstallSQL = preg_replace("#--[^\n]*\n#i","",$strInstallSQL);
				$arrQueries = explode(";",$strInstallSQL);

				foreach($arrQueries as $strQuery){
					if(trim($strQuery) != ''){
						$strQuery = str_replace('/*TABLE_PREFIX*/`','`'.DATABASE_TABLE_PREFIX,$strQuery);

						if(!$this->_databaseQuery($strQuery)){
							$blOut = false;
						}
					}
				}

				if($blOut){
					$this->_debug("OK",$this->strHexGreen);
				}else{
					$this->_debug("FAIL",$this->strHexRed);
				}
			}else{
				$this->_debug("No tables to be installed");
			}

			return $blOut;
		}

		public function updatePackageTables($strType,$strPackage,$strPreviousVersion){

			$blOut = true;
			$strUpdateDir = sprintf('%s/../../%s/%s/install/updates',dirname(__FILE__),$strType,$strPackage);

			if(file_exists($strUpdateDir)){

				$arrUpdateDirFiles = scandir($strUpdateDir);
				if(count($arrUpdateDirFiles) > 2){

					$this->_debug(sprintf("Updating package database tables from %s",$strPreviousVersion));
					$this->_databaseConnect();

					$arrUpdates = array();

					//Get a list of all the update files
					foreach($arrUpdateDirFiles as $strFile){
						if(!in_array($strFile,array('.','..')) && strstr($strFile,'.sql')){
							$strVersion = str_replace('.sql','',$strFile);
							$arrUpdates[$strVersion] = $strFile;
						}
					}

					ksort($arrUpdates);

					foreach($arrUpdates as $strVersion => $strFile){

						//If hte previous version is older than the version to be installed then install it
						if(version_compare($strPreviousVersion,$strVersion,'lt')){

							$strInstallFile = sprintf('%s/%s',$strUpdateDir,$strFile);
							if(file_exists($strInstallFile)){

								$this->_debug(sprintf("- updating SQL to version %s",$strVersion));

								//Install the user database
								$strInstallSQL = file_get_contents( $strInstallFile );
								$strInstallSQL = preg_replace("#--[^\n]*\n#i","",$strInstallSQL);
								$arrQueries = explode(";",$strInstallSQL);

								foreach($arrQueries as $strQuery){
									if(trim($strQuery) != ''){
										$strQuery = str_replace('/*TABLE_PREFIX*/`','`'.DATABASE_TABLE_PREFIX,$strQuery);

										if(!$this->_databaseQuery($strQuery)){
											$blOut = false;
										}
									}
								}
							}
						}
					}

					if($blOut){
						$this->_debug("OK",$this->strHexGreen);
					}else{
						$this->_debug("FAIL",$this->strHexRed);
					}

				}else{
					$this->_debug("No tables to be updated");
				}
			}else{
				$this->_debug("No tables to be updated");
			}

			return $blOut;
		}

		protected function updatePackageSettings($strType,$strPackage){

			$strPackageDir = sprintf('%s/../../%s/%s',dirname(__FILE__),$strType,$strPackage);

			$strSettingsJSON = sprintf("%s/install/settings.json",$strPackageDir);
			if(file_exists($strSettingsJSON)){

				$this->_debug("Updating package settings");
				$this->_databaseConnect();

				//Get the json settings data
				$jsonData = file_get_contents($strSettingsJSON);
				$arrSettings = json_decode($jsonData,true);

				//Add each settings into the settings table
				foreach($arrSettings as $strKey => $arrOptions){
					$this->storeSetting(
						$strPackage,
						$strType,
						$strKey,
						$arrOptions['default'],
						$arrOptions['title'],
						$arrOptions['description'],
						$arrOptions['default'],
						$arrOptions['type'],
						$arrOptions['options'],
						$arrOptions['null']
					);
				}
			}else{
				$this->_debug("No settings to be updated");
			}
		}

		protected function storeSetting($strPackage,$strGroup,$strKey,$mxdValue,$strTitle,$strDescription,$strDefault,$strType,$strOptions,$blNull = false){

			if(DATABASE_PROTOCOL == 'none'){

				$strSettingsJSON = sprintf('%s/../../config/settings.json',dirname(__FILE__));

				if(is_writable(sprintf('%s/../../config/',dirname(__FILE__)))){
					if(!file_exists($strSettingsJSON)){
						file_put_contents($strSettingsJSON,'{}');
					}
				}else{
					throw new \Exception("Error, Twist Framework could not setup its core settings file. Please ensure that the twist/config folder is writable");
				}

				$rawJSON = file_get_contents($strSettingsJSON);
				$arrSettings = json_decode($rawJSON,true);

				if(array_key_exists($strKey,$arrSettings)){

					$arrSettings[$strKey] = array(
						'title' => $strTitle,
						'description' => $strDescription,
						'default' => $strDefault,
						'type' => $strType,
						'options' => $strOptions,
						'null' => ($blNull) ? 1 : 0,
						`deprecated` => 0
					);
				}else{

					$arrSettings[$strKey] = array(
						'package' => $strPackage,
						'group' => strtolower($strGroup),
						'key' => $strKey,
						'value' => $mxdValue,
						'title' => $strTitle,
						'description' => $strDescription,
						'default' => $strDefault,
						'type' => $strType,
						'options' => $strOptions,
						'null' => ($blNull) ? 1 : 0,
						`deprecated` => 0
					);
				}

				file_put_contents($strSettingsJSON,json_encode($arrSettings));

				return true;
			}else{

				$strSQL = sprintf("INSERT INTO `%s`.`%ssettings`
									SET `package` = '%s',
										`group` = '%s',
										`key` = '%s',
										`value` = '%s',
										`title` = '%s',
										`description` = '%s',
										`default` = '%s',
										`type` = '%s',
										`options` = '%s',
										`null` = '%s',
										`deprecated` = '0'
								ON DUPLICATE KEY UPDATE
										`title` = '%s',
										`description` = '%s',
										`default` = '%s',
										`type` = '%s',
										`options` = '%s',
										`null` = '%s',
										`deprecated` = '0'",
					DATABASE_NAME,
					DATABASE_TABLE_PREFIX,
					$this->_databaseEscape($strPackage),
					$this->_databaseEscape(strtolower($strGroup)),
					$this->_databaseEscape(strtoupper($strKey)),
					$this->_databaseEscape($mxdValue),
					$this->_databaseEscape($strTitle),
					$this->_databaseEscape($strDescription),
					$this->_databaseEscape($strDefault),
					$this->_databaseEscape($strType),
					$this->_databaseEscape($strOptions),
					($blNull) ? '1' : '0',
					$this->_databaseEscape($strTitle),
					$this->_databaseEscape($strDescription),
					$this->_databaseEscape($strDefault),
					$this->_databaseEscape($strType),
					$this->_databaseEscape($strOptions),
					($blNull) ? '1' : '0'
				);

				return $this->_databaseQuery($strSQL);
			}
		}


		protected function updateMultiple($arrParameters,$strType){

			$blOverall = true;

			//Verify array
			if(is_array($arrParameters) && count($arrParameters) > 0){
				$blValidParams = true;
				$this->_debug('Checking update parameters');

				foreach($arrParameters as $arrDetails){
					if(!array_key_exists('repo',$arrDetails) || !array_key_exists('package',$arrDetails) || !array_key_exists('version',$arrDetails)){
						$blValidParams = false;
						break;
					}
				}

				if($blValidParams){
					$this->_debug('OK',$this->strHexGreen);
					$intCount = 1;
					$intTotal = count($arrParameters);

					foreach($arrParameters as $arrDetails){

						$this->_debug(sprintf('Installing %d of %d %s',$intCount,$intTotal,$strType));

						if($strType == 'modules'){
							$arrData = $this->_downloadModule($arrDetails['repo'],$arrDetails['package'],$arrDetails['version']);
						}else{
							$arrData = $this->_downloadInterface($arrDetails['repo'],$arrDetails['package'],$arrDetails['version']);
						}

						$this->_debugProgress((100/$intTotal)*($intCount-0.5));
						$blOut = $this->_installFiles($arrData);

						if($blOut == false){
							$blOverall = false;
						}

						$this->_debugProgress((100/$intTotal)*$intCount);
						$intCount++;
					}

				}else{
					$this->_debug('FAIL',$this->strHexRed);
					$blOverall = false;
				}
			}

			return $blOverall;
		}

		public function getErrors(){
			return $this->arrErrors;
		}

		public function getRepositories(){
			return $this->arrRepositories;
		}

		public function authenticate($strRepoKey){
			return $this->_authenticate($strRepoKey);
		}

		public function installRepository($strURL){
			return $this->_install($strURL);
		}

		public function deleteRepository($strRepoKey){

			$blOut = false;

			if(array_key_exists($strRepoKey,$this->arrRepositories)){
				unlink(sprintf('%s/%s.json',$this->strRepoPath,$strRepoKey));
				$this->_loadRepositories();
				$blOut = true;
			}

			return $blOut;
		}

		public function getModules(){

			//Get the installed modules
			$arrModules = $this->_installedModules();

			//Go through the available modules from all providers and add them to the array
			foreach($this->arrRepositories as $strKey => $arrRepoInfo){

				if($arrRepoInfo['enabled'] == '1'){

					$arrAvailable = $this->_modules($strKey);

					foreach($arrAvailable as $strModuleKey => $arrData){
						if(array_key_exists($strModuleKey,$arrModules)){
							$arrModules[$strModuleKey]['available'] = $arrData['available'];

							$intAvailableVersion = (substr_count($arrData['available']['version'],'.') == 2) ? $arrData['available']['version'].'.0' : $arrData['available']['version'];
							$intCurrentVersion = (substr_count($arrModules[$strModuleKey]['current']['version'],'.') == 2) ? $arrModules[$strModuleKey]['current']['version'].'.0' : $arrModules[$strModuleKey]['current']['version'];

							$arrModules[$strModuleKey]['update'] = (version_compare($intCurrentVersion,$intAvailableVersion,'lt')) ? '1' : '0';
						}else{
							$arrModules[$strModuleKey] = $arrData;
						}
					}
				}
			}

			return $arrModules;
		}

		public function getInterfaces(){

			//Get the installed interfaces
			$arrInterfaces = $this->_installedInterfaces();

			//Go through the available modules from all providers and add them to the array
			foreach($this->arrRepositories as $strKey => $arrRepoInfo){

				if($arrRepoInfo['enabled'] == '1'){

					$arrAvailable = $this->_interfaces($strKey);

					foreach($arrAvailable as $strInterfaceKey => $arrData){
						if(array_key_exists($strInterfaceKey,$arrInterfaces)){
							$arrInterfaces[$strInterfaceKey]['available'] = $arrData['available'];

							$intAvailableVersion = (substr_count($arrData['available']['version'],'.') == 2) ? $arrData['available']['version'].'.0' : $arrData['available']['version'];
							$intCurrentVersion = (substr_count($arrInterfaces[$strInterfaceKey]['current']['version'],'.') == 2) ? $arrInterfaces[$strInterfaceKey]['current']['version'].'.0' : $arrInterfaces[$strInterfaceKey]['current']['version'];

							$arrInterfaces[$strInterfaceKey]['update'] = (version_compare($intCurrentVersion,$intAvailableVersion,'lt')) ? '1' : '0';
						}else{
							$arrInterfaces[$strInterfaceKey] = $arrData;
						}
					}
				}
			}

			return $arrInterfaces;
		}

		public function getCore(){

			//Get the installed Core
			$arrCore = $this->_installedCore();
			$arrAvailable = $this->_core();

			if(count($arrAvailable)){

				$arrAvailable = array_pop($arrAvailable);
				$arrCore['available'] = $arrAvailable['available'];

				$intAvailableVersion = (substr_count($arrAvailable['available']['version'],'.') == 2) ? $arrAvailable['available']['version'].'.0' : $arrAvailable['available']['version'];
				$intCurrentVersion = (substr_count($arrCore['current']['version'],'.') == 2) ? $arrCore['current']['version'].'.0' : $arrCore['current']['version'];

				$arrCore['update'] = (version_compare($intCurrentVersion,$intAvailableVersion,'lt')) ? '1' : '0';
			}

			return $arrCore;
		}

		public function enableRepository($strRepo,$blStatus = false){

			$strRepoPath = sprintf('%s/%s.json',$this->strRepoPath,$strRepo);
			if(file_exists($strRepoPath)){
				$jsonRaw = file_get_contents($strRepoPath);
				$arrRepo = json_decode($jsonRaw,true);
				$arrRepo['enabled'] = ($blStatus == true || $blStatus == '1') ? '1' : '0';
				file_put_contents($strRepoPath,json_encode($arrRepo));

				//Re-load repository list
				$this->_loadRepositories();
			}
		}

		public function updateLicence($strRepo,$strLicence,$strToken){

			$strRepoPath = sprintf('%s/%s.json',$this->strRepoPath,$strRepo);
			if(file_exists($strRepoPath)){
				$jsonRaw = file_get_contents($strRepoPath);
				$arrRepo = json_decode($jsonRaw,true);
				$arrRepo['licence'] = str_replace(array('"',"'","\\"),'',$strLicence);
				$arrRepo['token'] = str_replace(array('"',"'","\\"),'',$strToken);
				file_put_contents($strRepoPath,json_encode($arrRepo));

				//Re-load repository list
				$this->_loadRepositories();
			}
		}

		protected function ignoreUserAbort(){
			ignore_user_abort(true);
			set_time_limit(0);
		}

		public function updateCore($strVersion){

			$this->ignoreUserAbort();
			$this->_debugReset();

			$arrData = $this->_downloadCore($strVersion);
			$blStatus = $this->_installFiles($arrData);

			$this->_debugProgress(100);
			$this->_debugSuccess($blStatus);
			$this->_debugComplete(true);

			return $arrData;
		}

		public function updateModule($strRepo,$strPackage,$strVersion){

			$this->ignoreUserAbort();
			$this->_debugReset();

			$arrData = $this->_downloadModule($strRepo,$strPackage,$strVersion);
			$blStatus = $this->_installFiles($arrData);

			$this->_debugProgress(100);
			$this->_debugSuccess($blStatus);
			$this->_debugComplete(true);

			return $arrData;
		}

		public function updateModules($arrModules){

			$this->ignoreUserAbort();
			$this->_debugReset();
			$blStatus = $this->updateMultiple($arrModules,'modules');

			$this->_debug('Finished update process');
			($blStatus) ? $this->_debug('OK',$this->strHexGreen) : $this->_debug('FAIL',$this->strHexRed);

			$this->_debugProgress(100);
			$this->_debugSuccess($blStatus);
			$this->_debugComplete(true);

			return $blStatus;
		}

		public function removeModule($strPackage){

			$this->ignoreUserAbort();
			$this->_debugReset();

			$arrData = array(
				'package' => $strPackage,
				'package-type' => 'modules'
			);

			$blStatus = $this->_removeFiles($arrData);

			$this->_debugProgress(100);
			$this->_debugSuccess($blStatus);
			$this->_debugComplete(true);

			return $arrData;
		}

		public function updateInterface($strRepo,$strPackage,$strVersion){

			$this->ignoreUserAbort();
			$this->_debugReset();

			$arrData = $this->_downloadInterface($strRepo,$strPackage,$strVersion);
			$blStatus = $this->_installFiles($arrData);

			$this->_debugProgress(100);
			$this->_debugSuccess($blStatus);
			$this->_debugComplete(true);

			return $arrData;
		}

		public function updateInterfaces($arrInterfaces){

			$this->ignoreUserAbort();
			$this->_debugReset();
			$blStatus = $this->updateMultiple($arrInterfaces,'interfaces');

			$this->_debug('Finished update process');
			($blStatus) ? $this->_debug('OK',$this->strHexGreen) : $this->_debug('FAIL',$this->strHexRed);

			$this->_debugProgress(100);
			$this->_debugSuccess($blStatus);
			$this->_debugComplete(true);

			return $blStatus;
		}

		public function removeInterface($strPackage){

			$this->ignoreUserAbort();
			$this->_debugReset();

			$arrData = array(
				'package' => $strPackage,
				'package-type' => 'interfaces'
			);

			$blStatus = $this->_removeFiles($arrData);

			$this->_debugProgress(100);
			$this->_debugSuccess($blStatus);
			$this->_debugComplete(true);

			return $arrData;
		}
	}