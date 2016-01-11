# Create new database tables

TwistPHP uses its own database object to add and edit mySQL tables.

## Create a new table

To create a new table, grab a copy of the database object and use the method `table()` to return a new database table object.

```php
<?php

    $resFruit = Twist::Database() -> table( 'fruit' );
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

Commit the new table the database by calling `commit()` on the new database table object.

```php
<?php

    $resFruit -> create();
```