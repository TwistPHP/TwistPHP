<?php

class User extends \PHPUnit_Framework_TestCase{

	public function testLogin(){

		//Create new super user test user
		$resUser = \Twist::User()->create();

		$resUser->firstname('Travis');
		$resUser->surname('CI');
		$resUser->level(0);
		$resUser->email('travisci2@unit-test-twistphp.com');
		$resUser->password('X123Password');

		$this -> assertEquals(2,$resUser->commit());

		//Login as the new test user
		$arrSessionArray = \Twist::User()->authenticate('travisci2@unit-test-twistphp.com','X123Password');
		$this -> assertTrue($arrSessionArray['status']);
	}

	public function testLoggedIn(){

		$this -> assertEquals(2,\Twist::User()->loggedInID());

		$this -> assertEquals(0,\Twist::User()->loggedInLevel());

		$this -> assertTrue(is_object(\Twist::User()->current()));

		$this -> assertEquals(2,\Twist::User()->currentID());

		$this -> assertEquals(0,\Twist::User()->currentLevel());
	}
}

