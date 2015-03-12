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

	/**
	* All paths are relative to the location of the twist folder
	*
	* DIR_PUBLIC_ROOT - Path to the public root of your site, usually your public_html folder "*"
	* DIR_APP - Path to your app folder, usually placed in your public_html folder
	* DIR_PACKAGES - Path to your packages folder, usually placed in your public_html folder
	*
	* "*" If twist is installed in your public_html folder the DIR_PUBLIC_ROOT would be '/'
	* "*" If twist is installed above your public_html folder the DIR_PUBLIC_ROOT would be '/public_html'
	*/
	Twist::define('DIR_SITE_ROOT',DIR_BASE.'{data:site_root}');
	Twist::define('DIR_APP',DIR_BASE.'{data:app_path}');
	Twist::define('DIR_PACKAGES',DIR_BASE.'{data:package_path}');