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

	use \Twist\Core\Controllers\Base;
	use Packages\install\Models\Install;
	use Twist\Core\Models\Protect\Firewall;
	use Twist\Core\Models\Protect\Scanner;
	use \Twist\Core\Models\ScheduledTasks;

	/**
	 * The route controller for the framework manager, generates the pages of the manager tool.
	 * @package Twist\Core\Controllers
	 */
	class Settings extends Base{

		/**
		 * An overview of all the settings in the TwistPHP Settings table, from here all settings can be updated as necessary.
		 * @return string
		 */
		public function _index(){

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

					if(false && count($arrOptions) <= 3){
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
				}elseif($arrEachItem['type'] === 'json'){
					$arrEachItem['input'] .= sprintf('<textarea type="text" name="settings[%s]">%s</textarea>',$arrEachItem['key'],$arrEachItem['value']);
				}else{
					//Unknown types
					$arrEachItem['input'] .= sprintf('<input type="text" name="settings[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);
				}

				//Output the original settings in hidden inputs
				$arrEachItem['input'] .= sprintf('<input type="hidden" name="original[%s]" value="%s">',$arrEachItem['key'],$arrEachItem['value']);

				list($strPackage,$strkeyPart) = explode('_',$arrEachItem['key'],2);
				$strPackage = ucwords(strtolower($strPackage));

				if($arrEachItem['package'] !== 'core'){
					$strPackage = $arrEachItem['package'];
				}

				//Fix any undefined index's
				if(!array_key_exists($strPackage,$arrOption)){
					$arrOption[$strPackage] = array('count' => 0,'html' => '');
				}

				$arrOption[$strPackage]['count']++;
				$arrOption[$strPackage]['html'] .= $this->_view('components/settings/each-setting.tpl', $arrEachItem );
			}

			$strGeneral = '';
			$arrTags = array('settings' => '');
			foreach($arrOption as $strKey => $arrList){

				if($arrList['count'] == 1){
					$strGeneral .= $arrList['html'];
				}else{
					$arrListTags = array('title' => $strKey, 'list' => $arrList['html']);

					$arrTags['tabs'] .= $this->_view('components/settings/tab.tpl', $arrListTags );
					$arrTags['tob_content'] .= $this->_view('components/settings/tab_content.tpl', $arrListTags );
				}
			}

			if($strGeneral != ''){
				$arrListTags = array('title' => 'General', 'list' => $strGeneral);
				$arrTags['tabs'] = $this->_view('components/settings/tab.tpl', $arrListTags ).$arrTags['tabs'];
				$arrTags['tob_content'] = $this->_view('components/settings/tab_content.tpl', $arrListTags ).$arrTags['tob_content'];
			}

			return $this->_view('pages/settings.tpl',$arrTags);
		}

		/**
		 * Store all the setting changes POST'ed  form the settings page.
		 */
		public function POST_index(){

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
		 * HTaccess manager to all the editing of browser cache settings, rewrite rules and default host name redirects such as using www. or not and forcing https.
		 * @return string
		 */
		public function performance(){

			$arrTags = array();

			return $this->_view('pages/performance.tpl',$arrTags);
		}

		public function POSTperformance(){

			\Twist::framework()->setting('SITE_WWW',$_POST['SITE_WWW']);
			\Twist::framework()->setting('SITE_PROTOCOL',$_POST['SITE_PROTOCOL']);
			\Twist::framework()->setting('SITE_PROTOCOL_FORCE',$_POST['SITE_PROTOCOL_FORCE']);
			\Twist::framework()->setting('SITE_DIRECTORY_INDEX',$_POST['SITE_DIRECTORY_INDEX']);

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

			//Rebuild the htaccess file
			\Packages\manager\Models\htaccess::rebuild();

			return $this->performance();
		}

		/**
		 * HTaccess manager to all the editing of browser cache settings, rewrite rules and default host name redirects such as using www. or not and forcing https.
		 * @return string
		 */
		public function redirects(){

			$arrTags = array('rewrite_rules' => '');

			$arrRewrites = json_decode(\Twist::framework()->setting('HTACCESS_REWRITES'),true);

			if(count($arrRewrites)){
				foreach($arrRewrites as $arrEachRewrite){
					$arrTags['rewrite_rules'] .= $this->_view('components/htaccess/rewrite-rule.tpl',$arrEachRewrite);
				}
			}

			return $this->_view('pages/redirects.tpl',$arrTags);
		}

		public function POSTredirects(){

			$arrRewriteRules = array();
			foreach($_POST['rewrite'] as $intKey => $strRewriteURI){
				if(array_key_exists($intKey,$_POST['rewrite-redirect']) && array_key_exists($intKey,$_POST['rewrite-options']) && $strRewriteURI != '' && $_POST['rewrite-redirect'][$intKey] != ''){
					$arrRewriteRules[] = array('rule' => $strRewriteURI,'redirect' => $_POST['rewrite-redirect'][$intKey],'options' => $_POST['rewrite-options'][$intKey]);
				}
			}

			\Twist::framework()->setting('HTACCESS_REWRITES',json_encode($arrRewriteRules));
			\Twist::framework()->setting('HTACCESS_CUSTOM',$_POST['HTACCESS_CUSTOM']);

			//Rebuild the htaccess file
			\Packages\manager\Models\htaccess::rebuild();

			\Twist::successMessage('Redirects have been stores and are now live');

			return $this->redirects();
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

	}