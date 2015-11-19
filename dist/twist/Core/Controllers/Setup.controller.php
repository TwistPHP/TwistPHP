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

namespace Twist\Core\Controllers;

/**
 * The route controller for the Setup wizard that provides the TwistPHP with its in intuitive in-browser setup wizard.
 * @package Twist\Core\Controllers
 */
class Setup extends Base{

	/**
	 * @alias welcome
	 * @return string
	 */
	public function _index(){
		return $this->welcome();
	}

	/**
	 * Welcome page, this is the start page for the setup process
	 * @return string
	 */
	public function welcome(){

		setcookie('twist_setup_test','1');

		\Twist::Session()->data('twist-setup',array(
			'licence' => array('status' => false),
			'checks' => array('status' => false),
			'database' => array('status' => false,'details' => array('table_prefix' => 'twist_')),
			'settings' => array('status' => false),
			'user' => array('status' => false),
			'interfaces' => array('status' => false),
		));

		/**
		 * Update the .htaccess file to be a TwistPHP htaccess file
		 */
		$dirHTaccessFile = sprintf('%s/.htaccess',dirname($_SERVER['SCRIPT_FILENAME']));

		if(file_exists($dirHTaccessFile)){
			\Twist::File()->move($dirHTaccessFile,sprintf('%s/old.htaccess',dirname($_SERVER['SCRIPT_FILENAME'])));
		}

		file_put_contents($dirHTaccessFile,"# TWISTPHP\nRewriteEngine on\n# Routes Rewrite to allow for dynamic pages\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [L,QSA]\n# /TWISTPHP");

		return \Twist::View()->build('pages/welcome.tpl');
	}

	/**
	 * Licence Agreement page, the user must agree to the licence to use the software
	 * @return string
	 */
	public function licence(){

		$strLicenceText = file_get_contents(sprintf('%s/LICENCE.txt',TWIST_FRAMEWORK));
		$strLicenceText = str_replace(array("\n\n","\n"),array("<br><br>"," "),$strLicenceText);

		$arrTags = array('licence' => $strLicenceText);

		return \Twist::View()->build('pages/licence.tpl',$arrTags);
	}

	/**
	 * Check that all the prerequisite have been met by the users server/hosting configuration.
	 * The framework requires PHP5.3+ and the files/folders need to be writable by the PHP user.
	 * @return string
	 */
	public function checks(){

		$arrSession = \Twist::Session()->data('twist-setup');

		$blVersion = (version_compare(PHP_VERSION, '5.3.0') >= 0);
		$blPermissions = (is_dir(TWIST_DOCUMENT_ROOT) && is_writable(TWIST_DOCUMENT_ROOT));

		try{ $blCurl = (function_exists('curl_init') || class_exists('curl_init')); }catch(\Exception $resException){ $blCurl = false; }
		try{ $blMysql = (function_exists('mysql_connect') || function_exists('mysqli_connect')); }catch(\Exception $resException){ $blMysql = false; }
		try{ $blZip = (function_exists('ZipArchive') || class_exists('ZipArchive')); }catch(\Exception $resException){ $blZip = false; }
		try{ $blMultiByte = function_exists('mb_strlen'); }catch(\Exception $resException){ $blMultiByte = false; }

		$blCookies = (is_array($_COOKIE) && array_key_exists('twist_setup_test',$_COOKIE));

		$blPassChecks = ($blVersion && $blPermissions);

		$arrChecks = array(
			'php_version' => ($blVersion) ? 'success' : 'error',
			'file_permissions' => ($blPermissions) ? 'success' : 'error',
			'php_curl' => ($blCurl) ? 'success' : 'warning',
			'php_mysql' => ($blMysql) ? 'success' : 'warning',
			'php_zip' => ($blZip) ? 'success' : 'warning',
			'php_multibyte' => ($blMultiByte) ? 'success' : 'warning',
			'php_cookies' => ($blCookies) ? 'success' : 'warning',
			'continue_status' => ($blPassChecks) ? '' : ' hidden'
		);

		$arrSession['checks']['status'] = $blPassChecks;
		$arrSession['checks']['details'] = $arrChecks;

		\Twist::Session()->data('twist-setup',$arrSession);

		return \Twist::View()->build('pages/checks.tpl',$arrChecks);
	}

