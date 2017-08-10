<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Copyright (C) 2016  Shadow Technologies Ltd.
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

	namespace Twist\Core\Helpers;

	use Twist\Core\Models\Package;
	use Twist\Core\Models\Hooks;
	use Twist\Core\Models\Debug;
	use Twist\Core\Models\Register;
	use Twist\Core\Models\Settings;
	use Twist\Core\Models\Tools;

	/**
	 * This utility is used by the framework directly providing support for all core framework features such as debug, settings, tools and handlers.
	 * @package Twist\Core\Helper
	 */
	class Framework{

		private $objDebug = null;
		private $objHooks = null;
		private $objPackage = null;
		private $objRegister = null;
		private $objSettings = null;
		private $objTools = null;

		public function __construct(){ }

		public function databaseDebug(){
			return false;
		}

		/**
		 * Returns and object of the debug tool, this can be used to process the debug window.
		 * @return null|\Twist\Core\Models\Debug
		 */
		public function debug(){
			$this->objDebug = (is_null($this->objDebug)) ? new Debug() : $this->objDebug;
			return $this->objDebug;
		}

		/**
		 * Returns and object of the hooks handler to allow custom code and functionality to be hooked into the framework and its packages
		 * @return null|\Twist\Core\Models\Hooks
		 */
		public function hooks(){
			$this->objHooks = (is_null($this->objHooks)) ? new Hooks() : $this->objHooks;
			return $this->objHooks;
		}

		/**
		 * An object with useful functions to install, uninstall and work with Twist and third party packages.
		 * @return null|\Twist\Core\Models\Package
		 */
		public function package(){
			$this->objPackage = (is_null($this->objPackage)) ? new Package() : $this->objPackage;
			return $this->objPackage;
		}

		/**
		 * An object to register and manager functions such as Shutdown handlers and Error Handlers.
		 * @return null|\Twist\Core\Models\Register
		 */
		public function register(){
			$this->objRegister = (is_null($this->objRegister)) ? new Register() : $this->objRegister;
			return $this->objRegister;
		}

		/**
		 * Get or set a single setting by its key, pass in a value (2nd parameter to set/store the value against the key).
		 * @param string $strKey
		 * @param null $strValue
		 * @return bool|null
		 */
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

		/**
		 * Returns the settings object where settings can be added/edited/reset and installed.
		 * @return null|\Twist\Core\Models\Settings
		 */
		public function settings(){
			$this->objSettings = (is_null($this->objSettings)) ? new Settings() : $this->objSettings;
			return $this->objSettings;
		}

		/**
		 * Return an object of useful tools that don't really fit anywhere else in the framework at this point.
		 * @return null|\Twist\Core\Models\Tools
		 */
		public function tools(){
			$this->objTools = (is_null($this->objTools)) ? new Tools() : $this->objTools;
			return $this->objTools;
		}

		/**
		 * Return the URI to the framework folder
		 * @return mixed
		 */
		public function getURI(){
			return str_replace(TWIST_DOCUMENT_ROOT,'',TWIST_FRAMEWORK);
		}

		/**
		 * Determin if the script is being run on Shell, CronTab or by a Webserver
		 * @param bool $blDetails
		 * @return int
		 */
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