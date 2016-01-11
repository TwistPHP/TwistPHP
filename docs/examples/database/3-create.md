# Adding a new table row

Database objects allow the creation of database rows in a simple, OO manner.

## Initialise a new table row

Use the `createRecord()` method in the database class to create an object for insertion into the database.

```php
<?php
    
    /*
     * --------------------------------
     * Create an object of a row in the
     * books table that will house this
     * data
     * --------------------------------
     */
    $book = Twist::Database() -> createRecord( 'books' );
```

## Set the values of the fields

Using the new object, you can set the properties (fields) of the new database row.

```php
<?php

    /*
     * --------------------------------
     * This is an unbelievable book and
     * we really strongly recommend you
     * read it!
     * --------------------------------
     */
    $book -> set( 'title', 'The Hitchhiker\'s Guide to the Galaxy' );
    $book -> set( 'author', 'Douglas Adams' );
    $book -> set( 'isbn10', '0330508539' );
    $book -> set( 'isbn13', '978-0330508537' );
```

## Write the changes back to the database

Once you are ready to commit the new row to the database, simply use the `commit()` method to write the changes. The method will return the auto increment field if there is one, else true if the query was successful.

```php
<?php

    /*
     * --------------------------------
     * Commit the row to the table - if
     * successful, then you will either
     * get returned true or the current
     * value of the autoincrement field
     * else you get false returned
     * --------------------------------
     */
    $intBookID = $book -> commit();
    
    echo $intBook; // 42
```