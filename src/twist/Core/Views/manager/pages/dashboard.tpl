<div class="grid-66 tablet-grid-66 mobile-grid-100">
	<h2 class="no-top-padding">Dashboard</h2>
	<dl class="inline">
        <dt>Version Installed</dt><dd>3</dd>
    </dl>
	<div class="clear"></div>
</div>
<div class="grid-33 tablet-grid-33 mobile-grid-100">
	<h3>Status</h3>
	<p class="{data:maintenance-mode=='Off'?'success':'error'}"><small class="float-right">[<a href="{route:registered_uri}/update-setting?setting=MAINTENANCE_MODE&setting_value={data:maintenance-mode=='Off'?'1':'0'}">Turn {data:maintenance-mode=='Off'?'On':'Off'}</a>]</small><strong>Maintenance Mode:</strong> {data:maintenance-mode}</p>
	<p class="{data:development-mode=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}/update-setting?setting=DEVELOPMENT_MODE&setting_value={data:development-mode=='Off'?'1':'0'}">Turn {data:development-mode=='Off'?'On':'Off'}</a>]</small><strong>Development Mode:</strong> {data:development-mode}</p>
	<p class="{data:debug-bar=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}/update-setting?setting=DEVELOPMENT_DEBUG_BAR&setting_value={data:debug-bar=='Off'?'1':'0'}">Turn {data:debug-bar=='Off'?'On':'Off'}</a>]</small><strong>Debug Bar:</strong> {data:debug-bar}</p>
	<p class="{data:data-caching=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:registered_uri}/update-setting?setting=CACHE_ENABLED&setting_value={data:data-caching=='Off'?'1':'0'}">Turn {data:data-caching=='Off'?'On':'Off'}</a>]</small><strong>Data Caching:</strong> {data:data-caching}</p>
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