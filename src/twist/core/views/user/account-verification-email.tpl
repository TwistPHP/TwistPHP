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
                    <p>You must verify the email address registered to your <strong>{data:site_name}</strong> account before you can login.</p>
                    <p>To verify your account, please <a href="{data:verification_link}">click here</a>.</p>
                    <p>If you have a problem with this link, you can copy the below link into your browser and proceed to login.
                        <br /><a href="{data:verification_link}">{data:verification_link}</a></p>
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