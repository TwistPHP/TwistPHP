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

	use Twist\Core\Classes\Instance;

	class Base{

		/**
		 * Return the name of the package class
		 * @return string
		 */
		protected function __calledClass(){
			return (function_exists('get_called_class')) ? get_called_class() : get_class($this);
		}

		/**
		 * Return information about the current package as an array
		 * @return array
		 */
		protected function __info(){
			return \Twist::framework() -> package() -> information($this->__calledClass());
		}

		/**
		 * Return the version number of the current package
		 * @return mixed
		 */
		protected function __version(){
			$arrData = \Twist::framework() -> package() -> information($this->__calledClass());
			return $arrData['version'];
		}

		/**
		 * Return the URI of the current package
		 * @return mixed
		 */
		protected function __uri(){
			$arrData = \Twist::framework() -> package() -> information($this->__calledClass());
			return $arrData['uri'];
		}

		/**
		 * Return the path to the current package
		 * @return mixed
		 */
		protected function __path(){
			$arrData = \Twist::framework() -> package() -> information($this->__calledClass());
			return $arrData['path'];
		}

		/**
		 * Return the registered extensions for the current package
		 * @return mixed
		 */
		protected function __extensions(){
			$arrData = \Twist::framework() -> package() -> information($this->__calledClass());
			return $arrData['extensions'];
		}

		/**
		 * Short link to the framework class itself
		 * @deprecated
		 * @return Framework
		 */
		protected function framework(){

			//Only required allow IDE auto-complete of code, otherwise only return Instance::retrieveObject('CoreFramework') would be fine
			$resTwistModule = (!Instance::isObject('CoreFramework')) ? new Framework() : Instance::retrieveObject('CoreFramework');
			Instance::storeObject('CoreFramework',$resTwistModule);
			return $resTwistModule;
		}
	}