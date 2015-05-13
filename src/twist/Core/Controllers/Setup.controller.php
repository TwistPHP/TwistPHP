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
use Twist\Core\Classes\BaseController;

class Setup extends BaseController{

	public function _default(){
		return $this->welcome();
	}

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

	public function licence(){

		$strLicenceText = file_get_contents(sprintf('%s/LICENCE.txt',DIR_FRAMEWORK));
		$strLicenceText = str_replace(array("\n\n","\n"),array("<br><br>"," "),$strLicenceText);

		$arrTags = array('licence' => $strLicenceText);

		return \Twist::View()->build('pages/licence.tpl',$arrTags);
	}

	public function checks(){

		$arrSession = \Twist::Session()->data('twist-setup');

		$blVersion = (version_compare(PHP_VERSION, '5.3.0') >= 0);
		$blPermissions = (is_dir(DIR_FRAMEWORK) && is_writable(DIR_FRAMEWORK) && is_dir(DIR_FRAMEWORK_CONFIG) && is_writable(DIR_FRAMEWORK_CONFIG));
		$blCurl = (function_exists('curl_init') || class_exists('curl_init'));
		$blMysql = (function_exists('mysql_connect') || function_exists('mysqli_connect'));
		$blZip = (function_exists('ZipArchive') || class_exists('ZipArchive'));
		$blCookies = (is_array($_COOKIE) && array_key_exists('twist_setup_test',$_COOKIE));

		$blPassChecks = ($blVersion && $blPermissions);

		$arrChecks = array(
			'php_version' => ($blVersion) ? 'success' : 'error',
			'file_permissions' => ($blPermissions) ? 'success' : 'error',
			'php_curl' => ($blCurl) ? 'success' : 'warning',
			'php_mysql' => ($blMysql) ? 'success' : 'warning',
			'php_zip' => ($blZip) ? 'success' : 'warning',
			'php_cookies' => ($blCookies) ? 'success' : 'warning',
			'continue_status' => ($blPassChecks) ? '' : ' hidden'
		);

		$arrSession['checks']['status'] = $blPassChecks;
		$arrSession['checks']['details'] = $arrChecks;

		\Twist::Session()->data('twist-setup',$arrSession);

		return \Twist::View()->build('pages/checks.tpl',$arrChecks);
	}

