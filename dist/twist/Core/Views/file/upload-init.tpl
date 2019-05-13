<script>
    new twistfileupload( 'twistupload-{data:uniqid}', '{data:uri}', '{data:name}', {
        acceptTypes: {data:acceptTypes!==undefined?data:acceptTypes:'[]'},
        acceptExtensions: {data:acceptExtensions!==undefined?data:acceptExtensions:'[]'},
        multiple: {data:multiple==1?'true':'false'},
		dragdrop: {data:dragdrop}
    },
	{data:preload!==undefined?data:preload:'[]'});
</script>