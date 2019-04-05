<h1>TwistProtect: Firewall</h1>
<p>Visitors that have been blocked from accessing the site, these visitors will see a 403 error message for a specified period of time. Reasons for being block are too many failed login attempts, too many password resets, too many 403,404 requests or being manually blocked.</p>
{messages:all}

<div class="box">
    <div class="boxTitle">
        <h3>Stats</h3>
    </div>
    <div class="boxContent">
        <dl class="inline">
            <dt>Watched IPs</dt><dd>{data:watched_count}</dd>
            <dt>Blocked IPs</dt><dd>{data:blocked_count}</dd>
            <dt>Whitelisted IPs</dt><dd>{data:whitelist_count}</dd>
            <dt>ProtectGlobal IPs</dt><dd>{data:whitelist_count}</dd><dd>Updated: 3 March 2019 as 03:00</dd><dd>Database of banned IP address that have been detected across all participating TwistPHP installations</dd>
        </dl>
    </div>
    <div class="boxFooter">

    </div>
</div>

<div class="box box50">
    <div class="boxTitle">
        <h3>Blocked IPs</h3>
    </div>
    <div class="boxContent">
        <table>
            <thead>
            <tr>
                <th>IP Address</th>
                <th>Type</th>
                <th>Reason</th>
                <th>Expires</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {data:blocked_ips}
            </tbody>
        </table>
    </div>
    <div class="boxFooter">
        <a href="firewallBlockIP" class="button button-primary modalLink">Add New</a>
    </div>
</div>

<div class="box box50">
    <div class="boxTitle">
        <h3>Whitelisted IPs</h3>
    </div>
    <div class="boxContent">
        <table>
            <thead>
            <tr>
                <th>IP Address</th>
                <th>Added</th>
                <th>Reason</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {data:whitelist_ips}
            </tbody>
        </table>
    </div>
    <div class="boxFooter">
        <a href="firewallWhitelistIP" class="button button-primary modalLink">Add New</a>
    </div>
</div>