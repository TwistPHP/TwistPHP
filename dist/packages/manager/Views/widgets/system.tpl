<div class="box box33">
	<div class="boxTitle">
		<h3>System</h3>
	</div>
	<div class="boxContent">
		<dl class="inline">
			<dt>Maintenance Mode</dt><dd>{data:maintenance-mode}<small class="float-right">[<a href="{route:registered_uri}?maintenance-mode={data:maintenance-mode=='Off'?'1':'0'}">Turn {data:maintenance-mode=='Off'?'On':'Off'}</a>]&nbsp;</small></dd>
			<dt>Development Mode</dt><dd>{data:development-mode}<small class="float-right">[<a href="{route:registered_uri}?development-mode={data:development-mode=='Off'?'1':'0'}">Turn {data:development-mode=='Off'?'On':'Off'}</a>]&nbsp;</small></dd>
			<dt>Debug Bar</dt><dd>{data:debug-bar}<small class="float-right">[<a href="{route:registered_uri}?debug-bar={data:debug-bar=='Off'?'1':'0'}">Turn {data:debug-bar=='Off'?'On':'Off'}</a>]&nbsp;</small></dd>
			<dt>Server</dt><dd>{data:server}</dd>
			<dt>PHP Version</dt><dd>{data:php_version}</dd>
			<dt>Memory Limit</dt><dd>{bytestosize[data:php_memory]}</dd>
			<dt>Max Upload</dt><dd>{bytestosize[data:php_upload_max]}</dd>
			<dt>Max Execution Time</dt><dd>{data:php_max_execution}s</dd>
		</dl>
		<div class="clear"></div>
	</div>
	<div class="boxFooter">
		TwistPHP v{data:version}
	</div>
</div>