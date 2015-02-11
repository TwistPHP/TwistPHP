<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6 class="no-top-margin">Change Your Password</h6>
	<p class="error" style="display: {data:error_message!=null?'block':'none'};">{data:error_message}</p>
	<label class="shadowCurrentPassword">Your Current Password</label>
	<input type="password" name="current_password" value="" tabindex="1" size="35" placeholder="Current Password" required>
	<label class="shadowPassword">Choose New Password</label>
	<input type="password" name="password" value="" tabindex="2" size="35" placeholder="New Password" required>
	<label class="shadowConfirm">Confirm New Password</label>
	<input type="password" name="confirm_password" value="" tabindex="3" size="35" placeholder="Confirm Password" required>
	<button type="submit" tabindex="3">Change Password</button>
	<div class="clear"></div>
</form>