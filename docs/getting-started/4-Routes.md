#Routes

Routes allow you to create responses to URIs. By setting up various responses, you can create an array of pages for your site.

1. Create a new template - *home.tpl* in a new pages directory within your *templates* folder:
	```html
	<h2>Welcome to my site</h2>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
	```

	Your tree should now look like this:

	* public_html
		* templates
			* common
				* head.tpl
			* components
				* count.php
			* **pages**
				* **home.tpl**
			* base.tpl
		* twist
			* ...
		* .htaccess
		* index.php

2. Update your *index.php* file:
	```php
	<?php
		require_once 'twist/framework.php';
		$arrHomeContent = array(
			'title' => 'My First Page',
			'welcome' => 'Hello world, again!',
			'meta' => array(
				'type' => 'description',
				'value' => 'An awesome site'
			)
		);
		$arrCountContent = array(
			'title' => 'My Second Page',
			'welcome' => 'This is how I count',
			'meta' => array(
			'type' => 'description',
			'value' => 'I can count!'
			)
		);
		Twist::Route() -> baseTemplate( 'base.tpl' );
		Twist::Route() -> template( '/', 'pages/home.tpl', true, false, $arrHomeContent );
		Twist::Route() -> element( '/count', 'components/count.php,5', true, false, $arrCountContent );
		Twist::Route() -> redirect( '/twitter', 'https://twitter.com/' );
		Twist::Route() -> serve();
	```

3. Finally update your *base.tpl* file:
	```html
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		{template:common/head.tpl}
		<body>
			<h1>{data:welcome}</h1>
			<ul>
				<li><a href="/">Home</a></li>
				<li><a href="/count">Countdown</a></li>
				<li><a href="/twitter">Twitter</a></li>
			</ul>
			{route:response}
			<p>You requested {route:request} which used the item {route:response_item} with the array {route:data}</p>
		</body>
	</html>
	```

Your site should now have a welcome page and a counting page as well as a page linking to an external site. You can easily add more pages by extending and reusing the existing syntax.