<h4 class="no-top-padding">My Devices</h4>
<p>All devices you have used to login into {setting:SITE_NAME}, these devices are still logged into your account. You can forget a device, next time you use the device you will be prompted to login again.</p>
<p><strong>Notifications:</strong>
    <span {data:notifications==1?'':'style="display: none;"'}>Notify me when a new device is used to login to my account. (<a href="?notifications=off">Disable Notifications</a>)</span>
    <span {data:notifications==1?'style="display: none;"':''}>Do not notify me when a new device is used to login to my account. (<a href="?notifications=on">Enable Notifications</a>)</span>
</p>
<table>
    <thead>
        <tr>
	        <th colspan="3">Your Devices</th>
        </tr>
    </thead>
    <tbody>
        {data:device_list}
    </tbody>
</table>