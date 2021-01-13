<tr>
    <th>{data:ip_address}</th>
    <td>{data:type=='full'?'Permenant':'Temporary'}</td>
    <td>{data:reason}</td>
    <td>{date[data:expire,'d M Y, H:i']}</td>
    <td><a href="?unban={data:ip_address}">Unban</a></td>
</tr>