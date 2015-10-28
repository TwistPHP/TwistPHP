<form action="{uri:./device-manager}" method="post">
    <tr>
        <td>
            <i class="fa {data:fa-os-icon}" title="{data:os}"></i> <small>{data:version}</small><br>
            <i class="fa {data:fa-browser-icon}" title="{data:browser}"></i> <small>{data:browser}</small>
        <td>
            <input type="text" name="device-name" value="{data:device_name}" maxlength="32" placeholder="Home PC" autofocus><br>
            <small>Last Active {prettytime[data:last_login]}</small>
        </td>
        <td>
            <input type="hidden" name="save-device" value="{data:device}">
            <button class="button" type="submit">Save</button>
        </td>
    </tr>
</form>