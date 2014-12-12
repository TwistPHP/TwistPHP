#Database

There are very few restrictions when using our database class. You are free to create the tables you need that best suit your needs.

Our database class allows you to communicate with a range of databases, the standard library being MySQLi.

1. We are going to create a mySQL table called *fruit* using the database library. You can create a file to run in the browser that reads:
	```php
	<?php
		
		$resFruit = Twist::Database() -> table( 'fruit' );
	
		$resFruit -> addField( 'id', 'int', 11 );
		$resFruit -> addField( 'name', 'char', '64' );
		$resFruit -> addField( 'colour', 'char', '16' );
		$resFruit -> setAutoIncrement( 'id' );
	
		$resFruit -> create();
	```
	
2. In order to populate the table, use the createRecord() method in the database object:
	```php
	<?php

		$resFruitRecord = Twist::Database() -> createRecord( 'fruit' );
		$resFruitRecord -> set( 'name', 'Apple' );
		$resFruitRecord -> set( 'colour', 'green' );
		$resFruitRecord -> commit();

		$resFruitRecord = Twist::Database() -> createRecord( 'fruit' );
		$resFruitRecord -> set( 'name', 'Banana' );
		$resFruitRecord -> set( 'colour', 'yellow' );
		$resFruitRecord -> commit();

		$resFruitRecord = Twist::Database() -> createRecord( 'fruit' );
		$resFruitRecord -> set( 'name', 'Cherry' );
		$resFruitRecord -> set( 'colour', 'red' );
		$resFruitRecord -> commit();
	```

3. Now you can create a new element, *fruit.php* in your site root to display all of the fruit in the database:
	* public_html
		* templates
			* common
				* head.tpl
			* components
				* count.php
				* **fruit.php**
			* pages
				* home.tpl
			* base.tpl
		* twist
			* ...
		* .htaccess
		* index.php

	```php
	<?php
		$arrFruits = Twist::Database() -> getAll( 'fruit' );
		foreach( $arrFruits as $arrFruit ) {
			echo sprintf( '<p>The %s is %s</p>', $arrFruit['name'], $arrFruit['colour'] );
		}
	```

4. Add a new route to your *index.php* file as well as some more content:
	```php
	<?php
		require_once 'twist/framework.php';
		$arrHomeContent= array(
			'title' => 'My First Page',
			'welcome' => 'Hello world, again!',
			'meta' => array(
				'type' => 'description',
				'value' => 'An awesome site'
			)
		);
		$arrCountContent = array(
			'title' => 'My Second Page',
			'welcome' => 'This is how I count',
			'meta' => array(
				'type' => 'description',
				'value' => 'I can count!'
			)
		);
		$arrFruitContent = array(
			'title' => 'My Fruity Page',
			'welcome' => 'Fruit colours',
			'meta' => array(
				'type' => 'description',
				'value' => 'Eat healthily'
			)
		);
		Twist::Route() -> baseTemplate( 'base.tpl' );
		Twist::Route() -> template( '/', 'pages/home.tpl', true, false, $arrHomeContent );
		Twist::Route() -> element( '/count', 'components/count.php,5', true, false, $arrCountContent );
		Twist::Route() -> element( '/fruit', 'components/fruit.php', true, false, $arrFruitContent );
		Twist::Route() -> redirect( '/twitter', 'https://twitter.com/' );
		Twist::Route() -> process();
	```

5. Finally update the menu in your *base.tpl* file:
	```html
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		{template:common/head.tpl}
		<body>
			<h1>{data:welcome}</h1>
			<ul>
				<li><a href="/">Home</a></li>
				<li><a href="/count">Countdown</a></li>
				<li><a href="/fruit">Fruit</a></li>
				<li><a href="/twitter">Twitter</a></li>
			</ul>
			{route:response}
			<p>You requested {route:request} which used the item {route:response_item} with the array {route:data}</p>
		</body>
	</html>
	```

Navigating to your `/fruit` page should now display a list of fruit from the database.