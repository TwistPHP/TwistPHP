# Getting data from the database

The easiest way of getting data out of the database is with the helpers that come with TwistPHP.

Using the `records()` method, you are given an object with all the functionality to read and write data to the database.

It accepts two parameters, the first being the table name and optionally the second parameter is the database name, which by default is set to the value of the `TWIST_DATABASE_NAME` config variable.

All data passed in to any of the `records()` methods is automatically escaped before being used in an SQL query.

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
     * one area in the areas table that
     * matches the postcode field value
     * of 'PL4 7EX'
     * --------------------------------
     */
    $area = Twist::Database() -> records( 'areas' ) -> get( 'PL4 7EX', 'postcode' );
    
    echo $area -> get( 'city' ); // The 'city' field of this row object
```

Alternatively you can get a single row as an array rather than an object, to do this pass in `true` as a third parameter.
 
```php
 <?php
     
     /*
      * --------------------------------
      * Passing a value of 'true' to the
      * third parameter returns an array
      * with the results, rather than an
      * object
      * --------------------------------
      */
     $area = Twist::Database() -> records( 'areas' ) -> get( 'PL4 7EX', 'postcode', true );
     
     echo $area['city']; // The row data is now in an array
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
         * The returned value for find() is
         * an array of values which you can
         * iterate through
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
     * Exercise caution using the all()
     * method, as you may be returned a
     * huge number of rows depending on
     * your database size
     * --------------------------------
     */
    $likes = Twist::Database() -> records( 'likes' ) -> all();
    
    count( $likes ) // We hope you get loads!
```