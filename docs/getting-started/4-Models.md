#Models

A model is used to interact with and manipulate data.

1. Our model in this example is used to get and set details about fruits. Create a file called *Fruits.model.php* in the */app/models* folder:
	```php
	<?php

		// Set the namespace
		namespace App\Fruits;

		// Name your model
		class Fruits {

			// Create a table and some records for this example
			private function init() {
				$resFruitTable = Twist::Database() -> table( 'fruits' );
				$resFruitTable -> addField( 'id', 'int', 11 );
				$resFruitTable -> addField( 'name', 'char', '64' );
				$resFruitTable -> addField( 'colour', 'char', '16' );
				$resFruitTable -> setAutoIncrement( 'id' );
				$resFruitTable -> create();
				
				$resFruit = Twist::Database() -> createRecord( 'fruits' );
				$resFruit -> set( 'name', 'Apple' );
				$resFruit -> set( 'colour', 'green' );
				$resFruit -> commit();
		
				$resFruit = Twist::Database() -> createRecord( 'fruits' );
				$resFruit -> set( 'name', 'Banana' );
				$resFruit -> set( 'colour', 'yellow' );
				$resFruit -> commit();
		
				$resFruit = Twist::Database() -> createRecord( 'fruits' );
				$resFruit -> set( 'name', 'Cherry' );
				$resFruit -> set( 'colour', 'red' );
				$resFruit -> commit();
			}

			public static function _construct() {
				$this -> _init();
			}

			// Get all fruits
			public static function all() {
				return \Twist::Database() -> getAll( 'fruits' );
			}

			// Get a single fruit
			public static function get( $intFruitID ) {
				return \Twist::Database() -> get( 'fruits', $intFruitID, 'id' );
			}

			// Set details of a fruit
			public static function set( $intFruitID, $strName, $strColour ) {
				// Get the fruit
				$resFruit = Twist::Database() -> get( 'fruit', $intFruitID, 'id' );
				// Set the details of the fruit
				$resFruit -> set( 'name', $strName );
				$resFruit -> set( 'colour', $strColour );
				// Commit the changes to the database
				return $resFruit -> commit();
			}

	```
	
2. Now we can update the Site controller and add the following methods:
	```php
		// This method creates the page /fruits
		public function fruits() {
			$this -> _title( 'Fruit, glorious fruit' );
			$strReturn = '';
			$resFruits = \App\Models\Fruits;
			foreach( $resFruits -> getAll() as $arrFruit ) {
				$strReturn .= '';
			}
			return $strReturn;
		}

		//TODO
	```

Navigating to your `/fruits` page should now display a list of fruit from the database.