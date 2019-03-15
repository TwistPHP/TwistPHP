<?php

	\Twist::define('ACCOUNTS_VIEWS',dirname(__FILE__).'/../Views');

	$this -> controller( '/users/%', 'Packages\users\Controllers\Manager' );