<h2 class="no-top-padding">Package Information</h2>
<p>View all information relating any particular package that is installed or available through the TwistPHP Manager.</p>
<dl class="inline">
	<dt>Name</dt>
	<dd>{data:name}</dd>
	<dt>Description</dt>
	<dd>{data:description}</dd>
	<dt>Type</dt>
	<dd>{data:type}</dd>
	<dt>Repository</dt>
	<dd><a href="{data:homepage}">{data:homepage}</a> [{data:repo}]</dd>
	<dt>Author</dt>
	<dd>{data:author}</dd>
	<dt>Licenced</dt>
	<dd>{data:licenced=='1'?'Yes':'No'}</dd>
	<dt>Installed Version</dt>
	<dd{data:installed==0?' style="display:none;"':''}>{data:current/version} | {data:current/channel} | {prettytime[data:current/release]}</dd>
	<dd{data:installed==0?' style="display:inline-block;"':''}>-</dd>
	<dt>Available Version</dt>
	<dd{data:available/version==undefined?' style="display:none;"':''}>{data:available/version} | {data:available/channel} | {prettytime[data:available/release]}</dd>
	<dd{data:available/version==undefined?' style="inline-block;"':''}>-</dd>
</dl>