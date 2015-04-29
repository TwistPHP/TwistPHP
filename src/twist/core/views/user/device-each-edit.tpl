<div>
	<form action="?save-device={data:device}" method="post">
		<input type="text" name="device-name" value="{data:device_name}" placeholder="Home PC">
		<button type="submit">Save</button>
		<small>Last Login {prettytime[data:last_login]}</small>
	</form>
</div>