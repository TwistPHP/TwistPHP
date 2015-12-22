#Manual Installation

Download a copy of the TwistPHP framework from the our website, you will find the latest version on our homepage.

Extract the archive and copy the contents of the /dist folder into the `public_html` directory of your website.

Your directory should now look like this:

* public_html
    * twist
        * ...
    * .htaccess
    * index.php
    
Create the folder path `app/Config` in your `public_html` directory

Copy the file `public_html/twist/Config/default.php` into the `public_html/app/Config` directory.

Rename the file `public_html/app/Config/default.php` to `public_html/app/Config/config.php`

Open the `public_html/app/Config/config.php` file in your favourite editor and edit accordingly

    * If you do not require a database uncomment 'TWIST_DATABASE_PROTOCOL' and set it to JSON.
        `Twist::define('TWIST_DATABASE_PROTOCOL','json');`
    * If you are using an MySQL database uncomment all options and ensure to set 'TWIST_DATABASE_NAME','TWIST_DATABASE_USERNAME' and 'TWIST_DATABASE_PASSWORD'
    
Edit your index file `public_html/index.php`

```php
    
    /* ================================================================================
     * TwistPHP - Default index.php
     * --------------------------------------------------------------------------------
     * Author:          Shadow Technologies Ltd.
     * Documentation:   https://twistphp.com/docs
     * ================================================================================
     */

    define('TWIST_PUBLIC_ROOT',dirname(__FILE__));
    define('TWIST_APP',dirname(__FILE__).'/app');
    define('TWIST_PACKAGES',dirname(__FILE__).'/packages');
    define('TWIST_UPLOADS',dirname(__FILE__).'/uploads');

    require_once 'twist/framework.php';

    Twist::ServeRoutes(false);
   
```