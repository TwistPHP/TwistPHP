<style>
	.showOnHover{ display: none; } .showHover:hover .showOnHover{ display: block; } tbody a{ font-size: 30px; color: #000;padding-right: 5px;} tbody a:hover{ color: #529F00; transition: all 0.5s ease-in;} p a{ font-size: 16px; text-decoration: none; color: #000; } p a:hover{ color: #529F00; transition: all 0.5s ease-in; } a:visited{ color: #000;}
</style>

<div class="grid-100 tablet-grid-100 mobile-grid-100">
	<h2>Users</h2>
    <p>Here is a list of users currently registered on your application. Through this dashboard you can either create,edit or delete users.</p>
    <p><a href="/manager/users/create">Create a user <span class="fas fa-user-plus"></span></a></p>
    <table>
        <thead>
        <tr>
            <td>UserID</td>
            <td>Email</td>
            <td>Name</td>
            <td>Last active</td>
            <td>User permissions</td>
            <td>User IP</td>
            <td></td>
        </tr>
        </thead>
        <tbody>
            {data:users}
        </tbody>
    </table>
    <script>
        $(document).on('click','.delete-user',function(){
           return confirm("Are you sure you want to delete this user?");
        });
    </script>

</div>