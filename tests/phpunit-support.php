<?php

	use PHPUnit\Framework\TestCase;

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

			if(method_exists($this,'assertStringContainsString')){
				return parent::assertStringContainsString($strValue1,$strValue2);
			}else{
				return $this->assertContains($strValue1,$strValue2);
			}
		}

		public function testSupport(){
			$this->assertTrue(true);
		}
	}
