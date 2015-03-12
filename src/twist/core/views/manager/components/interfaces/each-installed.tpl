<tr>
	<td>{data:repository}</td>
	<th title="{escape[data:description]}">{data:name}</th>
	<td>{data:current/version}</td>
	<td>{data:update=='1'?data:available/version:'-'}</td>
	<td><a href="{route:base_uri}/processupdate?action=remove&repo={data:repository}&package={data:name}&package-type=interfaces&package-version={data:current/version}" class="button red{data:repository=='-'?' hidden':''}">Remove</a></td>
	<td><a href="{route:base_uri}/packageinformation?repo={data:repository}&package={data:name}&package-type=interfaces" class="button">Info</a></td>
	<td><a href="{route:base_uri}/processupdate?action=update&repo={data:repository}&package={data:name}&package-type=interfaces&package-version={data:update=='1'?data:available/version:'-'}" class="button{data:update=='1'?'':' hidden'}">Update</a></td>
</tr>