<?php

	Twist::framework()->package()->install();

	/**
	 * Setup the page and menu items in the manager
	 */
	\Twist::framework()->hooks()->register('TWIST_MANAGER_ROUTE','users-manager',dirname(__FILE__).'/Hooks/manager.php',true);
	\Twist::framework()->hooks()->register('TWIST_MANAGER_MENU','users-manager-menu',file_get_contents(dirname(__FILE__).'/Data/manager-menu.json'),true);