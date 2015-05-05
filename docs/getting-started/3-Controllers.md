#Controllers

Routing requests through a controller allows you to create responses to URIs. By setting up various responses, you can create various pages for your site.

1. Create a new page view, *about.tpl* in a your */app/views/pages* folder:
	```html
	<h1>All about me...</h1>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
	```

2. Create a new controller file in *app/controllers* called *Site.controller.php* that contains:
	```php
	<?php

		// Set the namespace
		namespace App\Controllers;

		// Use the BaseController namespace from TwistPHP
		use Twist\Core\Classes\BaseController;
		// The Error class allows you to return various HTTP responses to the user
		use Twist\Core\Classes\Error;

		// The controller must extend the BaseController from TwistPHP
		class Site extends BaseController {

			// The _index (or _default) function returns when the request URI is simply /
			public function _index() {
				// Set the title of the page
				$this -> _title( 'My First Page' );
				// Optionally, create some content to pass into the view
				$arrContent = array(
					'welcome' => 'Hello world, again!'
				);
				// Return the view to the route with the array content passed in
				return $this -> _view( 'pages/home.tpl', $arrContent );
			}

			// This method is declared in the BaseController, but let's redeclare it
			public function _fallback() {
				// ...how amusing!
				Error::errorPage( 418 );
				// Return false to the route handler
				return false;
			}

			// This method is called when the URI /about is requested
			public function about() {
				$this -> _title( 'All about my site' );
				return $this -> _view( 'pages/about.tpl' );
			}
	```

	Your tree should now look like this:

	* public_html
		* app
			* ajax
			* assets
			* cache
			* config
			* controllers
				* **Site.controller.php**
			* models
			* resources
			* views
				* base.tpl
				* **pages**
					* **home.tpl**
					* **about.tpl**
		* twist
			* ...
		* .htaccess
		* index.php

2. Update your *index.php* file:
	```php
	<?php

		require_once 'twist/framework.php';

		// Set the 'base' view for the site to insert the response into
		Twist::Route() -> baseView( 'base.tpl' );
		// Call the Site controller when any URI is requested - the % is the dynamic part of the request that calls a method
		Twist::Route() -> controller( '/%', 'Site' );
		// Allow the TwistPHP Manager to be used when you go to /manager
		Twist::Route() -> manager();
		// Serve all the registered routes
		Twist::Route() -> serve();
	```

3. Finally update your *base.tpl* file:
	```html
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<title>{meta:title}</title>
			{resource:arable}
		</head>
		<body>
			<ul class="tabs">
				<li><a href="/">Home</a></li>
				<li><a href="/about">About</a></li>
			</ul>
			<!-- Insert the response of the registered routes based on URI -->
			{route:response}
		</body>
	</html>
	```

Your site should now have a welcome page and a counting page as well as a page linking to an external site. You can easily add more pages by extending and reusing the existing syntax.