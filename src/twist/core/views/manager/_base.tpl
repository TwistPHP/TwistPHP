<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>TwistPHP Manager</title>
		<!--================================ META ================================-->
		<meta charset="utf-8">
		<!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

		<!--================================ THIRD-PARTY RESOURCES ================================-->
		{resource:unsemantic}
		{resource:font-awesome}
		{resource:arable}
		{resource:jquery}

		<!--================================ CSS ================================-->
		<link href="{resource:core-uri}manager/css/base.css" type="text/css" rel="stylesheet">

		<!--================================ JAVASCRIPT ================================-->
		<!--<script src="{route:interface_uri}/resources/js/base.js"></script>-->

		<!--================================ LINKED DOCUMENTS ================================-->
		<link rel="shortcut icon" type="image/x-icon" href="{core:logo-favicon}">

		<!--================================ MOBILE STUFF ================================-->
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
	</head>
	<body>
		<div class="grid-container">
			<div class="grid-100 tablet-grid-100 mobile-grid-100">
				<h1 class="no-top-margin"><img src="{resource:core-uri}logos/logo.png">TwistPHP</h1>
			</div>
			<ul class="tabs">
				<li {route:request=='/'?'class="current"':''}><a href="{route:base_uri}/">Dashboard<i class="fa fa-tachometer"></i></a></li><!--
				--><li {route:request=='/settings'?'class="current"':''}><a href="{route:base_uri}/settings">Settings<i class="fa fa-cogs"></i></a></li><!--
				--><li {route:request=='/repositories'?'class="current"':''}><a href="{route:base_uri}/repositories">Repositories<i class="fa fa-cubes"></i></a></li><!--
				--><li {route:request=='/modules'?'class="current"':''}><a href="{route:base_uri}/modules">Modules<i class="fa fa-puzzle-piece"></i></a></li><!--
				--><li {route:request=='/interfaces'?'class="current"':''}><a href="{route:base_uri}/interfaces">Interfaces<i class="fa fa-flask"></i></a></li><!--
				--><li><a href="{route:base_uri}/?logout">Logout<i class="fa fa-sign-out"></i></a></li>
			</ul>
			<div class="grid-80 prefix-10 tablet-grid-90 tablet-prefix-5 mobile-grid-90 mobile-prefix-5 grid-parent">
				{route:response}
			</div>
		</div>
	</body>
</html>