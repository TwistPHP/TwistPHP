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

	use Twist\Core\Controllers\BaseAJAX;
	use Twist\Core\Models\Protect\Firewall;

	class AJAX extends BaseAJAX{

		public function firewallBlockIP(){

			if(count($_POST)){
				$this->_required('ip_address','ip');
				$this->_required('reason','string',true);
				$this->_required('full_ban','integer');

				if($this->_check()){
					Firewall::banIP($_POST['ip_address'],$_POST['reason'],($_POST['full_ban'] == '1') ? true : false);
					\Twist::successMessage('IP Address has been banned from the system');
				}
			}

			$arrOut = array(
				'html' => $this->_view('components/firewall/modal-block-ip.tpl')
			);

			return $this->_ajaxRespond($arrOut);
		}

		public function firewallWhitelistIP(){

			if(count($_POST)){
				$this->_required('ip_address','ip');
				$this->_required('reason','string',true);

				if($this->_check()){
					Firewall::whitelistIP($_POST['ip_address'],$_POST['reason']);
					\Twist::successMessage('IP Address has been whitelisted in the system');
				}
			}

			$arrOut = array(
				'html' => $this->_view('components/firewall/modal-whitelist-ip.tpl')
			);

			return $this->_ajaxRespond($arrOut);
		}
	}