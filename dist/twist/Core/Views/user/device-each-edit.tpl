<form action="{uri:./device-manager}" method="post">
    <tr>
        <td>
            <i class="fa {data:os/fa-icon}" title="{data:os/title}"></i> <small>{data:os/version}</small><br>
            <i class="fa {data:browser/fa-icon}" title="{data:browser/title}"></i> <small>{data:browser/title}</small>
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