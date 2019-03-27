<h1>Rules and Redirects</h1>
<p>Manage the editing of browser cache settings, rewrite rules and default host name redirects such as using www. or not and forcing https. Saving changes will re-generate your .htaccess file, if you require any custom rules please place them in the custom rules box at the bottom of this page.</p>
{messages:all}

<form class="inline foldable large" action="{uri:}" method="post">

	<div class="buttonBar">
		<button type="submit" class="button button-primary"><i class="fa fa-save"></i> Save Changes</button>
	</div>

	<div class="box">
		<div class="boxTitle">
			<h3>Redirects</h3>
		</div>
		<div class="boxContent">
			<script>
				function addRewriteRule(){
					$('#rewriteRules tbody').append('<tr><td><input type="text" name="rewrite[]" value=""></td><td><input type="text" name="rewrite-redirect[]" value=""></td><td><select name="rewrite-options[]"><option value="L">[L]</option><option value="R,L">[R,L]</option><option value="R=301,L">[R=301,L]</option><option value="NC,L">[NC,L]</option><option value="NC,R,L">[NC,R,L]</option><option value="NC,R=301,L">[NC,R=301,L]</option></select></td><td><a href="#" class="button" title="Remove Rule" onclick="return removeRewriteRule(this);">X</a></td></tr>');
					return false;
				}

				function removeRewriteRule(resRow){
					$(resRow).parent().parent().remove();
					return false;
				}
			</script>
			<table id="rewriteRules">
				<thead>
				<tr>
					<th>URI</th>
					<th>Redirect URL</th>
					<th>Options</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				{data:rewrite_rules}
				{view:../components/htaccess/rewrite-rule.tpl}
				<!--<tr>
					<td><input type="text" name="rewrite[]" value=""></td>
					<td><input type="text" name="rewrite-redirect[]" value=""></td>
					<td><select name="rewrite-options[]"><option value="L">[L]</option><option value="R,L">[R,L]</option><option value="R=301,L">[R=301,L]</option><option value="NC,L">[NC,L]</option><option value="NC,R,L">[NC,R,L]</option><option value="NC,R=301,L">[NC,R=301,L]</option></select></td>
					<td><a href="#" class="button" title="Remove Rule" onclick="return removeRewriteRule(this);">X</a></td>
				</tr>-->
				</tbody>
			</table>
			<div class="clear"></div>
		</div>
		<div class="boxFooter">
			<a href="#" class="button button-outline-primary" onclick="return addRewriteRule();">Add Rule</a>
		</div>
	</div>

	<div class="box">
		<div class="boxTitle">
			<h3>Custom .htaccess Rules</h3>
		</div>
		<div class="boxContent">

			<label>Custom rules will be applied at the top of the .htaccess file</label>
			<textarea name="HTACCESS_CUSTOM" rows="12">{setting:HTACCESS_CUSTOM}</textarea>
			<div class="clear"></div>
		</div>
		<div class="boxFooter">

		</div>
	</div>

</form>
