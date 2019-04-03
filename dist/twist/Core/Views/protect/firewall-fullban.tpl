<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{data:code} {data:title}</title>
	<!--================================ META ================================-->
	<meta charset="utf-8">
	<!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
	<meta name="robots" content="noindex, nofollow">

	<!--================================ SHADOW RESOURCES ================================-->
	{resource:arable,inline}

	<!--================================ CSS ================================-->
	<style type="text/css">
		body {
			font-size: 16px;
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
<h1 class="no-bottom-padding align-center">Access Denied (Permanent)</h1>
<h2 class="no-top-padding lightweight align-center">Access from your IP Address has been permanently blocked by our security policy</h2>
<table>
	<tbody>
	<tr>
		<th>IP Address</th>
		<td>{data:ip_address}</td>
	</tr>
	<tr>
		<th>Restriction</th>
		<td>Soft Ban</td>
	</tr>
	<tr>
		<th>Reason</th>
		<td>{data:reason}</td>
	</tr>
	</tbody>
</table>
</body>
</html>