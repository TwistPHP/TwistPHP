<div class="grid-66 tablet-grid-100 mobile-grid-100">
	<h2 class="no-top-padding">Dashboard</h2>
	<dl class="inline">
        <dt>TwistPHP Version</dt><dd>{data:version}</dd>
        <dt>Status</dt><dd>{data:version_status}</dd>
    </dl>
	<div class="clear"></div>
</div>
<div class="grid-33 tablet-grid-100 mobile-grid-100">
	<h3>Status</h3>
	<p class="{data:maintenance-mode=='Off'?'success':'error'}"><small class="float-right">[<a href="{route:registered_uri}../../../../index.php">Turn {data:maintenance-mode=='Off'?'On':'Off'}</a>]</small><strong>Maintenance Mode:</strong> {data:maintenance-mode}</p>
	<p class="{data:data-caching=='Off'?'warning':'success'}"><small class="float-right">[<a href="{route:registered_uri}../../../../index.php">Turn {data:data-caching=='Off'?'On':'Off'}</a>]</small><strong>Data Caching:</strong> {data:data-caching}</p>
	<p class="{data:development-mode=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}../../../../index.php">Turn {data:development-mode=='Off'?'On':'Off'}</a>]</small><strong>Development Mode:</strong> {data:development-mode}</p>
	<p class="{data:debug-bar=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}../../../../index.php">Turn {data:debug-bar=='Off'?'On':'Off'}</a>]</small><strong>Debug Bar:</strong> {data:debug-bar}</p>
	<p class="{data:debug-bar=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}../../../../index.php">Turn {data:debug-bar=='Off'?'On':'Off'}</a>]</small><strong>TwistProtect: Firewall</strong> {data:debug-bar}</p>
	<p class="{data:debug-bar=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}../../../../index.php">Turn {data:debug-bar=='Off'?'On':'Off'}</a>]</small><strong>TwistProtect: Scanner</strong> {data:debug-bar}</p>
</div>
<div class="clear"></div>
<div class="grid-100 tablet-grid-100 mobile-grid-100">
    <h3>Malicious Code Scanner</h3>
    <p>Automatically scans your site for malicious PHP code, you can <a href="{route:registered_uri}../../../../index.php">manage the code scanner</a> for more options and to see a list of any potentially infected files.</p>
    <dl class="inline">
        <dt>Last Scan</dt>
        <dd>{prettytime[data:scanner/scanned]}</dd>
        <dt>Files Scanned</dt>
        <dd>{data:scanner/files}</dd>
        <dt>Infected Files</dt>
        <dd>{data:scanner/infected/count}</dd>
    </dl>
</div>
<div class="clear"></div>
<div class="grid-100 tablet-grid-100 mobile-grid-100">
	<h3>Application Summary</h3>
	<dl class="inline">
		<dt>Cache Size</dt>
		<dd>32M (300 Files)</dd>
		<dt>Routes</dt>
		<dd>{data:route-data}</dd>
		<dt>User Accounts</dt>
		<dd>{data:user-accounts}</dd>
	</dl>
</div>
<div class="clear"></div>