<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
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

namespace Twist\Core\Controllers;
use Twist\Core\Models\Install;

/**
 * The route controller for the Install wizard that provides the TwistPHP with its in intuitive in-browser install wizard.
 * @package Twist\Core\Controllers
 */
class InstallWizard extends Base{

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
			'packages' => array('status' => false),
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

		if(array_key_exists('site_root', $arrSession['settings']['details'])){
			$arrTags['site_root'] = $arrSession['settings']['details']['site_root'];
		}

		if(array_key_exists('app_path', $arrSession['settings']['details'])){
			$arrTags['app_path'] = $arrSession['settings']['details']['app_path'];
		}

		if(array_key_exists('packages_path', $arrSession['settings']['details'])){
			$arrTags['packages_path'] = $arrSession['settings']['details']['packages_path'];
		}

		if(array_key_exists('uploads_path', $arrSession['settings']['details'])){
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

			header('Location: package');
		}

		return \Twist::View()->build('pages/user.tpl',$arrTags);
	}

	/**
	 * Pre-install some packages and setup some systems like the manager
	 * Currently skips this step in initial release of V3
	 */
	public function package(){

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
			//TODO: Skip the interfaces step for the time being, it will become packages when ready
			header('Location: finish');
		}else{
			header('Location: user');
		}

		//return \Twist::View()->build('pages/packages.tpl',$arrTags);
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

		$arrConfiguration = array(
			'database' => $arrSession['database']['details'],
			'settings' => $arrSession['settings']['details'],
			'user' => $arrSession['user']['details']
		);

		Install::framework($arrConfiguration);
		\Twist::Session()->remove('twist-setup');

		return \Twist::View()->build('pages/finish.tpl');
	}
}