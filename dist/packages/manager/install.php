<?php

	Twist::framework()->package()->install();

	\Twist::framework()->hooks()->register('TWIST_MANAGER_CSS','twist-manager-fontawesome',array(
		'order' => 0,
		'files' => array(
			array(
				'href' => 'https://use.fontawesome.com/releases/v5.7.2/css/all.css',
				'integrity' => 'sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr',
				'crossorigin' => 'anonymous'
			)
		)
	),true);

	\Twist::framework()->hooks()->register('TWIST_MANAGER_JS','twist-manager-jquery',array(
		'order' => 0,
		'files' => array(
			'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'
		)
	),true);