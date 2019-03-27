<form class="inline foldable large" action="{uri:}" method="post">
	<h2>Add an IP</h2>

	<label for="ip_address">IP Address</label>
	<input type="text" id="ip_address" name="ip_address" value="" placeholder="xxx.xxx.xxx.xxx">
	<p><small>Enter the IP address that you wish to apply add to the whitelist/blocklist</small></p>

	<label for="list_action">API Allowed Request Methods</label>
	<select id="list_action" name="list_action">
		<option value="ban">Block this IP</option>
		<option value="whitelist">Whitelist this IP</option>
	</select>

	<button type="submit" class="fat"><i class="fa fa-save"></i> Submit</button>
</form>