<h1>API Keys</h1>
<div class="buttonBox">
    <a href="?generate=1" class="button button-primary"><i class="fa fa-key"></i> Generate New Key</a>
</div>
<table>
    <thead>
    <tr>
        <th>Key</th>
        <th>Allowed IPs</th>
        <th>Enabled</th>
        <th>Created</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    {data:keys}
    </tbody>
</table>

<form class="inline foldable large" action="{uri:}" method="post">
    <div class="box">
        <div class="boxTitle">
            <h3>API Settings</h3>
        </div>
        <div class="boxContent">

            <label for="API_ALLOWED_REQUEST_METHODS">API Allowed Request Methods</label>
            <input type="text" id="API_ALLOWED_REQUEST_METHODS" name="API_ALLOWED_REQUEST_METHODS" value="{setting:API_ALLOWED_REQUEST_METHODS}">
            <p><small>Comma separated list of request methods that are enabled. Common methods: GET,POST,PUT,DELETE,HEAD,OPTIONS,CONNECT</small></p>

            <label for="API_REQUEST_HEADER_AUTH">API Auth via Request Headers</label>
            <input type="checkbox" id="API_REQUEST_HEADER_AUTH" name="API_REQUEST_HEADER_AUTH" value="1"{setting:API_REQUEST_HEADER_AUTH==true?' checked':''}>
            <p><small>The base REST controllers will require authentication to be carried out via HTTP Request Headers</small></p>

            <div class="clear"></div>
        </div>
        <div class="boxFooter">
            <button type="submit" class="button button-primary">Save Changes</button>
        </div>
    </div>
</form>