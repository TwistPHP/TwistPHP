# TWISTPHP
    # ================================================================================
    # TwistPHP - Default .htaccess
    #
    # All the options in this .htaccess can be edited within the framework manager
    #
    # DO NOT REMOVE!
    # --------------------------------------------------------------------------------
    # Author:      	    Shadow Technologies Ltd.
    # Licence:      	https://www.gnu.org/licenses/gpl.html GPL License
    # Documentation:    https://twistphp.com/docs
    # Note:             Do not edit information inside the '# TWISTPHP' tags
    # ================================================================================

    # An index.html will supersede Twist (if it exists)
    DirectoryIndex {setting:HTACCESS_DIRECTORY_INDEX}

    RewriteEngine on
    RewriteBase {setting:SITE_BASE_URI==''?'/':setting:SITE_BASE_URI}

    # Custom HTAccess rules that have been setup in the manager
    {setting:HTACCESS_CUSTOM}

    # www redirect when enabled in the settings
    {setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteCond %{HTTP_HOST} {setting:SITE_WWW==true?'!':''}^www\.
    {setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteRule ^(.*)$ {setting:SITE_PROTOCOL}://{setting:SITE_WWW==true?'www.':''}{setting:SITE_HOST}%{REQUEST_URI} [L,R=301]

    # HTTPS redirect when enabled in the settings
    {setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteCond %{HTTPS} {setting:SITE_PROTOCOL=='https'?'off':'on'}
    {setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteRule ^(.*)$ {setting:SITE_PROTOCOL}://{setting:SITE_WWW==true?'www.':''}{setting:SITE_HOST}%{REQUEST_URI} [L,R=301]

    # Rewrite rules that have been setup in the manager
    {data:rewrite_rules}

    # Disable directory browsing
    {setting:HTACCESS_DISABLE_DIRBROWSING==true?'':'#'}Options All -Indexes

    # Disable access to HTaccess and HTpass filesincluding any other files that might end in hta or htp
    {setting:HTACCESS_DISABLE_HTACCESS==true?'':'#'}<Files ~ "^.*\.([Hh][Tt][AaPp])">
    {setting:HTACCESS_DISABLE_HTACCESS==true?'':'#'}    order allow,deny
    {setting:HTACCESS_DISABLE_HTACCESS==true?'':'#'}    deny from all
    {setting:HTACCESS_DISABLE_HTACCESS==true?'':'#'}    satisfy all
    {setting:HTACCESS_DISABLE_HTACCESS==true?'':'#'}</Files>

    # Disallow a list of file extensions form being served
    {setting:HTACCESS_DISABLE_EXTENSIONS!=''?'':'#'}<Files ~ "^.*\.({setting:HTACCESS_DISABLE_EXTENSIONS})">
    {setting:HTACCESS_DISABLE_EXTENSIONS!=''?'':'#'}    order allow,deny
    {setting:HTACCESS_DISABLE_EXTENSIONS!=''?'':'#'}    deny from all
    {setting:HTACCESS_DISABLE_EXTENSIONS!=''?'':'#'}    satisfy all
    {setting:HTACCESS_DISABLE_EXTENSIONS!=''?'':'#'}</Files>

    # Disable hotlinking of images with extensions (jpg|jpeg|png|gif|svg)
    {setting:HTACCESS_DISABLE_HOTLINKS==true?'':'#'}RewriteCond %{HTTP_REFERER} !^$
    {setting:HTACCESS_DISABLE_HOTLINKS==true?'':'#'}RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?{setting:SITE_HOST}/.*$ [NC]
    {setting:HTACCESS_DISABLE_HOTLINKS==true?'':'#'}RewriteRule \.(jpg|jpeg|png|gif|svg)$ {setting:SITE_PROTOCOL}://{setting:SITE_WWW==true?'www.':''}{setting:SITE_HOST}/hotlink.gif [R,L]

    # Disable PHP files from being run within the Uploads directory
    {setting:HTACCESS_DISABLE_UPLOADEDPHP==true?'':'#'}RewriteRule ^uploads/.*\.(?:php[1-6]?|pht|phtml?)$ - [NC,F]

    # Filter suspicious query strings in the URL
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} \.\.\/ [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} etc/passwd [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} boot\.ini [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} ftp\:  [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} http\:  [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} https\:  [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|%3D) [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} base64_encode.*\(.*\) [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} ^.*(%24&x).* [NC,OR]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteCond %{QUERY_STRING} ^.*(127\.0).* [NC]
    {setting:HTACCESS_DISABLE_QUERYSTRINGS==true?'':'#'}RewriteRule ^.* - [F]

    # Rewrite rules to allow routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L,QSA]

    # Set the cache-control max-age
    {setting:HTACCESS_CACHE_HTML!=0?'':'#'}<FilesMatch "\.(html|htm)$">
    {setting:HTACCESS_CACHE_HTML!=0?'':'#'}    Header set Cache-Control "max-age={setting:HTACCESS_CACHE_HTML}{setting:HTACCESS_REVALIDATE_HTML==true?', must-revalidate':''}"
    {setting:HTACCESS_CACHE_HTML==0||(setting:HTACCESS_CACHE_HTML!=0&&setting:HTACCESS_REVALIDATE_HTML==true)?'#':''}    Header unset Last-Modified
    {setting:HTACCESS_CACHE_HTML!=0?'':'#'}</FilesMatch>

    {setting:HTACCESS_CACHE_CSS!=0?'':'#'}<FilesMatch "\.(css|map)$">
    {setting:HTACCESS_CACHE_CSS!=0?'':'#'}    Header set Cache-Control "max-age={setting:HTACCESS_CACHE_CSS}, public{setting:HTACCESS_REVALIDATE_CSS==true?', must-revalidate':''}"
    {setting:HTACCESS_CACHE_CSS==0||(setting:HTACCESS_CACHE_CSS!=0&&setting:HTACCESS_REVALIDATE_CSS==true)?'#':''}    Header unset Last-Modified
    {setting:HTACCESS_CACHE_CSS!=0?'':'#'}</FilesMatch>

    {setting:HTACCESS_CACHE_JS!=0?'':'#'}<FilesMatch "\.(js)$">
    {setting:HTACCESS_CACHE_JS!=0?'':'#'}    Header set Cache-Control "max-age={setting:HTACCESS_CACHE_JS}, public{setting:HTACCESS_REVALIDATE_JS==true?', must-revalidate':''}"
    {setting:HTACCESS_CACHE_JS==0||(setting:HTACCESS_CACHE_JS!=0&&setting:HTACCESS_REVALIDATE_JS==true)?'#':''}    Header unset Last-Modified
    {setting:HTACCESS_CACHE_JS!=0?'':'#'}</FilesMatch>

    {setting:HTACCESS_CACHE_IMAGES!=0?'':'#'}<FilesMatch "\.(ico|pdf|flv|jpg|png|gif|swf|svg)$">
    {setting:HTACCESS_CACHE_IMAGES!=0?'':'#'}    Header set Cache-Control "max-age={setting:HTACCESS_CACHE_IMAGES}, public{setting:HTACCESS_REVALIDATE_IMAGES==true?', must-revalidate':''}"
    {setting:HTACCESS_CACHE_IMAGES==0||(setting:HTACCESS_CACHE_IMAGES!=0&&setting:HTACCESS_REVALIDATE_IMAGES==true)?'#':''}    Header unset Last-Modified
    {setting:HTACCESS_CACHE_IMAGES!=0?'':'#'}</FilesMatch>

    # Turn off the ETags
    {setting:HTACCESS_ETAG==true?'#':''}Header unset ETag
    {setting:HTACCESS_ETAG==true?'#':''}FileETag None

    # Deflate diffrent files by content type
    {setting:HTACCESS_DEFLATE_HTML==true?'':'#'}AddOutputFilterByType DEFLATE text/html
    {setting:HTACCESS_DEFLATE_CSS==true?'':'#'}AddOutputFilterByType DEFLATE text/css
    {setting:HTACCESS_DEFLATE_JS==true?'':'#'}AddOutputFilterByType DEFLATE text/javascript application/javascript application/x-javascript
    {setting:HTACCESS_DEFLATE_IMAGES==true?'':'#'}AddOutputFilterByType DEFLATE image/jpg image/png image/svg image/svg+xml

# /TWISTPHP