<div class="twist-debug-box-{data:response=='success'?'green':''}{data:response=='empty'?'yellow':''}{data:response=='fail'?'red':''} twist-debug-query" data-title="{data:type}">
	<pre>{data:query}</pre>
	<div class="twist-debug-more-details">
		<dl>
            <dt>Query type</dt>
            <dd>{data:type}</dd>
			<dt>Run time</dt>
			<dd>{data:time_formatted}</dd>
            <!--<dd>{data:time}</dd>-->
			<dt>Results</dt>
			<dd>{data:num_rows}</dd>
			<dt>Affected rows</dt>
			<dd>{data:affected_rows}</dd>
			<dt>Insert ID</dt>
			<dd>{data:insert_id==0?'<em>none</em>':data:insert_id}</dd>
			<dt>Function called</dt>
			<dd>{data:trace/0/function}()</dd>
			<dd>{data:trace/0/file}</dd>
			<dd>Line {data:trace/0/line}</dd>
            <dt>Error</dt>
            <dd>{data:error==''?'<em>none</em>':data:error}</dd>
		</dl>
	</div>
</div>