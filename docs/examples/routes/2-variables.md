# URI variables

If you need to catch variables in your requests then you can just add the variables you want to catch in your registered URI.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * --------------------------------
     * Any request URI that begins with
     * shop will match this rule and be
     * sent on to the MySite controller
     * --------------------------------
     * URI       | METHOD | _var( 'p' )
     * /vars     | _index | undefined
     * /vars/x   | _index | x
     * /vars/x/a | alpha  | x
     * /vars/x/b | beta   | x
     * /vars/x/c | gamma  | x
     * /vars/y   | _index | y
     * /vars/y/a | alpha  | y
     * /vars/y/b | beta   | y
     * /vars/y/c | gamma  | y
     * --------------------------------
     */
    Twist::Route() -> controller( '/vars/{p}/%', '' );

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

| Key                      | Type    | Description                   | Example                                            |
| ------------------------ | ------- | ----------------------------- | -------------------------------------------------- |
| `base_url`               | String  | ?                             | `'https://twistphp.com'`                           |
| `url`                    | String  | ?                             | `'https://twistphp.com/examples/routes/variables'` |
| `base_uri`               | String  | ?                             | ?                                                  |
| `uri`                    | String  | ?                             | `'/examples/routes/variables'`                     |
| `registered_uri`         | String  | ?                             | `'/examples'`                                      |
| `registered_uri_current` | String  | ?                             | `'variables'`                                      |
| `dynamic`                | String  | ?                             | ?                                                  |
| `parts`                  | Array   | ?                             | `Array( [0] => debug )`                            |
| `vars`                   | Array   | ?                             | ?                                                  |
| `wildcard`               | Boolean | ?                             | `1`                                                |
| `regx`                   | String  | ?                             | ?                                                  |
| `type`                   | String  | ?                             | `'controller'`                                     |
| `method`                 | String  | (ANY, GET, POST, DELETE, PUT) | `'ANY'`                                            |
| `request_method`         | String  | (ANY, GET, POST, DELETE, PUT) | `'GET'`                                            |
| `item`                   | Array   | ?                             | ?                                                  |
| `data`                   | Array   | ?                             | ?                                                  |
| `model`                  | String  | ?                             | ?                                                  |
| `base_view`              | Boolean | ?                             | `1`                                                |
| `cache`                  | Boolean | ?                             | ?                                                  |
| `cache_key`              | String  | ?                             | ?                                                  |
| `cache_life`             | Integer | ?                             | ?                                                  |
| `title`                  | String  | ?                             | `'Route Variables Code Examples - TwistPHP'`       |