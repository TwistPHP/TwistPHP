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

use Packages\manager\Models\htaccess;
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

		$arrTags = array('widgets' => '');

		$arrWidgets = array('system','security','updates');
		foreach($arrWidgets as $strEachWidget){
			$arrTags['widgets'] .= $this->_view('widgets/'.$strEachWidget.'.php');
		}

		return $this->_view('pages/dashboard.tpl',$arrTags);
	}

}