<tr>
    <td>IMG</td>
    <td>
        <strong>{data:device_name==''?'Untitled':data:device_name}{data:current==true?' (current)':''}</strong><br>
        <span>Last Active {prettytime[data:last_login]}</span><br>
        <small>{data:os} - {data:browser}</small>
    </td>
    <td>
        <a href="?forget-device={data:device}" class="button">forget</a>
        <a href="?edit-device={data:device}" class="button">edit</a>
    </td>
</tr>