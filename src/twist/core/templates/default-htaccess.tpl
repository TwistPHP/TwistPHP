# TWISTPHP
	# ================================================================================
	# TwistPHP .htaccess - Do not remove!
	# --------------------------------------------------------------------------------
	# Author:      	    Shadow Technologies
	# Documentation:    http://twistphp.com/documentation
	# Note:             Do not edit information inside the '# TWISTPHP' tags
	# ================================================================================
	RewriteEngine on

	# WWW. redirect when enable in the settings
	{setting:SITE_WWW==true?'':'#'}RewriteCond %{HTTP_HOST} !^www\.
	{setting:SITE_WWW==true?'':'#'}RewriteRule ^(.*)$ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

	# HTTPS redirect when enable in the settings
	{setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteCond %{HTTPS} off
	{setting:SITE_PROTOCOL_FORCE==true?'':'#'}RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

	# Routes Rewrite to allow for dynamic pages
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php [L,QSA]
# /TWISTPHP