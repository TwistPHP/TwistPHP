<form class="inline foldable" action="interfaces" method="post" autocomplete="off">
    <h2>Root User</h2>
    <p>Create a root user account that will have full access to all interfaces and user systems.</p>
    {data:error_message}

    <label>Firstname</label>
    <input type="text" name="firstname" placeholder="John" value="{session:twist-setup/user/details/firstname}" autocomplete="off">

    <label>Lastname</label>
    <input type="text" name="lastname" placeholder="Smith" value="{session:twist-setup/user/details/lastname}" autocomplete="off">

    <label>Email</label>
    <input type="email" maxlength="255" name="email" placeholder="john.smith@{session:twist-setup/settings/details/site_host}" value="{session:twist-setup/user/details/email}" autocomplete="off">

    <label>Password</label>
    <input type="password" name="password" placeholder="" value="" autocomplete="off">

    <label>Confirm Password</label>
    <input type="password" name="confirm_password" placeholder="" value="" autocomplete="off">

    <button type="submit">Next</button>
    <div class="clear"></div>
</form>