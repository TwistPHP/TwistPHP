<h1>Scheduled Tasks</h1>
<p>All scheduled tasks setup through TwistPHP are listed below. An indication of when the scheduler was last run can be found below.</p>

<div class="buttonBar">
	<a href="?add=1" class="button button-primary"><i class="fas fa-plus"></i> Add Task</a>
</div>

<table>
	<thead>
	<tr>
		<th>Task</th>
		<th>Frequency</th>
		<th>Command</th>
		<th>Tools</th>
	</tr>
	</thead>
	<tbody>
	{data:tasks}
	</tbody>
</table>

<div class="box box50">
	<div class="boxTitle">
		<h3>Stats</h3>
	</div>
	<div class="boxContent">
		<dl class="inline">
			<dt>Status</dt><dd>{data:pulse/status}</dd>
			<dt>Last Run</dt><dd>{data:pulse/date}</dd>
			<dt>Frequency</dt><dd>Every {data:pulse/frequency} seconds</dd>
		</dl>
		<div class="clear"></div>
	</div>
	<div class="boxFooter">

	</div>
</div>

<div class="box box50">
	<div class="boxTitle">
		<h3>Setup</h3>
	</div>
	<div class="boxContent">
		{data:pulse/active==true?'':view:../components/scheduled/setup.tpl}
		<div class="clear"></div>
	</div>
	<div class="boxFooter">

	</div>
</div>

