<?php


	Twist::framework()->package()->uninstall();

	//Optional Line: Add this line if you are removing all package settings
	Twist::framework()->package()->removeSettings();


	/**
	 * Remove all User Package Hooks for the system
	 */
	\Twist::framework()->hooks()->cancel('TWIST_MANAGER_ROUTE','users-manager',true);
	\Twist::framework()->hooks()->cancel('TWIST_MANAGER_MENU','users-manager-menu',true);
