<div class="grid-100 tablet-grid-100 mobile-grid-100">
    <h2 class="no-top-padding">Malicious Code Scanner</h2>
    <p>Automatically scans your site for malicious PHP code, adjust the settings to get better results. You can also enable as a cron job to help protect yor site on a daily basis.</p>
    <p><a href="?scan-now=1" class="button">Scan Now</a></p>
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
    </dl>
    <h2>Infected Files ({data:scanner/infected/count})</h2>
    <ul>{data:infected_list}</ul>
</div>