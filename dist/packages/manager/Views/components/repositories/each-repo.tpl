<tr>
	<th>{data:name}</th>
	<td>{data:url}</td>
	<td>{data:enabled=='1'?'Yes':'No'}</td>
	<td>{data:module_count}</td>
	<td>{data:interface_count}</td>
	<td><a href="../../../../../../../TwistPHP-website/dist/repository" class="button">Manage</a></td>
	<td><a href="./deleterepository?repo-key={data:key}" class="button red">Delete</a></td>
</tr>