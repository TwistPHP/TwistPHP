<div class="twist-debug-query-{data:status==true&&data:result>0?'success':''}{data:status==true&&data:result==0?'empty':''}{data:status==false?'fail':''}">
	<pre>{data:query}</pre>
	<span>
		Run Time: {data:time}<br>{data:error==''?'':data:error}<br>Results: {data:result}<br>Called {data:trace/0/function}() on line {data:trace/0/line} of {data:trace/0/file}
	</span>
</div>