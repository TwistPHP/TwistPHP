<h1>Dashboard</h1>

<div class="box box33">
	<div class="boxTitle">
		<h3>System</h3>
	</div>
	<div class="boxContent">
		<dl class="inline">
			<dt>TwistProtect</dt><dd>Firewall Enabled</dd><dd>Scanner Enabled</dd>
			<dt>TwistPulse</dt><dd>Detected (1bpm)</dd>
			<dt></dt><dd></dd>
			<dt>Server</dt><dd>{data:server}</dd>
			<dt>PHP Version</dt><dd>{data:php_version}</dd>
			<dt>Memory Limit</dt><dd>{bytestosize[data:php_memory]}</dd>
			<dt>Max Upload</dt><dd>{bytestosize[data:php_upload_max]}</dd>
			<dt>Execution Time</dt><dd>{data:php_max_execution}s</dd>
		</dl>
		<div class="clear"></div>
	</div>
	<div class="boxFooter">
		TwistPHP v{data:version}
	</div>
</div>

<div class="box box33">
	<div class="boxTitle">
		<h3>Quick Tools</h3>
	</div>
	<div class="boxContent">
		<p class="notification {data:maintenance-mode=='Off'?'success':'error'}"><small class="float-right">[<a href="{route:registered_uri}?maintenance-mode={data:maintenance-mode=='Off'?'1':'0'}">Turn {data:maintenance-mode=='Off'?'On':'Off'}</a>]</small><strong>Maintenance Mode:</strong> {data:maintenance-mode}</p>
		<p class="notification {data:data-caching=='Off'?'warning':'success'}"><small class="float-right">[<a href="{route:registered_uri}?data-caching={data:data-caching=='Off'?'1':'0'}">Turn {data:data-caching=='Off'?'On':'Off'}</a>]</small><strong>Data Caching:</strong> {data:data-caching}</p>
		<p class="notification {data:development-mode=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}?development-mode={data:development-mode=='Off'?'1':'0'}">Turn {data:development-mode=='Off'?'On':'Off'}</a>]</small><strong>Development Mode:</strong> {data:development-mode}</p>
		<p class="notification {data:debug-bar=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}?debug-bar={data:debug-bar=='Off'?'1':'0'}">Turn {data:debug-bar=='Off'?'On':'Off'}</a>]</small><strong>Debug Bar:</strong> {data:debug-bar}</p>
		<p class="notification {data:twistprotect-firewall=='Off'?'warning':'success'}"><small class="float-right">[<a href="{route:registered_uri}?twistprotect-firewall={data:twistprotect-firewall=='Off'?'1':'0'}">Turn {data:twistprotect-firewall=='Off'?'On':'Off'}</a>]</small><strong>TwistProtect: Firewall</strong> {data:twistprotect-firewall}</p>
		<p class="notification {data:twistprotect-scanner=='Off'?'warning':'success'}"><small class="float-right">[<a href="{route:registered_uri}?twistprotect-scanner={data:twistprotect-scanner=='Off'?'1':'0'}">Turn {data:twistprotect-scanner=='Off'?'On':'Off'}</a>]</small><strong>TwistProtect: Scanner</strong> {data:twistprotect-scanner}</p>
		<div class="clear"></div>
	</div>
	<div class="boxFooter">

	</div>
</div>

<div class="box box33">
	<div class="boxTitle">
		<h3>Updates</h3>
	</div>
	<div class="boxContent">
		<p>Latest package and framework updates</p>
		<div class="clear"></div>
	</div>
	<div class="boxFooter">
		<a href="#" class="button button-primary">Update All</a>
	</div>
</div>