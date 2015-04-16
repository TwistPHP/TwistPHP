<h2 class="no-top-padding">Packages</h2>
<form class="inline foldable">
	<h3>Installed Packages</h3>
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
			{data:packages_installed}
		</tbody>
	</table>
	<!--<a href="#" class="button blue"><strong>Update All</strong></a>-->
	<div class="clear"></div>
</form>
<form class="inline foldable" action="processUpdate" method="post">
	<h3>Available Packages</h3>
	<p>Packages in your /packages folder that have not yet been installed.</p>
	<table>
		<thead>
		<tr>
			<th>Name</th>
			<th>Description</th>
			<th>Version</th>
			<th>Install</th>
		</tr>
		</thead>
		<tbody>
			{data:packages_available}
		</tbody>
	</table>
	<!--<button type="submit">Install Selected</button>-->
	<div class="clear"></div>
</form>