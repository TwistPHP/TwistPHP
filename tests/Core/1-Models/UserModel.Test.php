<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../phpunit-support.php';

class UserModel extends PHPUnitSupport{

	public static $intUserID = 0;

	public function testCreate(){

		$resUser = \Twist::User()->create();

		$resUser->firstname('Travis');
		$resUser->surname('CI');
		$resUser->email('travisci@unit-test-twistphp.com');
		$resUser->password('X123Password');

		self::$intUserID = $resUser->commit();
		$this -> assertTrue((self::$intUserID > 0));
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

	public function testLoginPOST(){

		$_POST['email'] = 'travisci@unit-test-twistphp.com';
		$_POST['password'] = 'X123Password';

		$arrSessionArray = \Twist::User()->authenticate();
		$this -> assertTrue($arrSessionArray['status']);

		\Twist::User()->logout();
		$this -> assertFalse(\Twist::User()->loggedIn());
	}

	public function testEdit(){

		$resUser = \Twist::User()->get(self::$intUserID);
		$resUser->surname('CI_2');

		$this -> assertEquals(1,$resUser->commit());
		unset($resUser);

		$resUser = \Twist::User()->get(self::$intUserID);
		$this -> assertEquals('CI_2',$resUser->surname());
	}

	public function testDisable(){

		$resUser = \Twist::User()->get(self::$intUserID);
		$resUser->disable();

		$this -> assertEquals(1,$resUser->commit());
		unset($resUser);

		$arrSessionArray = \Twist::User()->authenticate('travisci@unit-test-twistphp.com','X123Password');
		$this -> assertFalse($arrSessionArray['status']);
	}

	public function testDelete(){

		$resUser = \Twist::User()->get(self::$intUserID);
		$this -> assertTrue($resUser->delete());

		$this -> assertEquals(0,count(\Twist::User()->getData(self::$intUserID)));
	}
}