<?php

	Twist::framework()->package()->install();

	//Optional Line: Add this line if you are adding database tables
	//Twist::framework()->package()->importSQL('manager/Data/twitter.sql');

	//Optional Line: Add this line if you are adding framework settings
	//Twist::framework()->package()->importSettings('manager/Data/settings.json');