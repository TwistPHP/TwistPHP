<?php

	require_once '../dist/twist/framework.php';

	$arrSession = array(
		'checks' => array(
			'status' => 1,
			'details' => array(
				'php_version' => 'success',
				'file_permissions' => 'success',
				'php_curl' => 'success',
				'php_mysql' => 'success',
				'php_zip' => 'success',
				'php_multibyte' => 'success',
				'php_cookies' => 'success',
				'continue_status' => ''
			)
		),
		'database' => array(
			'status' => 1,
			'details' => array(
				'type' => 'json',
				'protocol' => 'none',
				'host' => 'localhost',
				'username' => 'root',
				'password' => '',
				'name' => 'travis_ci_twist_test',
				'table_prefix' => 'twist_'
			)
		),
		'settings' => array(
			'status' => 1,
			'details' => array(
				'site_name' => 'Travis CI Test',
				'site_host' => 'localhost',
				'site_www' => '0',
				'http_protocol' => 'http',
				'http_protocol_force' => '0',
				'timezone' => 'Europe/London',
				'relative_path' => '/var/www/default/public_html/',
				'site_root' => '',
				'app_path' => 'app',
				'packages_path' => 'packages',
				'uploads_path' => 'uploads'
			)
		),
		'user' => array(
			'status' => '1',
			'details' => array(
				'firstname' => '',
				'lastname' => '',
				'email' => '',
				'password' => '',
				'confirm_password' => ''
			)
		)
	);

	$resFile = \Twist::File();
	$resFile->recursiveCreate( sprintf( '%s%s', $arrSession['settings']['details']['relative_path'], $arrSession['settings']['details']['packages_path'] ) );
	$resFile->recursiveCreate( sprintf( '%s%s', $arrSession['settings']['details']['relative_path'], $arrSession['settings']['details']['uploads_path'] ) );
	$resFile->recursiveCreate( sprintf( '%sAssets', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sCache', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sConfig', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sControllers', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sLogs', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sModels', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sResources', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sResources/css', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sResources/fonts', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sResources/images', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sResources/js', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sViews', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sTwist', $strApplicationPath ) );
	$resFile->recursiveCreate( sprintf( '%sPackages', $strApplicationPath ) );

	//Create the config in the apps/config folder
	$arrConfigTags = array(
		'account_token' => '',
		'licence_key' => '',
		'database_protocol' => $arrSession['database']['details']['protocol'],
		'database_server' => $arrSession['database']['details']['host'],
		'database_username' => $arrSession['database']['details']['username'],
		'database_password' => $arrSession['database']['details']['password'],
		'database_name' => $arrSession['database']['details']['name'],
		'database_table_prefix' => $arrSession['database']['details']['table_prefix'],
	);

	file_put_contents( sprintf( '%sConfig/config.php', $strApplicationPath ), \Twist::View()->build( 'config.tpl', $arrConfigTags ) );

	\Twist::define( '_TWIST_PUBLIC_ROOT', TWIST_DOCUMENT_ROOT . '/' . $arrSession['settings']['details']['site_root'] );

	\Twist::define( '_TWIST_APP', TWIST_DOCUMENT_ROOT . '/' . $arrSession['settings']['details']['app_path'] );
	\Twist::define( '_TWIST_APP_CONFIG', _TWIST_APP . '/Config/' );
	\Twist::define( '_TWIST_PACKAGES', TWIST_DOCUMENT_ROOT . '/' . $arrSession['settings']['details']['packages_path'] );
	\Twist::define( '_TWIST_UPLOADS', TWIST_DOCUMENT_ROOT . '/' . $arrSession['settings']['details']['uploads_path'] );

	if( $arrSession['database']['details']['type'] === 'database' ) {

		\Twist::Database()->connect(
			$arrSession['database']['details']['host'],
			$arrSession['database']['details']['username'],
			$arrSession['database']['details']['password'],
			$arrSession['database']['details']['name'],
			$arrSession['database']['details']['protocol']
		);

		\Twist::define( 'TWIST_DATABASE_PROTOCOL', $arrSession['database']['details']['protocol'] );
		\Twist::define( 'TWIST_DATABASE_NAME', $arrSession['database']['details']['name'] );
		\Twist::define( 'TWIST_DATABASE_HOST', $arrSession['database']['details']['host'] );
		\Twist::define( 'TWIST_DATABASE_USERNAME', $arrSession['database']['details']['username'] );
		\Twist::define( 'TWIST_DATABASE_PASSWORD', $arrSession['database']['details']['password'] );
		\Twist::define( 'TWIST_DATABASE_TABLE_PREFIX', $arrSession['database']['details']['table_prefix'] );

		//Disable file config as we are using database
		\Twist::framework()->settings()->fileConfigOverride( false );

		$this->importSQL( sprintf( '%sinstall.sql', TWIST_FRAMEWORK_INSTALL ) );
	}

	//Update all the core settings, add to a file when no Database is being used
	$this->importSettings( sprintf( '%ssettings.json', TWIST_FRAMEWORK_INSTALL ) );

	//Add new settings to the chosen settings storage method
	\Twist::framework()->setting( 'SITE_NAME', $arrSession['settings']['details']['site_name'] );
	\Twist::framework()->setting( 'SITE_HOST', $arrSession['settings']['details']['site_host'] );
	\Twist::framework()->setting( 'SITE_WWW', $arrSession['settings']['details']['site_www'] );
	\Twist::framework()->setting( 'SITE_PROTOCOL', $arrSession['settings']['details']['http_protocol'] );
	\Twist::framework()->setting( 'SITE_PROTOCOL_FORCE', $arrSession['settings']['details']['http_protocol_force'] );
	\Twist::framework()->setting( 'TIMEZONE', $arrSession['settings']['details']['timezone'] );

	//Create the level 0 user into the system - this will only occur is a database connection is present
	if( $arrSession['user']['status'] && $arrSession['database']['details']['protocol'] != 'none' ) {

		$objUser = \Twist::User()->create();

		$objUser->firstname( $arrSession['user']['details']['firstname'] );
		$objUser->surname( $arrSession['user']['details']['lastname'] );
		$objUser->email( $arrSession['user']['details']['email'] );
		$objUser->password( $arrSession['user']['details']['password'] );
		$objUser->level( 0 );
		$intUserID = $objUser->commit();
	}