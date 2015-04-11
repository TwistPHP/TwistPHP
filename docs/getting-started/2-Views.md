#Templates

Inspired by the simplicity of CodeIgniters templating parser, we managed to create a unique and powerful templating engine of our own.

1. Create a *templates* folder in your site root.

2. Create a file called *base.tpl* in your *templates* folder and paste in the following:
	```html
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<title>My Test Page</title>
		</head>
		<body>
			<h1>Hello world!</h1>
			<p>The date is {date:jS F Y} and the time is {date:H:i}.</p>
			<p>Your IP address is: {server:remote_addr}</p>
		</body>
	</html>
	```

	Your directory should now look like this:

	* public_html
		* **templates**
			* **base.tpl**
		* twist
			* ...
		* .htaccess
		* index.php

3. Update your *index.php* file:
	```php
	<?php
		require_once 'twist/framework.php';
		echo Twist::Template() -> build( 'base.tpl' );
	```

	If you view your site in a browser now, you should see that the template tags have been populated with the relevant data, in this case the date (using the same syntax as the PHP `date()` function) and the `$_SERVER['REMOTE_ADDR']` value.

##Passing parameters

1. Update your template file:
	```html
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<title>{data:title}</title>
			<meta name="{data:meta/type}" content="{data:meta/value}">
		</head>
		<body>
			<h1>{data:welcome}</h1>
			<p>The date is {date:Y-m-d} and the time is {date:H:i}.</p>
			<p>Your IP address is: {server:REMOTE_ADDR}</p>
		</body>
	</html>
	```

	If you run this in your browser, there will be blank spaces where there has been no data passed in.

2. In your *index.php* file, we now need to pass in some data to populate the template with. Insert the data into an array and pass it into the `build()` function as a second parameter:
	```php
	<?php
		require_once 'twist/framework.php';
		$arrContent = array(
			'title' => 'My First Page',
			'welcome' => 'Hello world, again!',
			'meta' => array(
				'type' => 'description',
				'value' => 'An awesome site'
			)
		);
		echo Twist::Template() -> build( 'base.tpl', $arrContent );
	```

	When run now, the content is inserted into the template. Notice that we can use multidimensional tags such as `{data:meta/type}`.

##Extending your templates

The real power behind the template engine after pre-defined variables and content lies in including other files within your template to minimise HTML duplication.

1. Create a new subfolder in your *templates* folder called common and create a file called *head.tpl*:
	* public_html
		* templates
			* **common**
				* **head.tpl**
			* base.tpl
		* twist
			* ...
		* .htaccess
		* index.php

2. Move the following HTML from your *base.tpl* into your new *head.tpl* file:
	```html
	<head>
		<title>{data:title}</title>
		<meta name="{data:meta/type}" content="{data:meta/value}">
	</head>
	```

3. Now update your *base.tpl* file to read as such:
	```html
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		{template:common/head.tpl}
		<body>
			<h1>{data:welcome}</h1>
			<p>The date is {date:Y-m-d} and the time is {date:H:i}.</p>
			<p>Your IP address is: {server:REMOTE_ADDR}</p>
		</body>
	</html>
	```

The page will now include the head.tpl template file and pass on the content data so it too can be populated. You can structure your templates folder however you want and include templates from anywhere within it.

###Template files location

When using the `{template:...}` tag, the template files need to be linked relative to the templates directory in the root. You can change this later if you wish.