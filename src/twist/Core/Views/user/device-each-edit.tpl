<div>
	<form action="{uri:./device-manager}" method="post">
		<input type="text" name="device-name" value="{data:device_name}" maxlength="32" placeholder="Home PC" autofocus>
		<input type="hidden" name="save-device" value="{data:device}">
		<button type="submit">Save</button>
		<small>Last Active {prettytime[data:last_login]}</small>
	</form>
</div>