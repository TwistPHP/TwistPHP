#PHP Views

While the templating system allows you to do some simple PHP functions, such as `{md5[date:Y-m-d H:i:s]}` to create and MD5 hash of the datetime, more complex and bespoke functions require PHP.

1. Within your *app/views/elements* folder, create a PHP file called *count.php*:
	* public_html
		* app
			* ajax
            * assets
            * cache
            * config
            * controllers
            * models
            * resources
			* views
				* base.tpl
				* elements
					* head.tpl
					* **count.php**
		* twist
			* ...
		* .htaccess
		* index.php

2. Copy the following PHP into your *count.php* view:
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
		{template:elements/head.tpl}
		<body>
			<h1>{data:welcome}</h1>
			{view:elements/count.php,5}
		</body>
	</html>
	```

	The output of the *count.php* file will now be placed within the template. Remember that you can also call more templates within your element by using `Twist::View() -> build()`.