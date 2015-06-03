<div id="twistupload-{data:uniqid}-wrapper" class="twistupload-wrapper">
	<input id="twistupload-{data:uniqid}" class="twistupload" type="file" name="{data:name}{data:multiple==1?'[]':''}"{data:accept}{data:multiple==1?' multiple':''}>
	<input id="twistupload-{data:uniqid}-pseudo" type="hidden" value="">
	<input type="hidden" name="{data:name}_uri" value="{data:uri}">
	<a href="#" id="twistupload-{data:uniqid}-clear" class="twistupload-clear">Clear</a>
	<div id="twistupload-{data:uniqid}-progress-wrapper" class="twistupload-progress-wrapper">
		<progress id="twistupload-{data:uniqid}-progress" class="twistupload-progress" value="0" max="100"></progress>
		<span id="twistupload-{data:uniqid}-count-wrapper" class="twistupload-count-wrapper"><span id="twistupload-{data:uniqid}-count"></span>/<span id="twistupload-{data:uniqid}-total"></span></span>
		<a href="#" id="twistupload-{data:uniqid}-cancel" class="twistupload-cancel">Cancel</a>
	</div>
	<div id="twistupload-{data:uniqid}-list"></div>
	{view:./upload-js.tpl}
</div>