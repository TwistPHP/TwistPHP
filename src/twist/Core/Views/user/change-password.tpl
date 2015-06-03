<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post" autocomplete="off">
	<h6 class="no-top-padding">Change Your Password</h6>
	<p class="error" style="display: {data:error_message!=null?'block':'none'};">{data:error_message}</p>
	<label class="shadowCurrentPassword">Your Current Password</label>
	<input type="password" name="current_password" tabindex="401" placeholder="Current Password" autocomplete="off" required autofocus>
	<label class="shadowPassword">Choose New Password</label>
	<input type="password" name="password" tabindex="402" placeholder="New Password" autocomplete="off" required>
	<label class="shadowConfirm">Confirm New Password</label>
	<input type="password" name="confirm_password" tabindex="403" placeholder="Confirm Password" autocomplete="off" required>
	<button type="submit" tabindex="404">Change Password</button>
	<div class="clear"></div>
</form>