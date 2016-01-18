# Running an SQL query

Running custom SQL queries is simple, pass the query into the `query()` method and get back a result object.

## Running a query a new table

When running a SQL simply pass the query into the `query()` function and get your result object back

```php
<?php

    $result = Twist::Database() -> query( "SELECT * FROM `devices` WHERE `screen_width` > 720");
    
```

When running a SQL query in the query function you can pass in additional parameters all of which will be automatically escaped, we do this using `sprintf()`. To find out the full syntax see please read PHP's `sprintf()` documentation.

```php
<?php

    $tablet_min_screen_width = 720;
    $tablet_max_screen_width = 1024;
    
    $result = Twist::Database() -> query( "SELECT * FROM `devices` WHERE `screen_width` > %d AND `screen_width` < %d", $tablet_min_screen_width, $tablet_max_screen_width );
    
```

## Using the result object

Now we can check to see that the query ran successfully and that we have some results, if we do we can run though each tablet and output its name

```php
<?php

    if($result->status() && $result->numberRows()){
        
        foreach($result->getFullArray() as $device){
            echo "Tablet Device: ".$device['name']
        }
    }else{
        echo "No Tablets Found!";
    }
    
```

## Result object functionality

When using the result object you will get a range of functionality, see below for a full list.

| Method                   | Type     | Description                                               |
| ------------------------ | -------- | --------------------------------------------------------- |
| `status()`               | Boolean  | Status of the SQL query (true if successfully run)        |
| `sql()`                  | String   | SQL statement that was run to produce the result          |
| `insertID()`             | Integer  | ID of the newly inserted row (INSERT queries Only)        |
| `affectedRows()`         | Integer  | Number of rows affected/changed by the query              |
| `numberRows()`           | Integer  | Number of rows returned by the query (SELECT only)        |
| `getArray()`             | Array    | Single dimensional field value array of returned row      |
| `getFullArray()`         | Array    | All rows returned in multi-dimensional field value array  |
| `errorNo()`              | Integer  | Error number in relation to a failed query (status false) |
| `errorMessage()`         | String   | Error message in relation to a failed query (status false)|
