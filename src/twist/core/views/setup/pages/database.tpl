<form class="inline foldable" action="settings" method="post">
    <h2>Database Settings</h2>
    <p>Setup database connection settings, alternatively you can use Twist without a database but this will limit what you can do and use.</p>
    {data:error_message}
    <label>Protocol</label>
    <input name="protocol" type="radio" id="database-none" value="none">
    <label for="database-none">No Database</label>
    <input name="protocol" type="radio" id="database-mysql" value="mysql">
    <label for="database-mysql">MySQL</label>
    <input name="protocol" type="radio" id="database-mysqli" value="mysqli" checked>
    <label for="database-mysqli">MySQLi</label>
    <input name="protocol" type="radio" id="database-pdo" value="pdo">
    <label for="database-pdo">PDO</label>

    <label>Address
        <br><small>Leave blank for localhost</small></label>
    <input type="text" name="host" placeholder="Database server host address" value="{session:twist-setup/database/details/host}" autocomplete="off" autocapitalize="on">

    <label>Username</label>
    <input type="text" name="username" placeholder="Database connection username" value="{session:twist-setup/database/details/username}" autocomplete="off" autocapitalize="on">

    <label>Password</label>
    <input type="text" name="password" placeholder="Database connection password" value="{session:twist-setup/database/details/password}" autocomplete="off" autocapitalize="on">

    <label>Database</label>
    <input type="text" name="database" placeholder="Database name" value="{session:twist-setup/database/details/name}" autocomplete="off" autocapitalize="on">

	<label>Table Prefix</label>
	<input type="text" name="table_prefix" placeholder="Table prefix such as 'twist_'" value="{session:twist-setup/database/details/table_prefix}" autocomplete="off" autocapitalize="on">

    <button type="submit">Next</button>
    <div class="clear"></div>
</form>