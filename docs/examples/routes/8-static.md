# Serving static content

For the most part, content should be served through controllers. However, on the odd occasion you need to serve content statically, you have several methods at your disposal.

## Views

Respond directly to requests with a view file. Less dynamic than a controller, but good for use in tight situations.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * ================================
     * This method simply registers the
     * feedback.tpl view to be returned
     * when a GET request for /feedback
     * is made
     * ================================
     */
    Twist::Route() -> view( '/feedback', 'feedback.tpl' );
    
    /*
     * ================================
     * Use the thankyou.tpl view when a
     * POST request is received for the
     * URI /feedback
     * ================================
     */
    Twist::Route() -> postView( '/feedback', 'thankyou.tpl' );

    Twist::Route() -> serve();
```

## Verb functions

A really basic way to respond to requests is just define a function directly routing registration.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * ================================
     * Respond to a GET request for the
     * URI /testing with an appropriate
     * response
     * ================================
     */
    Twist::Route() -> get( '/testing',
        function() {
            return '1...2...3';
        }
    );
    
    /*
     * ================================
     * This function is used to respond
     * to POST requests to /testing
     * ================================
     */
    Twist::Route() -> post( '/testing',
       function() {
           return 'Thanks for the $_POST!';
       }
    );

    Twist::Route() -> serve();
```