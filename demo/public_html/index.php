<?php

	require_once( 'twist/framework.php' );

	Twist::Route() -> baseTemplate( '_base.tpl' );

	Twist::Route() -> template( '/', 'pages/home.tpl' );
	Twist::Route() -> element( '/count', 'count.php,5' );
	Twist::Route() -> redirect( '/twitter', 'https://twitter.com/' );

	Twist::Route() -> controller( '/examples/%', 'Examples' );

	Twist::Route() -> serve();