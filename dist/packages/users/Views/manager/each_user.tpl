<tr>
    <td>{data:id}</td>
    <td>{data:email}</td>
    <td>{data:firstname} {data:surname}</td>
    <td>{data:last_active}</td>
    <td>{data:level}</td>
    <td>{data:last_login_ip}</td>
    <td><a class="button" href="/manager/users/edit">Edit</a> <a class="button red delete-user" href="/manager/users?delete-user={data:id}">Delete</a></td>
</tr>
