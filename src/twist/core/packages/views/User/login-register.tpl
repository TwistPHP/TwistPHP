<form class="twist-user-login" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6>Log In</h6>
	<p class="twist-user-login-error" style="display: {data:login_error_message!=null?'block':'none'};">{data:login_error_message}</p>
	<p class="twist-user-login-message" style="display: {data:login_message!=null?'block':'none'};">{data:login_message}</p>
	<label class="twist-user-login-email">Email</label>
	<input type="email" name="email" value="" placeholder="Email" size="35" tabindex="1" required>
	<label class="twist-user-login-password">Password</label>
	<input type="password" name="password" value="" size="35" placeholder="Password" tabindex="2" required>
	<label class="twist-user-login-remember">Keep me logged in</label>
	<input type="checkbox" name="remember" value="1" tabindex="3">
	<input type="submit" class="twist-user-login-submit" value="Log In" tabindex="4">
	<p><a href="?forgotten" class="twist-user-login-forgotten">Forgotten your password?</a></p>
	<p><a href="?register" class="twist-user-login-register">Register</a></p>
</form>