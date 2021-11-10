<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>TwistPHP Exception</title>
		<!--================================ META ================================-->
		<meta charset="utf-8">
		<!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		<meta name="robots" content="noindex, nofollow">

		<!--================================ THIRD-PARTY RESOURCES ================================-->
		{resource:unsemantic,inline}

		<!--================================ SHADOW RESOURCES ================================-->
		{resource:arable,inline}

		<!--================================ CSS ================================-->
		<style type="text/css">
			html::after {
				width: 475px;
				height: 500px;

				display: block;

				position: fixed;
				right: -50px;
				bottom: -50px;

				background: url("{core:logo-large}") left no-repeat;

				content: "";

				z-index: -1;
			}

			body {
				margin: 35px;
				font-family: Arial;
			}

			.grid-container {
				padding: 20px 10px;

				background: rgba(255, 255, 255, 0.85);

				border: 1px #CCC solid;
				border-radius: 4px;
			}

			h1,
			h2 {
				text-align: center;
			}

			pre {
				position: relative;
				margin: 30px 0 2em;
				border:1px solid #CCC;
				border-radius: 4px;
				padding:4px;
			}

			pre:before{
				content:attr(title);
				position: absolute;
				top:-24px;
				left:0;
				display: block;
				background-color: #CCC;
				color:#666;
				padding:4px;
				font-size: 14px;
				line-height: 14px;
				border-radius: 4px;
			}

			pre .highlight{
				background-color: yellow;
			}

			table{
				width:100%;
				max-width: 100%;
			}

			table thead tr th{
				background-color: #529f00;
				color:#FFF;
				text-align: left;
				padding:6px;
			}
			table th{
				padding:6px;
			}
			table tbody td,
			table tbody th{
				word-break: break-all;
				overflow-wrap: anywhere;
				padding:6px;
			}
			dl {
				display: flex;
				flex-flow: row wrap;
				border: solid #529f00;
				border-width: 1px 1px 0 0;
				border-radius: 4px;
				overflow: hidden;
			}
			dt {
				flex-basis: 20%;
				padding: 6px 12px;
				background: #529f00;
				text-align: right;
				color: #fff;
			}
			dd {
				flex-basis: 70%;
				flex-grow: 1;
				margin: 0;
				padding: 6px 12px;
				border-bottom: 1px solid #529f00;
				word-break: break-all;
				overflow-wrap: anywhere;
			}

		</style>

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
				<h1 class="no-bottom-padding">{data:type} ({data:code})</h1>
			</div>
			<div class="grid-80 prefix-10 tablet-grid-90 tablet-prefix-5 mobile-grid-90 mobile-prefix-5">
				<p class="warning">A {data:type} has occurred whilst loading this page, the {data:type} has been logged.
					<br>Please <a href="javascript:window.location.reload( true );">refresh</a> the page to try again. If this problem persists please contact the site administrator.</p>
				<hr>
				<p><small><a href="{setting:site_protocol}://{setting:site_host}" target="_blank">{setting:site_name}</a> is powered by <a href="https://github.com/Shadow-Technologies/TwistPHP" target="_blank">TwistPHP</a>.</small></p>
			</div>
		</div>
	</body>
</html>