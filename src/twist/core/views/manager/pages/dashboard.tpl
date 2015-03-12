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
	<h3>Site Summary</h3>
	<!--<dl class="inline">
		<dt>PHP</dt>
		<dd>6 files, 2,380 lines, 2,347,155 characters</dd>
		<dt>HTML</dt>
		<dd>6 files, 2,380 lines, 2,347,155 characters</dd>
		<dt>CSS</dt>
		<dd>6 files, 2,380 lines, 2,347,155 characters</dd>
		<dt>JavaScript</dt>
		<dd>6 files, 2,380 lines, 2,347,155 characters</dd>
		<dt>Other</dt>
		<dd>6 files, 2,380 lines, 2,347,155 characters</dd>
	</dl>-->
	<table>
		<thead>
		<tr>
			<th>File Type</th>
			<th>File Extensions</th>
			<th>Files</th>
			<th>Lines</th>
			<th>Characters</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th colspan="2">Total</th>
			<td>6</td>
			<td>2,342</td>
			<td>983,357</td>
		</tr>
		</tfoot>
		<tbody>
		<tr>
			<th>PHP</th>
			<td>.php, .php4, .php5</td>
			<td>6</td>
			<td>2,342</td>
			<td>983,357</td>
		</tr>
		<tr>
			<th>HTML</th>
			<td>.html, .htm, .tpl</td>
			<td>6</td>
			<td>2,342</td>
			<td>983,357</td>
		</tr>
		<tr>
			<th>CSS</th>
			<td>.css, .scss, .sass</td>
			<td>6</td>
			<td>2,342</td>
			<td>983,357</td>
		</tr>
		<tr>
			<th>JavaScript</th>
			<td>.js</td>
			<td>6</td>
			<td>2,342</td>
			<td>983,357</td>
		</tr>
		<tr>
			<th colspan="2">Other</th>
			<td>6</td>
			<td>2,342</td>
			<td>983,357</td>
		</tr>
		</tbody>
	</table>
</div>
<div class="clear"></div>