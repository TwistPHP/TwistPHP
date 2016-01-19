# Running a normal SQL query

While the database objects in TwistPHP provide a huge range of functionality, there will be that odd occasion you need to run an 'old skool' query.

## Running a query

To run a SQL statement, simply pass the query into the `query()` method to get back an object.

```php
<?php

    /*
     * --------------------------------
     * This very nice query returns you
     * an object with all the requested
     * data wrapped in some methods
     * --------------------------------
     */
    $query = Twist::Database() -> query( "SELECT `subscribers`.`email`
                                            FROM `subscribers`
                                            WHERE `subscribers`.`joined` >= '2014-01-01'" );
    
```

### Escaping variables

When using the `query()` method, you can pass in additional parameters, similar to the [`sprintf()`](http://php.net/manual/en/function.sprintf.php) function, all of which will be automatically escaped.

```php
<?php
    
    /*
     * --------------------------------
     * Who knows what could be in these
     * variables - let's make sure they
     * are escaped (also, make sure you
     * don't construct queries with GET
     * values like this!)
     * --------------------------------
     */
    $query = Twist::Database() -> query( "SELECT `subscribers`.`email`
                                            FROM `subscribers`
                                            WHERE `subscribers`.`joined` >= '%s'
                                                AND `subscribers`.`joined` < '%s'",
                                        $_GET['from'],
                                        $_GET['to'] );
    
```

## The `query` object

The object returned from the `query()` method has the following methods:

| Method           | Returns | Description                                                                                        |
| ---------------- | ------- | -------------------------------------------------------------------------------------------------- |
| `status()`       | Boolean | Status of the SQL query (`true` if successfully run, `false` otherwise)                            |
| `sql()`          | String  | The compiled SQL statement that was run                                                            |
| `insertID()`     | Integer | The ID of the newly inserted row (on `INSERT` queries only)                                        |
| `affectedRows()` | Integer | Number of rows affected or changed by the query                                                    |
| `numberRows()`   | Integer | Number of rows returned by the query (on `SELECT` queries only)                                    |
| `row()`          | Array   | Array of key/value pairs of a single returned row (pass a row number in to get that row - 0-based) |
| `rows()`         | Array   | Multidimensional array of all row values in key/value pairs                                        |
| `errorNo()`      | Integer | Error number returned on a failed query                                                            |
| `errorMessage()` | String  | Error message in relation to a failed query                                                        |


### Using the `query` object

Now we can check to see that the query ran successfully and that we have some results. If so, we can iterate through each returned row.

```php
<?php

    /*
     * --------------------------------
     * If the query was successful, and
     * there were rows returned then go
     * through each row, before using a
     * seemingly random 42nd row result
     * --------------------------------
     */
    if( $query -> status() ) {
        if( $query -> numberRows() ) {
            foreach( $query -> rows() as $subscriber ) {
                echo 'Subscriber: ' . $subscriber['email'];
            }
            
            $query -> row( 41 ) // Get the 42nd result (if it exists, 0-based array)
        } else {
            echo 'No subscribers';
        }
    } else {
        echo 'The query failed';
    }
    
```

## Escaping strings

If you are manually building your SQL query and need to escape any data before adding it to a query you can use the `escapeString()` method.

```php
<?php

    /*
     * --------------------------------
     * Escape the string allowing it to
     * safely be used in a query
     * --------------------------------
     */
    $escaped = \Twist::Database() -> escapeString( "Robert'); DROP TABLE Students;--" ); // Little Bobby Tables
    
```