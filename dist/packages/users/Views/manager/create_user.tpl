
<div class="grid-100">
    <h2>Create User</h2>
    <p>This is where you can create users which will be automatically added to the database to reuse within your app.</p>
    <form action="/manager/users/create" method="post">
        <label>First Name<sup>*</sup></label>
        <input type="text" name="firstname" required>
        <label>Surname<sup>*</sup></label>
        <input type="text" name="surname" required>
        <label>Email<sup>*</sup></label>
        <input type="email" name="email" required>
        <label>User level<sup>*</sup></label>
        <select name="level" id="levels">
            <option value="0">Root user</option>
            <option value="10">Super Admin</option>
            <option value="20">Admin</option>
            <option value="30">Normal user</option>
        </select>
        <label>Password<sup>*</sup></label>
        <input type="password" name="password" required>
        <button type="submit">Submit</button>
    </form>
</div>