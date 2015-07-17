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
use Twist\Core\Classes\BaseControllerUser;

class Manager extends BaseControllerUser{

	public function __construct(){
		\Twist::Route()->setDirectory(sprintf('%smanager/',TWIST_FRAMEWORK_VIEWS));
		$this->_aliasURI('update-setting','getUpdateSetting');
	}

	public function _index(){
		return $this->dashboard();
	}

	public function dashboard(){

		$arrTags['development-mode'] = (\Twist::framework()->setting('DEVELOPMENT_MODE') == '1') ? 'On' : 'Off';
		$arrTags['maintenance-mode'] = (\Twist::framework()->setting('MAINTENANCE_MODE') == '1') ? 'On' : 'Off';
		$arrTags['debug-bar'] = (\Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR') == '1') ? 'On' : 'Off';
		$arrTags['data-caching'] = (\Twist::framework()->setting('CACHE_ENABLED') == '1') ? 'On' : 'Off';

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

		$arrSettingsInfo = \Twist::framework()->settings()->arrSettingsInfo;

		if(array_key_exists('settings',$_POST) && count($_POST['settings']) && count($_POST['original'])){
			foreach($_POST['original'] as $strKey => $strValue){
				if(array_key_exists($strKey,$_POST['settings'])){
					//Store the new setting
					\Twist::framework() ->setting($strKey,$_POST['settings'][$strKey]);
				}else{
					//Store '0' as we can consider this an unchecked checkbox
					if($arrSettingsInfo[$strKey]['type'] === 'boolean'){
						\Twist::framework() ->setting($strKey,0);
					}
				}
			}
			$arrTags['message'] = '<p class="success">You new module settings were saved successfully</p>';
			//$arrSettings = \Twist::framework() -> settings() -> cache();
		}

		\Twist::redirect('./settings');
	}

	public function getUpdateSetting(){

		$arrAllowedSettings = array('DEVELOPMENT_MODE','MAINTENANCE_MODE','DEVELOPMENT_DEBUG_BAR','CACHE_ENABLED');

		if(array_key_exists('setting',$_GET) && array_key_exists('setting_value',$_GET) && in_array($_GET['setting'],$arrAllowedSettings)){
			\Twist::framework() ->setting($_GET['setting'],$_GET['setting_value']);
		}

		\Twist::redirect('./dashboard');
	}

	public function packages(){

		$arrTags = array();
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

	public function uninstall(){

		//Run the package installer
		if(array_key_exists('package',$_GET)){
			\Twist::framework()->package()->uninstaller($_GET['package']);
		}

		\Twist::redirect('./packages');
	}
}