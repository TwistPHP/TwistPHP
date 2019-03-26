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
	class Packages extends BaseUser{

		/**
		 * Display all the installed and un-installed packages that are currently in your packages folder. The page does not currently have an APP store feature.
		 * @return string
		 */
		public function _index(){

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

			if(array_key_exists('download',$_GET)){
				//Run the package download and installer
				\Twist::framework()->package()->download($_GET['download']);
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
	}