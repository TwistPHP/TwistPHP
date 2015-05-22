<div class="twist-debug-box-{data:response=='success'?'green':''}{data:response=='empty'?'yellow':''}{data:response=='fail'?'red':''} twist-debug-query" data-title="{data:type}">
	<pre>{data:query}</pre>
	<div class="twist-debug-more-details">
		<dl>
			<dt>Run Time</dt>
			<dd>{data:time}</dd>
			<dt>Query Type</dt>
			<dd>{data:type}</dd>
			<dt>Error</dt>
			<dd>{data:error}</dd>
			<dt>Results</dt>
			<dd>{data:num_rows}</dd>
			<dt>Affected Rows</dt>
			<dd>{data:affected_rows}</dd>
			<dt>Insert ID</dt>
			<dd>{data:insert_id}</dd>
			<dt>Called {data:trace/0/function}()</dt>
			<dd>Line {data:trace/0/line} of {data:trace/0/file}</dd>
		</dl>
	</div>
</div>