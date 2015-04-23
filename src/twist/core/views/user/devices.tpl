<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
    <h6 class="no-top-padding">Devices</h6>
    <p>All devices that you are logged into, name your current device or forget old devices.</p>
    <label>Current Device</label>
	<input type="text" name="device_name" value="" placeholder="Home PC" size="40" tabindex="1" required>
    <button type="submit" tabindex="2">Save</button>
    <div class="clear"></div>
</form>
<ul>{data:device_list}</ul>