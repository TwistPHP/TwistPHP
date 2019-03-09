<?php

	use PHPUnit\Framework\TestCase;

	/**
	 * Class PHPUnitSupport
	 * Add backwards compatible support for testing older versions of PHP
	 */
	class PHPUnitSupport extends TestCase{

		/**
		 * Add support for a new function as assertContains was deprecated in old PHPUnit
		 * @param $needle
		 * @param $haystack
		 * @param string $message
		 * @return mixed
		 */
		public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
		{

			if(method_exists(get_called_class(),'assertStringContainsString')){
				parent::assertStringContainsString($needle, $haystack, $message);
			}else{
				self::assertContains($needle,$haystack,$message,false);
			}
		}

		/**
		 * Add support for a new function as assertContains was deprecated in old PHPUnit
		 * @param $needle
		 * @param $haystack
		 * @param string $message
		 * @return mixed
		 */
		public static function assertStringContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
		{

			if(method_exists(get_called_class(),'assertStringContainsStringIgnoringCase')){
				parent::assertStringContainsStringIgnoringCase($needle, $haystack, $message);
			}else{
				self::assertContains($needle,$haystack,$message,true);
			}
		}

		public function testSupport(){
			$this->assertTrue(true);
		}
	}
