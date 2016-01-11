# Getting data from the databse

The easiest way of getting data out of the database is with the helpers that come with TwistPHP.

## Get a single row

You can get a single row from the database by using the `get()` method. It will always return one row.

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
    $area = Twist::Database() -> get( 'areas', 'PL4 7EX', 'postcode' );
    
    echo $area -> get( 'city' ); // Plymouth
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
    $hatchbacks = Twist::Database() -> find( 'cars', 'layout', 'hatchback' );
    
    foreach( $hatchbacks as $hatchback ) {
        /*
         * --------------------------------
         * Each array item is a separate DB
         * object which can be modified and
         * then committed back to the DB as
         * required
         * --------------------------------
         */
        echo $hatchback -> get( 'model' ); // Fiesta etc.
    }
```

## Get all rows in a table

You can get an array of objects for every row in a table by using the `getAll()` method.

```php
<?php
    
    /*
     * --------------------------------
     * The getAll method should be used
     * sparingly as you may have a huge
     * number of rows in your table
     * --------------------------------
     */
    $devices = Twist::Database() -> getAll( 'devices' );
    
    foreach( $devices as $device ) {
        echo $hatchback -> get( 'make' ); // Nexus etc.
    }
```