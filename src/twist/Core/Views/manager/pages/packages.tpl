<div class="grid-100 tablet-grid-100 mobile-grid-100">
    <h2 class="no-top-padding">Packages</h2>
    <form class="inline foldable">
        <h3>Local Packages</h3>
        <p>All the packages that are in your local <strong>/packages</strong> folder can be seen below. Install the packages in order for them to become active. Uninstalling a package will remove all associated data, ensure you have taken a backup first.</p>
        <table>
            <thead>
            <tr>
                <th>Repository</th>
                <th>Name</th>
                <th>Version</th>
                <th colspan="3">Actions</th>
            </tr>
            </thead>
            <tbody>
            {data:local_packages}
            </tbody>
        </table>
        <!--<a href="#" class="button blue"><strong>Update All</strong></a>-->
        <div class="clear"></div>
    </form>
    <h3>Package Repository</h3>
    <p>Search the TwistPHP package repository for packages that have been written by TwistPHP Developers and other 3rd parties. All packages come with a one click install option as well as an manual install if required.</p>
    <div>
        <form action="{uri:./packages}" method="get">
            <ul class="inline">
                <li><a href="{uri:./packages}?filter=featured">Featured Packages</a></li>
                <li><a href="{uri:./packages}?filter=all">All Packages</a></li>
                <li><input type="text" name="search" value="" placeholder="Search ..."></li>
            </ul>
        </form>
    </div>
    <div id="twist-manager-repolist">
        <ul>{data:repository-packages}</ul>
    </div>
</div>