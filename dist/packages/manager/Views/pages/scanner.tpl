<h1>TwistProtect: Code Scanner</h1>
<p>Automatically scans your site for malicious PHP code, adjust the settings to get better results. You can also enable as a cron job to help protect your site on a daily basis.</p>

<div class="box">
	<a href="?scan-now=1" class="button fat blue float-right"><i class="fa fa-play"></i> Scan Now</a>

    <dl class="inline">
        <dt>Last Scan</dt>
        <dd>{prettytime[data:scanner/scanned]}</dd>
        <dt>Files Scanned</dt>
        <dd>{data:scanner/files}</dd>
        <dt>Directories Scanned</dt>
        <dd>{data:scanner/dirs}</dd>
        <dt>Files Skipped</dt>
        <dd>{data:scanner/skipped}</dd>
        <dt>Infected Files</dt>
        <dd>{data:scanner/infected/count}</dd>
        <dt>Changed Files</dt>
        <dd>{data:scanner/changed/count}</dd>
        <dt>New Files</dt>
        <dd>{data:scanner/new/count}</dd>
    </dl>
    <h2>Infected Files ({data:scanner/infected/count})</h2>
    <ul>{data:infected_list}</ul>
    <h2>Changed Files ({data:scanner/changed/count})</h2>
    <ul>{data:changed_list}</ul>
    <h2>New Files ({data:scanner/new/count})</h2>
    <ul>{data:new_list}</ul>
</div>