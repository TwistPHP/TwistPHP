<h1>Scheduled Tasks</h1>
<form action="" method="post" class="inline foldable large">

	<div class="box box50">
		<div class="boxTitle">
			<h3>Log</h3>
		</div>
		<div class="boxContent">

			<pre name="log">{data:log}</pre>

			<div class="clear"></div>
		</div>
		<div class="boxFooter">

		</div>
	</div>

	<div class="box box50">
		<div class="boxTitle">
			<h3>Task Information</h3>
		</div>
		<div class="boxContent">

			<dl class="inline">
				<dt>Description</dt><dd>{data:description}</dd>
				<dt>Frequency</dt><dd>{data:frequency}</dd>
				<dt>Package</dt><dd>{data:package_slug}</dd>
				<dt>Status</dt><dd>{data:status}</dd>
				<dt>Runtime</dt><dd>{data:runtime}</dd>
				<dt>Last Run</dt><dd>{data:last_run}</dd>
			</dl>

			<div class="clear"></div>
		</div>
		<div class="boxFooter">

		</div>
	</div>

</form>