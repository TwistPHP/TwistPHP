<?php

use PHPUnit\Framework\TestCase;

require_once '../../phpunit-support.php';

class Localisation extends PHPUnitSupport{

	public function testLanguages(){

		$arrLanguage = \Twist::Localisation()->getLanguage('EN');
		$this->assertTrue(count($arrLanguage) > 0);

		$arrLanguages = \Twist::Localisation()->getLanguages();
		$this->assertTrue(count($arrLanguages) > 1);

		$arrLanguagesLocalised = \Twist::Localisation()->getLanguages(true);
		$this->assertTrue(count($arrLanguagesLocalised) > count($arrLanguages));

		$arrOfficialLanguage = \Twist::Localisation()->getOfficialLanguage('GB');
		$this->assertTrue(count($arrOfficialLanguage) > 0);
	}

	public function testCountries(){

		$arrCountry = \Twist::Localisation()->getCountry('GB');
		$this->assertTrue(count($arrCountry) > 0);

		$arrCountries = \Twist::Localisation()->getCountries();
		$this->assertTrue(count($arrCountries) > 1);
	}

	public function testCurrency(){

		$arrCurrency = \Twist::Localisation()->getCurrency('GBP');
		$this->assertTrue(count($arrCurrency) > 0);

		$arrCountries = \Twist::Localisation()->getCurrencies();
		$this->assertTrue(count($arrCountries) > 1);

		$arrOfficialCurrency = \Twist::Localisation()->getOfficialCurrency('GB');
		$this->assertTrue(count($arrOfficialCurrency) > 0);

		try{
			/**
			 * Need to look at these more as sometimes they dont manage to get me a result
			 */
			\Twist::framework()->setting('CURRENCY_CONVERSION_API','webservicex.net');

			$fltConversionRate = \Twist::Localisation()->currencyConversionRate('GBP','USD');
			$this->assertTrue($fltConversionRate !== false);

			\Twist::framework()->setting('CURRENCY_CONVERSION_API','yahooapis');

			$fltConversionRate = \Twist::Localisation()->currencyConversionRate('GBP','USD');
			$this->assertTrue($fltConversionRate !== false);

			$fltUSD = \Twist::Localisation()->convertCurrency('GBP','USD',10,false);
			$this->assertTrue(($fltConversionRate * 10) == $fltUSD);

		}catch(\Exception $resException){
			//Do nothing, the above tests are relying on an external API and may not always work
		}
	}

	public function testTimezones(){

		$arrTimezone = \Twist::Localisation()->getTimezone('Europe/London');
		$this->assertTrue(count($arrTimezone) > 0);

		$arrTimezones = \Twist::Localisation()->getTimezones();
		$this->assertTrue(count($arrTimezones) > 1);
	}
}