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
                    <p>This email is to notify you that your account password for <strong>{data:site_name}</strong> has been changed.</p>
                    <p>If you believe this has been done in error, please contact the site administrator.</p>
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