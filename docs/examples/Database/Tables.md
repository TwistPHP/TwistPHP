#mySQL Tables

TwistPHP uses its own database object to add and edit mySQL tables.

## Creating a new table

To create a new table, grab a copy of the database object and use the method `table()` to return a new database table object.

```php
$resFruit = Twist::Database() -> table( 'fruit' );
```

### Add fields

You can create database fields to the table object using the `addField()` method of the database table object.

```php
$resFruit = Twist::Database() -> table( 'fruit' );

$resFruit -> addField( 'id', 'int', 11 );
$resFruit -> addField( 'name', 'char', '64' );
$resFruit -> addField( 'colour', 'char', '16' );
$resFruit -> setAutoIncrement( 'id' );
```

### Set auto increment

An auto increment field can be set with the `setAutoIncrement()` method.

```php
$resFruit = Twist::Database() -> table( 'fruit' );

$resFruit -> addField( 'id', 'int', 11 );
$resFruit -> addField( 'name', 'char', '64' );
$resFruit -> addField( 'colour', 'char', '16' );
$resFruit -> setAutoIncrement( 'id' );
```

### Commit the new table

Commit the new table the database by calling `commit()` on the new database table object.

```php
$resFruit = Twist::Database() -> table( 'fruit' );

$resFruit -> addField( 'id', 'int', 11 );
$resFruit -> addField( 'name', 'char', '64' );
$resFruit -> addField( 'colour', 'char', '16' );
$resFruit -> setAutoIncrement( 'id' );

$resFruit -> create();
```