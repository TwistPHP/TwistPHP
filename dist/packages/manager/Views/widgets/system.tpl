<div class="box box33">
	<div class="boxTitle">
		<h3>System</h3>
	</div>
	<div class="boxContent">
		<dl class="inline">
			<dt>Server</dt><dd>{data:server}</dd>
			<dt>PHP Version</dt><dd>{data:php_version}</dd>
			<dt>Memory Limit</dt><dd>{bytestosize[data:php_memory]}</dd>
			<dt>Max Upload</dt><dd>{bytestosize[data:php_upload_max]}</dd>
			<dt>Max Execution Time</dt><dd>{data:php_max_execution}s</dd>
		</dl>
		<h3>Dev Tools</h3>
		<dl>
			<dt>Maintenance Mode</dt><dd><small><input type="checkbox" value="1" name="maintenance-mode"{data:maintenance-mode=='Off'?'':' checked'}"></small></dd>
			<dt>Development Mode</dt><dd><small><input type="checkbox" value="1" name="development-mode"{data:development-mode=='Off'?'':' checked'}"></small></dd>
			<dt>Debug Bar</dt><dd><small><input type="checkbox" value="1" name="debug-mode"{data:debug-bar=='Off'?'':' checked'}"></small></dd>
		</dl>
		<div class="clear"></div>
	</div>
	<div class="boxFooter">
		TwistPHP v{data:version}
	</div>
</div>