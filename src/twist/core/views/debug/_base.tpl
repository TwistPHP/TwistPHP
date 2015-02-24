<link href="/twist/core/resources/debug/debug.css" type="text/css" rel="stylesheet">
<script type="text/javascript">

	if(window.jQuery === undefined){
		var headTag = document.getElementsByTagName("head")[0];
		var jqTag = document.createElement('script');
		jqTag.type = 'text/javascript';
		jqTag.src = '/twist/core/resources/jquery/jquery-2.1.3.min.js';
		headTag.appendChild(jqTag);
	}

	function toggleDebug(){
		$('#twistDebug').toggleClass('minimize');
	}
	function twistDebugTab(strTabID,resButton){
		$('.twistTabs >div').removeClass('hide').addClass('hide');
		$('#'+strTabID).toggleClass('hide');

		$('.twistDebugHeader li').removeClass('current');
		$(resButton).addClass('current');
	}
</script>

<div id="twistDebug" class="minimize">
	<div class="twistDebugHeader">
		<a href="https://twistphp.com/docs" target="_blank"><img src="/twist/core/resources/logos/logo-32.png" width="20" title="TwistPHP" alt="TwistPHP"> Debug Bar</a>
		<ul>
			<li onclick="twistDebugTab('twistTabError',this);" class="current">PHP Errors</li><!--
		--><li onclick="twistDebugTab('twistTabDatabase',this);">Query Log</li><!--
		--><li onclick="twistDebugTab('twistTabDebug',this);">Debug/Help</li><!--
		--><li onclick="twistDebugTab('twistTabRoutes',this);">Routes</li><!--
		--><li onclick="twistDebugTab('twistTabViews',this);">Views</li><!--
		--><li onclick="twistDebugTab('twistTabStats',this);">Stats</li><!--
		--><li onclick="twistDebugTab('twistTabCache',this);">Cache</li>
		</ul>
		<a class="toggleButton" href="#" onclick="toggleDebug();">X</a>
	</div>
	<div class="twistTabs">

		<div id="twistTabError">
			<div class="twistTabTitle"><strong>PHP Errors</strong>All the PHP errors that occurred during this page load</div>
			{data:errors}
		</div>

		<div id="twistTabDatabase" class="hide">
			<div class="twistTabTitle"><strong>Database Query Log</strong>All the database queries that where run during this page load</div>
			<div class="databaseQuery">
				<p><strong>*PASS*</strong> SELECT * FROM `database`.`table` WHERE `field` = '1';<br><span>[Run Time: 0.0243]</span></p>
				<code>Called from [Line: 402] /my/file.php</code>
			</div>
			{data:database}
		</div>

		<div id="twistTabDebug" class="hide">
			<div class="twistTabTitle"><strong>Debug Log</strong>Debug messages that have been output form this page</div>
			<ul>
				<li>Messages on this page</li>
				<li>Messages on other pages</li>
				<li>Todos in the site code</li>
				<li>Deprecated functions used</li>
			</ul>
		</div>

		<div id="twistTabRoutes" class="hide">
			<div class="twistTabTitle"><strong>Routes</strong>All the routes registered in the system</div>
			<strong>Current Route</strong><br>
			<pre>
				{data:route_current}
			</pre>
			<strong>Registered Routes</strong><br>
			<table>
				<thead>
				<tr>
					<th>URI</th>
					<th>Method</th>
					<th>Item</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<th>/</th>
					<td>GET</td>
					<td>/%</td>
				</tr>
				<tr>
					<th>/test</th>
					<td>ANY</td>
					<td>/test/{id}</td>
				</tr>
				</tbody>
			</table>
		</div>

		<div id="twistTabViews" class="hide">
			<div class="twistTabTitle"><strong>Views</strong>All the templates and elements used in this page load</div>
			<table>
				<thead>
				<tr>
					<th>Instance</th>
					<th>File</th>
					<th>Tags</th>
				</tr>
				</thead>
				<tbody>
				{data:templates}
				</tbody>
			</table>
		</div>

		<div id="twistTabStats" class="hide">
			<div class="twistTabTitle"><strong>Performance Stats</strong>Page load time stats, memory usage and other useful information</div>
			{data:stats}
			<ul>
				<li>Page Process Time</li>
				<li>Slow Pages</li>
				<li>Recent Exceptions</li>
				<li>Recent Errors (other pages)</li>
			</ul>
		</div>

		<div id="twistTabCache" class="hide">
			<div class="twistTabTitle"><strong>Cache Manager</strong>Manage the server side cache files</div>
			<table>
				<thead>
				<tr>
					<th>Instance</th>
					<th>Key</th>
					<th>Size</th>
					<th>Expiry</th>
					<th>Tools</th>
				</tr>
				</thead>
				<tbody>
				{data:cache}
				</tbody>
			</table>
		</div>

	</div>
</div>