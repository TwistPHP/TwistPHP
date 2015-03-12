<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-gb" dir="ltr">
<head>
	<title>Login | TwistPHP Manager</title>
	<!--================================ META ================================-->
	<meta charset="utf-8">
	<!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

	<!--================================ THIRD-PARTY RESOURCES ================================-->
	<script src="{route:interface_uri}/resources/third-party/jquery-2.1.1.min.js"></script>
	<link href="{route:interface_uri}/resources/third-party/unsemantic-grid-responsive-tablet-no-ie7.css" type="text/css" rel="stylesheet">
	<link href="{route:interface_uri}/resources/third-party/font-awesome/css/font-awesome.min.css" type="text/css" rel="stylesheet">

	<!--================================ SHADOW RESOURCES ================================-->
	<link href="{route:interface_uri}/resources/third-party/arable.min.css" type="text/css" rel="stylesheet">
	<!--<script src="{route:interface_uri}/resources/third-party/shadow-js.min.js"></script>-->

	<!--================================ CSS ================================-->
	<link href="{route:interface_uri}/resources/css/base.css" type="text/css" rel="stylesheet">

	<!--================================ JAVASCRIPT ================================-->
	<!--<script src="{route:interface_uri}/resources/js/base.js"></script>-->

	<!--================================ LINKED DOCUMENTS ================================-->
	<link rel="shortcut icon" type="image/x-icon" href="{route:interface_uri}/resources/images/icons/favicon.png">

	<!--================================ MOBILE STUFF ================================-->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
</head>
<body>
	<div class="grid-container">
		<div class="grid-100 tablet-grid-100 mobile-grid-100">
			<h1 class="no-top-margin"><img src="{route:interface_uri}/resources/images/logo.png">TwistPHP</h1>
		</div>
		<div class="grid-80 prefix-10 tablet-grid-90 tablet-prefix-5 mobile-grid-90 mobile-prefix-5 grid-parent">
			{user:login_form,/manager}
		</div>
	</div>
</body>
</html>