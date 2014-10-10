<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>TwistPHP Exception</title>
		<!--================================ META ================================-->
		<meta charset="utf-8">
		<!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		<meta name="robots" content="noindex, nofollow">

		<!--================================ THIRD-PARTY RESOURCES ================================-->
		<link href="{core:unsemantic}" type="text/css" rel="stylesheet">

		<!--================================ SHADOW RESOURCES ================================-->
		<link href="{core:shadow-css}" type="text/css" rel="stylesheet">

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
			}

			.grid-container {
				padding: 20px 0;

				background: rgba(255, 255, 255, 0.85);

				border: 1px #CCC solid;
				border-radius: 3px;
			}

			h1,
			h2 {
				text-align: center;
			}

			pre {
				margin: 1.5em 0 2em;
			}

			pre + pre {
				margin-top: 0;
			}

			td {
				width: 100%;

				word-wrap: break-word;
				overflow-wrap: break-word;
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
				<h1 class="no-top-margin no-bottom-margin">{data:type} ({data:code})</h1>
				<h2 class="lightweight no-top-margin">{data:message}</h2>
			</div>
			<div class="grid-80 prefix-10 tablet-grid-90 tablet-prefix-5 mobile-grid-90 mobile-prefix-5 grid-parent">
				<div class="grid-100 tablet-grid-100 mobile-grid-100">
					<p class="warning">A {data:type} has occurred whilst loading this page, the issue has been logged and a bug report has been automatically logged.
						<br>Please <a href="javascript:window.location.reload( true );">refresh</a> the page to try again. If this problem persists please contact the site administrator.</p>
					<pre class="code" title="{data:file}">{data:php_code}</pre>
					{data:trace}
				</div>
				<div{data:request_headers==''?' class="hidden"':''}>
					<h3 class="grid-20 tablet-grid-20 mobile-grid-100 no-top-margin">Request</h3>
					<div class="grid-80 tablet-grid-80 mobile-grid-100">
						<dl class="inline">
							{data:request_headers}
						</dl>
					</div>
					<div class="clear"></div>
				</div>
				<h3 class="grid-20 tablet-grid-20 mobile-grid-100 no-top-margin">Server</h3>
				<div class="grid-80 tablet-grid-80 mobile-grid-100">
					<dl class="inline">
						{data:server_vars}
					</dl>
				</div>
				<div class="clear"></div>
				<h4 class="grid-20 tablet-grid-20 mobile-grid-100 no-top-margin lightweight">$_GET</h4>
				<div class="grid-80 tablet-grid-80 mobile-grid-100">
					<table>
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
								<th>Type</th>
								<th>Length</th>
							</tr>
						</thead>
						<tbody>
							{data:get}
						</tbody>
					</table>
				</div>
				<div class="clear"></div>
				<h4 class="grid-20 tablet-grid-20 mobile-grid-100 no-top-margin lightweight">$_POST</h4>
				<div class="grid-80 tablet-grid-80 mobile-grid-100">
					<table>
						<thead>
						<tr>
							<th>Key</th>
							<th>Value</th>
							<th>Type</th>
							<th>Length</th>
						</tr>
						</thead>
						<tbody>
						{data:post}
						</tbody>
					</table>
				</div>
				<div class="clear"></div>
				<h4 class="grid-20 tablet-grid-20 mobile-grid-100 no-top-margin lightweight">$_SESSION</h4>
				<div class="grid-80 tablet-grid-80 mobile-grid-100">
					<table>
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
								<th>Type</th>
								<th>Length</th>
							</tr>
						</thead>
						<tbody>
							{data:session}
						</tbody>
					</table>
				</div>
				<div class="clear"></div>
				<h4 class="grid-20 tablet-grid-20 mobile-grid-100 no-top-margin lightweight">$_COOKIE</h4>
				<div class="grid-80 tablet-grid-80 mobile-grid-100">
					<table>
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
								<th>Type</th>
								<th>Length</th>
							</tr>
						</thead>
						<tbody>
							{data:cookie}
						</tbody>
					</table>
				</div>
				<div class="clear"></div>
				<p class="grid-100 tablet-grid-100 mobile-grid-100"><small><a href="{setting:site_protocol}://{setting:site_host}" target="_blank">{setting:site_name}</a> is powered by <a href="https://github.com/Shadow-Technologies/TwistPHP" target="_blank">TwistPHP</a>.</small></p>
			</div>
		</div>
	</body>
</html>