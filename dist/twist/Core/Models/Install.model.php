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
	
	namespace Twist\Core\Models;
	
	/**
	 * Handle all package/package related enquiries, for instance if you want to know if a package is installed or what version it is.
	 */
	class Install{
		
		/**
		 * Final step, all the settings and tables are created, the user is then shown a finish page.
		 * @param $arrConfiguration
		 * @return string
		 */
		public static function framework($arrConfiguration){

			//Create all the required folders for twist
			$strApplicationPath = sprintf('%s%s/',$arrConfiguration['settings']['relative_path'],$arrConfiguration['settings']['app_path']);

			$resFile = \Twist::File();
			$resFile->recursiveCreate(sprintf('%s%s',$arrConfiguration['settings']['relative_path'],$arrConfiguration['settings']['packages_path']));
			$resFile->recursiveCreate(sprintf('%s%s',$arrConfiguration['settings']['relative_path'],$arrConfiguration['settings']['uploads_path']));
			$resFile->recursiveCreate(sprintf('%sAssets',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sCache',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sConfig',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sControllers',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sLogs',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sModels',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sResources',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sResources/css',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sResources/fonts',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sResources/images',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sResources/js',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sViews',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sTwist',$strApplicationPath));
			$resFile->recursiveCreate(sprintf('%sPackages',$strApplicationPath));

			//Create the config in the apps/config folder
			$arrConfigTags = array(
				'account_token' => '',
				'licence_key' => '',
				'database_protocol' => $arrConfiguration['database']['protocol'],
				'database_server' => $arrConfiguration['database']['host'],
				'database_username' => $arrConfiguration['database']['username'],
				'database_password' => $arrConfiguration['database']['password'],
				'database_name' => $arrConfiguration['database']['name'],
				'database_table_prefix' => $arrConfiguration['database']['table_prefix'],
			);

			file_put_contents(sprintf('%sConfig/config.php',$strApplicationPath),\Twist::View()->build(sprintf('%s/default-config.tpl',TWIST_FRAMEWORK_VIEWS),$arrConfigTags));

			\Twist::define('_TWIST_PUBLIC_ROOT',TWIST_DOCUMENT_ROOT.'/'.$arrConfiguration['settings']['site_root']);

			\Twist::define('_TWIST_APP',TWIST_DOCUMENT_ROOT.'/'.$arrConfiguration['settings']['app_path']);
			\Twist::define('_TWIST_APP_CONFIG',_TWIST_APP.'/Config/');
			\Twist::define('_TWIST_PACKAGES',TWIST_DOCUMENT_ROOT.'/'.$arrConfiguration['settings']['packages_path']);
			\Twist::define('_TWIST_UPLOADS',TWIST_DOCUMENT_ROOT.'/'.$arrConfiguration['settings']['uploads_path']);

			if($arrConfiguration['database']['type'] === 'database'){

				\Twist::Database()->connect(
					$arrConfiguration['database']['host'],
					$arrConfiguration['database']['username'],
					$arrConfiguration['database']['password'],
					$arrConfiguration['database']['name'],
					$arrConfiguration['database']['protocol']
				);

				\Twist::define('TWIST_DATABASE_PROTOCOL',$arrConfiguration['database']['protocol']);
				\Twist::define('TWIST_DATABASE_NAME',$arrConfiguration['database']['name']);
				\Twist::define('TWIST_DATABASE_HOST',$arrConfiguration['database']['host']);
				\Twist::define('TWIST_DATABASE_USERNAME',$arrConfiguration['database']['username']);
				\Twist::define('TWIST_DATABASE_PASSWORD',$arrConfiguration['database']['password']);
				\Twist::define('TWIST_DATABASE_TABLE_PREFIX',$arrConfiguration['database']['table_prefix']);

				//Disable file config as we are using database
				\Twist::framework()->settings()->fileConfigOverride(false);

				self::importSQL(sprintf('%sinstall.sql',TWIST_FRAMEWORK_INSTALL));
			}

			//Update all the core settings, add to a file when no Database is being used
			self::importSettings(sprintf('%ssettings.json',TWIST_FRAMEWORK_INSTALL));

			//Add new settings to the chosen settings storage method
			\Twist::framework()->setting('SITE_NAME',$arrConfiguration['settings']['site_name']);
			\Twist::framework()->setting('SITE_HOST',$arrConfiguration['settings']['site_host']);
			\Twist::framework()->setting('SITE_WWW',$arrConfiguration['settings']['site_www']);
			\Twist::framework()->setting('SITE_PROTOCOL',$arrConfiguration['settings']['http_protocol']);
			\Twist::framework()->setting('SITE_PROTOCOL_FORCE',$arrConfiguration['settings']['http_protocol_force']);
			\Twist::framework()->setting('TIMEZONE',$arrConfiguration['settings']['timezone']);

			//Create the level 0 user into the system - this will only occur is a database connection is present
			if(array_key_exists('user',$arrConfiguration) && $arrConfiguration['database']['protocol'] != 'none'){

				$objUser = \Twist::User()->create();

				$objUser->firstname($arrConfiguration['user']['firstname']);
				$objUser->surname($arrConfiguration['user']['lastname']);
				$objUser->email($arrConfiguration['user']['email']);
				$objUser->password($arrConfiguration['user']['password']);
				$objUser->level(0);
				$intUserID = $objUser->commit();
			}

			/**
			 * Update the index.php file to be a TwistPHP index file
			 */
			$dirIndexFile = sprintf('%s/index.php',_TWIST_PUBLIC_ROOT);

			if(file_exists($dirIndexFile)){
				\Twist::File()->move($dirIndexFile,sprintf('%s/old-index.php',_TWIST_PUBLIC_ROOT));
			}

			//Later on we can add in example templates etc if required
			$arrIndexTags = array(
				'public_path' => rtrim(_TWIST_PUBLIC_ROOT,'/'),
				'app_path' => _TWIST_APP,
				'packages_path' => _TWIST_PACKAGES,
				'uploads_path' => _TWIST_UPLOADS,
				'framework_path' => TWIST_FRAMEWORK,
				'interfaces' => '',
				'routes' => '',
				'serve' => 'Twist::Route() -> serve();'
			);

			file_put_contents($dirIndexFile,\Twist::View()->build(sprintf('%s/default-index.tpl',TWIST_FRAMEWORK_VIEWS),$arrIndexTags));

			/**
			 * Update the .htaccess file to be a TwistPHP htaccess file
			 */
			$dirHTaccessFile = sprintf('%s/.htaccess',_TWIST_PUBLIC_ROOT);
			file_put_contents($dirHTaccessFile,\Twist::View()->build(sprintf('%s/default-htaccess.tpl',TWIST_FRAMEWORK_VIEWS),array('rewrite_rules' => '')));

			return true;
		}

		/**
		 * Install any DB and tables required by the framework
		 * @param $dirInstallSQL
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
		 * @param $dirSettingsJSON
		 * @throws \Exception
		 */
		public static function importSettings($dirSettingsJSON){

			if(file_exists($dirSettingsJSON)){

				$arrSettings = json_decode(file_get_contents($dirSettingsJSON),true);
				if(count($arrSettings)){

					foreach($arrSettings as $strKey => $arrOptions){

						\Twist::framework()->settings()->install(
							'core',
							'core',
							$strKey,
							$arrOptions['default'],
							$arrOptions['title'],
							$arrOptions['description'],
							$arrOptions['default'],
							$arrOptions['type'],
							$arrOptions['options'],
							$arrOptions['null']
						);
					}
				}
			}
		}
	}