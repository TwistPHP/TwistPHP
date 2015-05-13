{resource:twist/debug}
<div id="twist-debug">
	<a href="https://twistphp.com/docs" target="_blank" id="twist-debug-icon" title="TwistPHP documentation"><img src="../src/twist/core/resources/twist/logos/logo-64.png"></a>
	<ul id="twist-debug-blocks">
		<li><a href="#twist-debug-messages"><span id="twist-debug-errors"><i class="fa fa-exclamation-circle"></i>2</span><span id="twist-debug-warnings"><i class="fa fa-exclamation-triangle"></i>7</span><span id="twist-debug-dumps"><i class="fa fa-comment"></i>3</span></a></li><!--
		--><li><a href="#twist-debug-timeline"><i class="fa fa-fw fa-lg fa-clock-o"></i>1.63s</a></li><!--
		--><li><a href="#twist-debug-memory"><i class="fa fa-fw fa-lg fa-line-chart"></i>12.64Mb</a></li><!--
		--><li><a href="#twist-debug-database"><i class="fa fa-fw fa-lg fa-database"></i>439</a></li><!--
		--><li><a href="#twist-debug-route"><strong>GET</strong> /shop/favourites</a></li>
	</ul>
</div>
<div id="twist-debug-details">
	<a href="#close-twist-debug-details"></a>
	<div id="twist-debug-messages">
		<h6>Messages</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				{data:errors}
			</div>
		</div>
	</div>
	<div id="twist-debug-timeline">
		<h6>Timeline</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				<div id="twist-debug-timeline-chart">
					<ul>
						{data:timeline}
					</ul>
				</div>
				<p>Execution Time: {data:execution_time}</p>
			</div>
			<div class="twist-debug-column-50"></div>
			<div class="twist-debug-column-50"></div>
		</div>
	</div>
	<div id="twist-debug-memory">
		<h6>Memory</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-50"></div>
			<div class="twist-debug-column-50"></div>
		</div>
	</div>
	<div id="twist-debug-database">
		<h6>Database Queries</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				{data:database}
			</div>
		</div>
	</div>
	<div id="twist-debug-route">
		<h6>Routes</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-50">
				<ul>
					<li>Registered Routes</li>
					<li>Current Route</li>
					<li>{data:route_current}</li>
				</ul>
			</div>
			<div class="twist-debug-column-50">
				<ul>
					<li>$_GET</li>
					<li>{data:get}</li>
					<li>$_POST</li>
					<li>{data:post}</li>
				</ul>
			</div>
		</div>
	</div>
</div>