<h1>Dashboard</h1>

<div class="box box33">
	<div class="boxTitle">
		<h3>System</h3>
	</div>
	<div class="boxContent">
		<dl class="inline">
			<dt>TwistPHP Version</dt><dd>{data:version}</dd>
			<dt>Status</dt><dd>{data:version_status}</dd>
		</dl>
		<div class="clear"></div>
	</div>
	<div class="boxFooter">

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