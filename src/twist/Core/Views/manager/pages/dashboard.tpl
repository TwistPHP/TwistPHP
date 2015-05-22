<div class="grid-66 tablet-grid-66 mobile-grid-100">
	<h2 class="no-top-padding">Dashboard</h2>
	{data:update-information}
	<div class="clear"></div>
</div>
<div class="grid-33 tablet-grid-33 mobile-grid-100">
	<h3>Status</h3>
	<p class="{data:maintenance-mode=='Off'?'success':'error'}"><small class="float-right">[<a href="{route:base_uri}/updatesetting?setting=MAINTENANCE_MODE&setting_value={data:maintenance-mode=='Off'?'1':'0'}">Turn {data:maintenance-mode=='Off'?'On':'Off'}</a>]</small><strong>Maintenance Mode:</strong> {data:maintenance-mode}</p>
	<p class="{data:development-mode=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:base_uri}/updatesetting?setting=DEVELOPMENT_MODE&setting_value={data:development-mode=='Off'?'1':'0'}">Turn {data:development-mode=='Off'?'On':'Off'}</a>]</small><strong>Development Mode:</strong> {data:development-mode}</p>
	<p class="{data:database-debug=='Off'?'success':'warning'}"><small class="float-right">[<a href="{route:base_uri}/updatesetting?setting=DATABASE_DEBUG&setting_value={data:database-debug=='Off'?'1':'0'}">Turn {data:database-debug=='Off'?'On':'Off'}</a>]</small><strong>Database Debug:</strong> {data:database-debug}</p>
	<p class="{data:release-channel=='Stable'?'success':'warning'}"><small class="float-right">[<a href="{route:base_uri}/updatesetting?setting=RELEASE_CHANNEL&setting_value={data:release-channel=='Stable'?'Nightly':'Stable'}">Use {data:release-channel=='Stable'?'Nightly':'Stable'}</a>]</small><strong>Release Channel:</strong> {data:release-channel}</p>
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