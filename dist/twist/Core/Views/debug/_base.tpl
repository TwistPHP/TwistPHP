<div id="twist-debug">
	<a href="https://twistphp.com/docs" target="_blank" id="twist-debug-icon" title="TwistPHP documentation"><img src="{resource:core-uri}twist/logos/logo-64.png" alt="TwistPHP documentation"></a>
	<ul id="twist-debug-blocks">
		<li><a href="#twist-debug-messages"><span id="twist-debug-errors"{data:warning_count==0?' class="twist-debug-hidden"':''}><i class="fa fa-exclamation-circle"></i>{data:warning_count}</span><span id="twist-debug-warnings"{data:notice_count==0?' class="twist-debug-hidden"':''}><i class="fa fa-exclamation-triangle"></i>{data:notice_count}</span><span id="twist-debug-dumps"{data:other_count==0?' class="twist-debug-hidden"':''}><i class="fa fa-comment"></i>{data:other_count}</span></a></li><!--
		--><li><a href="#twist-debug-timeline"><i class="fa fa-fw fa-lg fa-clock-o"></i>{data:execution_time_formatted}<i class="fa fa-fw fa-lg fa-line-chart"></i>{bytestosize[data:memory/peak]}</a></li><!--
		--><li><a href="#twist-debug-database"><i class="fa fa-fw fa-lg fa-database"></i>{data:database_query_count}</a></li><!--
		--><li><a href="#twist-debug-route"><strong>{server:REQUEST_METHOD}</strong> {route:uri}</a></li>
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
                <table id="twist-debug-timeline-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Time</th>
                            <!--<th>Timecode</th>-->
                            <th>Memory</th>
                            <!--<th>Memory (bytes)</th>-->
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        {data:timeline_table}
                    </tbody>
                </table>
                <div id="twist-debug-timeline-chart">
                    <ul>
                        {data:timeline}
                    </ul>
                </div>
            </div>
            <div id="twist-debug-timeline-memory">
                <div class="twist-debug-column-25">
                    <dl>
                        <dt>Start Memory</dt>
                        <dd>{bytestosize[data:memory/start]}</dd>
                    </dl>
                </div>
                <div class="twist-debug-column-25">
                    <dl>
                        <dt>End Memory</dt>
                        <dd>{bytestosize[data:memory/end]}</dd>
                    </dl>
                </div>
                <div class="twist-debug-column-25">
                    <dl>
                        <dt>Peak Memory</dt>
                        <dd>{bytestosize[data:memory/peak]}</dd>
                    </dl>
                </div>
                <div class="twist-debug-column-25">
                    <dl>
                        <dt>Memory Limit</dt>
                        <dd>{data:memory/limit}</dd>
                    </dl>
			    </div>
			</div>
		</div>
	</div>
	<div id="twist-debug-database">
		<h6>Database Queries</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
				{data:database_queries}
			</div>
		</div>
	</div>
	<div id="twist-debug-route">
		<h6>Route</h6>
		<div class="twist-debug-column-wrapper">
			<div class="twist-debug-column-100">
                <h6>Registered Routes</h6>
				<table>
					<thead>
                        <tr>
                            <th>URI</th>
                            <th>Method</th>
                            <th>Type</th>
                            <th>Call</th>
                            <th>Restriction</th>
                        </tr>
					</thead>
					<tbody>
					    {data:routes}
					</tbody>
				</table>
				<h6>Current Route</h6>
				<dl>{data:current_route}</dl>
				<div{data:get==''?' class="twist-debug-hidden"':''}>
					<h6>$_GET Params</h6>
					<dl>{data:get}</dl>
				</div>
				<div{data:post==''?' class="twist-debug-hidden"':''}>
					<h6>$_POST Params</h6>
					<dl>{data:post}</dl>
				</div>
				<div{data:php_session==''?' class="twist-debug-hidden"':''}>
					<h6>PHP $_SESSION</h6>
					<dl>{data:php_session}</dl>
				</div>
				<div{data:twist_session==''?' class="twist-debug-hidden"':''}>
					<h6>Twist $_SESSION</h6>
					<dl>{data:twist_session}</dl>
				</div>
				<div{data:cookie==''?' class="twist-debug-hidden"':''}>
					<h6>Cookies</h6>
					<dl>{data:cookie}</dl>
				</div>
				<h6>Request Headers</h6>
				<dl>{data:request_headers}</dl>
				<h6>Server Information</h6>
				<dl>{data:server}</dl>
			</div>
		</div>
	</div>
</div>
{resource:font-awesome}
{resource:twist/debug}