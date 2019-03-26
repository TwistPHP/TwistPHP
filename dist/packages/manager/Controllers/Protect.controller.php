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
	class Protect extends BaseUser{

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
	}