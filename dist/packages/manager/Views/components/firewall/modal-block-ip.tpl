<h2>Ban an IP</h2>
<p>Enter the IP address that you wish to apply add to the blocklist. Also give a reason for this IP to be blocked, the user will see this reason on the 403 page.</p>
<form action="{uri:}" method="post">

	<label for="ip_address">IP Address</label>
	<input type="text" id="ip_address" name="ip_address" value="" placeholder="xxx.xxx.xxx.xxx">

	<label for="reason">Reason</label>
	<input type="text" id="reason" name="reason" value="" placeholder="User has been snooping around">

	<label for="full_ban">Ban Type</label>
	<select id="full_ban" name="full_ban">
		<option value="0">Temporary (Soft Ban)</option>
		<option value="1">Permanent (Full Ban)</option>
	</select>

	<button type="submit" class="fat"><i class="fa fa-save"></i> Submit</button>
</form>