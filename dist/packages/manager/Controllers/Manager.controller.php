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

namespace Packages\manager\Controllers;

use \Twist\Core\Controllers\BaseUser;
use Packages\install\Models\Install;
use Twist\Core\Models\Protect\Firewall;
use Twist\Core\Models\Protect\Scanner;
use \Twist\Core\Models\ScheduledTasks;

/**
 * The route controller for the framework manager, generates the pages of the manager tool.
 * @package Twist\Core\Controllers
 */
class Manager extends BaseUser{

	public function __construct(){
		$this->_aliasURI('update-setting','GETupdatesetting');
		$this->_aliasURI('scheduled-tasks','scheduledtasks');
	}

	/**
	 * Over-ride the base view for the login page
	 * @return string
	 */
	public function login(){
		$this->_baseView('_login.tpl');
		return parent::login();
	}

	/**
	 * Over-ride the base view for the forgotten password page
	 * @return string
	 */
	public function forgottenpassword(){
		$this->_baseView('_login.tpl');
		return parent::forgottenpassword();
	}

	/**
	 * Over-ride the base view for the cookies page
	 * @return string
	 */
	public function cookies(){
		$this->_baseView('_login.tpl');
		return parent::cookies();
	}

	/**
	 * @alias dashboard
	 * @return string
	 */
	public function _index(){
		return $this->dashboard();
	}

