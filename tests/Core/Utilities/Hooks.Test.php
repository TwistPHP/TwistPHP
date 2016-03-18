<?php

class Hooks extends \PHPUnit_Framework_TestCase{

	public function getAllHooks(){
		$this->assertTrue(array_key_exists('TWIST_VIEW_TAG',\Twist::framework()->hooks()->getAll()));
	}

	public function registerHook(){
		\Twist::framework()->hooks()->register('travisCI','travis-test-hook','test-hook');
		$this->assertEquals('test-hook',\Twist::framework()->hooks()->get('travisCI','travis-test-hook'));
	}

	public function getRegisteredHook(){
		$this->assertEquals('test-hook',\Twist::framework()->hooks()->get('travisCI','travis-test-hook'));
	}

	public function cancelRegisteredHook(){
		\Twist::framework()->hooks()->cancel('travisCI','travis-test-hook');
		$this->assertEquals(array(),\Twist::framework()->hooks()->get('travisCI','travis-test-hook'));
	}

}