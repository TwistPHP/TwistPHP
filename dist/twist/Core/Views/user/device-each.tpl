<tr>
    <td>
	    <i class="fa {data:fa-os-icon}" title="{data:os}"></i> <small>{data:version}</small><br>
	    <i class="fa {data:fa-browser-icon}" title="{data:browser}"></i> <small>{data:browser}</small>
    </td>
    <td>
	    <a href="?edit-device={data:device}"><strong>{data:device_name==''?'[Untitled]':data:device_name}{data:current==true?' (current)':''}</strong></a><br>
	    <small>Last Active: {prettytime[data:last_login]}</small>
    </td>
    <td>
        <a href="?forget-device={data:device}" class="button">forget</a>
    </td>
</tr>