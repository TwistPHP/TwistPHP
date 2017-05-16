<div class="grid-100 tablet-grid-100 mobile-grid-100">
    <h2 class="no-top-padding">API Keys</h2>
    <a href="?generate=1" class="button fat blue float-right"><i class="fa fa-key"></i> Generate New Key</a>
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
    <div class="clear"></div>

    <form class="inline foldable large" action="{uri:}" method="post">
        <h2>API Settings</h2>

        <label>API Allowed Request Methods</label>
        <input type="text" name="API_ALLOWED_REQUEST_METHODS" value="{setting:API_ALLOWED_REQUEST_METHODS}">
        <p><small>Comma seperated list of request methods that are enabled. Common methods: GET,POST,PUT,DELETE,HEAD,OPTIONS,CONNECT</small></p>

        <label>API Auth via Request Headers</label>
        <input type="checkbox"  name="API_REQUEST_HEADER_AUTH" value="1"{setting:API_REQUEST_HEADER_AUTH==true?' checked':''}>
        <p><small>The base REST controllers will require authentication to be carried out via HTTP Request Headers</small></p>

        <button type="submit" class="fat"><i class="fa fa-save"></i> Save Changes</button>
    </form>
</div>