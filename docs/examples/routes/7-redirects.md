# Redirects

The routes also allow you to register redirects. They can be either temporary (302) or permanent (301).

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * Here, can have set up a redirect
     * to go from /beta to another URL,
     * which acts as a 302 redirect due
     * to the third parameter not being
     * passed
     * --------------------------------
     */
    Twist::Route() -> redirect( '/beta', 'https://beta.twistphp.com' );
    
    /*
     * --------------------------------
     * This redirect is a 301 permanent
     * redirect which will be cached at
     * so the user's end, so please use
     * with caution!
     * --------------------------------
     */
    Twist::Route() -> redirect( '/github', 'https://github.com/TwistPHP', true );

    Twist::Route() -> serve();
```