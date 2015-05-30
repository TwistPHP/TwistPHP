<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post" autocomplete="off">
	<h6 class="no-top-padding">Change Your Password</h6>
	<p class="error" style="display: {data:error_message!=null?'block':'none'};">{data:error_message}</p>
	<label class="shadowCurrentPassword">Your Current Password</label>
	<input type="password" name="current_password" value="" tabindex="401" maxlength="255" placeholder="Current Password" required autocomplete="off">
	<label class="shadowPassword">Choose New Password</label>
	<input type="password" name="password" value="" tabindex="402" maxlength="255" placeholder="New Password" required autocomplete="off">
	<label class="shadowConfirm">Confirm New Password</label>
	<input type="password" name="confirm_password" value="" tabindex="403" maxlength="255" placeholder="Confirm Password" required autocomplete="off">
	<button type="submit" tabindex="404">Change Password</button>
	<div class="clear"></div>
</form>