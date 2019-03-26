<h1>Settings</h1>
<div class="box">
	<button type="submit" class="button button-primary float-right"><i class="fa fa-save"></i> Save</button><a href="?import=core" class="button button-warning float-right"><i class="fa fa-download"></i> Import New Settings</a>
	<p>All the settings in from the framework settings table can be edited here, warning please consult the <a href="https://twistphp.com/docs">documentation</a> if you are unsure what you are doing.</p>
	<form class="inline" method="post">
		<div class="tabContainer">
			<ul class="tabButtons textOnly">
				{data:tabs}
			</ul>
			<div class="tabs">
				{data:tob_content}
			</div>
		</div>
    </form>
</div>