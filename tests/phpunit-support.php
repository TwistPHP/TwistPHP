<?php

	use PHPUnit\Framework\TestCase;

	/**
	 * Class PHPUnitSupport
	 * Add backwards compatible support for testing older versions of PHP
	 */
	class PHPUnitSupport extends TestCase{

		public function __construct(){
			if(!defined('TWIST_LAUNCHED')){
				require_once dirname(__FILE__).'/../dist/twist/framework.php';
				Twist::Route()->manager('/manager');
			}
		}

		/**
		 * Allow support for pre v7 of PHPUnit used by the PHP7.1 travis server test
		 * If someone can make a fix for this please replace all "_assert" to "assert"
		 * @param $needle
		 * @param $haystack
		 * @param string $message
		 * @return mixed
		 */
		public static function _assertStringContainsString(string $needle, string $haystack, string $message = ''): void
		{

			if(method_exists(get_called_class(),'assertStringContainsString')){
				self::assertStringContainsString($needle, $haystack, $message);
			}else{
				self::assertContains($needle,$haystack,$message,false);
			}
		}

		/**
		 * Allow support for pre v7 of PHPUnit used by the PHP7.1 travis server test
		 * If someone can make a fix for this please replace all "_assert" to "assert"
		 * @param $needle
		 * @param $haystack
		 * @param string $message
		 * @return mixed
		 */
		public static function _assertStringContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
		{

			if(method_exists(get_called_class(),'assertStringContainsStringIgnoringCase')){
				self::assertStringContainsStringIgnoringCase($needle, $haystack, $message);
			}else{
				self::assertContains($needle,$haystack,$message,true);
			}
		}

		public function testSupport(){
			$this->assertTrue(true);
		}
	}
