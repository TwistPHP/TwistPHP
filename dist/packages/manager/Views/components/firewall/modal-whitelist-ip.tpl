<h2>Whitelist and IP</h2>
<p>Enter the IP address that you wish to apply add to the whitelist, give a reason why this IP is being whitelisted.</p>
{messages:all}
<form action="firewallWhitelistIP" class="ajaxFrom" method="post" id="whitelistIPForm">

	<label for="ip_address">IP Address</label>
	<input type="text" id="ip_address" name="ip_address" value="" placeholder="xxx.xxx.xxx.xxx">

	<label for="reason">Reason</label>
	<input type="text" id="reason" name="reason" value="" placeholder="Admin's Office IP">

	<button type="submit" class="button button-primary"><i class="fa fa-save"></i> Submit</button>
</form>