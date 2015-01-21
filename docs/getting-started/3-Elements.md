#Elements

While the templating system allows you to do some simple PHP functions, such as `{md5[date:Y-m-d H:i:s]}` to create and MD5 hash of the datetime, more complex and bespoke functions require PHP.

Rather than mixing your HTML and PHP, we have created something we call elements which are small bits of PHP that make your site more dynamic whilst keeping your PHP and HTML separate.

1. Within your *templates* folder, create a folder called *components* with a PHP file called *count.php* within it:
	* public_html
		* templates
			* common
				* head.tpl
			* **components**
				* **count.php**
			* base.tpl
		* twist
			* ...
		* .htaccess
		* index.php

2. Copy the following PHP into your *count.php* element:
	```php
	<?php
		$arrParameters = $this -> getParameters();
		$intFrom = $arrParameters[0];
		for( $intNumber = $intFrom; $intNumber >= 0; $intNumber-- ) {
			echo sprintf( '%d...', $intNumber );
		}
		echo 'Blast Off!';
	```

3. Finally update your *base.tpl* file as so:
	```html
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		{template:common/head.tpl}
		<body>
			<h1>{data:welcome}</h1>
			{element:components/count.php,5}
		</body>
	</html>
	```

	The output of the *count.php* file will now be placed within the template. Remember that you can also call more templates within your element by using `Twist::Template() -> build()`.