	public function database(){

		$arrSession = \Twist::Session()->data('twist-setup');

		if($arrSession['checks']['status'] == false){
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

	public function settings(){

		$arrSession = \Twist::Session()->data('twist-setup');

		if(array_key_exists('protocol',$_POST) && array_key_exists('host',$_POST)){

			$arrSession['database']['details'] = array(
				'type' => (array_key_exists('protocol',$_POST) && $_POST['protocol'] == 'none') ? 'json' : 'database',
				'protocol' => $_POST['protocol'],
				'host' => ($_POST['host'] == '') ? 'localhost' : $_POST['host'],
				'username' => $_POST['username'],
				'password' => $_POST['password'],
				'name' => $_POST['database'],
				'table_prefix' => $_POST['table_prefix'],
			);

			if($arrSession['database']['details']['type'] == 'database'){

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
			}elseif($arrSession['database']['details']['type'] == 'json'){
				$arrSession['database']['status'] = true;
			}

			\Twist::Session()->data('twist-setup',$arrSession);
		}

		if($arrSession['database']['status'] == false){
			header('Location: database');
		}

		if(rtrim(DIR_BASE,'/') == dirname($_SERVER['SCRIPT_FILENAME'])){
			$strSiteRoot = '/';
		}elseif(strstr(rtrim(DIR_BASE,'/'),dirname($_SERVER['SCRIPT_FILENAME']))){
			$strSiteRoot = '/'.ltrim(str_replace(dirname($_SERVER['SCRIPT_FILENAME']),"",rtrim(DIR_BASE,'/')),'/');
		}else{
			$strSiteRoot = '/'.ltrim(str_replace(rtrim(DIR_BASE,'/'),"",dirname($_SERVER['SCRIPT_FILENAME'])),'/');
		}

		$arrTags = array(
			'error_message' => '',
			'relative_path' => rtrim(DIR_BASE,'/').'/',
			'site_root' => ltrim($strSiteRoot,'/'),
			'app_path' => ($strSiteRoot == '/') ? '' : ltrim($strSiteRoot,'/').'/app',
			'packages_path' => ($strSiteRoot == '/') ? '' : ltrim($strSiteRoot,'/').'/packages',
			'uploads_path' => ($strSiteRoot == '/') ? '' : ltrim($strSiteRoot,'/').'/uploads',
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

		if($arrSession['settings']['status'] == false){
			header('Location: settings');
		}

		$arrTags = array('error_message' => '');

		if(array_key_exists('message',$arrSession['user'])){
			$arrTags = array('error_message' => '<p class="error">'.$arrSession['user']['message'].'</p>');
			unset($arrSession['user']['message']);
			\Twist::Session()->data('twist-setup',$arrSession);
		}

		//Skip the user creation as no database is required
		if($arrSession['database']['details']['protocol'] == 'none'){

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

				if($arrSession['user']['details']['password'] == $arrSession['user']['details']['confirm_password']){
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

		if($arrSession['user']['status'] == false){
			header('Location: user');
		}else{
			//@todo Skip the interfaces step for the time being, it will become packages when ready
			header('Location: finish');
		}

		$arrTags = array('interfaces' => '');
		$arrInterfaces = \Twist::framework()->upgrade()->getInterfaces();

		foreach($arrInterfaces as $arrEachInterface){
			if($arrEachInterface['installed'] == '0' && $arrEachInterface['licenced'] == '0'){

				$arrEachInterface['checked'] = ($arrEachInterface['name'] == 'Manager' && $arrEachInterface['repository'] == 'twistphp') ? ' checked' : '';
				$arrEachInterface['recommended'] = ($arrEachInterface['name'] == 'Manager' && $arrEachInterface['repository'] == 'twistphp') ? ' (Recommended)' : '';

				$arrTags['interfaces'] .= \Twist::View()->build('components/interface-each.tpl',$arrEachInterface);
			}
		}

		return \Twist::View()->build('pages/interfaces.tpl',$arrTags);
	}

	public function finish(){

		//Remove the cookie test cookie
		setcookie('twist_setup_test',null,0);

		$arrSession = \Twist::Session()->data('twist-setup');

		//Create all the required folders for twist
		$strApplicationPath = sprintf('%s%s/',$arrSession['settings']['details']['relative_path'],$arrSession['settings']['details']['app_path']);

		$resFile = \Twist::File();
		$resFile->recursiveCreate(sprintf('%s%s',$arrSession['settings']['details']['relative_path'],$arrSession['settings']['details']['packages_path']));
		$resFile->recursiveCreate(sprintf('%s%s',$arrSession['settings']['details']['relative_path'],$arrSession['settings']['details']['uploads_path']));
		$resFile->recursiveCreate(sprintf('%sAssets',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sCache',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sConfig',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sControllers',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sModels',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources/css',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources/images',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sResources/js',$strApplicationPath));
		$resFile->recursiveCreate(sprintf('%sViews',$strApplicationPath));

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

		$arrAppTags = array(
			'site_root' => $arrSession['settings']['details']['site_root'],
			'app_path' => $arrSession['settings']['details']['app_path'],
			'packages_path' => $arrSession['settings']['details']['packages_path'],
			'uploads_path' => $arrSession['settings']['details']['uploads_path'],
		);

		//Create the apps config in the twist/config folder
		file_put_contents(sprintf('%sConfig/app.php',DIR_FRAMEWORK),\Twist::View()->build('app.tpl',$arrAppTags));

		\Twist::define('DIR_SITE_ROOT',DIR_BASE.$arrSession['settings']['details']['site_root']);
		\Twist::define('DIR_APP',DIR_BASE.$arrSession['settings']['details']['app_path']);
		\Twist::define('DIR_APP_CONFIG',DIR_APP.'/config/');
		\Twist::define('DIR_PACKAGES',DIR_BASE.$arrSession['settings']['details']['packages_path']);
		\Twist::define('DIR_UPLOADS',DIR_BASE.$arrSession['settings']['details']['uploads_path']);

		if($arrSession['database']['details']['type'] == 'database'){

			\Twist::Database()->connect(
				$arrSession['database']['details']['host'],
				$arrSession['database']['details']['username'],
				$arrSession['database']['details']['password'],
				$arrSession['database']['details']['name'],
				$arrSession['database']['details']['protocol']
			);

			\Twist::define('DATABASE_PROTOCOL',$arrSession['database']['details']['protocol']);
			\Twist::define('DATABASE_NAME',$arrSession['database']['details']['name']);
			\Twist::define('DATABASE_TABLE_PREFIX',$arrSession['database']['details']['table_prefix']);

			//Disable file config as we are using database
			\Twist::framework()->settings()->fileConfigOverride(false);

			\Twist::framework()->upgrade()->databaseSettings(
				$arrSession['database']['details']['protocol'],
				$arrSession['database']['details']['host'],
				$arrSession['database']['details']['name'],
				$arrSession['database']['details']['username'],
				$arrSession['database']['details']['password'],
				$arrSession['database']['details']['table_prefix']
			);

			//Install the core tables - Database Only
			\Twist::framework()->upgrade()->installCoreTables();
		}

		//Update all the core settings, add to a file when no Database is being used
		\Twist::framework()->upgrade()->updateCoreSettings();

		//Add new settings to the chosen settings storage method
		\Twist::framework()->setting('SITE_NAME',$arrSession['settings']['details']['site_name']);
		\Twist::framework()->setting('SITE_HOST',$arrSession['settings']['details']['site_host']);
		\Twist::framework()->setting('SITE_WWW',$arrSession['settings']['details']['site_www']);
		\Twist::framework()->setting('SITE_PROTOCOL',$arrSession['settings']['details']['http_protocol']);
		\Twist::framework()->setting('SITE_PROTOCOL_FORCE',$arrSession['settings']['details']['http_protocol_force']);
		\Twist::framework()->setting('TIMEZONE',$arrSession['settings']['details']['timezone']);

		//Create the level 0 user into the system - this will only occur is a database connection is present
		if($arrSession['user']['status'] == true && $arrSession['database']['details']['protocol'] != 'none'){

			$objUser = \Twist::User()->create();

			$objUser->firstname($arrSession['user']['details']['firstname']);
			$objUser->surname($arrSession['user']['details']['lastname']);
			$objUser->email($arrSession['user']['details']['email']);
			$objUser->password($arrSession['user']['details']['password']);
			$objUser->level(0);
			$intUserID = $objUser->commit();
		}

		\Twist::Session()->remove('twist-setup');

		$arrInterfaces = array();

		//Install any interfaces that has been selected at this point
		foreach($_POST as $strKey => $arrValue){
			if(strstr($strKey,'interface-') && array_key_exists('install',$arrValue)){
				\Twist::framework()->upgrade()->updateInterface($arrValue['repo'],$arrValue['package'],$arrValue['package-version']);
				$arrInterfaces[] = sprintf("Twist::Route() -> ui('/%s/%%','%s');",strtolower($arrValue['package']),$arrValue['package']);
			}
		}

		/**
		 * Update the index.php file to be a TwistPHP index file
		 */
		$dirIndexFile = sprintf('%s/index.php',DIR_SITE_ROOT);

		if(file_exists($dirIndexFile)){
			\Twist::File()->move($dirIndexFile,sprintf('%s/old-index.php',DIR_SITE_ROOT));
		}

		//Later on we can add in example templates etc if required
		$arrIndexTags = array(
			'framework_path' => DIR_FRAMEWORK,
			'interfaces' => implode("\n\t\t",$arrInterfaces),
			'routes' => '',
			'serve' => 'Twist::Route() -> serve();'
		);

		file_put_contents($dirIndexFile,\Twist::View()->build(sprintf('%s/default-index.tpl',DIR_FRAMEWORK_VIEWS),$arrIndexTags));

		/**
		 * Update the .htaccess file to be a TwistPHP htaccess file
		 */
		$dirHTaccessFile = sprintf('%s/.htaccess',DIR_SITE_ROOT);
		file_put_contents($dirHTaccessFile,\Twist::View()->build(sprintf('%s/default-htaccess.tpl',DIR_FRAMEWORK_VIEWS)));

		return \Twist::View()->build('pages/finish.tpl');
	}
}