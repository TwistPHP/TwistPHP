<tr>
    <td>{data:id}</td>
    <td>{data:email}</td>
    <td>{data:firstname} {data:surname}</td>
    <td>{data:last_active}</td>
    <td>{data:level}</td>
    <td>{data:last_login_ip}</td>
    <td><a href="/manager/users/edit/{data:id}" title="Edit user"><span class="fas fa-user-cog"></span></a> <a class="red delete-user" href="/manager/users?delete-user={data:id}" title="delete user"><span class="fas fa-user-times"></span></a></td>
</tr>
