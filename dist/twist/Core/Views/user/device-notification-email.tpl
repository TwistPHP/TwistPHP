<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{data:subject}</title>
</head>
<body>
<table width="600" style="margin: 20px auto;">
    <tr>
        <td>
            <p>Hi {data:firstname},</p>
        </td>
    </tr>
    <tr>
        <td>
            This email is to notify you that a new device has been used to access your account on "Twist Test".

            <p>This email is to notify you that a new device has been used to access your account on <strong>{data:site_name}</strong>.</p>
            <p><strong>Device</strong> {data:device}<br><strong>Browser</strong> {data:browser}</p>
            <p>If this activity sounds suspicious, you can revoke access for this device by visiting the "Device manager" page on <strong>Twist Test</strong> and clicking the "forget" button.
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <p>-- </p>
            <p><strong>{data:site_name}</strong>
                <br /><em><a href="{data:url}">{data:url}</a></em></p>
        </td>
    </tr>
</table>
</body>
</html>