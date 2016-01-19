# Installing a Package

Packages can be found on the packages page in your framework manager. All packages come with a one click install option, package files can be manually placed in the package folder.
 
## Install using the package manager

In the package manager you will first see all available packages listed with a download button next to each. Clicking the download button will download the code to the packages folder but will not install the package.
The package will not appear in the installed packages list at the top of the page, here you will be able to install/uninstall the package with a single click.

Find and install the "ExampleContactForm" package.

## Manually install a package

If you have a package that is not available in the package manager or need to manually install the it for any reason you will need to copy the package into your packages folder.

Copy `ExampleContactForm\dist\ExampleContactForm` into your `public_html/packages` folder.

Once you have placed your package in the packages folder you can either install the package through the manager or install using the below code.

```php
<?php
	
	Twist::framework()->package()->installer('ExampleContactForm');
		
```

Now the package is installed you can remove the above line of code.

## Using the installed package

Packages can be used in may different ways depending on what they contain and its intended purpose. In this example we are using the "ExampleContactForm" package which provides as very basic contact form, alert email and database storage.

To use this particular package you will need to register a route, we will use `/contact` as out route. To do this edit you `public_html/index.php` file and add the following line of code above your `Routes::ServeAll();`.

```php
<?php

    \Twist::Route()->controller('/contact/%s','\Packages\ExampleContactForm\Controllers\ContactForm');

```

Now when navigating to `/contact` on your website you will see the contact form. Filling out the form will take you to a thank you page. 
