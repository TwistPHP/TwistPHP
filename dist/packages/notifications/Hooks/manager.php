<?php

	\Twist::define('NOTIFICATIONS_VIEWS',dirname(__FILE__).'/../Views');

	$this -> controller( '/notifications/%', 'Packages\notifications\Controllers\Manager' );