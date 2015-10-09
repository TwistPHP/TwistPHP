<tr>
	<th>{data:name}{data:recommended}</th>
	<td>{data:description}</td>
	<td>{data:available/version}</td>
	<td>
		<input type="checkbox" name="interface-{data:key}[install]" value="1"{data:checked}>
		<input type="hidden" name="interface-{data:key}[action]" value="update">
		<input type="hidden" name="interface-{data:key}[repo]" value="{data:repository}">
		<input type="hidden" name="interface-{data:key}[package]" value="{data:name}">
		<input type="hidden" name="interface-{data:key}[package-type]" value="interfaces">
		<input type="hidden" name="interface-{data:key}[package-version]" value="{data:available/version}">
	</td>
</tr>