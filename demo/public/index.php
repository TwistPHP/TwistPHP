<?php

	/* ================================================================================
	 * TwistPHP Demo
	 * --------------------------------------------------------------------------------
	 * Author:          Shadow Technologies Ltd.
     * Licence:      	https://www.gnu.org/licenses/gpl.html GPL License
	 * Documentation:   https://twistphp.com/docs
	 * ================================================================================
	 */

	//Define the globals required
	define( 'TWIST_PUBLIC_ROOT', '/vagrant/demo/public' );
	define( 'TWIST_APP', './app' );

	//Include TwistPHP
	require_once '../../dist/twist/framework.php';

	//Define a controller
	Twist::Route()->controller( '/%', 'DEMO_HelloWorld' );
	Twist::Route()->ajax( '/ajax/%', 'DEMO_AJAX123' );

	//Register the framework manager
	Twist::Route()->manager( '/manager' );

	//Serve all registered routes
	Twist::ServeRoutes();
