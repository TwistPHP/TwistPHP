# Creating restrictions

```php
Twist::Route() -> restrict( '/admin/%' );
Twist::Route() -> restrictMembers( '/admin/%' );
Twist::Route() -> restrictAdvanced( '/admin/%' );
Twist::Route() -> restrictAdmin( '/admin/%' );
Twist::Route() -> restrictSuperAdmin( '/admin/%' );
Twist::Route() -> restrictRoot( '/admin/%' );
```

## Unresting a page

You can unrestrict a page, subpage or whole section to allow both logged in and guest access.

```php
Twist::Route() -> unrestrict( '/admin/contact' );
```

## User authentication and login pages

Allowing a user to login in to your website has never been so simple, you will need to extend your admin controller with the baseUser controller.

```php
<?php

    namespace App\Controllers;
        
    use \Twist\Core\Controllers\BaseUser;
    
    class Admin extends BaseUser{
    
        public function _index(){
            return '<h1>Index page</h1>' . print_r( $this -> _route() );
        }
    
        ...
    }
```

The BaseUser controller adds the following pages to your controller. You do not have to extend all your restricted controllers with BaseUser only the ones that you want a login page on

| URI                      | Description                          |
| ------------------------ | ------------------------------------ |
| login                    | String                               |
| change-password          | String                               |
| verify                   | String                               |
| forgotten-password       | String                               |
| device-manager           | String                               |
| authenticate             | String                               |
| logout                   | String                               |

You can add the restricted controller to your index file but you will need to un-restrict some pages in order for a user to be able to login.

```php
<?php

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
    Twist::Route() -> controller( '/admin/%', 'Admin' );
    
    Twist::Route() -> restrict( '/admin/%' );
    
    Twist::Route() -> unrestrict( '/admin/login' );
    Twist::Route() -> unrestrict( '/admin/authenticate' );
    Twist::Route() -> unrestrict( '/admin/forgotten-password' );
```