<div class="grid-100 tablet-grid-100 mobile-grid-100">
    <form class="inline foldable">
        <h2 class="no-top-padding">Local Packages</h2>
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
    <h2>Available Packages</h2>
    <p>Search the TwistPHP package repository for packages that have been written by TwistPHP developers and other 3rd parties. All packages come with a one click install option as well as an manual install if required.</p>
    <div>
	    <form action="{uri:./packages}" method="get" class="linear">
		    <input type="text" name="search" placeholder="Search...">
		    <label>Show</label>
	        <select name="filter">
		        <option value="featured" selected>Featured</option>
		        <option value="all">All</option>
	        </select>
	        <button type="submit">Search</button>
        </form>
    </div>
    <div id="twist-manager-repolist">
        <ul>{data:repository-packages}</ul>
    </div>
</div>