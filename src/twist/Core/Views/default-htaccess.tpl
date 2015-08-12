# TWISTPHP
	# ================================================================================
	# TwistPHP - Default .htaccess
	# DO NOT REMOVE!
	# --------------------------------------------------------------------------------
	# Author:      	    Shadow Technologies Ltd.
	# Documentation:    https://twistphp.com/docs
	# Note:             Do not edit information inside the '# TWISTPHP' tags
	# ================================================================================

	# An index.html will supersede Twist (if it exists)
	DirectoryIndex index.html index.php

	RewriteEngine on

	# www redirect when enabled in the settings
	{setting:SITE_WWW==true?'':'#'}RewriteCond %{HTTP_HOST} !^www\.
	{setting:SITE_WWW==true?'':'#'}RewriteRule ^(.*)$ {setting:SITE_PROTOCOL}://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

	# HTTPS redirect when enabled in the settings
	{setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteCond %{HTTPS} off
	{setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

	# Rewrite rules to allow routing
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php [L,QSA]

    # Set the cache-control max-age
    {setting:SITE_CACHECONTROLL_IMAGES==true?'':'#'}<FilesMatch "\.(ico|pdf|flv|jpg|png|gif|js|css|swf|svg)$">
    {setting:SITE_CACHECONTROLL_IMAGES==true?'':'#'}    Header set Cache-Control "max-age=2592000, public"
    {setting:SITE_CACHECONTROLL_IMAGES==true?'':'#'}</FilesMatch>

    {setting:SITE_CACHECONTROLL_TXT==true?'':'#'}<FilesMatch "\.(xml|txt)$">
    {setting:SITE_CACHECONTROLL_TXT==true?'':'#'}    Header set Cache-Control "max-age=86400, public, must-revalidate"
    {setting:SITE_CACHECONTROLL_TXT==true?'':'#'}</FilesMatch>

    {setting:SITE_CACHECONTROLL_HTML==true?'':'#'}<FilesMatch "\.(html|htm)$">
    {setting:SITE_CACHECONTROLL_HTML==true?'':'#'}    Header set Cache-Control "max-age=14400, must-revalidate"
    {setting:SITE_CACHECONTROLL_HTML==true?'':'#'}</FilesMatch>

    # Turn off the ETags
    {setting:SITE_ETAG==true?'':'#'}Header unset ETag
    {setting:SITE_ETAG==true?'':'#'}FileETag None

    # Turn off the Last Modified header except for html docs
    {setting:SITE_CACHECONTROLL_LASTMODIFIED==true?'':'#'}<FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css)$">
    {setting:SITE_CACHECONTROLL_LASTMODIFIED==true?'':'#'}    Header unset Last-Modified
    {setting:SITE_CACHECONTROLL_LASTMODIFIED==true?'':'#'}</FilesMatch>

    {setting:SITE_EXPIRESACTIVE==true?'':'#'}ExpiresActive On
    {setting:SITE_EXPIRESACTIVE==true?'':'#'}ExpiresByType image/gif A2592000
    {setting:SITE_EXPIRESACTIVE==true?'':'#'}ExpiresByType image/png A2592000
    {setting:SITE_EXPIRESACTIVE==true?'':'#'}ExpiresByType image/jpg A2592000
    {setting:SITE_EXPIRESACTIVE==true?'':'#'}ExpiresByType image/jpeg A2592000

    # Deflate diffrent files by content type
    {setting:SITE_DEFLATE_HTML==true?'':'#'}AddOutputFilterByType DEFLATE text/html
    {setting:SITE_DEFLATE_CSS==true?'':'#'}AddOutputFilterByType DEFLATE text/css
    {setting:SITE_DEFLATE_JS==true?'':'#'}AddOutputFilterByType DEFLATE text/javascript application/javascript application/x-javascript
    {setting:SITE_DEFLATE_IMAGES==true?'':'#'}AddOutputFilterByType DEFLATE image/jpg image/png image/svg image/svg+xml

# /TWISTPHP