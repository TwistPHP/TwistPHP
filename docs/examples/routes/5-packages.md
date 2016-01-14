# Using packages

Packages for TwistPHP should come with their own set of routes. You can register this route set under a URI using the `package()` method.

## An example package

In this example, the package's folder name is 'MyCMS' and is placed in the `/packages` folder.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * This example CMS package has two
     * route sets, FrontEnd and BackEnd
     * which we can register to the two
     * URIs / and /cms respectively
     * --------------------------------
     */
    Twist::Route() -> package( '/%', '\Packages\MyCMS\Routes\FrontEnd' );
    Twist::Route() -> package( '/cms/%', '\Packages\MyCMS\Routes\BackEnd' );

    Twist::Route() -> serve();
```