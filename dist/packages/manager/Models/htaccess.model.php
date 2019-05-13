<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
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

	namespace Packages\manager\Models;

	class htaccess{

		public static function rebuild(){

			$arrTags = array('rewrite_rules' => '');

			$arrRewriteRules = json_decode(\Twist::framework()->setting('HTACCESS_REWRITES'),true);
			foreach($arrRewriteRules as $intKey => $arrRule){
				$arrTags['rewrite_rules'] .= sprintf("\tRewriteRule %s %s [%s]\n",$arrRule['rule'],$arrRule['redirect'],$arrRule['options']);
			}

			//Update the .htaccess file to be a TwistPHP htaccess file
			$dirHTaccessFile = sprintf('%s/.htaccess',TWIST_PUBLIC_ROOT);
			file_put_contents($dirHTaccessFile,\Twist::View()->build(sprintf('%s/default-htaccess.tpl',TWIST_FRAMEWORK_VIEWS),$arrTags));
		}

	}