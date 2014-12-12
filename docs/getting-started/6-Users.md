#Users

1. Create a *login.tpl* template file with a `{user:login_register_form}` tag in your *templates/pages*. The second parameter is where the login page will redirect you after you successfully logged in, in this case `/count`.
	```html
	<h2>Please Log In</h2>
	{user:login_register_form,/count}
	```

	* public_html
		* templates
			* common
				* head.tpl
			* components
				* count.php
				* fruit.php
			* pages
				* home.tpl
				* **login.tpl**
			* base.tpl
		* twist
			* ...
		* .htaccess
		* index.php

2. In your *index.php*   file, add a route to output your login page and then a restriction for */count* and all the pages underneath it:
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
	    $arrFruitContent = array(
	        'title' => 'My Fruity Page',
	        'welcome' => 'Fruit colours',
	        'meta' => array(
	            'type' => 'description',
	            'value' => 'Eat healthily'
	        )
	    );
	    Twist::Route() -> baseTemplate( 'base.tpl' );
	    Twist::Route() -> template( '/', 'pages/home.tpl', true, false, $arrHomeContent );
	    Twist::Route() -> element( '/count', 'components/count.php,5', true, false, $arrCountContent );
	    Twist::Route() -> element( '/fruit', 'components/fruit.php', true, false, $arrFruitContent );
	    Twist::Route() -> redirect( '/twitter', 'https://twitter.com/' );
	    Twist::Route() -> template( '/login', 'pages/login.tpl' );
	    Twist::Route() -> restrict( '/count/%', '/login' );
	    Twist::Route() -> process();
	```

3. When you now try to visit `/count` in your browser, you will be redirected to the `/login` page. You now need a user account in order to be abe to log in, create an account by clicking the register button.

4. After logging in, you will be redirected to the `/count` page. You can add a link to `/login?logout` which will allow ou to log out of the system.

###No more registrations

After creating a user account, you can revert to just using a standard login form with no register link: `{user:login_form,/count}`