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

		public function _default(){
			return $this->dashboard();
		}

		public function dashboard(){

			//Set the release channel
			\Twist::framework()->upgrade()->channel(\Twist::framework()->setting('RELEASE_CHANNEL'));

			$arrCore = \Twist::framework()->upgrade()->getCore();

			$arrTags = array();
			$arrTags['update-information'] = ($arrCore['update'] == '1') ? \Twist::Template()->build('components/dashboard/update.tpl',$arrCore) : \Twist::Template()->build('components/dashboard/no-update.tpl',$arrCore);

			$arrTags['development-mode'] = (\Twist::framework()->setting('DEVELOPMENT_MODE') == '1') ? 'On' : 'Off';
			$arrTags['maintenance-mode'] = (\Twist::framework()->setting('MAINTENANCE_MODE') == '1') ? 'On' : 'Off';
			$arrTags['release-channel'] = \Twist::framework()->setting('RELEASE_CHANNEL');
			$arrTags['database-debug'] = (\Twist::framework()->setting('DATABASE_DEBUG') == '1') ? 'On' : 'Off';

			return \Twist::Template()->build('pages/dashboard.tpl',$arrTags);
		}

		public function settings(){

			$arrSettings = \Twist::framework() -> settings() -> arrSettingsInfo;
			$arrOption = array();

			foreach($arrSettings as $arrEachItem){

				$arrEachItem['input'] = '';

				if($arrEachItem['type'] == 'string'){
					$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
				}elseif($arrEachItem['type'] == 'boolean'){
					$arrEachItem['input'] .= sprintf('<input type="checkbox" name="settings[%s]" %svalue="1">',$arrEachItem['key'],($arrEachItem['value'] == '1') ? 'checked ' : '');
				}elseif($arrEachItem['type'] == 'options'){

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

				}elseif($arrEachItem['type'] == 'integer'){
					$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
				}else{
					//Unknown types
					$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
				}

				//Output the original settings in hidden inputs
				$arrEachItem['input'] .= sprintf('<input type="hidden" name="original[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);

				$arrOption[$arrEachItem['package']] .= \Twist::Template() -> build( 'components/settings/each-setting.tpl', $arrEachItem );
			}

			$arrTags = array();
			foreach($arrOption as $strKey => $strList){

				//if($strKey != 'Core'){
					$arrListTags = array('title' => $strKey, 'list' => $strList);
					$arrTags['settings'] .= \Twist::Template() -> build( 'components/settings/group.tpl', $arrListTags );
				//}
			}

			return \Twist::Template()->build('pages/settings.tpl',$arrTags);
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

			header(sprintf('Location: %s',$_SERVER['TWIST_ROUTE']['base_uri']));
		}

		public function getUpdateSetting(){

			$arrAllowedSettings = array('DEVELOPMENT_MODE','MAINTENANCE_MODE','RELEASE_CHANNEL','DATABASE_DEBUG');

			if(array_key_exists('setting',$_GET) && array_key_exists('setting_value',$_GET) && in_array($_GET['setting'],$arrAllowedSettings)){
				\Twist::framework() ->setting($_GET['setting'],$_GET['setting_value']);
			}

			header(sprintf('Location: %s',$_SERVER['TWIST_ROUTE']['base_uri']));
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
					if($arrEachInterface['repository'] == $strRepoKey && count($arrEachInterface['available'])){
						$arrEachRepo['interface_count']++;
					}
				}

				$arrEachRepo['module_count'] = 0;
				foreach($arrModules as $arrEachModule){
					if($arrEachModule['repository'] == $strRepoKey && count($arrEachModule['available'])){
						$arrEachRepo['module_count']++;
					}
				}

				if($strRepoKey == 'twistphp'){
					$arrTags['static'] = \Twist::Template() -> build( 'components/repositories/each-repo-static.tpl', $arrEachRepo );
				}else{
					$arrTags['third-party'] .= \Twist::Template() -> build( 'components/repositories/each-repo.tpl', $arrEachRepo );
				}

			}

			return \Twist::Template()->build('pages/repositories.tpl',$arrTags);
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
                    if($arrEachInterface['repository'] == $_GET['repo-key'] && count($arrEachInterface['available'])){
                        $arrTags['interfaces']++;
                    }
                }

                $arrTags['modules'] = 0;
                foreach($arrModules as $arrEachModule){
                    if($arrEachModule['repository'] == $_GET['repo-key'] && count($arrEachModule['available'])){
                        $arrTags['modules']++;
                    }
                }
            }

            return \Twist::Template()->build('pages/repository_manage.tpl',$arrTags);
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

					return \Twist::Template()->build('pages/package_information.tpl',$arrTags);
				}
			}

			header(sprintf('Location: %s',$_SERVER['TWIST_ROUTE']['base_uri']));
		}

		public function modules(){

			$arrTags = array();

			//Set the release channel
			\Twist::framework()->upgrade()->channel(\Twist::framework()->setting('RELEASE_CHANNEL'));

			$arrModules = \Twist::framework()->upgrade()->getModules();

			//print_r($arrModules);

			$arrTags['modules_installed'] = '';
			$arrTags['modules_official_available'] = '';
			$arrTags['modules_thirdparty_available'] = '';
			foreach($arrModules as $arrEachModule){

				if($arrEachModule['installed'] == '1'){
					$arrTags['modules_installed'] .= \Twist::Template()->build('components/modules/each-installed.tpl',$arrEachModule);
				}else{
					if($arrEachModule['repository'] == 'twistphp'){
						$arrTags['modules_official_available'] .= \Twist::Template()->build('components/modules/each-available.tpl',$arrEachModule);
					}else{
						$arrTags['modules_thirdparty_available'] .= \Twist::Template()->build('components/modules/each-available.tpl',$arrEachModule);
					}
				}
			}

			return \Twist::Template()->build('pages/modules.tpl',$arrTags);
		}

		public function interfaces(){

			$arrTags = array();

			//Set the release channel
			\Twist::framework()->upgrade()->channel(\Twist::framework()->setting('RELEASE_CHANNEL'));

			$arrInterfaces = \Twist::framework()->upgrade()->getInterfaces();
			//print_r($arrInterfaces);

			$arrTags['interfaces_installed'] = '';
			$arrTags['interfaces_official_available'] = '';
			$arrTags['interfaces_thirdparty_available'] = '';
			foreach($arrInterfaces as $arrEachInterface){

				if($arrEachInterface['installed'] == '1'){
					$arrTags['interfaces_installed'] .= \Twist::Template()->build('components/interfaces/each-installed.tpl',$arrEachInterface);
				}else{
					if($arrEachInterface['repository'] == 'twistphp'){
						$arrTags['interfaces_official_available'] .= \Twist::Template()->build('components/interfaces/each-available.tpl',$arrEachInterface);
					}else{
						$arrTags['interfaces_thirdparty_available'] .= \Twist::Template()->build('components/interfaces/each-available.tpl',$arrEachInterface);
					}
				}
			}

			return \Twist::Template()->build('pages/interfaces.tpl',$arrTags);
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

		public function update(){
			$arrTags = array();
			return \Twist::Template()->build('_update.tpl',$arrTags);
		}

		public function progress(){
			header('Content-Type: application/json');
			$strJsonFile = sprintf('%s/../progress.json',dirname(__FILE__));
			return (file_exists($strJsonFile)) ? file_get_contents($strJsonFile) : json_encode(array());
		}
	}