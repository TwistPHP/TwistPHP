<form class="twist-user-register" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6>Register</h6>
	<p class="twist-user-register-error" style="display: {data:register_error_message!=null?'block':'none'};">{data:register_error_message}</p>
	<label class="twist-user-register-email">Email</label>
	<input type="email" name="email" value="{post:email==''?'':post:email}" placeholder="Email" size="35" tabindex="1" required>
	<label class="twist-user-add-firstname">First Name</label>
	<input type="text" name="firstname" value="{post:firstname}" size="128" placeholder="First Name" tabindex="2" required>
	<label class="twist-user-add-surname">Last Name</label>
	<input type="text" name="lastname" value="{post:lastname}" size="128" placeholder="Last Name" tabindex="3" required>
	<input type="hidden" name="register" value="1">
	<input type="submit" class="twist-user-register-submit" value="Register" tabindex="4">
</form>