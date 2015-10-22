<form action="{uri:./device-manager}" method="post">
    <tr>
        <td>IMG</td>
        <td>
            <input type="text" name="device-name" value="{data:device_name}" maxlength="32" placeholder="Home PC" autofocus><br>
            <span>Last Active {prettytime[data:last_login]}</span><br>
            <small>{data:os} - {data:browser}</small>
        </td>
        <td>
            <input type="hidden" name="save-device" value="{data:device}">
            <button class="button" type="submit">Save</button>
        </td>
    </tr>
</form>