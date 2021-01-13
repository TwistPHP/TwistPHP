<?php

	/* ================================================================================
	 * TwistPHP - Default index.php
	 * --------------------------------------------------------------------------------
	 * Author:          Shadow Technologies Ltd.
     * Licence:      	https://www.gnu.org/licenses/gpl.html GPL License
	 * Documentation:   https://twistphp.com/docs
	 * ================================================================================
	 */

	define('TWIST_PUBLIC_ROOT','{data:public_path}');
	define('TWIST_APP','{data:app_path}');
	define('TWIST_PACKAGES','{data:packages_path}');
	define('TWIST_UPLOADS','{data:uploads_path}');

	require_once '{data:framework_path}framework.php';

	//Set the base URI for the framework install
    Twist::Route()->baseURI(\Twist::framework()->setting('SITE_BASE_URI'));

    //Register the framework manager
    Twist::Route()->manager('/manager');

	//Serve all routes
	Twist::ServeRoutes(false);