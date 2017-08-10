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

	use \Twist\Core\Models\Form\Builder;

	/**
	 * Simply Form Builder helper
	 */
	class Form extends Base{

		public function create(){
			return new Builder();
		}

		public function get($intFormID){
			return new Builder($intFormID);
		}

		public function process($intFormID){
			$resForm = new Builder($intFormID);
			$resForm->process();
		}

		public function render($intFormID){
			$resForm = new Builder($intFormID);
			return $resForm->render();
		}
	}