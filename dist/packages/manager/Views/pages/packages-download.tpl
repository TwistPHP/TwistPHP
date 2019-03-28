<h1>Packages: Download New</h1>
<p>Search the TwistPHP package repository for packages that have been written by TwistPHP developers and other 3rd parties. All packages come with a one click install option as well as an manual install if required.</p>
{messages:all}
<div class="box">
	<div class="boxTitle">
		<h3>Search & Filter</h3>
	</div>
	<div class="boxContent">
		<form action="{uri:./download}" method="get" class="linear">
			<input type="text" name="search" placeholder="Search...">
			<label>Show</label>
			<select name="filter">
				<option value="featured" selected>Featured</option>
				<option value="all">All</option>
			</select>
			<button type="submit">Search</button>
		</form>
	</div>
	<div class="boxFooter">

	</div>
</div>

<div id="twist-manager-repolist">
	<ul>{data:repository-packages}</ul>
</div>
