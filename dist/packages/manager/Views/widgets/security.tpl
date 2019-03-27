<div class="box box33">
	<div class="boxTitle">
		<h3>Security</h3>
	</div>
	<div class="boxContent">

		<div class="proC p{data:score}" style="margin:20px auto;">
			<span>{data:score}% {data:grade}</span>
			<div class="lhClip">
				<div class="f50Bar"></div>
				<div class="vBar"></div>
			</div>
		</div>

		<dl class="small">
			<dt>Pulse</dt><dd>{data:pulse/status} ({data:pulse/frequency})</dd>
			<dt>Firewall</dt><dd>{data:twistprotect-firewall=='Off'?'Disabled':'Enabled'}</dd>
			<dt>Scanner</dt><dd>{data:twistprotect-scanner=='Off'?'Disabled':'Enabled'}</dd>
			<dt>Last Scan</dt><dd>{prettytime[data:scanner/scanned]} [Pass]</dd>
			<dt>Security</dt><dd>High</dd>
			<dt>Core</dt><dd>Up-to-date</dd>
			<dt>Packages</dt><dd>Up-to-date</dd>
		</dl>

	</div>
	<div class="boxFooter">
		TwistProtect
	</div>
</div>