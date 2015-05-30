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

class Manager extends BaseController{

	public function __construct(){
		\Twist::Route()->setDirectory(sprintf('%smanager/',TWIST_FRAMEWORK_VIEWS));
		$this->_aliasURI('update-setting','getUpdateSetting');
	}

	public function _default(){
		return $this->dashboard();
	}

	public function login(){
		\Twist::Route()->baseViewIgnore();
		return $this->_view('_login.tpl');
	}

	public function update(){
		\Twist::Route()->baseViewIgnore();
		return $this->_view('_update.tpl');
	}

	public function progress(){
		\Twist::Route()->baseViewIgnore();
		header('Content-Type: application/json');
		$strJsonFile = sprintf('%s/../progress.json',dirname(__FILE__));
		return (file_exists($strJsonFile)) ? file_get_contents($strJsonFile) : json_encode(array());
	}

	public function dashboard(){

		//Set the release channel
		\Twist::framework()->upgrade()->channel(\Twist::framework()->setting('RELEASE_CHANNEL'));

		$arrCore = \Twist::framework()->upgrade()->getCore();

		$arrTags = array();
		$arrTags['update-information'] = ($arrCore['update'] == '1') ? $this->_view('components/dashboard/update.tpl',$arrCore) : $this->_view('components/dashboard/no-update.tpl',$arrCore);

		$arrTags['development-mode'] = (\Twist::framework()->setting('DEVELOPMENT_MODE') == '1') ? 'On' : 'Off';
		$arrTags['maintenance-mode'] = (\Twist::framework()->setting('MAINTENANCE_MODE') == '1') ? 'On' : 'Off';
		$arrTags['release-channel'] = \Twist::framework()->setting('RELEASE_CHANNEL');
		$arrTags['database-debug'] = (\Twist::framework()->setting('TWIST_DATABASE_DEBUG') == '1') ? 'On' : 'Off';

		$arrRoutes = \Twist::Route()->getAll();
		$arrTags['route-data'] = sprintf('<strong>%d</strong> ANY<br><strong>%d</strong> GET<br><strong>%d</strong> POST<br><strong>%d</strong> PUT<br><strong>%d</strong> DELETE',
			count($arrRoutes['ANY']),
			count($arrRoutes['GET']),
			count($arrRoutes['POST']),
			count($arrRoutes['PUT']),
			count($arrRoutes['DELETE']));

		$arrTags['user-accounts'] = sprintf('<strong>%d</strong> Superadmin,<br><strong>%d</strong> Admin,<br><strong>%d</strong> Advanced,<br><strong>%d</strong> Member',
			\Twist::Database()->count(sprintf('%susers',TWIST_DATABASE_TABLE_PREFIX),\Twist::framework()->setting('USER_LEVEL_SUPERADMIN'),'level'),
			\Twist::Database()->count(sprintf('%susers',TWIST_DATABASE_TABLE_PREFIX),\Twist::framework()->setting('USER_LEVEL_ADMIN'),'level'),
			\Twist::Database()->count(sprintf('%susers',TWIST_DATABASE_TABLE_PREFIX),\Twist::framework()->setting('USER_LEVEL_ADVANCED'),'level'),
			\Twist::Database()->count(sprintf('%susers',TWIST_DATABASE_TABLE_PREFIX),\Twist::framework()->setting('USER_LEVEL_MEMBER'),'level')
		);

		return $this->_view('pages/dashboard.tpl',$arrTags);
	}

	public function cache(){

		$arrTags = array();
		$arrFiles = scandir(TWIST_APP_CACHE);

		foreach($arrFiles as $strEachCache){
			if(!in_array($strEachCache,array('.','..')) && is_dir(TWIST_APP_CACHE.'/'.$strEachCache)){

				$arrFileTags = array(
					'file' => $strEachCache,
					'size' => \Twist::File()->directorySize(TWIST_APP_CACHE.'/'.$strEachCache)
				);

				$arrTags['cache'] .= $this->_view('components/cache/each-file.tpl',$arrFileTags);
			}
		}

		return $this->_view('pages/cache.tpl',$arrTags);
	}

