<form class="twist-user-add" action="{data:login_page}" method="post">
	<h6>Add a User</h6>
	<p class="twist-user-add-error" style="display: {data:login_error_message!=null?'block':'none'};">{data:login_error_message}</p>
	<label class="twist-user-add-email">Email</label>
	<input type="email" name="email" value="" placeholder="Email" size="35" tabindex="1" required>
	<label class="twist-user-add-firstname">First Name</label>
	<input type="text" name="firstname" value="" size="128" placeholder="First Name" tabindex="2" required>
	<label class="twist-user-add-surname">Last Name</label>
	<input type="text" name="lastname" value="" size="128" placeholder="Last Name" tabindex="3" required>
	<label class="twist-user-add-password">Password</label>
	<input type="password" name="password" value="" size="35" placeholder="Password" tabindex="4" required>
	<label class="twist-user-add-confirm">Confirm Password</label>
	<input type="password" name="confirm" value="" size="35" placeholder="Confirm Password" tabindex="5" required>
	<input type="submit" class="twist-user-add-submit" value="Register" tabindex="6">
</form>