<?php

	Twist::framework()->package()->install();

	//Optional Line: Add this line if you are adding database tables
	//Twist::framework()->package()->importSQL('install/Data/install.sql');

	//Optional Line: Add this line if you are adding framework settings
	//Twist::framework()->package()->importSettings('install/Data/settings.json');