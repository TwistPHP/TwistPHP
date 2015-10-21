<div class="grid-100 tablet-grid-100 mobile-grid-100">
    <form class="inline foldable" action="{uri:}" method="post">
        <h2 class="no-top-padding">.htaccess Manager</h2>
        <p>Manage the editing of browser cache settings, rewrite rules and default host name redirects such as using www. or not and forcing https. Saving changes will re-generate your .htaccess file, if you require any custom rules please place them in the custom rules box at the bottom of this page.</p>
        <h3>Domain Preferences</h3>

        <label>Preferred Domain Prefix</label>
        <select name="SITE_WWW">
            <option value="1"{setting:SITE_WWW==true?' selected':''}>With WWW</option>
            <option value="0"{setting:SITE_WWW==false?' selected':''}>Without WWW</option>
        </select>

        <label>Preferred Protocol</label>
        <select name="SITE_PROTOCOL">
            <option value="http"{setting:SITE_PROTOCOL=='http'?' selected':''}>HTTP</option>
            <option value="https"{setting:SITE_PROTOCOL=='https'?' selected':''}>HTTPS</option>
        </select>

        <label>Force Protocol/Prefix</label>
        <select name="SITE_PROTOCOL_FORCE">
            <option value="1"{setting:SITE_PROTOCOL_FORCE==true?' selected':''}>Yes, auto redirect</option>
            <option value="0"{setting:SITE_PROTOCOL_FORCE==false?' selected':''}>No, don't redirect</option>
        </select>

        <label>Directory Index Order</label>
        <select name="HTACCESS_DIRECTORY_INDEX">
            <option value="index.php index.html index.htm"{setting:HTACCESS_DIRECTORY_INDEX=="index.php index.html index.htm"?' selected':''}>index.php index.html index.htm</option>
            <option value="index.html index.htm index.php"{setting:HTACCESS_DIRECTORY_INDEX=="index.html index.htm index.php"?' selected':''}>index.html index.htm index.php</option>
        </select>

        <h3>Site Security</h3>
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

        <h3>Browser Caching and Compression</h3>

        <label>Allow ETags</label>
        <input type="checkbox"  name="HTACCESS_ETAG" value="1"{setting:HTACCESS_ETAG==true?' checked':''}>

        <hr>
        <p><strong>HTML</strong></p>

        <label>Cache Control (max-age)</label>
        <select name="HTACCESS_CACHE_HTML">
            <option value="0"{setting:HTACCESS_CACHE_HTML==0?' selected':''}>None</option>
            <option value="14400"{setting:HTACCESS_CACHE_HTML==14400?' selected':''}>4 Hours</option>
            <option value="43200"{setting:HTACCESS_CACHE_HTML==43200?' selected':''}>12 Hours</option>
            <option value="86400"{setting:HTACCESS_CACHE_HTML==86400?' selected':''}>1 Day</option>
            <option value="604800"{setting:HTACCESS_CACHE_HTML==604800?' selected':''}>1 Week</option>
            <option value="2592000"{setting:HTACCESS_CACHE_HTML==2592000?' selected':''}>1 Month</option>
        </select>

        <label>Allow Revalidate</label>
        <input type="checkbox"  name="HTACCESS_REVALIDATE_HTML" value="1"{setting:HTACCESS_REVALIDATE_HTML==true?' checked':''}>

        <label>Enable Compression</label>
        <input type="checkbox"  name="HTACCESS_DEFLATE_HTML" value="1"{setting:HTACCESS_DEFLATE_HTML==true?' checked':''}>

        <hr>
        <p><strong>CSS</strong></p>

        <label>Cache Control (max-age)</label>
        <select name="HTACCESS_CACHE_CSS">
            <option value="0"{setting:HTACCESS_CACHE_CSS==0?' selected':''}>None</option>
            <option value="14400"{setting:HTACCESS_CACHE_CSS==14400?' selected':''}>4 Hours</option>
            <option value="43200"{setting:HTACCESS_CACHE_CSS==43200?' selected':''}>12 Hours</option>
            <option value="86400"{setting:HTACCESS_CACHE_CSS==86400?' selected':''}>1 Day</option>
            <option value="604800"{setting:HTACCESS_CACHE_CSS==604800?' selected':''}>1 Week</option>
            <option value="2592000"{setting:HTACCESS_CACHE_CSS==2592000?' selected':''}>1 Month</option>
        </select>

        <label>Allow Revalidate</label>
        <input type="checkbox"  name="HTACCESS_REVALIDATE_CSS" value="1"{setting:HTACCESS_REVALIDATE_CSS==true?' checked':''}>

        <label>Enable Compression</label>
        <input type="checkbox"  name="HTACCESS_DEFLATE_CSS" value="1"{setting:HTACCESS_DEFLATE_CSS==true?' checked':''}>

        <hr>
        <p><strong>JS</strong></p>

        <label>Cache Control (max-age)</label>
        <select name="HTACCESS_CACHE_JS">
            <option value="0"{setting:HTACCESS_CACHE_JS==0?' selected':''}>None</option>
            <option value="14400"{setting:HTACCESS_CACHE_JS==14400?' selected':''}>4 Hours</option>
            <option value="43200"{setting:HTACCESS_CACHE_JS==43200?' selected':''}>12 Hours</option>
            <option value="86400"{setting:HTACCESS_CACHE_JS==86400?' selected':''}>1 Day</option>
            <option value="604800"{setting:HTACCESS_CACHE_JS==604800?' selected':''}>1 Week</option>
            <option value="2592000"{setting:HTACCESS_CACHE_JS==2592000?' selected':''}>1 Month</option>
        </select>

        <label>Allow Revalidate</label>
        <input type="checkbox"  name="HTACCESS_REVALIDATE_JS" value="1"{setting:HTACCESS_REVALIDATE_JS==true?' checked':''}>

        <label>Enable Compression</label>
        <input type="checkbox"  name="HTACCESS_DEFLATE_JS" value="1"{setting:HTACCESS_DEFLATE_JS==true?' checked':''}>

        <hr>
        <p><strong>Images</strong></p>

        <label>Cache Control (max-age)</label>
        <select name="HTACCESS_CACHE_IMAGES">
            <option value="0"{setting:HTACCESS_CACHE_IMAGES==0?' selected':''}>None</option>
            <option value="14400"{setting:HTACCESS_CACHE_IMAGES==14400?' selected':''}>4 Hours</option>
            <option value="43200"{setting:HTACCESS_CACHE_IMAGES==43200?' selected':''}>12 Hours</option>
            <option value="86400"{setting:HTACCESS_CACHE_IMAGES==86400?' selected':''}>1 Day</option>
            <option value="604800"{setting:HTACCESS_CACHE_IMAGES==604800?' selected':''}>1 Week</option>
            <option value="2592000"{setting:HTACCESS_CACHE_IMAGES==2592000?' selected':''}>1 Month</option>
        </select>

        <label>Allow Revalidate</label>
        <input type="checkbox"  name="HTACCESS_REVALIDATE_IMAGES" value="1"{setting:HTACCESS_REVALIDATE_IMAGES==true?' checked':''}>

        <label>Enable Compression</label>
        <input type="checkbox"  name="HTACCESS_DEFLATE_IMAGES" value="1"{setting:HTACCESS_DEFLATE_IMAGES==true?' checked':''}>

        <h3>Rewrite Rules</h3>
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
            <tfoot>
	            <tr>
	                <td colspan="4" class="align-center"><a href="#" class="button" onclick="return addRewriteRule();">Add Rule</a></td>
	            </tr>
            </tfoot>
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

        <h3>Custom Rules</h3>
        <label>Custom rules will be applied at the top of the .htaccess file</label>
        <textarea name="HTACCESS_CUSTOM" rows="12">{setting:HTACCESS_CUSTOM}</textarea>


        <button type="submit" class="fat"><i class="fa fa-save"></i> Save Changes</button>
        <div class="clear"></div>
    </form>
</div>