	/**
	 * Setup a database connection or choose not to use one.
	 * @return string
	 */
	public function database(){

		$arrSession = \Twist::Session()->data('twist-setup');

		if(!$arrSession['checks']['status']){
			header('Location: checks');
		}

		$arrTags = array('error_message' => '');

		if(array_key_exists('message',$arrSession['database'])){
			$arrTags = array('error_message' => '<p class="error"><strong>Error:</strong> '.$arrSession['database']['message'].'</p>');
			unset($arrSession['database']['message']);
			\Twist::Session()->data('twist-setup',$arrSession);
		}

		return \Twist::View()->build('pages/database.tpl',$arrTags);
	}

	/**
	 * Set all your initial settings so that you can be up and running faster.
	 * @return string
	 */
	public function settings(){

		$arrSession = \Twist::Session()->data('twist-setup');

		if(array_key_exists('protocol',$_POST) && array_key_exists('host',$_POST)){

			$arrSession['database']['details'] = array(
				'type' => (array_key_exists('protocol',$_POST) && $_POST['protocol'] === 'none') ? 'json' : 'database',
				'protocol' => $_POST['protocol'],
				'host' => ($_POST['host'] === '') ? 'localhost' : $_POST['host'],
				'username' => $_POST['username'],
				'password' => $_POST['password'],
				'name' => $_POST['database'],
				'table_prefix' => $_POST['table_prefix'],
			);

			if($arrSession['database']['details']['type'] === 'database'){

				//Check to see if the connection settings are valid
				try{

					$objDatabase = \Twist::Database('setup-test');
					$objDatabase->connect(
						$arrSession['database']['details']['host'],
						$arrSession['database']['details']['username'],
						$arrSession['database']['details']['password'],
						$arrSession['database']['details']['name'],
						$arrSession['database']['details']['protocol']
					);
					$objDatabase->close();

					$arrSession['database']['status'] = true;

				}catch(\Exception $resException){
					$arrSession['database']['status'] = false;
					$arrSession['database']['message'] = $resException->getMessage();
				}
			}elseif($arrSession['database']['details']['type'] === 'json'){
				$arrSession['database']['status'] = true;
			}

			\Twist::Session()->data('twist-setup',$arrSession);
		}

		if(!$arrSession['database']['status']){
			header('Location: database');
		}

		if(rtrim(TWIST_DOCUMENT_ROOT,'/') === dirname($_SERVER['SCRIPT_FILENAME'])){
			$strSiteRoot = '/';
		}elseif(strstr(rtrim(TWIST_DOCUMENT_ROOT,'/'),dirname($_SERVER['SCRIPT_FILENAME']))){
			$strSiteRoot = '/'.ltrim(str_replace(dirname($_SERVER['SCRIPT_FILENAME']),"",rtrim(TWIST_DOCUMENT_ROOT,'/')),'/');
		}else{
			$strSiteRoot = '/'.ltrim(str_replace(rtrim(TWIST_DOCUMENT_ROOT,'/'),"",dirname($_SERVER['SCRIPT_FILENAME'])),'/');
		}

		$arrTags = array(
			'error_message' => '',
			'relative_path' => rtrim(TWIST_DOCUMENT_ROOT,'/').'/',
			'site_root' => ltrim($strSiteRoot,'/'),
			'app_path' => ($strSiteRoot === '/') ? '' : ltrim($strSiteRoot,'/').'/app',
			'packages_path' => ($strSiteRoot === '/') ? '' : ltrim($strSiteRoot,'/').'/packages',
			'uploads_path' => ($strSiteRoot === '/') ? '' : ltrim($strSiteRoot,'/').'/uploads',
		);

		if(array_key_exists($arrSession['settings']['details'],'site_root')){
			$arrTags['site_root'] = $arrSession['settings']['details']['site_root'];
		}

		if(array_key_exists($arrSession['settings']['details'],'app_path')){
			$arrTags['app_path'] = $arrSession['settings']['details']['app_path'];
		}

		if(array_key_exists($arrSession['settings']['details'],'packages_path')){
			$arrTags['packages_path'] = $arrSession['settings']['details']['packages_path'];
		}

		if(array_key_exists($arrSession['settings']['details'],'uploads_path')){
			$arrTags['uploads_path'] = $arrSession['settings']['details']['uploads_path'];
		}

		if(array_key_exists('message',$arrSession['settings'])){
			$arrTags = array('error_message' => '<p class="error">'.$arrSession['settings']['message'].'</p>');
			unset($arrSession['settings']['message']);
			\Twist::Session()->data('twist-setup',$arrSession);
		}

		return \Twist::View()->build('pages/settings.tpl',$arrTags);
	}

