<!DOCTYPE html>
<html class="no-js" lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>TwistPHP - The PHP MVC Framework with a TWIST</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    {resource:arable}
    {resource:modernizr}
</head>
<body>
<h1>Uploading</h1>

<div id="twistupload-test-1"></div>
<div id="twistupload-test-2"></div>
<div id="twistupload-test-3"></div>
<div id="twistupload-test-4"></div>
{resource:twist/fileupload}
<script>
	var fileuploadtest1 = new twistfileupload( 'twistupload-test-1', '/upload/file', 'test-1', false, { foo: 'bar' } );
	var fileuploadtest2 = new twistfileupload( 'twistupload-test-2', '/upload/file', 'test-2', true, { foo: 'bar' } );
	var fileuploadtest3 = new twistfileupload( 'twistupload-test-3', '/upload/asset', 'test-3', false, { foo: 'bar' } );
	var fileuploadtest4 = new twistfileupload( 'twistupload-test-4', '/upload/asset', 'test-4', true, { foo: 'bar' } );
</script>
<!--
{file:upload}
{file:upload,multiple=1}
{asset:upload}
{asset:upload,multiple=1}
-->
</body>
</html>