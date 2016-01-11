#Quick Installation

Download a copy of the TwistPHP framework from the our website, you will find the latest version on our homepage.

Extract the archive and copy the contents of the `/dist` folder into the `public_html` directory of your website.

Your directory should now look like this:

* public_html
    * twist
        * ...
    * .htaccess
    * index.php
    
Edit your index file `public_html/index.php` and add the following code:

```php
    
    <?php
    
    define("TWIST_QUICK_INSTALL", json_encode(array(
        'database' => array(
            'type' => 'database',
            'protocol' => 'mysqli',
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'name' => 'travis_ci_twist_test',
            'table_prefix' => 'twist_'
        ),
        'settings' => array(
            'site_name' => 'Travis CI Test',
            'site_host' => 'localhost',
            'site_www' => '0',
            'http_protocol' => 'http',
            'http_protocol_force' => '0',
            'timezone' => 'Europe/London',
            'relative_path' => dirname(__FILE__).'/',
            'site_root' => '',
            'app_path' => 'app',
            'packages_path' => 'packages',
            'uploads_path' => 'uploads'
        ),
        'user' => array(
            'firstname' => 'Travis',
            'lastname' => 'CI',
            'email' => 'unittest@traviscit.test',
            'password' => 'travisci',
            'confirm_password' => 'travisci'
        )
    )));
    
    require_once 'twist/framework.php';
   
```

In your web browser navigate to the root of your website and your framework will be automatically installed using the settings you have provided.