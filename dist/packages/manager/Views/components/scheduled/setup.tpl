<h3>Getting Started</h3>
<p>To get up and running with TwistPHP's Scheduled Tasks you will need to setup a CronJob that runs once every minute. Why once a minute? TwistPHP will then manage each individual task running it at the appropriate time.</p>
<dl class="inline">
	<dt>Command</dt><dd>twist_cron=on php {data:public_root}/index.php</dd>
	<dt>Time</dt><dd>"* * * * *" Every Minute</dd>
</dl>
<p><strong>Note:</strong> If you are unable to run the command every minute set the script to run a regularly as possible and TwistPHP will compensate. Any system reliant on a more frequent task may not preform as well as expected.</p>