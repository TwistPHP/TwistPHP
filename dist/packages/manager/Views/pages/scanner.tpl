<h1>TwistProtect: Code Scanner</h1>
<p>Automatically scans your site for malicious PHP code, adjust the settings to get better results. You can also enable as a cron job to help protect your site on a daily basis.</p>
{messages:all}
<div class="box">
    <div class="boxTitle">
        <h3>Scan Statistics</h3>
    </div>
    <div class="boxContent">

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
        <div class="clear"></div>
    </div>
    <div class="boxFooter">
        <a href="?scan-now=1" class="button button-primary"><i class="fa fa-play"></i> Scan Now</a>
    </div>
</div>

<div class="box box33">
    <div class="boxTitle">
        <h3>Infected Files ({data:scanner/infected/count})</h3>
    </div>
    <div class="boxContent">
        <ul>{data:infected_list}</ul>
        <div class="clear"></div>
    </div>
    <div class="boxFooter">

    </div>
</div>

<div class="box box33">
    <div class="boxTitle">
        <h3>Changed Files ({data:scanner/changed/count})</h3>
    </div>
    <div class="boxContent">
        <ul>{data:changed_list}</ul>
        <div class="clear"></div>
    </div>
    <div class="boxFooter">

    </div>
</div>

<div class="box box33">
    <div class="boxTitle">
        <h3>New Files ({data:scanner/new/count})</h3>
    </div>
    <div class="boxContent">
        <ul>{data:new_list}</ul>
        <div class="clear"></div>
    </div>
    <div class="boxFooter">

    </div>
</div>