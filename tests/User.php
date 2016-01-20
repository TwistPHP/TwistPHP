<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class User extends \PHPUnit_Framework_TestCase{

	public function testCreate(){

		$resUser = \Twist::User()->create();

		$resUser->firstname('Travis');
		$resUser->surname('CI');
		$resUser->email('travisci@unit-test-twistphp.com');
		$resUser->password('X123Password');

		$this -> assertEquals(1,$resUser->commit());
	}

	public function testLogin(){

		$arrSessionArray = \Twist::User()->authenticate('travisci@unit-test-twistphp.com','X123Password');
		$this -> assertTrue($arrSessionArray['status']);
	}

	public function testLogout(){

		\Twist::User()->logout();
		$this -> assertFalse(\Twist::User()->loggedIn());
	}

	public function testLoginFail(){

		$arrSessionArray = \Twist::User()->authenticate('travisci@unit-test-twistphp.com','IncorrectPassword');
		$this -> assertFalse($arrSessionArray['status']);
	}

	public function testEdit(){

		$resUser = \Twist::User()->get(1);
		$resUser->surname('CI_2');

		$this -> assertEquals(1,$resUser->commit());
		unset($resUser);

		$resUser = \Twist::User()->get(1);
		$this -> assertEquals('CI_2',$resUser->surname());
	}

	public function testDisable(){

		$resUser = \Twist::User()->get(1);
		$resUser->disable();

		$this -> assertEquals(1,$resUser->commit());
		unset($resUser);

		$arrSessionArray = \Twist::User()->authenticate('travisci@unit-test-twistphp.com','X123Password');
		$this -> assertFalse($arrSessionArray['status']);
	}

	public function testDelete(){

		$resUser = \Twist::User()->get(1);
		$this -> assertTrue($resUser->delete());

		$this -> assertEquals(0,count(\Twist::User()->getData(1)));
	}
}