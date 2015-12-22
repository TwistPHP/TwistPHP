#Views

Inspired by the simplicity of CodeIgniters templating parser, we managed to create a unique and powerful templating engine of our own.

Create a file called *base.tpl* in your */app/Views* folder and paste in the following:
```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>My First Page</title>
        {resource:arable}
    </head>
    <body>
        <h1>Hello world!</h1>
        <!-- Some examples of template tags -->
        <p>The date is {date:jS F Y} and the time is {date:H:i}.</p>
        <p>Your IP address is: {server:REMOTE_ADDR}</p>
    </body>
</html>
```

Your directory should now look like this:

* public_html
    * app
        * Assets
        * Cache
        * Config
        * Controllers
        * Logs
        * Models
        * Packages
        * Resources
        * Twist
        * Views
            * **base.tpl**
    * twist
        * ...
    * .htaccess
    * index.php

Update your *index.php* file:
```php
<?php
    require_once 'twist/framework.php';

    // Echo the base view
    echo Twist::View() -> build( 'base.tpl' );
```

If you view your site in a browser now, you should see that the view tags have been populated with the relevant data, in this case the date (using the same syntax as the PHP `date()` function) and the `$_SERVER['REMOTE_ADDR']` value.

###Passing parameters

Update your view file:
```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>{data:title}</title>
        {resource:arable}
    </head>
    <body>
        <h1>{data:welcome}</h1>
        <p>The date is {date:Y-m-d} and the time is {date:H:i}.</p>
        <p>Your IP address is: {server:REMOTE_ADDR}</p>
    </body>
</html>
```

If you run this in your browser, there will be blank spaces where there has been no data passed in.

In your *index.php* file, we now need to pass in some data to populate the view with. Insert the data into an array and pass it into the `build()` function as a second parameter:
```php
<?php

    require_once 'twist/framework.php';

    // Create an array of content for the page
    $arrContent = array(
        'title' => 'My First Page',
        'welcome' => 'Hello world, again!'
    );

    // Echo the base view with the content
    echo Twist::View() -> build( 'base.tpl', $arrContent );
```

When run now, the content is inserted into the view.

###Extending your views

The real power behind the template engine after pre-defined variables and content lies in including other files within your view to minimise HTML duplication.

Copy the following HTML into a new *home.tpl* file in your */app/Views* folder:
```html
<h1>Welcome to my site</h1>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
<p>The date is {date:Y-m-d} and the time is {date:H:i}.</p>
<p>Your IP address is: {server:REMOTE_ADDR}</p>
```
* public_html
    * app
        * Assets
        * Cache
        * Config
        * Controllers
        * Logs
        * Models
        * Packages
        * Resources
        * Twist
        * Views
            * base.tpl
            * **pages**
                * **home.tpl**
    * twist
        * ...
    * .htaccess
    * index.php

Now update your *base.tpl* file to read as such:
```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>{data:title}</title>
        <!-- You can include resources like jQuery and Arable -->
        {resource:arable}
    </head>
    <body>
        <!-- Include the home view -->
        {view:pages/home.tpl}
    </body>
</html>
```

The page will now include the home.tpl view. You can structure your views folder however you want and include views from anywhere within it.