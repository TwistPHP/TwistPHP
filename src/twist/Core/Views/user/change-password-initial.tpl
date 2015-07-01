<form class="block" action="{uri:./change-password}" method="post" autocomplete="off">
	<h6 class="no-top-padding">Change Your Password</h6>
	<p class="error" style="display: {data:error_message!=null?'block':'none'};">{data:error_message}</p>
	<label class="shadowPassword">Choose New Password</label>
	<input type="password" name="password" tabindex="301" placeholder="New Password" required autofocus autocomplete="off">
	<label class="shadowConfirm">Confirm New Password</label>
	<input type="password" name="confirm_password" tabindex="302" placeholder="Confirm Password" required autocomplete="off">
	<button type="submit" tabindex="303">Change Password</button>
	<div class="clear"></div>
</form>