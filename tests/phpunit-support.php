<?php

	use PHPUnit\Framework\TestCase;

	class TestCaseTest extends TestCase{
		public function testFunctions(){
			$this->assertTrue(true);
		}
	}

	if(method_exists(new TestCaseTest(),'assertStringContainsString')){

		/**
		 * Class PHPUnitSupport
		 * Add forward compatible support for testing newer versions of PHP
		 */
		class PHPUnitSupport extends TestCase{

			public function testSupport(){
				$this->assertTrue(true);
			}
		}

	}else{

		/**
		 * Class PHPUnitSupport
		 * Add backwards compatible support for testing older versions of PHP
		 */
		class PHPUnitSupport extends TestCase{

			/**
			 * Add support for a new function as assertContains was deprecated in old PHPUnit
			 * @param $strValue1
			 * @param $strValue2
			 * @return mixed
			 */
			public function assertStringContainsString($strValue1,$strValue2){
				return $this->assertContains($strValue1,$strValue2);
			}

			public function testSupport(){
				$this->assertTrue(true);
			}
		}
	}