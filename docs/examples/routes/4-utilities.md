# TwistPHP utilities

Included in the framework are several utilities that you can register for use.

## TwistPHP manager

To use the TwistPHP management GUI, just register the route with the `manager()` method.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * By default this method registers
     * itself to the URI /manager - but
     * you can change this by passing a
     * URI into the method's parameter
     * --------------------------------
     */
    Twist::Route() -> manager(); // Available at /manager
    Twist::Route() -> manager( '/admin' ); // Available at /admin

    Twist::Route() -> serve();
```

## Image placeholders

TwistPHP features the ability to generate image placeholders on the fly.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * By default this method registers
     * itself to the URI /placeholder -
     * but just as with other utilities
     * this can be changed by passing a
     * URI into the method's parameter
     * --------------------------------
     */
    Twist::Route() -> placeholder(); // Placeholders begin with /placeholder
    Twist::Route() -> placeholder( '/image' ); // Placeholders begin with /image

    Twist::Route() -> serve();
```

Images can now be generated using a URI like `/placeholder?width=960&height=540`.

## TwistPHP upload handler

When using the built-in TwistPHP JS uploader, you can register a location for the uploads to be handled.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * As before there is a default URI
     * of /upload that can quite easily
     * be changed
     * --------------------------------
     */
    Twist::Route() -> upload(); // Uploads are handled by /upload
    Twist::Route() -> upload( '/process' ); // Uploads are handled by /process

    Twist::Route() -> serve();
```