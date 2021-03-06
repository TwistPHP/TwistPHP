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

	//Include TwistPHP
	require_once '../../dist/twist/framework.php';

	//Define a controller
	Twist::Route()->controller( '/%', 'HelloWorld' );
	Twist::Route()->ajax( '/ajax/%', 'AJAX123' );
	Twist::Route()->upload( '/process-upload/%' );

	//Register the framework manager
	Twist::Route()->manager( '/manager' );

	//Serve all registered routes
	Twist::ServeRoutes();