	/**
	 * Manager dashboard page, here you have access to some of the core framework settings and information
	 * @return string
	 */
	public function dashboard(){

		if(array_key_exists('development-mode',$_GET)){
			\Twist::framework()->setting('DEVELOPMENT_MODE',($_GET['development-mode'] === '1') ? '1' : '0');
		}elseif(array_key_exists('maintenance-mode',$_GET)){
			\Twist::framework()->setting('MAINTENANCE_MODE',($_GET['maintenance-mode'] === '1') ? '1' : '0');
		}elseif(array_key_exists('debug-bar',$_GET)){
			\Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR',($_GET['debug-bar'] === '1') ? '1' : '0');
		}elseif(array_key_exists('data-caching',$_GET)){
			\Twist::framework()->setting('CACHE_ENABLED',($_GET['data-caching'] === '1') ? '1' : '0');
		}elseif(array_key_exists('twistprotect-firewall',$_GET)){
			\Twist::framework()->setting('TWISTPROTECT_FIREWALL',($_GET['twistprotect-firewall'] === '1') ? '1' : '0');
		}elseif(array_key_exists('twistprotect-scanner',$_GET)){
			\Twist::framework()->setting('TWISTPROTECT_SCANNER',($_GET['twistprotect-scanner'] === '1') ? '1' : '0');
		}

		$arrTags['development-mode'] = (\Twist::framework()->setting('DEVELOPMENT_MODE') == '1') ? 'On' : 'Off';
		$arrTags['maintenance-mode'] = (\Twist::framework()->setting('MAINTENANCE_MODE') == '1') ? 'On' : 'Off';
		$arrTags['debug-bar'] = (\Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR') == '1') ? 'On' : 'Off';
		$arrTags['data-caching'] = (\Twist::framework()->setting('CACHE_ENABLED') == '1') ? 'On' : 'Off';
		$arrTags['twistprotect-firewall'] = (\Twist::framework()->setting('TWISTPROTECT_FIREWALL') == '1') ? 'On' : 'Off';
		$arrTags['twistprotect-scanner'] = (\Twist::framework()->setting('TWISTPROTECT_SCANNER') == '1') ? 'On' : 'Off';

		$arrLatestVersion = \Twist::framework()->package()->getRepository('twistphp');
		$arrTags['version'] = \Twist::version();

		if(count($arrLatestVersion) && array_key_exists('stable',$arrLatestVersion)){
			$arrTags['version_status'] = (\Twist::version() == $arrLatestVersion['stable']['version']) ? '<span class="tag green">Twist is Up-to-date</span>' : 'A new version of TwistPHP is available [<a href="https://github.com/TwistPHP/TwistPHP/releases" target="_blank">download it now</a>]';
		}else{
			$arrTags['version_status'] = '<span class="tag red">Failed to retrieve version information, try again later!</span>';
		}

		$objCodeScanner = new Scanner();
		$arrTags['scanner'] = $objCodeScanner->getLastScan(TWIST_DOCUMENT_ROOT);

		$arrRoutes = \Twist::Route()->getAll();
		$arrTags['route-data'] = sprintf('<strong>%d</strong> ANY<br><strong>%d</strong> GET<br><strong>%d</strong> POST<br><strong>%d</strong> PUT<br><strong>%d</strong> DELETE',
			count($arrRoutes['ANY']),
			count($arrRoutes['GET']),
			count($arrRoutes['POST']),
			count($arrRoutes['PUT']),
			count($arrRoutes['DELETE']));

		$strUsersTable = sprintf('%susers',TWIST_DATABASE_TABLE_PREFIX);

		$arrTags['user-accounts'] = sprintf('<strong>%d</strong> Superadmin<br><strong>%d</strong> Admin<br><strong>%d</strong> Advanced<br><strong>%d</strong> Member',
			\Twist::Database()->records($strUsersTable)->count(\Twist::framework()->setting('USER_LEVEL_SUPERADMIN'),'level'),
			\Twist::Database()->records($strUsersTable)->count(\Twist::framework()->setting('USER_LEVEL_ADMIN'),'level'),
			\Twist::Database()->records($strUsersTable)->count(\Twist::framework()->setting('USER_LEVEL_ADVANCED'),'level'),
			\Twist::Database()->records($strUsersTable)->count(\Twist::framework()->setting('USER_LEVEL_MEMBER'),'level')
		);

		return $this->_view('pages/dashboard.tpl',$arrTags);
	}

	/**
	 * Overview of the TwistPHP cache system with the ability to clear out cache data so that it must be re-generated.
	 * @return string
	 */
	public function cache(){

		$this->parseCache(TWIST_APP_CACHE);

		$arrTags = array('cache' => '');
		foreach($this->arrCacheFiles as $strKey => $arrData){
			$arrTags['cache'] .= $this->_view('components/cache/each-file.tpl',$arrData);
		}

		return $this->_view('pages/cache.tpl',$arrTags);
	}

	var $arrCacheFiles = array();

	/**
	 * Run through all the cache files and build up a list of what has been cached
	 * @param string $strCacheFolder
	 */
	protected function parseCache($strCacheFolder){

		foreach(scandir($strCacheFolder) as $strEachCache){
			if(!in_array($strEachCache,array('.','..','.htaccess'))){

				$strCurrentItem = sprintf('%s/%s',rtrim($strCacheFolder,'/'),$strEachCache);
				$strCacheKey = str_replace(TWIST_APP_CACHE,'',rtrim($strCacheFolder,'/'));

				if(is_dir($strCurrentItem)){
					$this->parseCache($strCurrentItem);
				}else{

					//Define the array key before appending files and sizes
					if(!array_key_exists($strCacheKey,$this->arrCacheFiles)){
						$this->arrCacheFiles[$strCacheKey] = array(
							'key' => $strCacheKey,
							'files' => 0,
							'size' => 0
						);
					}

					$this->arrCacheFiles[$strCacheKey]['files']++;
					$this->arrCacheFiles[$strCacheKey]['size'] += filesize($strCurrentItem);
				}
			}
		}
	}

    /**
     * HTaccess manager to all the editing of browser cache settings, rewrite rules and default host name redirects such as using www. or not and forcing https.
     * @return string
     */
    public function apikeys(){

        if(array_key_exists('generate',$_GET)){

            $resNewKey = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'apikeys')->create();
            $resNewKey->set('key',\Twist::framework()->tools()->randomString(16));
            $resNewKey->set('enabled','1');
            $resNewKey->set('created',date('Y-m-d H:i:s'));
            $resNewKey->commit();

            \Twist::redirect('apikeys');
        }

        $arrTags = array('keys' => '');
        $arrKeys = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'apikeys')->all();

        foreach($arrKeys as $arrEachKey){
            $arrTags['keys'] .= $this->_view('components/apikeys/key-each.tpl',$arrEachKey);
        }

        if($arrTags['keys'] == ''){
            $arrTags['keys'] = $this->_view('components/apikeys/key-none.tpl');
        }

