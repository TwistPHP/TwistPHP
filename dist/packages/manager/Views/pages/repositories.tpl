<h1 class="no-top-padding">Repositories</h1>
<form class="inline foldable" method="post">
	<table>
		<thead>
		<tr>
			<th>Name</th>
			<th>URL</th>
			<th>Enabled</th>
			<th>Modules Available</th>
			<th>Interfaces Available</th>
			<th colspan="2">Actions</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th></th>
			<td colspan="2"><input name="repository_url" type="url" placeholder="New Repo URL"></td>
			<td></td>
			<td></td>
			<td></td>
			<td><button type="submit">Add Repo</button></td>
		</tr>
		</tfoot>
		<tbody>
		{data:static}
		{data:third-party}
		</tbody>
	</table>
</form>
