<h1>General Security</h1>
<p>Manage the editing of browser cache settings, rewrite rules and default host name redirects such as using www. or not and forcing https. Saving changes will re-generate your .htaccess file, if you require any custom rules please place them in the custom rules box at the bottom of this page.</p>

<form class="inline foldable large" action="{uri:}" method="post">

	<div class="buttonBar">
		<button type="submit" class="button button-primary"><i class="fa fa-save"></i> Save Changes</button>
	</div>

	<div class="box">
		<div class="boxTitle">
			<h3>Settings</h3>
		</div>
		<div class="boxContent">
			<p>General settings to enhance the overall security of your website, it is generally advised to enable all of the below settings.</p>

			<label>Disable Directory Browsing</label>
			<input type="checkbox"  name="HTACCESS_DISABLE_DIRBROWSING" value="1"{setting:HTACCESS_DISABLE_DIRBROWSING==true?' checked':''}>

			<label>Disallow Serving of .htaccess</label>
			<input type="checkbox"  name="HTACCESS_DISABLE_HTACCESS" value="1"{setting:HTACCESS_DISABLE_HTACCESS==true?' checked':''}>

			<label>Disallow File Extensions</label>
			<input type="text" name="HTACCESS_DISABLE_EXTENSIONS" value="{setting:HTACCESS_DISABLE_EXTENSIONS}">
			<p><small>Each file extension must be separated by a pipe, Default value: "bash|git|hg|log|svn|swp|cvs"</small></p>

			<label>Disabled Image Hotlinks</label>
			<input type="checkbox"  name="HTACCESS_DISABLE_HOTLINKS" value="1"{setting:HTACCESS_DISABLE_HOTLINKS==true?' checked':''}>

			<label>Disable Uploaded PHP</label>
			<input type="checkbox"  name="HTACCESS_DISABLE_UPLOADEDPHP" value="1"{setting:HTACCESS_DISABLE_UPLOADEDPHP==true?' checked':''}>

			<label>Disable Suspicious Query Strings</label>
			<input type="checkbox"  name="HTACCESS_DISABLE_QUERYSTRINGS" value="1"{setting:HTACCESS_DISABLE_QUERYSTRINGS==true?' checked':''}>

			<div class="clear"></div>
		</div>
		<div class="boxFooter">

		</div>
	</div>

</form>
