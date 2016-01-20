<h2 class="no-top-padding">Repository Manager</h2>
<form class="inline foldable" method="post">
    <dl class="inline">
        <dt>Repository</dt>
        <dd>{data:name} [{data:key}]</dd>
        <dt>Repo URL</dt>
        <dd>{data:url}</dd>
        <dt>Homepage</dt>
        <dd><a href="{data:url}" target="_blank">{data:url}</a></dd>
        <dt>Enabled</dt>
        <dd>{data:enabled=='1'?'Yes':'No'} <a class="float-right" href="?repo-key={data:key}&repo-enable={data:enabled=='1'?'0':'1'}">{data:enabled=='1'?'Disable':'Enable'}</a></dd>
        <dt>Status</dt>
        <dd>{data:modules} Modules | {data:interfaces} Interfaces</dd>
        <!--<dt>Cache Update</dt>
        <dd>In about 10 Minutes [TBC] <a class="float-right" href="#">Force Update</a></dd>-->
    </dl>
    <p>Premium modules and interfaces require you to have a licence and token. You can obtain a licence for this repository from <a href="{data:url}" target="_blank">{data:url}</a>.</p>
    <label>Licence</label><input type="text" name="licence" value="">
    <label>Token</label><input type="text" name="token" value="">
    <button type="submit">Save</button>
</form>
