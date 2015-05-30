<div>
	<form action="?save-device={data:device}" method="get">
		<input type="text" name="device-name" value="{data:device_name}" maxlength="32" placeholder="Home PC">
		<button type="submit">Save</button>
		<small>Last Active {prettytime[data:last_login]}</small>
	</form>
</div>