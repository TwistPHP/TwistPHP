<style>
	.showOnHover{
		display: none;
	}
	.showHover:hover .showOnHover{
		display: block;
	}
</style>

<div class="grid-100 tablet-grid-100 mobile-grid-100">
	<h2>Users</h2>
    <p>Here is a list of users currently registered on your application. Through this dashboard you can either create,edit or delete users.</p>
    <table>
        <thead>
        <tr>
            <td>UserID</td>
            <td>Email</td>
            <td>Name</td>
            <td>Last active</td>
            <td>User permissions</td>
            <td>User IP</td>
        </tr>
        </thead>
        <tbody>
            {data:users}
        </tbody>
    </table>

</div>