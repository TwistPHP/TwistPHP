<div id="twistupload-{data:uniqid}-wrapper" class="twistupload-wrapper">
	<input id="twistupload-{data:uniqid}" class="twistupload" name="{data:name}" type="file"{data:mutiple==1?' multiple':''}>
	<div id="twistupload-{data:uniqid}-progress-wrapper" class="twistupload-progress-wrapper">
		<progress id="twistupload-{data:uniqid}-progress" class="twistupload-progress" value="0" max="100"></progress>
		<span id="twistupload-{data:uniqid}-count-wrapper" class="twistupload-count-wrapper"><span id="twistupload-{data:uniqid}-count"></span>/<span id="twistupload-{data:uniqid}-total"></span></span>
		<a href="#" id="twistupload-{data:uniqid}-cancel" class="twistupload-cancel">Cancel</a>
	</div>
	{data:include-js==1?'<script src="/twist/core/packages/resources/File/file-upload.js"></script>':''}
</div>