<?php

	/* ================================================================================
	 * TwistPHP - Default index.php
	 * --------------------------------------------------------------------------------
	 * Author:          Shadow Technologies Ltd.
	 * Documentation:   https://twistphp.com/docs
	 * ================================================================================
	 */

	define('TWIST_PUBLIC_ROOT','{data:public_path}');
	define('TWIST_APP','{data:app_path}');
	define('TWIST_PACKAGES','{data:packages_path}');
	define('TWIST_UPLOADS','{data:uploads_path}');

	require_once '{data:framework_path}framework.php';

	//TWISTPHP Interfaces
	{data:interfaces}

	//TWISTPHP Routes
	{data:routes}

	//TWISTPHP Serve
	{data:serve}