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

namespace TwistController;

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
		$dirHTaccessFile = sprintf('%s/.htaccess',DIR_BASE);

		if(file_exists($dirHTaccessFile)){
			\Twist::File()->move($dirHTaccessFile,sprintf('%s/old.htaccess',DIR_BASE));
		}

		file_put_contents($dirHTaccessFile,"# TWISTPHP\nRewriteEngine on\n# Routes Rewrite to allow for dynamic pages\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [L,QSA]\n# /TWISTPHP");

		return \Twist::Template()->build('pages/welcome.tpl');
	}

	public function licence(){

		$strLicenceText = file_get_contents(sprintf('%s/LICENCE.txt',DIR_FRAMEWORK));
		$strLicenceText = str_replace(array("\n\n","\n"),array("<br><br>"," "),$strLicenceText);

		$arrTags = array('licence' => $strLicenceText);

		return \Twist::Template()->build('pages/licence.tpl',$arrTags);
	}

	public function checks(){

		$arrSession = \Twist::Session()->data('twist-setup');
		$blPassChecks = false;

		$blVersion = (version_compare(PHP_VERSION, '5.3.0') >= 0) ? true : false;
		$blPermissions = (is_dir(DIR_FRAMEWORK) && is_writable(DIR_FRAMEWORK) && is_dir(DIR_FRAMEWORK_CONFIG) && is_writable(DIR_FRAMEWORK_CONFIG)) ? true : false;
		$blCurl = (function_exists('curl_init') || class_exists('curl_init')) ? true : false;
		$blMysql = (function_exists('mysql_connect') || function_exists('mysqli_connect')) ? true : false;
		$blZip = (function_exists('ZipArchive') || class_exists('ZipArchive')) ? true : false;
		$blCookies = (is_array($_COOKIE) && array_key_exists('twist_setup_test',$_COOKIE)) ? true : false;

		if($blVersion && $blPermissions && $blCurl && $blMysql && $blZip && $blCookies){
			$blPassChecks = true;
		}

		$arrChecks = array(
			'php_version' => ($blVersion) ? 'success' : 'error',
			'file_permissions' => ($blPermissions) ? 'success' : 'error',
			'php_curl' => ($blCurl) ? 'success' : 'error',
			'php_mysql' => ($blMysql) ? 'success' : 'error',
			'php_zip' => ($blZip) ? 'success' : 'error',
			'php_cookies' => ($blCookies) ? 'success' : 'error',
			'continue_status' => ($blPassChecks) ? '' : ' hidden'
		);

		$arrSession['checks']['status'] = $blPassChecks;
		$arrSession['checks']['details'] = $arrChecks;

		\Twist::Session()->data('twist-setup',$arrSession);

		return \Twist::Template()->build('pages/checks.tpl',$arrChecks);
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

		return \Twist::Template()->build('pages/database.tpl',$arrTags);
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

		$arrTags = array('error_message' => '');

		if(array_key_exists('message',$arrSession['settings'])){
			$arrTags = array('error_message' => '<p class="error">'.$arrSession['settings']['message'].'</p>');
			unset($arrSession['settings']['message']);
			\Twist::Session()->data('twist-setup',$arrSession);
		}

		return \Twist::Template()->build('pages/settings.tpl',$arrTags);
	}

	public function user(){

		$arrSession = \Twist::Session()->data('twist-setup');

		if(array_key_exists('site_name',$_POST)){

			$arrSession['settings']['details'] = array(
				'site_name' => $_POST['site_name'],
				'site_host' => $_POST['site_host'],
				'http_protocol' => $_POST['http_protocol'],
				'timezone' => $_POST['timezone']
			);

			if($arrSession['settings']['details']['site_name'] != '' &&
				$arrSession['settings']['details']['site_host'] != '' &&
				$arrSession['settings']['details']['http_protocol'] != '' &&
				$arrSession['settings']['details']['timezone'] != ''){
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

		return \Twist::Template()->build('pages/user.tpl',$arrTags);
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
		}

		$arrTags = array('interfaces' => '');
		$arrInterfaces = \Twist::framework()->upgrade()->getInterfaces();

		foreach($arrInterfaces as $arrEachInterface){
			if($arrEachInterface['installed'] == '0' && $arrEachInterface['licenced'] == '0'){

				$arrEachInterface['checked'] = ($arrEachInterface['name'] == 'Manager' && $arrEachInterface['repository'] == 'twistphp') ? ' checked' : '';
				$arrEachInterface['recommended'] = ($arrEachInterface['name'] == 'Manager' && $arrEachInterface['repository'] == 'twistphp') ? ' (Recommended)' : '';

				$arrTags['interfaces'] .= \Twist::Template()->build('components/interface-each.tpl',$arrEachInterface);
			}
		}

		return \Twist::Template()->build('pages/interfaces.tpl',$arrTags);
	}

	public function finish(){

		//Remove the cookie test cookie
		setcookie('twist_setup_test',null,0);

		$arrSession = \Twist::Session()->data('twist-setup');

		$arrConfigTags = array(
			'database_protocol' => $arrSession['database']['details']['protocol'],
			'database_server' => $arrSession['database']['details']['host'],
			'database_username' => $arrSession['database']['details']['username'],
			'database_password' => $arrSession['database']['details']['password'],
			'database_name' => $arrSession['database']['details']['name'],
			'database_table_prefix' => $arrSession['database']['details']['table_prefix'],
		);

		file_put_contents(sprintf('%s/config/config.php',DIR_FRAMEWORK),\Twist::Template()->build('config.tpl',$arrConfigTags));

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

			//Install the core tables - Database Only
			\Twist::framework()->upgrade()->installCoreTables();
		}

		//Update all the core settings, add to a file when no Database is being used
		\Twist::framework()->upgrade()->updateCoreSettings();

		//Add new settings to the chosen settings storage method
		\Twist::framework()->setting('SITE_NAME',$arrSession['settings']['details']['site_name']);
		\Twist::framework()->setting('SITE_HOST',$arrSession['settings']['details']['site_host']);
		\Twist::framework()->setting('SITE_PROTOCOL',$arrSession['settings']['details']['http_protocol']);
		\Twist::framework()->setting('TIMEZONE',$arrSession['settings']['details']['timezone']);

		//Create the level 0 user into the system - this will only occur is a database connection is present
		if($arrSession['user']['status'] == true){

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
		$dirIndexFile = sprintf('%s/index.php',DIR_BASE);

		if(file_exists($dirIndexFile)){
			\Twist::File()->move($dirIndexFile,sprintf('%s/old-index.php',DIR_BASE));
		}

		//Later on we can add in example templates etc if required
		$arrIndexTags = array(
			'interfaces' => implode("\n\t\t",$arrInterfaces),
			'routes' => '',
			'serve' => 'Twist::Route() -> serve();'
		);

		file_put_contents($dirIndexFile,\Twist::Template()->build(sprintf('%s/default-index.tpl',DIR_FRAMEWORK_TEMPLATES),$arrIndexTags));

		/**
		 * Update the .htaccess file to be a TwistPHP htaccess file
		 */
		$dirHTaccessFile = sprintf('%s/.htaccess',DIR_BASE);
		file_put_contents($dirHTaccessFile,\Twist::Template()->build(sprintf('%s/default-htaccess.tpl',DIR_FRAMEWORK_TEMPLATES)));

		return \Twist::Template()->build('pages/finish.tpl');
	}
}