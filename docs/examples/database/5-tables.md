# Create new database tables

You can use TwistPHP to add and edit mySQL tables in a simple, object-oriented manner.

The `tables()` method returns an object with methods like `exists()`, `structure()`, `copy()` and `rename()` that deals with the superficial functionality.

## Get a table object

```php
<?php

    /*
     * --------------------------------
     * Get access to a top-level tables
     * object from the database in this
     * case the 'fruit' table which may
     * or may not exist
     * --------------------------------
     */
    $table = Twist::Database() -> table( 'fruit' );
    
    $fruit = null;
    
    /*
     * --------------------------------
     * Check to see if the table exists
     * --------------------------------
     */
    if( $table -> exists() ) {
        /*
         * --------------------------------
         * Get a proper table object of the
         * 'fruit' table which we will then
         * be able to add more keys, fields
         * and indexes to
         * --------------------------------
         */
        $fruit = $table -> get();
    } else {
        /*
         * --------------------------------
         * Go and get an object of your new
         * table that you can then start to
         * add fields and parameters to
         * --------------------------------
         */
        $fruit = $table -> create();
    }
```


The second parameter of the `table()` method is optional. It is the name of the database which by default is set to the value of the `TWIST_DATABASE_NAME` config variable.

## Set up the table's data

You can create database fields to the table object using the `addField()` method of the database table object.

```php
<?php

    /*
     * --------------------------------
     * Now we can add some fields using
     * the three parameters field name,
     * type and max length
     * --------------------------------
     */
    $fruit -> addField( 'id', 'int', 11 );
    $fruit -> addField( 'name', 'char', '64' );
    $fruit -> addField( 'colour', 'char', '16' );
    
    /*
     * --------------------------------
     * ...and now set one of the fields
     * to be the auto incrementing one
     * --------------------------------
     */
    $fruit -> autoIncrement( 'id' );
    
    /*
     * --------------------------------
     * We can set either a single field
     * or an array of fields to our new
     * unique key
     * --------------------------------
     */
    $fruit -> addUniqueKey( 'uniquename', ['name'] );
    
    /*
     * --------------------------------
     * Add an index to the colour field
     * that will allow a more efficient
     * search when filtering by colour
     * --------------------------------
     */
    $fruit -> addIndex( 'colour' );
    
    /*
     * --------------------------------
     * Just so that when we review this
     * database table in the future, we
     * will hopefully be able to figure
     * out what we were thinking
     * --------------------------------
     */
    $fruit -> comment( 'This is where the fruit lives' );
```

## Save the table

To commit the table back to the database, simply use the `commit()` method on the database table object.

```php
<?php

    /*
     * --------------------------------
     * We can echo out the SQL query if
     * needed so that we can execute it
     * elsewhere
     * --------------------------------
     */
    echo $fruit -> sql();
    
    /*
     * --------------------------------
     * Write the table to the database
     * --------------------------------
     */
    $fruit -> commit();
```