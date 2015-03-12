<h6>Devices</h6>
<p>All devices that you are logged into, name your current device or forget old devices.</p>
<form class="twist-user-login" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<label class="twist-user-login-email">Current Device</label>
	<input type="text" name="device_name" value="" placeholder="Home PC" size="40" tabindex="1" required>
	<input type="submit" class="twist-user-login-submit" value="Save" tabindex="4">
</form>
<ul class="inline">{data:device_list}</ul>