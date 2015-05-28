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
	namespace Twist\Core\Classes;

	final class Framework{

		private $objDebug = null;
		private $objPackage = null;
		private $objRegister = null;
		private $objSettings = null;
		private $objTools = null;
		private $objUpgrade = null;

		public function __construct(){ }

		public function databaseDebug(){
			return false;
		}

		public function debug(){
			$this->objDebug = (is_null($this->objDebug)) ? new Debug() : $this->objDebug;
			return $this->objDebug;
		}

		public function package(){
			$this->objPackage = (is_null($this->objPackage)) ? new Package() : $this->objPackage;
			return $this->objPackage;
		}

		public function register(){
			$this->objRegister = (is_null($this->objRegister)) ? new Register() : $this->objRegister;
			return $this->objRegister;
		}

		public function setting($strKey,$strValue = null){

			$this->objSettings = (is_null($this->objSettings)) ? new Settings() : $this->objSettings;

			$mxdOut = null;
			if(is_null($strValue)){
				$mxdOut = $this->objSettings->get($strKey);
			}else{
				$mxdOut = $this->objSettings->set($strKey,$strValue);
			}
			return $mxdOut;
		}

		public function settings(){
			$this->objSettings = (is_null($this->objSettings)) ? new Settings() : $this->objSettings;
			return $this->objSettings;
		}

		public function tools(){
			$this->objTools = (is_null($this->objTools)) ? new Tools() : $this->objTools;
			return $this->objTools;
		}

		public function upgrade(){
			$this->objUpgrade = (is_null($this->objUpgrade)) ? new Upgrade() : $this->objUpgrade;
			return $this->objUpgrade;
		}

		public function getURI(){
			return str_replace(TWIST_DOCUMENT_ROOT,'',TWIST_FRAMEWORK);
		}

		public function runLevel($blDetails = false){

			$arrInfo = array(
				1 => array('level' => 1,'title'=>"Shell",'description'=>'The script was run from a manual invocation on a shell'),
				2 => array('level' => 2,'title'=>"CronTab",'description'=>'The script was run from the crontab entry'),
				3 => array('level' => 3,'title'=>"WebServer",'description'=>'The script was run from a webserver')
			);

			if(php_sapi_name() === 'cli'){
				$intRunLevel = (isset($_SERVER['TERM'])) ? 1 : 2;
			}else{
				$intRunLevel = 3;
			}

			return $blDetails ? $arrInfo[$intRunLevel] : $intRunLevel;
		}
	}