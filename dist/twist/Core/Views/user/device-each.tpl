<tr>
    <td>
	    <i class="fa fa-chrome"></i>
	    <i class="fa fa-firefox"></i>
	    <i class="fa fa-safari"></i>
	    <i class="fa fa-internet-explorer"></i>
	    <i class="fa fa-opera"></i>
	    <br>
	    <i class="fa fa-windows"></i>
	    <i class="fa fa-apple"></i>
	    <i class="fa fa-android"></i>
	    <i class="fa fa-linux"></i>
    </td>
    <td>
	    <a href="?edit-device={data:device}"><strong>{data:device_name==''?'[Untitled]':data:device_name}{data:current==true?' (current)':''}</strong></a><br>
        <span>{data:os} - {data:browser}</span><br>
	    <small>Last Active: {prettytime[data:last_login]}</small>
    </td>
    <td>
        <a href="?forget-device={data:device}" class="button">forget</a>
    </td>
</tr>