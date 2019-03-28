<h1>Settings</h1>
<p>All the settings in from the framework settings table can be edited here, warning please consult the <a href="https://twistphp.com/docs">documentation</a> if you are unsure what you are doing.</p>

<form class="inline" method="post">
	<div class="buttonBar">
		<button type="submit" class="button button-primary"><i class="fa fa-save"></i> Save</button><a href="?import=core" class="button button-warning"><i class="fa fa-download"></i> Import New Settings</a>
	</div>
	<div class="tabContainer">
		<ul class="tabButtons">
			{data:tabs}
		</ul>
		{data:tob_content}
	</div>
</form>