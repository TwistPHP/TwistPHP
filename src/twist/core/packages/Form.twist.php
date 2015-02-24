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

	namespace Twist\Core\Packages;
	use \Twist\Core\Classes\ModuleBase;

	/**
	 * Simply Form Builder Package
	 */
	class Form extends ModuleBase{

		public function __construct(){
			require_once sprintf('%s/libraries/Form/FormBuilder.lib.php',DIR_FRAMEWORK_PACKAGES);
		}

		public function create(){
			return new FormBuilder();
		}

		public function get($intFormID){
			return new FormBuilder($intFormID);
		}

		public function process($intFormID){
			$resForm = new FormBuilder($intFormID);
			$resForm->process();
		}

		public function render($intFormID){
			$resForm = new FormBuilder($intFormID);
			return $resForm->render();
		}
	}