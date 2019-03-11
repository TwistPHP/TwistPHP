
<div class="grid-100">
    <h2>Edit User</h2>
    {messages:error}
    <form action="/manager/users/edit" method="post">
        <label>First Name<sup>*</sup></label>
        <input type="text" name="firstname">
        <label>Surname<sup>*</sup></label>
        <input type="text" name="surname">
        <label>Email<sup>*</sup></label>
        <input type="email" name="email">
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