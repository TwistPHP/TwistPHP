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

		$arrTags['pulse'] = ScheduledTasks::pulseInfo();
		$arrTags['server'] = $_SERVER["SERVER_SOFTWARE"];
		$arrTags['php_version'] = phpversion();
		$arrTags['php_memory'] = $this->setting_to_bytes(ini_get('memory_limit'));
		$arrTags['php_upload_max'] = $this->setting_to_bytes(ini_get('upload_max_filesize'));
		$arrTags['php_max_execution'] = ini_get('max_execution_time');

		return $this->_view('pages/dashboard.tpl',$arrTags);
	}

	/**
	 * @param string $setting
	 *
	 * @return NULL|number
	 */
	protected function setting_to_bytes($setting){
		static $short = array('k' => 0x400,
			'm' => 0x100000,
			'g' => 0x40000000);

		$setting = (string)$setting;
		if (!($len = strlen($setting))) return NULL;
		$last    = strtolower($setting[$len - 1]);
		$numeric = (int) $setting;
		$numeric *= isset($short[$last]) ? $short[$last] : 1;
		return $numeric;
	}

}