        return $this->_view('pages/apikeys.tpl',$arrTags);
    }

    /**
     * Allow a select few settings to be updated using GET parameters, these are settings that are displayed as buttons throughout the manager.
     */
    public function POSTapikeys(){

        \Twist::framework()->setting('API_ALLOWED_REQUEST_METHODS',$_POST['API_ALLOWED_REQUEST_METHODS']);
        \Twist::framework()->setting('API_REQUEST_HEADER_AUTH',(array_key_exists('API_REQUEST_HEADER_AUTH',$_POST)) ? true : false);

        \Twist::redirect('./apikeys');
    }

	/**
	 * HTaccess manager to all the editing of browser cache settings, rewrite rules and default host name redirects such as using www. or not and forcing https.
	 * @return string
	 */
	public function htaccess(){

		$arrTags = array('rewrite_rules' => '');

		$arrRewrites = json_decode(\Twist::framework()->setting('HTACCESS_REWRITES'),true);

		if(count($arrRewrites)){
			foreach($arrRewrites as $arrEachRewrite){
				$arrTags['rewrite_rules'] .= $this->_view('components/htaccess/rewrite-rule.tpl',$arrEachRewrite);
			}
		}

		return $this->_view('pages/htaccess.tpl',$arrTags);
	}

	public function POSThtaccess(){

		\Twist::framework()->setting('SITE_WWW',$_POST['SITE_WWW']);
		\Twist::framework()->setting('SITE_PROTOCOL',$_POST['SITE_PROTOCOL']);
		\Twist::framework()->setting('SITE_PROTOCOL_FORCE',$_POST['SITE_PROTOCOL_FORCE']);
		\Twist::framework()->setting('SITE_DIRECTORY_INDEX',$_POST['SITE_DIRECTORY_INDEX']);

		\Twist::framework()->setting('HTACCESS_DISABLE_DIRBROWSING',(array_key_exists('HTACCESS_DISABLE_DIRBROWSING',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DISABLE_HTACCESS',(array_key_exists('HTACCESS_DISABLE_HTACCESS',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DISABLE_UPLOADEDPHP',(array_key_exists('HTACCESS_DISABLE_UPLOADEDPHP',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DISABLE_QUERYSTRINGS',(array_key_exists('HTACCESS_DISABLE_QUERYSTRINGS',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DISABLE_HOTLINKS',(array_key_exists('HTACCESS_DISABLE_HOTLINKS',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DISABLE_EXTENSIONS',$_POST['HTACCESS_DISABLE_EXTENSIONS']);

		\Twist::framework()->setting('HTACCESS_CACHE_HTML',$_POST['HTACCESS_CACHE_HTML']);
		\Twist::framework()->setting('HTACCESS_REVALIDATE_HTML',(array_key_exists('HTACCESS_REVALIDATE_HTML',$_POST)) ? '1' : '0');

		\Twist::framework()->setting('HTACCESS_CACHE_CSS',$_POST['HTACCESS_CACHE_CSS']);
		\Twist::framework()->setting('HTACCESS_REVALIDATE_CSS',(array_key_exists('HTACCESS_REVALIDATE_CSS',$_POST)) ? '1' : '0');

		\Twist::framework()->setting('HTACCESS_CACHE_JS',$_POST['HTACCESS_CACHE_JS']);
		\Twist::framework()->setting('HTACCESS_REVALIDATE_JS',(array_key_exists('HTACCESS_REVALIDATE_JS',$_POST)) ? '1' : '0');

		\Twist::framework()->setting('HTACCESS_CACHE_IMAGES',$_POST['HTACCESS_CACHE_IMAGES']);
		\Twist::framework()->setting('HTACCESS_REVALIDATE_IMAGES',(array_key_exists('HTACCESS_REVALIDATE_IMAGES',$_POST)) ? '1' : '0');

		\Twist::framework()->setting('HTACCESS_ETAG',(array_key_exists('HTACCESS_ETAG',$_POST)) ? '1' : '0');

		\Twist::framework()->setting('HTACCESS_DEFLATE_HTML',(array_key_exists('HTACCESS_DEFLATE_HTML',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DEFLATE_CSS',(array_key_exists('HTACCESS_DEFLATE_CSS',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DEFLATE_JS',(array_key_exists('HTACCESS_DEFLATE_JS',$_POST)) ? '1' : '0');
		\Twist::framework()->setting('HTACCESS_DEFLATE_IMAGES',(array_key_exists('HTACCESS_DEFLATE_IMAGES',$_POST)) ? '1' : '0');

		$arrTags = array('rewrite_rules' => '');
		$arrRewriteRules = array();

		foreach($_POST['rewrite'] as $intKey => $strRewriteURI){
			if(array_key_exists($intKey,$_POST['rewrite-redirect']) && array_key_exists($intKey,$_POST['rewrite-options']) && $strRewriteURI != '' && $_POST['rewrite-redirect'][$intKey] != ''){

				$arrRewriteRules[] = array('rule' => $strRewriteURI,'redirect' => $_POST['rewrite-redirect'][$intKey],'options' => $_POST['rewrite-options'][$intKey]);
				$arrTags['rewrite_rules'] .= sprintf("\tRewriteRule %s %s [%s]\n",$strRewriteURI,$_POST['rewrite-redirect'][$intKey],$_POST['rewrite-options'][$intKey]);
			}
		}

		\Twist::framework()->setting('HTACCESS_REWRITES',json_encode($arrRewriteRules));
		\Twist::framework()->setting('HTACCESS_CUSTOM',$_POST['HTACCESS_CUSTOM']);

		/**
		 * Update the .htaccess file to be a TwistPHP htaccess file
		 */
		$dirHTaccessFile = sprintf('%s/.htaccess',TWIST_PUBLIC_ROOT);
		file_put_contents($dirHTaccessFile,$this->_view(sprintf('%s/default-htaccess.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags));

		return $this->htaccess();
	}

	/**
	 * Malicious Code Scanner page, shows the results of a code scan.
	 * @return string
	 */
	public function scanner(){

		$arrTags = array();
		$objCodeScanner = new Scanner();

		if(array_key_exists('scan-now',$_GET)){
			$objCodeScanner->scan(TWIST_DOCUMENT_ROOT,true);
			$arrTags['scanner'] = $objCodeScanner->summary();
		}else{
			$arrTags['scanner'] = $objCodeScanner->getLastScan(TWIST_DOCUMENT_ROOT);
		}

		$arrTags['infected_list'] = '';
		foreach($arrTags['scanner']['infected']['files'] as $arrInfectedFile){
			$arrTags['infected_list'] .= $this->_view('components/scanner/each-infected.tpl',$arrInfectedFile);
		}

		$arrTags['changed_list'] = '';
		foreach($arrTags['scanner']['changed']['files'] as $strPath => $strKey){

			$arrFileTags = array(
				'file' => $strPath,
				'code' => $strKey
			);

			$arrTags['changed_list'] .= $this->_view('components/scanner/each-infected.tpl',$arrFileTags);
		}

		$arrTags['new_list'] = '';
		foreach($arrTags['scanner']['new']['files'] as $strPath => $strKey){

			$arrFileTags = array(
				'file' => $strPath,
				'code' => $strKey
			);

			$arrTags['new_list'] .= $this->_view('components/scanner/each-infected.tpl',$arrFileTags);
		}

		return $this->_view('pages/scanner.tpl',$arrTags);
	}

	/**
	 * An overview of all the settings in the TwistPHP Settings table, from here all settings can be updated as necessary.
	 * @return string
	 */
	public function settings(){

		if(array_key_exists('import',$_GET) && $_GET['import'] == 'core'){

			\Twist\Core\Models\Install::importSettings(sprintf('%sData/settings.json',TWIST_PACKAGE_INSTALL));
			\Twist::redirect('./settings');
		}

		$arrSettings = \Twist::framework() -> settings() -> arrSettingsInfo;
		$arrOption = array();

		foreach($arrSettings as $arrEachItem){

			$arrEachItem['input'] = '';

			if($arrEachItem['type'] === 'string'){
				$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
			}elseif($arrEachItem['type'] === 'boolean'){
				$arrEachItem['input'] .= sprintf('<input type="checkbox" name="settings[%s]" value="1" %s>',$arrEachItem['key'],($arrEachItem['value'] == '1') ? 'checked ' : '');
			}elseif($arrEachItem['type'] === 'options'){

				$strOptions = '';
				$arrOptions = explode(',',$arrEachItem['options']);

				if(count($arrOptions) <= 3){
					foreach($arrOptions as $strEachOption){
						$strChecked = (trim($strEachOption) == $arrEachItem['value']) ? 'checked': '';
						$strOptionKey = sprintf('%s-%s',$arrEachItem['key'],trim($strEachOption));
						$arrEachItem['input'] .= sprintf('<input type="radio" id="settings_%s" name="settings[%s]" value="%s" %s><label for="settings_%s">%s</label>',$strOptionKey,$arrEachItem['key'],trim($strEachOption),$strChecked,$strOptionKey,trim($strEachOption));
					}
				}else{
					foreach($arrOptions as $strEachOption){
						$strChecked = (trim($strEachOption) == $arrEachItem['value']) ? 'selected ': '';
						$strOptions .= sprintf('<option value="%s" %s>%s</option>',trim($strEachOption),$strChecked,trim($strEachOption));
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

			//Fix any undefined index's
			if(!array_key_exists($arrEachItem['package'],$arrOption)){
				$arrOption[$arrEachItem['package']] = '';
			}

			$arrOption[$arrEachItem['package']] .= $this->_view('components/settings/each-setting.tpl', $arrEachItem );
		}

		$arrTags = array('settings' => '');
		foreach($arrOption as $strKey => $strList){

			//if($strKey != 'Core'){
			$arrListTags = array('title' => $strKey, 'list' => $strList);
			$arrTags['settings'] .= $this->_view('components/settings/group.tpl', $arrListTags );
			//}
		}

		return $this->_view('pages/settings.tpl',$arrTags);
	}

	/**
	 * Store all the setting changes POST'ed  form the settings page.
	 */
	public function POSTsettings(){

		$arrSettingsInfo = \Twist::framework()->settings()->arrSettingsInfo;

		if(array_key_exists('settings',$_POST) && count($_POST['settings']) && count($_POST['original'])){
			foreach($_POST['original'] as $strKey => $strValue){
				if(array_key_exists($strKey,$_POST['settings'])){
					//Store the new setting
					\Twist::framework()->setting($strKey,$_POST['settings'][$strKey]);
				}else{
					//Store '0' as we can consider this an unchecked checkbox
					if($arrSettingsInfo[$strKey]['type'] === 'boolean'){
						\Twist::framework()->setting($strKey,0);
					}
				}
			}
			$arrTags['message'] = '<p class="success">You new module settings were saved successfully</p>';
			//$arrSettings = \Twist::framework() -> settings() -> cache();
		}

		\Twist::redirect('./settings');
	}

	/**
	 * Allow a select few settings to be updated using GET parameters, these are settings that are displayed as buttons throughout the manager.
	 */
	public function GETupdatesetting(){

		$arrAllowedSettings = array('DEVELOPMENT_MODE','MAINTENANCE_MODE','DEVELOPMENT_DEBUG_BAR','CACHE_ENABLED');

		if(array_key_exists('setting',$_GET) && array_key_exists('setting_value',$_GET) && in_array($_GET['setting'],$arrAllowedSettings)){
			\Twist::framework() ->setting($_GET['setting'],$_GET['setting_value']);
		}

		\Twist::redirect('./dashboard');
	}

	public function scheduledtasks(){

		$arrTags = array('tasks' => '');
		$arrTasks = ScheduledTasks::getAll();

		foreach($arrTasks as $arrEachTask){
			$arrTags['tasks'] .= $this->_view('components/scheduled/task-each.tpl',$arrEachTask);
		}

		if($arrTags['tasks'] == ''){
			$arrTags['tasks'] = $this->_view('components/scheduled/task-none.tpl');
		}

		return $this->_view('pages/scheduled-tasks.tpl',$arrTags);
	}

	public function POSTscheduledtasks(){
		$arrTags = array();
		return $this->_view('pages/scheduled-tasks.tpl',$arrTags);
	}

	/**
	 * Display all the installed and un-installed packages that are currently in your packages folder. The page does not currently have an APP store feature.
	 * @return string
	 */
	public function packages(){

		$arrTags = array();
		$arrModules = \Twist::framework()->package()->getAll();
		\Twist::framework()->package()->anonymousStats();

		$arrTags['local_packages'] = '';

		if(count($arrModules)){
			foreach($arrModules as $arrEachModule){

				if(array_key_exists('name',$arrEachModule)){

					if(array_key_exists('installed',$arrEachModule)){
						$arrTags['local_packages'] .= $this->_view('components/packages/each-installed.tpl',$arrEachModule);
					}else{
						$arrTags['local_packages'] .= $this->_view('components/packages/each-available.tpl',$arrEachModule);
					}
				}
			}
		}

		if($arrTags['local_packages'] === ''){
			$arrTags['local_packages'] = '<tr><td colspan="6">No packages found in your /packages folder</td></tr>';
		}

		$arrTags['repository-packages'] = '';
		$arrPackages = \Twist::framework()->package()->getRepository(array_key_exists('filter',$_GET) ? $_GET['filter'] : 'featured');

		if(count($arrPackages)){
			foreach($arrPackages as $arrEachPackage){
				$arrTags['repository-packages'] .= $this->_view('components/packages/each-repo-package.tpl',$arrEachPackage);
			}
		}

		return $this->_view('pages/packages.tpl',$arrTags);
	}

	/**
	 * Install a package into the system, pass the package slug in the GET param 'package'.
	 */
	public function install(){

		if(array_key_exists('package-key',$_GET)){
			//Run the package download and installer
			$arrPackageDetails = \Twist::framework()->package()->download($_GET['package-key']);
			\Twist::framework()->package()->installer($arrPackageDetails['slug']);

		}elseif(array_key_exists('package',$_GET)){
			//Run the package installer
			\Twist::framework()->package()->installer($_GET['package']);
		}

		\Twist::redirect('./packages');
	}

	/**
	 * Uninstall a package from the system, pass the package slug in the GET param 'package'.
	 */
	public function uninstall(){

		//Run the package installer
		if(array_key_exists('package',$_GET)){
			\Twist::framework()->package()->uninstaller($_GET['package']);
		}

		\Twist::redirect('./packages');
	}

	/**
	 * Uninstall a package from the system, pass the package slug in the GET param 'package'.
	 */
	public function firewall(){

		if(array_key_exists('list_action',$_POST) && array_key_exists('ip_address',$_POST)){

			if($_POST['list_action'] == 'ban'){
				Firewall::banIP($_POST['ip_address'],'',true);
				\Twist::successMessage('IP address '.$_POST['ip_address'].' has been banned!');
			}elseif($_POST['list_action'] == 'whitelist'){
				Firewall::whitelistIP($_POST['ip_address']);
				\Twist::successMessage('IP address '.$_POST['ip_address'].' has been whitelisted!');
			}

		}elseif(array_key_exists('unban',$_GET)){
			Firewall::unbanIP($_GET['unban']);
			\Twist::successMessage('IP address '.$_GET['ip_address'].' has been unbanned!');
		}elseif(array_key_exists('unwhitelist',$_GET)){
			Firewall::unwhitelistIP($_GET['unwhitelist']);
			\Twist::successMessage('IP address '.$_GET['ip_address'].' has been removed from the whitelisted!');
		}

		$arrTags = array();
		$arrData = Firewall::info();

		$arrTags['whitelist_count'] = count($arrData['whitelist_ips']);
		$arrTags['blocked_count'] = count($arrData['banned_ips']);
		$arrTags['watched_count'] = count($arrData['failed_actions']);

		$arrTags['blocked_ips'] = '';
		foreach($arrData['banned_ips'] as $mxdIPAddress => $arrSubData){
			$arrSubData['ip_address'] = $mxdIPAddress;
			$arrTags['blocked_ips'] .= $this->_view('components/firewall/blocked-ip.tpl',$arrSubData);
		}

		if($arrTags['blocked_ips'] == ''){
			$arrTags['blocked_ips'] = '<tr><td colspan="5">No IPs have been added to the blocklist</td></tr>';
		}

		$arrTags['whitelist_ips'] = '';
		foreach($arrData['whitelist_ips'] as $mxdIPAddress => $arrSubData){
			$arrSubData['ip_address'] = $mxdIPAddress;
			$arrTags['whitelist_ips'] .= $this->_view('components/firewall/whitelisted-ip.tpl',$arrSubData);
		}

		if($arrTags['whitelist_ips'] == ''){
			$arrTags['whitelist_ips'] = '<tr><td colspan="4">No IPs have been added to the whitelist</td></tr>';
		}

		return $this->_view('pages/firewall.tpl',$arrTags);
	}
}