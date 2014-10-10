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
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP;

	if(!class_exists('Instance')){
		class Instance{

			protected static $arrFrameworkObjects = array();

			public static function isObject($strObjectIdentifier){
				return (array_key_exists($strObjectIdentifier,self::$arrFrameworkObjects));
			}

			public static function storeObject($strObjectIdentifier,$objResource){
				self::$arrFrameworkObjects[$strObjectIdentifier] = $objResource;
			}

			public static function retrieveObject($strObjectIdentifier){
				return self::$arrFrameworkObjects[$strObjectIdentifier];
			}

			public static function removeObject($strObjectIdentifier){
				self::$arrFrameworkObjects[$strObjectIdentifier] = null;
			}

			public static function listObjects(){
				return array_keys(self::$arrFrameworkObjects);
			}
		}
	}