	public function settings(){

		$arrSettings = \Twist::framework() -> settings() -> arrSettingsInfo;
		$arrOption = array();

		foreach($arrSettings as $arrEachItem){

			$arrEachItem['input'] = '';

			if($arrEachItem['type'] === 'string'){
				$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
			}elseif($arrEachItem['type'] === 'boolean'){
				$arrEachItem['input'] .= sprintf('<input type="checkbox" name="settings[%s]" %svalue="1">',$arrEachItem['key'],($arrEachItem['value'] == '1') ? 'checked ' : '');
			}elseif($arrEachItem['type'] === 'options'){

				$strOptions = '';
				$arrOptions = explode(',',$arrEachItem['options']);

				if(count($arrOptions) <= 3){
					foreach($arrOptions as $strEachOption){
						$strChecked = (trim($strEachOption) == $arrEachItem['value']) ? ' checked': '';
						$strOptionKey = sprintf('%s-%s',$arrEachItem['key'],trim($strEachOption));
						$arrEachItem['input'] .= sprintf('<input type="radio" id="settings_%s" name="settings[%s]" value="%s"%s><label for="settings_%s">%s</label>',$strOptionKey,$arrEachItem['key'],trim($strEachOption),$strChecked,$strOptionKey,trim($strEachOption));
					}
				}else{
					foreach($arrOptions as $strEachOption){
						$strChecked = (trim($strEachOption) == $arrEachItem['value']) ? 'selected ': '';
						$strOptions .= sprintf('<option %svalue="%s">%s</option>',$strChecked,trim($strEachOption),trim($strEachOption));
					}
					$arrEachItem['input'] .= sprintf('<select name="settings[%s]">%s</select>',$arrEachItem['key'],$strOptions);
				}

			}elseif($arrEachItem['type'] === 'integer'){
				$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
			}else{
				//Unknown types
				$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
			}

			//Output the original settings in hidden inputs
			$arrEachItem['input'] .= sprintf('<input type="hidden" name="original[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);

			$arrOption[$arrEachItem['package']] .= $this->_view('components/settings/each-setting.tpl', $arrEachItem );
		}

		$arrTags = array();
		foreach($arrOption as $strKey => $strList){

			//if($strKey != 'Core'){
			$arrListTags = array('title' => $strKey, 'list' => $strList);
			$arrTags['settings'] .= $this->_view('components/settings/group.tpl', $arrListTags );
			//}
		}

		return $this->_view('pages/settings.tpl',$arrTags);
	}

	public function postSettings(){

		if(array_key_exists('settings',$_POST) && count($_POST['settings']) && count($_POST['original'])){
			foreach($_POST['original'] as $strKey => $strValue){
				if(array_key_exists($strKey,$_POST['settings'])){
					//Store the new setting
					\Twist::framework() ->setting($strKey,$_POST['settings'][$strKey]);
				}else{
					//Store '0' as we can consider this an unchecked checkbox
					//@todo add validation of the data type here
					\Twist::framework() ->setting($strKey,0);
				}
			}
			$arrTags['message'] = '<p class="success">You new module settings were saved successfully</p>';
			//$arrSettings = \Twist::framework() -> settings() -> cache();
		}

		\Twist::redirect('./settings');
	}

	public function getUpdateSetting(){

		$arrAllowedSettings = array('DEVELOPMENT_MODE','MAINTENANCE_MODE','RELEASE_CHANNEL','TWIST_DATABASE_DEBUG');

		if(array_key_exists('setting',$_GET) && array_key_exists('setting_value',$_GET) && in_array($_GET['setting'],$arrAllowedSettings)){
			\Twist::framework() ->setting($_GET['setting'],$_GET['setting_value']);
		}

		\Twist::redirect('./dashboard');
	}

	public function repositories(){

		$arrTags = array();

		//Set the release channel
		\Twist::framework()->upgrade()->channel(\Twist::framework()->setting('RELEASE_CHANNEL'));

		$arrRepositories = \Twist::framework()->upgrade()->getRepositories();
		$arrInterfaces = \Twist::framework()->upgrade()->getInterfaces();
		$arrModules = \Twist::framework()->upgrade()->getModules();

		$arrTags['static'] = '';
		$arrTags['third-party'] = '';
		foreach($arrRepositories as $strRepoKey => $arrEachRepo){

			$arrEachRepo['interface_count'] = 0;
			foreach($arrInterfaces as $arrEachInterface){
				if($arrEachInterface['repository'] === $strRepoKey && count($arrEachInterface['available'])){
					$arrEachRepo['interface_count']++;
				}
			}

			$arrEachRepo['module_count'] = 0;
			foreach($arrModules as $arrEachModule){
				if($arrEachModule['repository'] === $strRepoKey && count($arrEachModule['available'])){
					$arrEachRepo['module_count']++;
				}
			}

			if($strRepoKey === 'twistphp'){
				$arrTags['static'] = $this->_view('components/repositories/each-repo-static.tpl', $arrEachRepo );
			}else{
				$arrTags['third-party'] .= $this->_view('components/repositories/each-repo.tpl', $arrEachRepo );
			}

		}

		return $this->_view('pages/repositories.tpl',$arrTags);
	}

	public function postRepositories(){

		if(array_key_exists('repository_url',$_POST) && $_POST['repository_url'] != ''){
			$arrRepositories = \Twist::framework()->upgrade()->installRepository($_POST['repository_url']);
		}

		header(sprintf('Location: %s/repositories',$_SERVER['TWIST_ROUTE']['base_uri']));
	}

	public function deleteRepository(){

		if(array_key_exists('repo-key',$_GET)){
			\Twist::framework()->upgrade()->deleteRepository($_GET['repo-key']);
		}

		header(sprintf('Location: %s/repositories',$_SERVER['TWIST_ROUTE']['base_uri']));
	}

	public function repository(){

		$arrTags = array();

		if(array_key_exists('repo-key',$_GET) && array_key_exists('repo-enable',$_GET)){
			\Twist::framework()->upgrade()->enableRepository($_GET['repo-key'],$_GET['repo-enable']);
		}

		\Twist::framework()->upgrade()->channel(\Twist::framework()->setting('RELEASE_CHANNEL'));
		$arrRepositories = \Twist::framework()->upgrade()->getRepositories();

		if(array_key_exists('repo-key',$_GET) && array_key_exists($_GET['repo-key'],$arrRepositories)){

			$arrInterfaces = \Twist::framework()->upgrade()->getInterfaces();
			$arrModules = \Twist::framework()->upgrade()->getModules();

			$arrTags = $arrRepositories[$_GET['repo-key']];

			$arrTags['interfaces'] = 0;
			foreach($arrInterfaces as $arrEachInterface){
				if($arrEachInterface['repository'] === $_GET['repo-key'] && count($arrEachInterface['available'])){
					$arrTags['interfaces']++;
				}
			}

			$arrTags['modules'] = 0;
			foreach($arrModules as $arrEachModule){
				if($arrEachModule['repository'] === $_GET['repo-key'] && count($arrEachModule['available'])){
					$arrTags['modules']++;
				}
			}
		}

		return $this->_view('pages/repository_manage.tpl',$arrTags);
	}

	public function postRepository(){

		if(array_key_exists('repository_url',$_POST) && $_POST['repository_url'] != ''){
			$arrRepositories = \Twist::framework()->upgrade()->installRepository($_POST['repository_url']);
		}

		header(sprintf('Location: %s/repositories',$_SERVER['TWIST_ROUTE']['base_uri']));
	}

	public function getPackageInformation(){

		$arrPackages = array();

		if(array_key_exists('repo',$_GET) && array_key_exists('package',$_GET) && array_key_exists('package-type',$_GET)){

			$strRepo = $_GET['repo'];
			$strPackage = $_GET['package'];
			$strType = $_GET['package-type'];

			switch($strType){
				case'interfaces':
					$arrPackages = \Twist::framework()->upgrade()->getInterfaces();
					break;
				case'modules':
					$arrPackages = \Twist::framework()->upgrade()->getModules();
					break;
			}

			$strPackageKey = strtolower(sprintf('%s-%s',$strRepo,$strPackage));

			if(count($arrPackages) && array_key_exists($strPackageKey,$arrPackages)){
				$arrTags = $arrPackages[$strPackageKey];
				$arrTags['repo'] = $strRepo;
				$arrTags['type'] = ucfirst($strType);

				return $this->_view('pages/package_information.tpl',$arrTags);
			}
		}

		header(sprintf('Location: %s',$_SERVER['TWIST_ROUTE']['base_uri']));
	}

	public function packages(){

		$arrTags = array();

		//Set the release channel
		\Twist::framework()->upgrade()->channel(\Twist::framework()->setting('RELEASE_CHANNEL'));

		$arrModules = \Twist::framework()->package()->getAll();

		$arrTags['packages_installed'] = '';
		$arrTags['packages_available'] = '';

		foreach($arrModules as $arrEachModule){

			if(array_key_exists('name',$arrEachModule)){

				if(array_key_exists('installed',$arrEachModule)){
					$arrTags['packages_installed'] .= $this->_view('components/packages/each-installed.tpl',$arrEachModule);
				}else{
					$arrTags['packages_available'] .= $this->_view('components/packages/each-available.tpl',$arrEachModule);
				}
			}
		}

		if($arrTags['packages_installed'] === ''){
			$arrTags['packages_installed'] = '<tr><td colspan="6">No packages installed</td></tr>';
		}

		if($arrTags['packages_available'] === ''){
			$arrTags['packages_available'] = '<tr><td colspan="4">No packages to install</td></tr>';
		}

		return $this->_view('pages/packages.tpl',$arrTags);
	}

	public function install(){

		//Run the package installer
		if(array_key_exists('package',$_GET)){
			\Twist::framework()->package()->installer($_GET['package']);
		}

		\Twist::redirect('./packages');
	}

	public function processUpdate(){

		$arrActions = array();

		if(count($_POST)){
			foreach($_POST as $strKey => $arrPosts){
				if($arrPosts['install'] == '1'){
					$arrPosts['channel'] = strtolower(\Twist::framework()->setting('RELEASE_CHANNEL'));
					$arrActions[] = $arrPosts;
				}
			}
		}elseif(array_key_exists('action',$_GET) && array_key_exists('repo',$_GET) && array_key_exists('package',$_GET) && array_key_exists('package-type',$_GET) && array_key_exists('package-version',$_GET)){

			$arrActions = array(
				0 => array(
					'channel' => strtolower(\Twist::framework()->setting('RELEASE_CHANNEL')),
					'action' => $_GET['action'],
					'repo' => $_GET['repo'],
					'package' => $_GET['package'],
					'package-type' => $_GET['package-type'],
					'package-version' => $_GET['package-version']
				)
			);
		}


		$strJsonFile = sprintf('%s/../update-actions.json',dirname(__FILE__));
		if(count($arrActions)){
			file_put_contents($strJsonFile,json_encode($arrActions));
		}else{
			unlink($strJsonFile);
		}

		//Send the user to the update page
		header(sprintf('Location: %s/update',$_SERVER['TWIST_ROUTE']['base_uri']));
	}
}