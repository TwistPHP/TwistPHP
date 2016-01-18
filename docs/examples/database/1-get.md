# Getting data from the database

The easiest way of getting data out of the database is with the helpers that come with TwistPHP.

The `records()` method returns an object designed to make working with your database OO (and easy!). It accepts two parameters, the first being the table name and optionally the second parameter is the database name.
Parameter two by default is set to the value of the 'TWIST_DATABASE_NAME' config variable.

## Get a single row

You can get a single row from the database as an object by using the `get()` method. It will always return one row.

```php
<?php
    
    /*
     * --------------------------------
     * The get method will find the one
     * row that matches your string and
     * in the field that you specified,
     * for example this will return the
     * one area that has the value 'PL4
     * 7EX' for the postcode
     * --------------------------------
     */
    $area = Twist::Database() -> records( 'areas' ) -> get( 'PL4 7EX', 'postcode' );
    
    echo $area -> get( 'city' ); // Plymouth
```

Alternatively you can get a single row as an array rather than an object, to do this pass in `true` as a third paramter.
 
```php
 <?php
     
     /*
      * --------------------------------
      * Passing in the third parameter
      * of true will return an array as
      * the result rather than an object
      * --------------------------------
      */
     $area = Twist::Database() -> records( 'areas' ) -> get( 'PL4 7EX', 'postcode', true );
     
     echo $area['city']; // Plymouth
 ```

## Get multiple rows

When using the `find()` method, all the rows that match your string are returned in an array.

```php
<?php
    
    /*
     * --------------------------------
     * In reality, we hope you use link
     * tables to store data such as the
     * car's layout - we have just done
     * this as an example
     * --------------------------------
     */
    $hatchbacks = Twist::Database() -> records( 'cars' ) -> find( 'layout', 'hatchback' );
    
    foreach( $hatchbacks as $hatchback ) {
        /*
         * --------------------------------
         * Each array item is a separate DB
         * object which can be modified and
         * then committed back to the DB as
         * required
         * --------------------------------
         */
        echo $hatchback['model']; // Fiesta etc.
    }
```

## Get all rows in a table

You can get an array of all the rows in the table by using the `find()` method, passing no parameters will return everything.

```php
<?php
    
    /*
     * --------------------------------
     * The getAll method should be used
     * sparingly as you may have a huge
     * number of rows in your table
     * --------------------------------
     */
    $devices = Twist::Database() -> records( 'devices' ) -> find();
    
    foreach( $devices as $device ) {
        echo $device['make']; // Nexus etc.
    }
```