	/**
	 * Setup an initial (super admin, level 0) user account, this is only used at the moment if you have a DB setup, otherwise this step is skipped.
	 * @return string
	 */
	public function user(){

		$arrSession = \Twist::Session()->data('twist-setup');

		if(array_key_exists('site_name',$_POST)){

			$arrSession['settings']['details'] = array(
				'site_name' => $_POST['site_name'],
				'site_host' => $_POST['site_host'],
				'site_www' => (array_key_exists('site_www',$_POST)) ? $_POST['site_www'] : '0',
				'http_protocol' => $_POST['http_protocol'],
				'http_protocol_force' => (array_key_exists('http_protocol_force',$_POST)) ? $_POST['http_protocol_force'] : '0',
				'timezone' => $_POST['timezone'],
				'relative_path' => $_POST['relative_path'],
				'site_root' => trim($_POST['site_root'],'/'),
				'app_path' => ($_POST['app_path'] == '') ? 'app' :  trim($_POST['app_path'],'/'),
				'packages_path' => ($_POST['packages_path'] == '') ? 'packages' : trim($_POST['packages_path'],'/'),
				'uploads_path' => ($_POST['uploads_path'] == '') ? 'uploads' : trim($_POST['uploads_path'],'/')
			);

			if($arrSession['settings']['details']['site_name'] != '' &&
				$arrSession['settings']['details']['site_host'] != '' &&
				$arrSession['settings']['details']['http_protocol'] != '' &&
				$arrSession['settings']['details']['timezone'] != '' &&
				is_dir(sprintf('%s%s',$arrSession['settings']['details']['relative_path'],$arrSession['settings']['details']['site_root']))){
				$arrSession['settings']['status'] = true;
			}else{
				$arrSession['settings']['status'] = false;
				$arrSession['settings']['message'] = 'Error, you must fill in all setting fields';
			}

			\Twist::Session()->data('twist-setup',$arrSession);
		}

		if(!$arrSession['settings']['status']){
			header('Location: settings');
		}

		$arrTags = array('error_message' => '');

		if(array_key_exists('message',$arrSession['user'])){
			$arrTags = array('error_message' => '<p class="error">'.$arrSession['user']['message'].'</p>');
			unset($arrSession['user']['message']);
			\Twist::Session()->data('twist-setup',$arrSession);
		}

		//Skip the user creation as no database is required
		if($arrSession['database']['details']['protocol'] === 'none'){

			$arrSession['user']['details'] = array(
				'firstname' => '',
				'lastname' => '',
				'email' => '',
				'password' => '',
				'confirm_password' => ''
			);
			$arrSession['user']['status'] = true;
			\Twist::Session()->data('twist-setup',$arrSession);

			header('Location: interfaces');
		}

		return \Twist::View()->build('pages/user.tpl',$arrTags);
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function interfaces(){

		$arrSession = \Twist::Session()->data('twist-setup');

		if(array_key_exists('firstname',$_POST) &&
			array_key_exists('lastname',$_POST) &&
			array_key_exists('email',$_POST) &&
			array_key_exists('password',$_POST) &&
			array_key_exists('confirm_password',$_POST)){

			$arrSession['user']['details'] = array(
				'firstname' => $_POST['firstname'],
				'lastname' => $_POST['lastname'],
				'email' => $_POST['email'],
				'password' => $_POST['password'],
				'confirm_password' => $_POST['confirm_password']
			);

			if($arrSession['user']['details']['firstname'] != '' &&
				$arrSession['user']['details']['lastname'] != '' &&
				$arrSession['user']['details']['email'] != '' &&
				$arrSession['user']['details']['password'] != ''){

				if($arrSession['user']['details']['password'] === $arrSession['user']['details']['confirm_password']){
					$arrSession['user']['status'] = true;
				}else{
					$arrSession['user']['status'] = false;
					$arrSession['user']['message'] = 'Error, your passwords did not match';
				}
			}else{
				$arrSession['user']['status'] = false;
				$arrSession['user']['message'] = 'Error, you must fill in all user fields';
			}

			\Twist::Session()->data('twist-setup',$arrSession);
		}

		if($arrSession['user']['status']){
			//@todo Skip the interfaces step for the time being, it will become packages when ready
			header('Location: finish');
		}else{
			header('Location: user');
		}

		//return \Twist::View()->build('pages/interfaces.tpl',$arrTags);
	}

	/**
	 * Final step, all the settings and tables are created, the user is then shown a finish page.
	 * @return string
	 * @throws \Exception
	 */
	public function finish(){

		//Remove the cookie test cookie
		setcookie('twist_setup_test',null,0);

		$arrSession = \Twist::Session()->data('twist-setup');

		return '<pre>'.print_r($arrSession, true).'</pre>';

		//Create all the required folders for twist
		$strApplicationPath = sprintf('%s%s/',$arrSession['settings']['details']['relative_path'],$arrSession['settings']['details']['app_path']);

		$resFile = \Twist::File();
		$resFile->recursiveCreate(sprintf('%s%s',$arrSession['settings']['details']['relative_path'],$arrSession['settings']['details']['packages_path']));
		$resFile->recursiveCreate(sprintf('%s%s',$arrSession['settings']['details']['relative_path'],$arrSession['settings']['details']['uploads_path']));
		$resFile->recursiveCreate(sprintf('%sAssets',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sCache',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sConfig',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sControllers',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sLogs',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sModels',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources/css',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources/fonts',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources/images',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources/js',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sViews',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sTwist',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sPackages',$strApplicationPath));

		//Create the config in the apps/config folder
		$arrConfigTags = array(
			'account_token' => '',
			'licence_key' => '',
			'database_protocol' => $arrSession['database']['details']['protocol'],
			'database_server' => $arrSession['database']['details']['host'],
			'database_username' => $arrSession['database']['details']['username'],
			'database_password' => $arrSession['database']['details']['password'],
			'database_name' => $arrSession['database']['details']['name'],
			'database_table_prefix' => $arrSession['database']['details']['table_prefix'],
		);

		file_put_contents(sprintf('%sConfig/config.php',$strApplicationPath),\Twist::View()->build('config.tpl',$arrConfigTags));

		\Twist::define('_TWIST_PUBLIC_ROOT',TWIST_DOCUMENT_ROOT.'/'.$arrSession['settings']['details']['site_root']);

		\Twist::define('_TWIST_APP',TWIST_DOCUMENT_ROOT.'/'.$arrSession['settings']['details']['app_path']);
		\Twist::define('_TWIST_APP_CONFIG',_TWIST_APP.'/Config/');
		\Twist::define('_TWIST_PACKAGES',TWIST_DOCUMENT_ROOT.'/'.$arrSession['settings']['details']['packages_path']);
		\Twist::define('_TWIST_UPLOADS',TWIST_DOCUMENT_ROOT.'/'.$arrSession['settings']['details']['uploads_path']);

		if($arrSession['database']['details']['type'] === 'database'){

			\Twist::Database()->connect(
				$arrSession['database']['details']['host'],
				$arrSession['database']['details']['username'],
				$arrSession['database']['details']['password'],
				$arrSession['database']['details']['name'],
				$arrSession['database']['details']['protocol']
			);

			\Twist::define('TWIST_DATABASE_PROTOCOL',$arrSession['database']['details']['protocol']);
			\Twist::define('TWIST_DATABASE_NAME',$arrSession['database']['details']['name']);
			\Twist::define('TWIST_DATABASE_HOST',$arrSession['database']['details']['host']);
			\Twist::define('TWIST_DATABASE_USERNAME',$arrSession['database']['details']['username']);
			\Twist::define('TWIST_DATABASE_PASSWORD',$arrSession['database']['details']['password']);
			\Twist::define('TWIST_DATABASE_TABLE_PREFIX',$arrSession['database']['details']['table_prefix']);

			//Disable file config as we are using database
			\Twist::framework()->settings()->fileConfigOverride(false);

			$this->importSQL(sprintf('%sinstall.sql',TWIST_FRAMEWORK_INSTALL));
		}

		//Update all the core settings, add to a file when no Database is being used
		$this->importSettings(sprintf('%ssettings.json',TWIST_FRAMEWORK_INSTALL));

		//Add new settings to the chosen settings storage method
		\Twist::framework()->setting('SITE_NAME',$arrSession['settings']['details']['site_name']);
		\Twist::framework()->setting('SITE_HOST',$arrSession['settings']['details']['site_host']);
		\Twist::framework()->setting('SITE_WWW',$arrSession['settings']['details']['site_www']);
		\Twist::framework()->setting('SITE_PROTOCOL',$arrSession['settings']['details']['http_protocol']);
		\Twist::framework()->setting('SITE_PROTOCOL_FORCE',$arrSession['settings']['details']['http_protocol_force']);
		\Twist::framework()->setting('TIMEZONE',$arrSession['settings']['details']['timezone']);

		//Create the level 0 user into the system - this will only occur is a database connection is present
		if($arrSession['user']['status'] && $arrSession['database']['details']['protocol'] != 'none'){

			$objUser = \Twist::User()->create();

			$objUser->firstname($arrSession['user']['details']['firstname']);
			$objUser->surname($arrSession['user']['details']['lastname']);
			$objUser->email($arrSession['user']['details']['email']);
			$objUser->password($arrSession['user']['details']['password']);
			$objUser->level(0);
			$intUserID = $objUser->commit();
		}

		\Twist::Session()->remove('twist-setup');

		/**
		 * Update the index.php file to be a TwistPHP index file
		 */
		$dirIndexFile = sprintf('%s/index.php',_TWIST_PUBLIC_ROOT);

		if(file_exists($dirIndexFile)){
			\Twist::File()->move($dirIndexFile,sprintf('%s/old-index.php',_TWIST_PUBLIC_ROOT));
		}

		//Later on we can add in example templates etc if required
		$arrIndexTags = array(
			'public_path' => rtrim(_TWIST_PUBLIC_ROOT,'/'),
			'app_path' => _TWIST_APP,
			'packages_path' => _TWIST_PACKAGES,
			'uploads_path' => _TWIST_UPLOADS,
			'framework_path' => TWIST_FRAMEWORK,
			'interfaces' => '',
			'routes' => '',
			'serve' => 'Twist::Route() -> serve();'
		);

		file_put_contents($dirIndexFile,\Twist::View()->build(sprintf('%s/default-index.tpl',TWIST_FRAMEWORK_VIEWS),$arrIndexTags));

		/**
		 * Update the .htaccess file to be a TwistPHP htaccess file
		 */
		$dirHTaccessFile = sprintf('%s/.htaccess',_TWIST_PUBLIC_ROOT);
		file_put_contents($dirHTaccessFile,\Twist::View()->build(sprintf('%s/default-htaccess.tpl',TWIST_FRAMEWORK_VIEWS),array('rewrite_rules' => '')));

		return \Twist::View()->build('pages/finish.tpl');
	}

	/**
	 * Install any DB and tables required by the framework
	 * @param $dirInstallSQL
	 */
	protected function importSQL($dirInstallSQL){

		if(file_exists($dirInstallSQL)) {

			//Create a temp file with all the required table pre-fixes
			$dirImportFile = tempnam(sys_get_temp_dir(), 'twist-import');

			$blMBSupport = \Twist::Database()->mbSupport();

			file_put_contents($dirImportFile, str_replace(
				array(
					"/*TWIST_DATABASE_TABLE_PREFIX*/`",
					"/*TWIST_DATABASE_NAME*/",
					($blMBSupport) ? 'utf8' : 'utf8mb4' //Only replace utf8mb4 with utf8 when there is no multi-byte support
					),
				array(
					sprintf('`%s', TWIST_DATABASE_TABLE_PREFIX),
					TWIST_DATABASE_NAME,
					'utf8'
				),
				file_get_contents($dirInstallSQL)
			));

			//Import the SQL form the temp file
			\Twist::Database()->importSQL($dirImportFile);

			//Remove the temp file form the system
			unlink($dirImportFile);
		}
	}

	/**
	 * Install any framework settings that are required by the core.
	 * @param $dirSettingsJSON
	 * @throws \Exception
	 */
	public function importSettings($dirSettingsJSON){

		if(file_exists($dirSettingsJSON)){

			$arrSettings = json_decode(file_get_contents($dirSettingsJSON),true);
			if(count($arrSettings)){

				foreach($arrSettings as $strKey => $arrOptions){

					\Twist::framework()->settings()->install(
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
			}
		}
	}
}