
<div class="grid-100">
    <h2>Edit User</h2>
    {messages:error}
    <form action="/manager/users/edit/{data:id}" method="post" autocomplete="false">
        <label>First Name<sup>*</sup></label>
        <input type="text" name="firstname" value="{data:firstname}" autocomplete="false">
        <label>Surname<sup>*</sup></label>
        <input type="text" name="surname" value="{data:surname}" autocomplete="false">
        <label>Email<sup>*</sup></label>
        <input type="email" name="email" value="{data:email}" autocomplete="false">
        <label>User level<sup>*</sup></label>
        <select name="level" id="levels" value="{data:level}">
            <option value="0" {data:level==0?"selected":""} >Root user</option>
            <option value="10" {data:level==10?"selected":""} >Super Admin</option>
            <option value="20" {data:level==20?"selected":""} >Admin</option>
            <option value="30" {data:level==30?"selected":""} >Normal user</option>
        </select>
        <label>Password<sup>*</sup></label>
        <input type="password" name="password" required>
        <button type="submit">Submit</button>
    </form>
</div>