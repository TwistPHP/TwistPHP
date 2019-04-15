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

	namespace Twist\Core\Models;

	/**
	 * Handle all package/package related enquiries, for instance if you want to know if a package is installed or what version it is.
	 */
	class Install{

		/**
		 * Install a package, this is required before a package can be run by the framework
		 * @param string $dirPackageJSON
		 * @return bool|int|null
		 */
		public static function package($dirPackageJSON){

			$intPackage = null;

			if(file_exists($dirPackageJSON)){

				$dirPackage = dirname($dirPackageJSON);
				$rawJson = file_get_contents($dirPackageJSON);
				$arrDetails = json_decode($rawJson,true);

				$strSlug = strtolower(basename($dirPackage));

				$arrResources = $arrRoutes = $arrBlocks = $arrExtensions = array();

				$resPackage = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'packages')->create();

				$resPackage->set('slug',$strSlug);
				$resPackage->set('name',$arrDetails['name']);
				$resPackage->set('version',$arrDetails['version']);
				$resPackage->set('folder',basename($dirPackage));
				$resPackage->set('package',(is_file(sprintf('%s/package.php',$dirPackage))) ? '1' : '0');
				$resPackage->set('installed',date('Y-m-d H:i:s'));
				$resPackage->set('resources',json_encode($arrResources));
				$resPackage->set('routes',json_encode($arrRoutes));
				$resPackage->set('blocks',json_encode($arrBlocks));
				$resPackage->set('extensions',json_encode($arrExtensions));

				$intPackage = $resPackage->commit();
				\Twist::framework()->package()->anonymousStats('install',$strSlug,$arrDetails['version']);
			}

			return $intPackage;
		}

		/**
		 * Remove a package, un-registers it from the framework
		 * @param string $strPackageSlug
		 * @return null
		 */
		public static function removePackage($strPackageSlug){
			\Twist::framework()->package()->anonymousStats('uninstall',$strPackageSlug,null);
			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'packages')->delete($strPackageSlug,'slug');
		}

		/**
		 * Install any DB and tables required by the framework
		 * @param string $dirInstallSQL
		 */
		public static function importSQL($dirInstallSQL){

			if(file_exists($dirInstallSQL)) {

				//Create a temp file with all the required table pre-fixes
				$dirImportFile = tempnam(sys_get_temp_dir(), 'twist-import');

				$blMBSupport = \Twist::Database()->mbSupport();

				file_put_contents($dirImportFile, str_replace(
					array(
						"/*TWIST_DATABASE_TABLE_PREFIX*/`",
						"/*TWIST_DATABASE_NAME*/",
						($blMBSupport) ? 'utf8' : 'utf8mb4' //Only replace utf8mb4 with utf8 when there is no multi-byte support
					),
					array(
						sprintf('`%s', TWIST_DATABASE_TABLE_PREFIX),
						TWIST_DATABASE_NAME,
						'utf8'
					),
					file_get_contents($dirInstallSQL)
				));

				//Import the SQL form the temp file
				\Twist::Database()->importSQL($dirImportFile);

				//Remove the temp file form the system
				unlink($dirImportFile);
			}
		}

		/**
		 * Install any framework settings that are required by the core.
		 * @param string $dirSettingsJSON
		 * @param string $strPackage
		 * @param string $strGroup
		 * @throws \Exception
		 */
		public static function importSettings($dirSettingsJSON,$strPackage = 'core',$strGroup = 'core'){

			if(file_exists($dirSettingsJSON)){

				$arrSettings = json_decode(file_get_contents($dirSettingsJSON),true);
				if(count($arrSettings)){

					$arrCoreSettings = array();
					$arrAllSettings = \Twist::framework()->settings()->arrSettingsInfo;
					foreach($arrAllSettings as $arrEachSetting){
						if(array_key_exists($arrEachSetting,'package') && $arrEachSetting['package'] == 'core'){
							$arrCoreSettings[$arrEachSetting['key']] = $arrEachSetting;
						}
					}

					foreach($arrSettings as $strKey => $arrOptions){

						\Twist::framework()->settings()->install(
							$strPackage,
							$strGroup,
							$strKey,
							$arrOptions['default'],
							$arrOptions['title'],
							$arrOptions['description'],
							$arrOptions['default'],
							$arrOptions['type'],
							$arrOptions['options'],
							$arrOptions['null']
						);

						unset($arrCoreSettings[$strKey]);
					}

					//Remove the old settings
					foreach($arrCoreSettings as $arrEachSettings){
						self::removeSettings($arrEachSettings['package'],$arrEachSettings['group'],$arrEachSettings['key']);
					}
				}
			}
		}

		/**
		 * Remove settings from the framework, these settings can be package or code settings
		 * @param string $strPackage
		 * @param string $strGroup
		 * @param null $strKey to remove a single settings only pass its key
		 */
		public static function removeSettings($strPackage,$strGroup,$strKey = null){
			\Twist::framework()->settings()->uninstall($strPackage,$strGroup,$strKey);
		}
	}