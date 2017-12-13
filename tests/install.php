<?php

	$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/';

	define('TWIST_PACKAGES',dirname(__FILE__).'/dist/packages/');
	define('TWIST_PACKAGE_INSTALL',dirname(__FILE__).'/packages/install/');

	/**
	 * We don't want to create a user upon setup at this point
	 */
	define("TWIST_QUICK_INSTALL", json_encode(array(
		'database' => array(
			'type' => 'database',
			'protocol' => 'mysqli',
			'host' => 'localhost',
			'username' => 'root',
			'password' => '',
			'name' => 'travis_ci_twist_test',
			'table_prefix' => 'twist_'
		),
		'settings' => array(
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
		)/*,
		'user' => array(
			'firstname' => 'Travis',
			'lastname' => 'CI',
			'email' => 'unittest@traviscit.test',
			'password' => 'travisci',
			'confirm_password' => 'travisci'
		)*/
	)));

	require_once( 'dist/twist/framework.php' );