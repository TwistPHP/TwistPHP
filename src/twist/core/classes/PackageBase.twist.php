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

	if(!class_exists('PackageBase')){
		class PackageBase{

			protected function __calledClass(){
				return (function_exists('get_called_class')) ? get_called_class() : get_class($this);
			}

			protected function __info(){
				return $this->framework() -> module() -> information($this->__calledClass());
			}

			protected function __version(){
				$arrData = $this->framework() -> module() -> information($this->__calledClass());
				return $arrData['version'];
			}

			protected function __uri(){
				$arrData = $this->framework() -> module() -> information($this->__calledClass());
				return $arrData['uri'];
			}

			protected function __path(){
				$arrData = $this->framework() -> module() -> information($this->__calledClass());
				return $arrData['path'];
			}

			protected function __extensions(){
				$arrData = $this->framework() -> module() -> information($this->__calledClass());
				return $arrData['extensions'];
			}

			protected function framework(){

				//Only required allow IDE auto-complete of code, otherwise only return Instance::retrieveObject('CoreFramework') would be fine
				$resTwistModule = (!Instance::isObject('CoreFramework')) ? new Framework() : Instance::retrieveObject('CoreFramework');
				Instance::storeObject('CoreFramework',$resTwistModule);
				return $resTwistModule;
			}

		}
	}