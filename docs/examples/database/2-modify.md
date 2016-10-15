# Modify table rows

Database objects allow really easy modifying of database rows in a simple, object-oriented manner.

## Get a row from the database

Use the `get()` method in the database class to retrieve an object of a table row.

```php
<?php
    
    /*
     * ================================
     * This query is getting a row with
     * the ID of 84 - another field can
     * be used by inserting it into the
     * second parameter but the default
     * is 'id'
     * ================================
     */
    $film = Twist::Database() -> records( 'films' ) -> get( 84 );
```

## Read and modify the values

Now you are able to get and set any new values using the `get()` and `set()` methods.

```php
<?php
    
    /*
     * ================================
     * It seems they have exhausted any
     * original ideas for classic scifi
     * films so let's now distinguish
     * ================================
     */
    echo $film -> get( 'title' ); // 'Total Recall'
    $film -> set( 'title', 'Total Recall (1990)' );
```

## Write the changes back to the database

Commit the new table the database by calling `commit()` on the database object.

```php
<?php

    /*
     * ================================
     * If the commit is successful, the
     * value returned will be true else
     * it will be false
     * ================================
     */
    $blSuccess = $film -> commit();
```