<form class="block" action="{route:login_uri==undefiend?data:login_page:route:login_uri}" method="post">
	<h6 class="no-top-margin">Account Verification</h6>
	<p>Please verify your account by clicking the link in your verification email. If you cannot find your verification email you can request a new verification code below.</p>
	<p>Remember to check your spam and junk folders.</p>
	<p>Email: <strong>{session:user/email}</strong></p>
	<input type="hidden" required name="verification_email" value="{session:user/email}" size="35">
	<button type="submit" tabindex="1">Resend Verification Email</button>
	<div class="clear"></div>
</form>