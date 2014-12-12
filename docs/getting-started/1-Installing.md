#Installing the framework

1. Get the ZIP of TwistPHP framework and extract the folder *twist* into your public_html directory.

2. Create an *index.php* file in the sites root that contains:
	```php
	<?php
		require_once 'twist/framework.php';
	```

	Your directory should now look like this:
	* public_html
		* **twist**
			* ...
		* **index.php**

3. Open your site in a browser and you should be presented with the setup wizard. Follow the steps to set up your framework.