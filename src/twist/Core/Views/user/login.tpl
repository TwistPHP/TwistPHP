<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6 class="no-top-padding">Log In</h6>
	<p class="error" style="display: {data:login_error_message!=null?'block':'none'};">{data:login_error_message}</p>
	<p class="notice" style="display: {data:login_message!=null?'block':'none'};">{data:login_message}</p>
	<label>Email</label>
	<input type="email" name="email" value="{post:email!==undefined?post:email:''}" placeholder="Email" maxlength="255" tabindex="1" autofocus>
	<label>Password</label>
	<input type="password" name="password" placeholder="Password" tabindex="2">
	<p><a href="?forgotten">Forgotten your password?</a></p>
	<label>Keep me logged in</label>
	<input type="checkbox" name="remember" value="1" tabindex="3">
	<button type="submit" tabindex="4">Log In</button>
	<div class="clear"></div>
</form>