<h1>Scheduled Tasks</h1>
<p>All scheduled tasks setup through TwistPHP are listed below. An indication of when the scheduler was last run can be found below.</p>

<div class="buttonBar">
	<a href="?add=1" class="button button-primary"><i class="fas fa-plus"></i> Add Task</a>
</div>

<div class="box box50">
	<div class="boxTitle">
		<h3>Stats</h3>
	</div>
	<div class="boxContent">
		<dl class="inline">
			<dt>Status</dt><dd>{data:pulse/active==true?'Active':'Inactive'}</dd>
			<dt>Last Run</dt><dd>{data:pulse/date}</dd>
			<dt>Frequency</dt><dd><select name="frequency">
					<option value="1" {data:pulse/frequency==60?'selected':''}>Every minute</option>
					<option value="2" {data:pulse/frequency==120?'selected':''}>Every 2 minutes</option>
					<option value="5" {data:pulse/frequency==300?'selected':''}>Every 5 minutes</option>
					<option value="10" {data:pulse/frequency==600?'selected':''}>Every 10 minutes</option>
					<option value="15" {data:pulse/frequency==900?'selected':''}>Every 15 minutes</option>
					<option value="20" {data:pulse/frequency==1200?'selected':''}>Every 20 minutes</option>
					<option value="30" {data:pulse/frequency==1800?'selected':''}>Every 30 minutes</option>
					<option value="60" {data:pulse/frequency==3600?'selected':''}>Every hour</option>
					<option value="120" {data:pulse/frequency==7200?'selected':''}>Every 2 hours</option>
					<option value="240" {data:pulse/frequency==14400?'selected':''}>Every 4 hours</option>
					<option value="360" {data:pulse/frequency==21600?'selected':''}>Every 6 hours</option>
					<option value="720" {data:pulse/frequency==43200?'selected':''}>Every 12 hours</option>
					<option value="1440 {data:pulse/frequency==86400?'selected':''}">Every 24 hours</option>
				</select></dd>
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