<div class="grid-100 tablet-grid-100 mobile-grid-100">
	<h2 class="no-top-padding">Settings</h2>
	<a href="?import=core" class="button fat float-right"><i class="fa fa-download"></i> Import New Settings</a>
	<p>All the settings in from the framework settings table can be edited here, warning please consult the <a href="https://twistphp.com/docs">documentation</a> if you are unsure what you are doing.</p>
	<form class="inline foldable" method="post">
        {data:settings}
        <button type="submit" class="fat"><i class="fa fa-save"></i> Save</button>
    </form>
</div>