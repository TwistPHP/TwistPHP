# URI variables

If you need to catch variables in your requests then you can just add the variables you want to catch in your registered URI.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * Any request URI that begins with
     * vars will match this rule and be
     * sent on to the MySite controller
     * --------------------------------
     * URI           | METHOD | myVar
     * /vars         | _index | ''
     * /vars/alpha   | _index | 'alpha'
     * /vars/x       | _index | 'x'
     * /vars/x/alpha | alpha  | 'x'
     * /vars/x/beta  | beta   | 'x'
     * /vars/x/gamma | gamma  | 'x'
     * /vars/y       | _index | 'y'
     * /vars/y/alpha | alpha  | 'y'
     * /vars/y/beta  | beta   | 'y'
     * /vars/y/gamma | gamma  | 'y'
     * --------------------------------
     */
    Twist::Route() -> controller( '/vars/{myVar}/%', '' );

    Twist::Route() -> serve();
```

The variables can be retrieved within your controller with the `_var()` method:

```php
<?php
    
    /*
     * --------------------------------
     * This will return you an array of
     * all the caught variables in your
     * requested URI
     * --------------------------------
     */
    $this -> _var();
    
    /*
     * --------------------------------
     * To get just one of the variables
     * all you need do is pass its name
     * as the methods first parameter
     * --------------------------------
     */
    $this -> _var( 'myVar' );
```

## Route variables

You can access the route variables using the `_route()` method in your controller. Pass in the key as the first parameter to get a single value, e.g. `$this -> _route( 'base_uri' );`.

| Key                      | Type    | Description                                                  | Example                                  |
| ------------------------ | ------- | ------------------------------------------------------------ | ---------------------------------------- |
| `base_url`               | String  | URL of the site requested                                    | `'https://twistphp.com'`                 |
| `url`                    | String  | Full URL of the page requested                               | `'https://twistphp.com/examples/routes'` |
| `base_uri`               | String  | Base URI set                                                 | `'/'`                                    |
| `uri`                    | String  | URI portion of the URL requested                             | `'/examples/routes'`                     |
| `registered_uri`         | String  | URI that features in the registered route                    | `'/examples/%'`                          |
| `registered_uri_current` | String  | Current matched URI                                          | `'/examples/routes'`                     |
| `dynamic`                | String  | Dynamic part of your URI is the `%` that relates to a method | ?                                        |
| `parts`                  | Array   | The 'parts' that make up your URI (exploded by `/`)          | `Array( [0] => 'debug' )`                |
| `vars`                   | Array   | Variables caught in the registered route                     | `Array( [version] => '3.0.0' )`          |
| `wildcard`               | Boolean | Does the URI have a wildcard `%`?                            | `1`                                      |
| `regx`                   | String  | ?                                                            | ?                                        |
| `type`                   | String  | Type of route that was matched                               | `'controller'`                           |
| `method`                 | String  | Controller method verb matched (ANY, GET, POST, DELETE, PUT) | `'ANY'`                                  |
| `request_method`         | String  | HTTP verb requested (ANY, GET, POST, DELETE, PUT)            | `'GET'`                                  |
| `item`                   | Array   | ?                                                            | ?                                        |
| `data`                   | Array   | ?                                                            | ?                                        |
| `model`                  | String  | ?                                                            | ?                                        |
| `base_view`              | Boolean | Registered base view file                                    | `1`                                      |
| `cache`                  | Boolean | ?                                                            | ?                                        |
| `cache_key`              | String  | ?                                                            | ?                                        |
| `cache_life`             | Integer | ?                                                            | ?                                        |
| `title`                  | String  | ?                                                            | `'Route Examples - TwistPHP'`            |
