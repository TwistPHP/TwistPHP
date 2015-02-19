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
# /TWISTPHP