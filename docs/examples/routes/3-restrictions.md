# Creating restrictions

TwistPHP allows you to easily restrict certain areas of your site to any of the preset user levels:

* Members (have an active account in the `users` database table)
* Advanced ('premium' members that may require more access than a standard member)
* Admin (you can use this to allow access to tools that administer site content, for example)
* Super admin (the highest level of general access)
* Developer (root level accounts which have unrestricted access to everything)

## Using the TwistPHP user controller

Instead of the standard base controller (`\Twist\Core\Controllers\Base`) that all your controllers should extend, for restricted areas of the site you need to extend the TwistPHP base user controller, `\Twist\Core\Controllers\BaseUser`.

The BaseUser controller comes with several methods to facilitate login forms, forgotten password screens and various other features:

| Method/URI             | Description                                                                                  |
| ---------------------- | -------------------------------------------------------------------------------------------- |
| `login()`              | Display a login form and handle the various post requests needed to log in                   |
| `change-password()`    | The change password form, used when the user is logged in but wants to change their password |
| `verify()`             | If user verification is enabled, this displays the verification screen                       |
| `forgotten-password()` | A form to enter an email address into when the user has forgotten their password             |
| `device-manager()`     | Shows a list of all the users logged in devices with the ability to delete open sessions     |
| `authenticate()`       | The method used to authenticate logins and other posts                                       |
| `logout()`             | Logs the user out                                                                            |

Your user controller should look something like this:

```php
<?php

    /*
     * --------------------------------
     * Same namespace as before - it is
     * all just your controllers is the
     * app namespace
     * --------------------------------
     */
    namespace App\Controllers;
    
    /*
     * --------------------------------
     * Use the BaseUser controller that
     * comes with TwistPHP
     * --------------------------------
     */
    use \Twist\Core\Controllers\BaseUser;
    
    /*
     * --------------------------------
     * Extend the BaseUser class so you
     * can inherit all the user goodies
     * --------------------------------
     */
    class AdminArea extends BaseUser {
    
        /*
         * --------------------------------
         * For any custom methods it's just
         * business as usual
         * --------------------------------
         */
        public function _index() {
            return '<h1>Welcome to the restricted area</h1>';
        }
    
        public function help() {
            return '<h1>Don\'t panic!</h1>';
        }
        
    }
```

## Applying restrictions to routes

You can apply your restrictions after you are done registering your routes in your project's `index.php` file. 


```php
<?php

    require_once( 'twist/framework.php' );
    
    /*
     * --------------------------------
     * Let's register the new AdminArea
     * controller for any requests that
     * start with /admin
     * --------------------------------
     */
    Twist::Route() -> controller( '/admin/%', 'AdminArea' );

    /*
     * --------------------------------
     * Now we are going to restrict the
     * access for anyone requesting our
     * admin area and an added redirect
     * if they attempt access - default
     * level for admin users is 30, but
     * this can quite easily be changed
     * in the settings database table
     * --------------------------------
     */
    Twist::Route() -> restrictAdmin( '/admin/%', '/admin/login' );
    
    /*
     * --------------------------------
     * These are all the standard level
     * restrictions that are available,
     * although developers with a level
     * 0 account are able to access all
     * restricted areas after they have
     * logged in
     * --------------------------------
     */
    //Twist::Route() -> restrict( '/admin/%' ); // Must be a valid user
    //Twist::Route() -> restrictMembers( '/admin/%' ); // Must be a valid user, at least level 10
    //Twist::Route() -> restrictAdvanced( '/admin/%' ); // Must be a valid user, at least level 20
    //Twist::Route() -> restrictAdmin( '/admin/%' ); // Must be a valid user, at least level 30
    //Twist::Route() -> restrictSuperAdmin( '/admin/%' ); // Must be a valid user, at least level 40
    //Twist::Route() -> restrictRoot( '/admin/%' ); // Only valid root users (level 0) can access
    
    /*
     * --------------------------------
     * When we are ready, go go go!
     * --------------------------------
     */
    Twist::Route() -> serve();
```

### Unrestricting certain URIs

If needed, you can unrestrict a page, sub page or a whole area of your project to allow open access.

```php
<?php

    /*
     * --------------------------------
     * Here we want the help page to be
     * access by everyone, not just the
     * users who are logged in
     * --------------------------------
     */
    Twist::Route() -> unrestrict( '/admin/help' );
```

In order to allow the user to log in, we must unrestrict some pages:

```php
<?php
    
    Twist::Route() -> unrestrict( '/admin/login' );
    Twist::Route() -> unrestrict( '/admin/authenticate' );
    Twist::Route() -> unrestrict( '/admin/forgotten-password' );
```