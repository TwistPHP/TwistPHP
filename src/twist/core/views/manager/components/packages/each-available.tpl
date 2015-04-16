<tr>
	<th>{data:name}</th>
	<td>{data:details/description}</td>
	<td>{data:version}</td>
	<td>
        <!--<input type="checkbox" name="{data:key}[install]" value="1">
        <input type="hidden" name="{data:key}[action]" value="update">
        <input type="hidden" name="{data:key}[repo]" value="{data:repository}">
        <input type="hidden" name="{data:key}[package]" value="{data:name}">
        <input type="hidden" name="{data:key}[package-type]" value="modules">
        <input type="hidden" name="{data:key}[package-version]" value="{data:available/version}">-->
		<a href="{uri:./install}?package={data:slug}" class="button blue"><strong>Install</strong></a>
    </td>
</tr>