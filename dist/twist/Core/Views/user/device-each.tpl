<tr>
    <td>
        <i class="fa {data:os/fa-icon}" title="{data:os/title}"></i> <small>{data:os/version}</small><br>
        <i class="fa {data:browser/fa-icon}" title="{data:browser/title}"></i> <small>{data:browser/title}</small>
    </td>
    <td>
	    <a href="?edit-device={data:device}"><strong>{data:device_name==''?'[Untitled]':data:device_name}{data:current==true?' (current)':''}</strong></a><br>
	    <small>Last Active: {prettytime[data:last_validated]}</small>
    </td>
    <td>
        <a href="?forget-device={data:device}" class="button">forget</a>
    </td>
</tr>