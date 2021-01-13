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
            <p>The most recent scan of your website using TwistProtect has revealed some points of interest.</p>
            {data:infected_files}
            {data:changed_files}
            {data:new_files}
            <p>You can manage, fix and exclude files from the above results using the TwistPHP Manager interface.</p>
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