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
	class Scheduler extends BaseUser{

		public function _index(){

			if(array_key_exists('task',$_GET)){

				$arrTasks = ScheduledTasks::get($_GET['task']);

				return $this->_view('components/scheduled/edit.tpl',$arrTasks);

			}elseif(array_key_exists('add',$_GET)){

				$arrTags = array(
					'commands' => ''
				);

				$arrFiles = scandir(TWIST_APP.'/Crons/');
				foreach($arrFiles as $strEachFile){
					if(!in_array($strEachFile,array('.','..')) && strstr($strEachFile,'.cron.php')){
						$strPath = ltrim(str_replace(TWIST_PUBLIC_ROOT,'',TWIST_APP.'/Crons/'.$strEachFile),'/');
						$arrTags['commands'] .= '<option value="app,'.$strPath.'">'.$strEachFile.' [App]</option>';
					}
				}

				$arrPackages = scandir(TWIST_PACKAGES);
				foreach($arrPackages as $strEachPackage){
					if(!in_array($strEachPackage,array('.','..'))){

						$arrFiles = scandir(TWIST_PACKAGES.'/'.$strEachPackage.'/Crons/');
						foreach($arrFiles as $strEachFile){
							if(!in_array($strEachFile,array('.','..')) && strstr($strEachFile,'.cron.php')){

								$strPath = ltrim(str_replace(TWIST_PUBLIC_ROOT,'',TWIST_PACKAGES.'/'.$strEachPackage.'/Crons/'.$strEachFile),'/');
								$arrTags['commands'] .= '<option value="'.$strEachPackage.','.$strPath.'">'.$strEachFile.' [Package: '.$strEachPackage.']</option>';
							}
						}

					}
				}

				$arrFiles = scandir(TWIST_FRAMEWORK.'Core/Crons/');
				foreach($arrFiles as $strEachFile){
					if(!in_array($strEachFile,array('.','..')) && strstr($strEachFile,'.cron.php')){
						$strPath = ltrim(str_replace(TWIST_PUBLIC_ROOT,'',TWIST_FRAMEWORK.'Core/Crons/'.$strEachFile),'/');
						$arrTags['commands'] .= '<option value="twist,'.$strPath.'">'.$strEachFile.' [TwistPHP]</option>';
					}
				}


				return $this->_view('components/scheduled/add.tpl',$arrTags);

			}elseif(array_key_exists('log',$_GET)){

				$arrTasks = ScheduledTasks::get($_GET['log']);

				$arrHistory = ScheduledTasks::history($_GET['log']);
				if(!count($arrHistory)){
					$arrHistory[] = ScheduledTasks::lastResult($_GET['log'])['output'];
				}
				$arrTasks['log'] = implode('<hr>',$arrHistory);

				return $this->_view('components/scheduled/log.tpl',$arrTasks);

			}else{
				$arrTags = array('tasks' => '','pulse' => '');
				$arrTags['pulse'] = ScheduledTasks::pulseInfo();
				$arrTags['pulse']['date'] = date('D d M, Y \a\t H:i',$arrTags['pulse']['last_pulse']);

				$arrTasks = ScheduledTasks::getAll();

				foreach($arrTasks as $arrEachTask){
					$arrTags['tasks'] .= $this->_view('components/scheduled/task-each.tpl',$arrEachTask);
				}

				if($arrTags['tasks'] == ''){
					$arrTags['tasks'] = $this->_view('components/scheduled/task-none.tpl');
				}

				$arrTags['public_root'] = rtrim(TWIST_PUBLIC_ROOT,'/');

				return $this->_view('pages/scheduled-tasks.tpl',$arrTags);
			}
		}

		public function POST_index(){

			if(array_key_exists('task',$_POST)){

				ScheduledTasks::editTask(
					$_POST['task'],
					$_POST['description'],
					$_POST['frequency'],
					$_POST['command'],
					$_POST['history'],
					$_POST['email'],
					($_POST['enabled'] == '1')
				);

			}elseif(array_key_exists('create_task',$_POST)){

				if(trim($_POST['description']) == ''){
					return $this->_index();
				}else{
					list($strPackage,$strCommand) = explode(',',$_POST['command']);

					ScheduledTasks::createTask(
						$_POST['description'],
						$_POST['frequency'],
						$strCommand,
						$_POST['history'],
						$_POST['email'],
						($_POST['enabled'] == '1'),
						$strPackage
					);
				}
			}

			\Twist::redirect('scheduled-tasks');
		}
	}