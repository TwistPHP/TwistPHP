<h1>Scheduled Tasks</h1>
<form action="" method="post" class="inline foldable large">
	<div class="box box50">
		<div class="boxTitle">
			<h3>Edit Task</h3>
		</div>
		<div class="boxContent">

			<label>Description</label><input type="text" name="description" value="{data:description}" placeholder="">
			<label>Frequency</label><select name="frequency">
				<option value="1" {data:frequency==1?'selected':''}>Every minute</option>
				<option value="2" {data:frequency==2?'selected':''}>Every 2 minutes</option>
				<option value="5" {data:frequency==5?'selected':''}>Every 5 minutes</option>
				<option value="10" {data:frequency==10?'selected':''}>Every 10 minutes</option>
				<option value="15" {data:frequency==15?'selected':''}>Every 15 minutes</option>
				<option value="20" {data:frequency==20?'selected':''}>Every 20 minutes</option>
				<option value="30" {data:frequency==30?'selected':''}>Every 30 minutes</option>
				<option value="60" {data:frequency==60?'selected':''}>Every hour</option>
				<option value="120" {data:frequency==120?'selected':''}>Every 2 hours</option>
				<option value="240" {data:frequency==240?'selected':''}>Every 4 hours</option>
				<option value="360" {data:frequency==360?'selected':''}>Every 6 hours</option>
				<option value="720" {data:frequency==720?'selected':''}>Every 12 hours</option>
				<option value="1440 {data:frequency==1440?'selected':''}">Every 24 hours</option>
			</select>
			<label>Command</label><input type="text" name="command" value="{data:command}" placeholder="">
			<label>Keep History</label><input type="text" name="history" value="{data:history}" placeholder="">
			<label>Email</label><input type="text" name="email" value="{data:email}" placeholder="">
			<label>Enabled</label><select name="enabled">
				<option value="1" {data:enabled=='1'?'selected':''}>Enabled</option>
				<option value="0" {data:enabled=='0'?'selected':''}>Disabled</option>
			</select>

			<input type="hidden" name="task" value="{get:task}">
			<div class="clear"></div>
		</div>
		<div class="boxFooter">
			<button type="submit" class="button button-primary">Save Changes</button>
		</div>
	</div>
	<div class="box box50">
		<div class="boxTitle">
			<h3>Task Information</h3>
		</div>
		<div class="boxContent">

			<dl class="inline">
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