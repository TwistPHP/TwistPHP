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
                    <p>Welcome! An account has been created for you on <strong>{data:site_name}</strong> (<a href="{data:url}">{data:url}</a>).</p>
                    {data:verification}
                    <p>The email address you can use to log in is: <strong>{data:email}</strong>
                        <br />The password for your account is: <strong>{data:password}</strong></p>
                    <p>You may be prompted to enter a more memorable password after you login.</p>
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