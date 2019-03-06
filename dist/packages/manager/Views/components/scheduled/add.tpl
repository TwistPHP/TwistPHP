<div class="grid-100 tablet-grid-100 mobile-grid-100">
	<h2 class="no-top-padding">Scheduled Tasks: Add</h2>
	<form action="" method="post" class="inline foldable large">

		<label>Description</label><input type="text" name="description" value="{post:description==undefined?'':post:description}" placeholder="Do something every X minutes">
		<label>Frequency</label><select name="frequency">
			<option value="1" {post:frequency==undefined||post:frequency==1?'selected':''}>Every minute</option>
			<option value="2" {post:frequency==2?'selected':''}>Every 2 minutes</option>
			<option value="5" {post:frequency==5?'selected':''}>Every 5 minutes</option>
			<option value="10" {post:frequency==10?'selected':''}>Every 10 minutes</option>
			<option value="15" {post:frequency==15?'selected':''}>Every 15 minutes</option>
			<option value="20" {post:frequency==20?'selected':''}>Every 20 minutes</option>
			<option value="30" {post:frequency==30?'selected':''}>Every 30 minutes</option>
			<option value="60" {post:frequency==60?'selected':''}>Every hour</option>
			<option value="120" {post:frequency==120?'selected':''}>Every 2 hours</option>
			<option value="240" {post:frequency==240?'selected':''}>Every 4 hours</option>
			<option value="360" {post:frequency==360?'selected':''}>Every 6 hours</option>
			<option value="720" {post:frequency==720?'selected':''}>Every 12 hours</option>
			<option value="1440 {post:frequency==1440?'selected':''}">Every 24 hours</option>
		</select>
		<label>Command</label><select name="command">
			{data:commands}
		</select>
		<label>Keep History</label><input type="text" name="history" value="{post:history==undefined?'0':post:history}" placeholder="0">
		<label>Email</label><input type="text" name="email" value="{post:email==undefined?'':post:email}" placeholder="joe@bloggs.com, Leave blank for no notifications">
		<label>Enabled</label><select name="enabled">
			<option value="1" {post:enabled==undefined||post:enabled=='1'?'selected':''}>Enabled</option>
			<option value="0" {post:enabled=='0'?'selected':''}>Disabled</option>
		</select>

		<input type="hidden" name="create_task" value="create_task">
		<button type="submit">Create Task</button>
	</form>
</div>