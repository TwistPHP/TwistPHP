# Create new database tables

TwistPHP uses its own database object to add and edit mySQL tables.

The `tables()` method returns an object designed to make working with your database tables OO (and easy!). It accepts two parameters, the first being the table name and optionally the second parameter is the database name.
Parameter two by default is set to the value of the 'TWIST_DATABASE_NAME' config variable.

## Create a new table

To create a new table, grab a copy of the database object and use the method `create()` to return a new database table object.

```php
<?php

    $resFruit = Twist::Database() -> tables( 'fruit' ) -> create();
```

## Add the fields

You can create database fields to the table object using the `addField()` method of the database table object.

```php
<?php

    $resFruit -> addField( 'id', 'int', 11 );
    $resFruit -> addField( 'name', 'char', '64' );
    $resFruit -> addField( 'colour', 'char', '16' );
```

### Set an auto increment field

An auto increment field can be set with the `setAutoIncrement()` method.

```php
<?php

    $resFruit -> setAutoIncrement( 'id' );
```

## Commit the new table

Commit the new table the database by calling `create()` on the new database table object.

```php
<?php

    $resFruit -> create();
```