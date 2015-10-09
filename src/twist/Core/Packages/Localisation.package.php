<?php
	/**
	 * This file is part of TwistPHP.
	 *
	 * TwistPHP is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * TwistPHP is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @author	 Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license	https://www.gnu.org/licenses/gpl.html LGPL License
	 * @link	   http://twistphp.com/
	 *
	 */

	namespace Twist\Core\Packages;

	/**
	 * Localisation of websites is becoming a necessity, the ability to list counties, currencies, timezones, languages and there relationship is essential.
	 * Get a full list of countries and their ISO codes.
	 * Get the native spoken language of a country by its ISO code.
	 * Get the name of a language by its ISO language code.
	 * Get the official currency by its ISO currency code.
	 */
	class Localisation extends Base{

		protected $arrLanguages = array();
		protected $arrLanguagesLocalised = array();
		protected $arrCountries = array();
		protected $arrCurrencies = array();
		protected $arrTimezones = array();

		/**
		 * Load in the language and country information
		 */
		public function __construct(){

			$jsonLanguage = file_get_contents(sprintf('%sCore/Data/localisation/languages.json',TWIST_FRAMEWORK));
			$arrLanguages = json_decode($jsonLanguage,true);

			//Build the array of languages to include those with variants
			foreach($arrLanguages as $arrEachLanguage){
				if(!is_null($arrEachLanguage['variant'])){
					$strKey = sprintf("%s-%s",$arrEachLanguage['iso'],$arrEachLanguage['variant']);
					$this->arrLanguagesLocalised[strtoupper($strKey)] = $arrEachLanguage;
				}else{
					$this->arrLanguagesLocalised[strtoupper($arrEachLanguage['iso'])] = $arrEachLanguage;
					$this->arrLanguages[strtoupper($arrEachLanguage['iso'])] = $arrEachLanguage;
				}
			}

			$jsonCountries = file_get_contents(sprintf('%sCore/Data/localisation/countries.json',TWIST_FRAMEWORK));
			$this->arrCountries = json_decode($jsonCountries,true);

			$jsonCountries = file_get_contents(sprintf('%sCore/Data/localisation/currencies.json',TWIST_FRAMEWORK));
			$this->arrCurrencies = json_decode($jsonCountries,true);

			$jsonTimezones = file_get_contents(sprintf('%sCore/Data/localisation/timezones.json',TWIST_FRAMEWORK));
			$arrTimezones = json_decode($jsonTimezones,true);

			foreach($arrTimezones as $arrEachTimezone){
				$this->arrTimezones[strtoupper($arrEachTimezone['code'])] = $arrEachTimezone;
			}
		}

		/**
		 * Get a single-dimensional array of language information related to the provided 2 Character ISO language code.
		 *
		 * @param $strLanguageISO 2 Character ISO code
		 * @return array Returns a single-dimensional language array
		 */
		public function getLanguage($strLanguageISO){
			$strLanguageISO = strtoupper($strLanguageISO);
			return (array_key_exists($strLanguageISO,$this->arrLanguagesLocalised)) ? $this->arrLanguagesLocalised[$strLanguageISO] : array();
		}

		/**
		 * Get multi-dimensional array of all languages. Optionally include the localised/native language name.
		 *
		 * @related getLanguage
		 * @param $blIncludeLocalisation Enable localised/native language name
		 * @return array Returns a multi-dimensional array of languages
		 */
		public function getLanguages($blIncludeLocalisation = false){
			return ($blIncludeLocalisation) ? $this->arrLanguagesLocalised : $this->arrLanguages;
		}

		/**
		 * Get the official language of any given country by 2 character ISO country code.
		 *
		 * @related getLanguage
		 * @param $strCountryISO 2 Character ISO code
		 * @return array Returns an single-dimensional language array
		 */
		public function getOfficialLanguage($strCountryISO){
			$arrCountry = $this->getCountry($strCountryISO);
			return (count($arrCountry)) ? $this->getLanguage($arrCountry['official_language_iso']) : array();
		}

		/**
		 * Get a single-dimensional array of country information by its 2 Character ISO country code.
		 *
		 * @param $strCountryISO 2 Character ISO code
		 * @return array Returns an single-dimensional country array
		 */
		public function getCountry($strCountryISO){
			$strCountryISO = strtoupper($strCountryISO);
			return (array_key_exists($strCountryISO,$this->arrCountries)) ? $this->arrCountries[$strCountryISO] : array();
		}

		/**
		 * Get an array of all country names, ISO codes and Official spoken language.
		 *
		 * @related getCountry
		 * @return array Returns a multi-dimensional array of countries
		 */
		public function getCountries(){
			return $this->arrCountries;
		}

		/**
		 * Get a single-dimensional array of currency information by its 3 Character ISO currency code.
		 *
		 * @param $strCurrencyISO 3 Character ISO code
		 * @return array Returns an single-dimensional country array
		 */
		public function getCurrency($strCurrencyISO){
			$strCurrencyISO = strtoupper($strCurrencyISO);
			return (array_key_exists($strCurrencyISO,$this->arrCurrencies)) ? $this->arrCurrencies[$strCurrencyISO] : array();
		}

		/**
		 * Get an array of all currencies names, ISO codes and Symbols.
		 * @return array
		 */
		public function getCurrencies(){
			return $this->arrCurrencies;
		}

		/**
		 * Get the official currency of any given country by 2 character ISO country code.
		 *
		 * @related getCurrency
		 * @param $strCountryISO 2 Character ISO code
		 * @return array Returns an single-dimensional language array
		 */
		public function getOfficialCurrency($strCountryISO){
			$arrCountry = $this->getCountry($strCountryISO);
			return (count($arrCountry)) ? $this->getCurrency($arrCountry['official_currency_iso']) : array();
		}

		/**
		 * Get a single-dimensional array of timezone information related to the provided timezone code.
		 *
		 * @param $strTimezoneCode Timezone code i.e 'Europe/London'
		 * @return array Returns a single-dimensional language array
		 */
		public function getTimezone($strTimezoneCode){
			$strTimezoneCode = strtoupper($strTimezoneCode);
			return (array_key_exists($strTimezoneCode,$this->arrTimezones)) ? $this->arrTimezones[$strTimezoneCode] : array();
		}

		/**
		 * Get multi-dimensional array of all timezones.
		 *
		 * @related getTimezone
		 * @return array Returns a multi-dimensional array of timezones
		 */
		public function getTimezones(){
			return $this->arrTimezones;
		}

		/**
		 * Convert an amount between two Currency ISO codes
		 * @param $strFromISO
		 * @param $strToISO
		 * @param $fltAmount
		 * @param bool $blFormat
		 * @return string
		 * @throws \Exception
		 */
		public function convertCurrency($strFromISO,$strToISO,$fltAmount,$blFormat = true){

			$intConversionRate = $this->currencyConversionRate($strFromISO,$strToISO);
			$fltConverted = ($fltAmount * $intConversionRate);

			return ($blFormat) ? number_format($fltConverted,2,'.','') : $fltConverted;
		}

		/**
		 * Get the conversion rate between two provided currency ISO codes.
		 * @param $strFromISO
		 * @param $strToISO
		 * @return \SimpleXMLElement
		 * @throws \Exception
		 */
		public function currencyConversionRate($strFromISO,$strToISO){

			$fltConversionRate = false;

			switch(strtolower(\Twist::framework()->setting('CURRENCY_CONVERSION_API'))){

				case 'yahooapis':

					//Yahoo Currency API
					$arrParameters = array(
						'q' => sprintf('select * from yahoo.finance.xchange where pair in ("%s%s")',$strFromISO,$strToISO),
						'format' => 'json',
						'env' => 'store://datatables.org/alltableswithkeys',
					);

					$strResult = \Twist::Curl()->get('http://query.yahooapis.com/v1/public/yql',$arrParameters);

					$arrResult = json_decode($strResult,true);
					$fltConversionRate = (is_array($arrResult) && array_key_exists('rate',$arrResult['query']['results'])) ? $arrResult['query']['results']['rate']['Rate'] : false;
					break;

				case 'webservicex.net':

					//webservicex.net
					$arrParameters = array(
						'FromCurrency' => $strFromISO,
						'ToCurrency' => $strToISO
					);

					$strResult = \Twist::Curl()->post('http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate',$arrParameters);

					\Twist::XML()->loadRawData($strResult);
					$arrData = \Twist::XML()->getArray();

					$fltConversionRate = (is_array($arrData) && count($arrData)) ? $arrData['data'][0]['content'][0] : false;
					break;
			}

			if($fltConversionRate === false){
				throw new \Exception(sprintf('TwistPHP Error: Unable to retrieve currency conversion rate for %s to %s',$strFromISO,$strToISO));
			}

			return $fltConversionRate;
		}
	}