<?php

	define("TWIST_QUICK_INSTALL", json_encode(array(
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
				'type' => 'database',
				'protocol' => 'mysqli',
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
				'relative_path' => dirname(__FILE__).'/',
				'site_root' => '',
				'app_path' => 'app',
				'packages_path' => 'packages',
				'uploads_path' => 'uploads'
			)
		),
		'user' => array(
			'status' => 0,
			'details' => array(
				'firstname' => 'Travis',
				'lastname' => 'CI',
				'email' => 'unittest@traviscit.test',
				'password' => 'travisci',
				'confirm_password' => 'travisci'
			)
		)
	)));

	require_once( 'dist/twist/framework.php' );