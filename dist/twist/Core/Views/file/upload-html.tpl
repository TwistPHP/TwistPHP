<div id="twistupload-{data:uniqid}-wrapper" class="twistupload-wrapper">
	<input id="twistupload-{data:uniqid}" class="twistupload" type="file" name="{data:name}{data:multiple==1?'[]':''}"{data:accept}{data:multiple==1?' multiple':''}>
	<input id="twistupload-{data:uniqid}-pseudo" type="hidden" value="{data:value}">
	<!--<button id="twistupload-{data:uniqid}-clear" class="twistupload-clear">Clear</button>-->
	<div id="twistupload-{data:uniqid}-progress-wrapper" class="twistupload-progress-wrapper">
		<progress id="twistupload-{data:uniqid}-progress" class="twistupload-progress" value="0" max="100"></progress>
		<span id="twistupload-{data:uniqid}-count-wrapper" class="twistupload-count-wrapper"><span id="twistupload-{data:uniqid}-count"></span>/<span id="twistupload-{data:uniqid}-total"></span></span>
		<button id="twistupload-{data:uniqid}-cancel" class="twistupload-cancel">Cancel</button>
	</div>
	<ul id="twistupload-{data:uniqid}-list" class="twistupload-file-list"></ul>
	{view:./upload-js.tpl}
</div>