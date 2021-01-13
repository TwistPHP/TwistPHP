<?php

	Twist::framework()->package()->uninstall();

	\Twist::framework()->hooks()->cancel('TWIST_MANAGER_CSS','twist-manager-fontawesome',true);
	\Twist::framework()->hooks()->cancel('TWIST_MANAGER_JS','twist-manager-jquery',true);