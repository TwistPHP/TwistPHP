<dl class="inline">
	<dt>Version Installed</dt>
	<dd>{data:current/version}</dd>
	<dt>Version Available</dt>
	<dd>{data:available/version}</dd>
</dl>
<p class="notice"><strong>Update:</strong> You have an update available</p>
<a href="{route:base_uri}/processupdate?action=update&repo={data:repository}&package={data:name}&package-type=core&package-version={data:available/version}" class="button blue float-right"><strong>Update TwistPHP Core</strong></a>