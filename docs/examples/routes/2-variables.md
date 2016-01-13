# URI variables

If you need to catch variables in your requests then you can just add the variables you want to catch in your registered URI.

```php
<?php

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
```

The variables can be retrieved within your controller with the `_var()` method:

```php
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

You can access the route variables using the `_route()` method in your controller:

| Key                      | Type                           | Description | Example |
| ------------------------ | ------------------------------ | ----------- | ------- |
| `regx`                   | String                         | ? | ? |
| `registered_uri`         | String                         | ? | ? |
| `registered_uri_current` | String                         | ? | ? |
| `base_uri`               | String                         | ? | ? |
| `dynamic`                | String                         | ? | ? |
| `parts`                  | Array                          | ? | ? |
| `uri`                    | String                         | ? | ? |
| `url`                    | String                         | ? | ? |
| `method`                 | String ANY/GET/POST/DELETE/PUT | ? | `'ANY'` |
| `request_method`         | String ANY/GET/POST/DELETE/PUT | ? | `'GET'` |
| `type`                   | String                         | ? | `'controller'` |
| `item`                   | Array                          | ? | ? |
| `data`                   | Array                          | ? | ? |
| `model`                  | String                         | ? | ? |
| `base_view`              | Integer                        | ? | ? |
| `wildcard`               | Integer                        | ? | ? |
| `cache`                  | String                         | ? | ? |
| `cache`                  | String                         | ? | ? |
| `cache_key`              | String                         | ? | ? |
| `cache_life`             | Integer                        | ? | ? |
| `title`                  | String                         | ? | ? |
| `vars`                   | Array                          | ? | ? |