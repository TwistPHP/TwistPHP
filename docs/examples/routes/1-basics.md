# Routing basics

When registering controllers in your project's `index.php` file, you need two variables: the path to match and the controller to pass the request on to.

For this example, our controller looks like this:

```php
<?php

    namespace App\Controllers;
    
    use \Twist\Core\Controllers\Base;
    
    class MySite extends Base {
    
        public function _index() {
            return '<h1>Index page</h1>' . print_r( $this -> _route() );
        }
    
        public function alpha() {
            return '<h1>Page A</h1>' . print_r( $this -> _route() );
        }
    
        public function beta() {
            return '<h1>Page B</h1>' . print_r( $this -> _route() );
        }
    
        public function gamma() {
            return '<h1>Page C</h1>' . print_r( $this -> _route() );
        }
        
    }
```

## Registering routes

You will most likely need a controller that handles the main pages of your project such as the index page. To do this, simply register the route `/%` which will pass any URI that is requested will be sent to the controller specified. The `%` is the wildcard that will match the method in the controller.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * Register the following routes to
     * allow all requests to be handled
     * by the MySite controller
     * --------------------------------
     * URI | METHOD
     * /   | _index
     * /a  | alpha
     * /b  | beta
     * /c  | gamma
     * --------------------------------
     */
    Twist::Route() -> controller( '/%', 'MySite' );
```

To specify controllers for different areas of the site just update the URI to match in the registration method.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * Any request URI that begins with
     * shop will match this rule and be
     * sent on to the MySite controller
     * --------------------------------
     * URI     | METHOD
     * /shop   | _index
     * /shop/a | alpha
     * /shop/b | beta
     * /shop/c | gamma
     * --------------------------------
     */
    Twist::Route() -> controller( '/shop/%', 'MySite' );
```

## Serving the registered routes

To serve your app's registered routes, simply add the following line to your project's `index.php` file:

```php
<?php

    Twist::Route() -> serve();
```