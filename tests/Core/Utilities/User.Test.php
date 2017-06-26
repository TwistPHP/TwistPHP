<?php

class User extends \PHPUnit_Framework_TestCase{

	public static $intUserID = 0;

	public function testLogin(){

		//Create new super user test user
		$resUser = \Twist::User()->create();

		$resUser->firstname('Travis');
		$resUser->surname('CI');
		$resUser->level(0);
		$resUser->email('travisci2@unit-test-twistphp.com');
		$resUser->password('X123Password');

		self::$intUserID = $resUser->commit();

		$this -> assertTrue((self::$intUserID > 0));

		//Login as the new test user
		$arrSessionArray = \Twist::User()->authenticate('travisci2@unit-test-twistphp.com','X123Password');
		$this -> assertTrue($arrSessionArray['status']);
	}

	public function testLoggedIn(){

		$this -> assertEquals(self::$intUserID,\Twist::User()->loggedInID());

		$this -> assertEquals(0,\Twist::User()->loggedInLevel());

		$this -> assertTrue(is_object(\Twist::User()->current()));

		$this -> assertEquals(self::$intUserID,\Twist::User()->currentID());

		$this -> assertEquals(0,\Twist::User()->currentLevel());
	}

	public function testLegacyPassword(){

		$resUser = \Twist::Database()->records('twist_users')->get(self::$intUserID);
		$resUser->set('password',sha1('X123Password'));
		$resUser->commit();

		\Twist::User()->logout();

		//Test the login using a legacy password hash (in this case sha1)
		$arrSessionArray = \Twist::User()->authenticate('travisci2@unit-test-twistphp.com','X123Password');
		$this -> assertTrue($arrSessionArray['status']);

		//Check that the legacy password has been updated
		$this -> assertNotEquals(sha1('X123Password'),$resUser->get('password'));

		//Logout again
		\Twist::User()->logout();

		//Test the login again this time using the new passwrod hash
		$arrSessionArray = \Twist::User()->authenticate('travisci2@unit-test-twistphp.com','X123Password');
		$this -> assertTrue($arrSessionArray['status']);
	}
}

