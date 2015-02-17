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
	namespace TwistPHP;

	final class Framework{

		private $objDebug = null;
		private $objInterfaces = null;
		private $objModule = null;
		private $objRegister = null;
		private $objSettings = null;
		private $objTools = null;
		private $objUpgrade = null;

		public function __construct(){

			require_once sprintf('%sSettings.twist.php',DIR_FRAMEWORK_CLASSES);
			$this->objSettings = new Settings();

			require_once sprintf('%sDebug.twist.php',DIR_FRAMEWORK_CLASSES);
			require_once sprintf('%sInterfaces.twist.php',DIR_FRAMEWORK_CLASSES);
			require_once sprintf('%sModule.twist.php',DIR_FRAMEWORK_CLASSES);
			require_once sprintf('%sRegister.twist.php',DIR_FRAMEWORK_CLASSES);
			require_once sprintf('%sShutdown.twist.php',DIR_FRAMEWORK_CLASSES);
			require_once sprintf('%sTools.twist.php',DIR_FRAMEWORK_CLASSES);
			require_once sprintf('%sUpgrade.twist.php',DIR_FRAMEWORK_CLASSES);

			$this->objDebug = new Debug();
			$this->objInterfaces = new Interfaces();
			$this->objModule = new Module();
			$this->objRegister = new Register();
			$this->objTools = new Tools();
			$this->objUpgrade = new Upgrade();
		}

		public function databaseDebug(){
			return false;
		}

		public function debug(){
			return $this->objDebug;
		}

		public function interfaces(){
			return $this->objInterfaces;
		}

		public function module(){
			return $this->objModule;
		}

		public function register(){
			return $this->objRegister;
		}

		public function setting($strKey,$strValue = null){
			$mxdOut = null;
			if(is_null($strValue)){
				$mxdOut = $this->objSettings->get($strKey);
			}else{
				$mxdOut = $this->objSettings->set($strKey,$strValue);
			}
			return $mxdOut;
		}

		public function settings(){
			return $this->objSettings;
		}

		public function tools(){
			return $this->objTools;
		}

		public function upgrade(){
			return $this->objUpgrade;
		}

		public function getURI(){
			return str_replace(BASE_LOCATION,'',DIR_FRAMEWORK);
		}

		public function runLevel($blDetails = false){

			$arrInfo = array(
				1 => array('level' => 1,'title'=>"Shell",'description'=>'The script was run from a manual invocation on a shell'),
				2 => array('level' => 2,'title'=>"CronTab",'description'=>'The script was run from the crontab entry'),
				3 => array('level' => 3,'title'=>"WebServer",'description'=>'The script was run from a webserver')
			);

			if(php_sapi_name() == 'cli'){
				$intRunLevel = (isset($_SERVER['TERM'])) ? 1 : 2;
			}else{
				$intRunLevel = 3;
			}

			return ($blDetails == true) ? $arrInfo[$intRunLevel] : $intRunLevel;
		}
	}