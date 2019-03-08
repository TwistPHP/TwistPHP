<?php


	Twist::framework()->package()->uninstall();

	//Optional Line: Add this line if you are uninstalling database tables
	Twist::framework()->package()->importSQL(sprintf('%s/Data/uninstall.sql',dirname(__FILE__)));

	//Optional Line: Add this line if you are removing all package settings
	Twist::framework()->package()->removeSettings();

	//\Twist\Core\Models\ScheduledTasks::deletePackageTasks('notifications-queue');

	/**
	 * Remove all Lavish Shopping Hooks for the system
	 */
	\Twist::framework()->hooks()->cancel('TWIST_MANAGER_ROUTE','users-manager',true);
	\Twist::framework()->hooks()->cancel('TWIST_MANAGER_MENU','users-manager-menu',true);
	//\Twist::framework()->hooks()->cancel('TWIST_NOTIFICATION_METHODS','notifications-method-email',true);