<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6 class="no-top-padding">Register</h6>
    <p class="error" style="display: {data:register_error_message!=null?'block':'none'};">{data:register_error_message}</p>
    <p class="notice" style="display: {data:register_message!=null?'block':'none'};">{data:register_message}</p>
    <div style="display: {data:register_message!=null?'none':'block'};">
        <label>Email</label>
        <input type="email" name="email" value="{post:email==''?'':post:email}" placeholder="Email" size="255" tabindex="101" required>
        <label>First Name</label>
        <input type="text" name="firstname" value="{post:firstname}" size="128" placeholder="First Name" tabindex="102" required>
        <label>Last Name</label>
        <input type="text" name="lastname" value="{post:lastname}" size="128" placeholder="Last Name" tabindex="103" required>
        <input type="hidden" name="register" value="1">
        <button type="submit" tabindex="104">Register</button>
        <div class="clear"></div>
    </div>
</form>