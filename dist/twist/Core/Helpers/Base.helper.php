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

	/**
	 * All core helpers should extend the helper base controller, this controller gives some default functionality that is required.
	 * @package Twist\Core\Helper
	 */
	class Base{

		/**
		 * Return the name of the helper class
		 * @return string
		 */
		protected function __calledClass(){
			return (function_exists('get_called_class')) ? get_called_class() : get_class($this);
		}

		/**
		 * Return information about the current helper as an array
		 * @return array
		 */
		protected function __info(){
			return \Twist::framework() -> package() -> information($this->__calledClass());
		}

		/**
		 * Return the version number of the current helper
		 * @return mixed
		 */
		protected function __version(){
			$arrData = \Twist::framework() -> package() -> information($this->__calledClass());
			return $arrData['version'];
		}

		/**
		 * Return the URI of the current helper
		 * @return mixed
		 */
		protected function __uri(){
			$arrData = \Twist::framework() -> package() -> information($this->__calledClass());
			return $arrData['uri'];
		}

		/**
		 * Return the path to the current helper
		 * @return mixed
		 */
		protected function __path(){
			$arrData = \Twist::framework() -> package() -> information($this->__calledClass());
			return $arrData['path'];
		}
	}