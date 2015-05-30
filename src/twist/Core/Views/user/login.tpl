<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6 class="no-top-padding">Log In</h6>
	<p class="error" style="display: {data:login_error_message!=null?'block':'none'};">{data:login_error_message}</p>
	<p class="notice" style="display: {data:login_message!=null?'block':'none'};">{data:login_message}</p>
	<label>Email</label>
	<input type="email" name="email" value="" placeholder="Email" maxlength="255" tabindex="1" required autofocus>
	<label>Password</label>
	<input type="password" name="password" value="" maxlength="255" placeholder="Password" tabindex="2" required>
	<p><a href="?forgotten">Forgotten your password?</a></p>
	<button type="submit" tabindex="3">Log In</button>
	<label class="button">Keep me logged in<input type="checkbox" name="remember" value="1" tabindex="4"></label>
	<div class="clear"></div>
</form>