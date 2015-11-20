<form class="inline foldable" action="interfaces" method="post" autocomplete="off">
    <h2>Root User</h2>
    <p>Create a root user account that will have full access to all interfaces and user systems.</p>
    {data:error_message}

    <label>Firstname</label>
    <input type="text" name="firstname" maxlength="128" placeholder="John" value="{session:twist-setup/user/details/firstname}" autocomplete="off" autocapitalize="on" autofocus>

    <label>Lastname</label>
    <input type="text" name="lastname" maxlength="128" placeholder="Smith" value="{session:twist-setup/user/details/lastname}" autocomplete="off" autocapitalize="on">

    <label>Email</label>
    <input type="email" maxlength="255" name="email" placeholder="john.smith@{session:twist-setup/settings/details/site_host}" value="{session:twist-setup/user/details/email}" autocomplete="off" autocapitalize="off">

    <label>Password</label>
    <input type="password" name="password" autocomplete="off" autocapitalize="off">

    <label>Confirm Password</label>
    <input type="password" name="confirm_password" autocomplete="off" autocapitalize="off">

    <button type="submit">Next</button>
    <div class="clear"></div>
</form>