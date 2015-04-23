<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6 class="no-top-padding">Register</h6>
    <p class="error" style="display: {data:register_error_message!=null?'block':'none'};">{data:register_error_message}</p>
	<label>Email</label>
	<input type="email" name="email" value="{post:email==''?'':post:email}" placeholder="Email" size="255" tabindex="1" required>
	<label>First Name</label>
	<input type="text" name="firstname" value="{post:firstname}" size="128" placeholder="First Name" tabindex="2" required>
	<label>Last Name</label>
	<input type="text" name="lastname" value="{post:lastname}" size="128" placeholder="Last Name" tabindex="3" required>
	<input type="hidden" name="register" value="1">
    <button type="submit" tabindex="4">Register</button>
    <div class="clear"></div>
</form>