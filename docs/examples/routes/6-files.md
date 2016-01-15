# Serving files and folders

You can use routes to serve files and folders with nice URIs.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * We can serve a file by way of an
     * 'alias' URI which means the real
     * is safely hidden from the world,
     * in this case the URI /my-file is
     * a nice alias to a file elsewhere
     * on your server, with the options
     * to serve your file under another
     * filename
     * --------------------------------
     */
    Twist::Route() -> file( '/my-file', '/path/to/my/file.zip' );
    Twist::Route() -> file( '/my-other-file', '/yuk/Horibley-NAmed-f1le!_2015_09_24.tar', 'nice-filename.zip' );

    Twist::Route() -> serve();
```

Make the contents of a folder that may or may not already be in your public root available through the browser

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * Using exactly the same method as
     * above we can serve a folder to a
     * user by 'aliasing' the directory
     * with a nice URI
     * --------------------------------
     */
    Twist::Route() -> file( '/downloadables/%', '/path/to/my/downloadables/folder' );

    Twist::Route() -> serve();
```