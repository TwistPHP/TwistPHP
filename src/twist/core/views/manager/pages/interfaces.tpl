<h2 class="no-top-padding">Interfaces</h2>
<form class="inline foldable">
	<h3>Installed Interfaces</h3>
	<table>
		<thead>
		<tr>
			<th>Repository</th>
			<th>Name</th>
			<th>Version Installed</th>
			<th>Version Available</th>
			<th colspan="3ååå">Actions</th>
		</tr>
		</thead>
		<tbody>
			{data:interfaces_installed}
		</tbody>
	</table>
	<a href="#" class="button blue"><strong>Update All</strong></a>
	<div class="clear"></div>
</form>
<form class="inline foldable" action="processUpdate" method="post">
	<h3>Official TwistPHP Interfaces</h3>
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
			{data:interfaces_official_available}
		</tbody>
	</table>
	<h3>Third-party Interfaces</h3>
	<table>
		<thead>
		<tr>
			<th>Repo</th>
			<th>Name</th>
			<th>Description</th>
			<th>Version</th>
			<th>Install</th>
		</tr>
		</thead>
		<tbody>
			{data:interfaces_thirdparty_available}
		</tbody>
	</table>
	<button type="submit">Install Selected</button>
	<div class="clear"></div>
</form>