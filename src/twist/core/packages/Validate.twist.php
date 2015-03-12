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
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
 * @link       https://twistphp.com
 *
 */

namespace Twist\Core\Packages;
use \Twist\Core\Classes\PackageBase;

/**
 * Data validation package can validate different types of data i.e Email Address, URLS, Telephone numbers, UK Postcodes and much more.
 * Also includes a testing suite that allows testing of an array of data providing detailed results, very useful for HTML form validation.
 * @package TwistPHP\Packages
 */
class Validate extends PackageBase{

	public function __construct(){
		require_once sprintf('%s/libraries/Validate/Validator.lib.php',DIR_FRAMEWORK_PACKAGES);
	}

	/**
	 * Get a validator object, form here you can define all your validator checks and then test your data against the checks
	 *
	 * @return_object Validator core/packages/libraries/Validator/Validator.lib.php
	 * @return object Returns an object of the Validator tool
	 */
	public function createTest(){
		return new Validator();
	}

	/**
	 * Validate the format of a Email
	 *
	 * @reference http://php.net/manual/en/filter.constants.php
	 * @param $strEmailAddress Email Address to be validated
	 * @return mixed
	 */
	public function email($strEmailAddress){
		return filter_var($strEmailAddress, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validate Domain Names, allows all of the following combinations
	 * - localhost
	 * - test.test.com
	 * - test-1.test.com
	 * - test.a.do-main.com
	 * - a.b
	 * The first character and last character of any part (split by .) cannot be a - or _ and the last part (.com or .co.uk) can only contain a-z
	 *
	 * @param $strDomain Domain name excluding the protocol, slashes and spaces
	 * @return mixed The returned data will either be the validated domain or false
	 */
	public function domain($strDomain){
		return $this->regx($strDomain,"/^(locahost|([a-z\d]([a-z\d\-\_]+[a-z\d])*\.)*([a-z\d]([a-z\d\-\_]+[a-z\d])*)(\.[a-z]+)+)$/i");
	}

	/**
	 * Validate the format of a URL
	 *
	 * @reference http://php.net/manual/en/filter.constants.php
	 * @param $urlFullLink URL to be validated
	 * @return mixed
	 */
	public function url($urlFullLink){
		return filter_var($urlFullLink, FILTER_VALIDATE_URL);
	}

	/**
	 * Validate the format of a IP address, can validate both IPv4 and IPv6 addresses
	 *
	 * @reference http://php.net/manual/en/filter.constants.php
	 * @param $mxdIPAddress IP address to be validated
	 * @param $blValidateIPV6 Set to true if and IPv6 address is to be validated
	 * @return mixed
	 */
	public function ip($mxdIPAddress,$blValidateIPV6 = false){
		$strFilterFlag = ($this->boolean($blValidateIPV6)) ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4;
		return filter_var($mxdIPAddress, FILTER_VALIDATE_IP,$strFilterFlag);
	}

	/**
	 * Validate a boolean state
	 *
	 * @reference http://php.net/manual/en/filter.constants.php
	 * @param $blBoolean Boolean to be validated
	 * @return mixed
	 */
	public function boolean($blBoolean){
		return filter_var($blBoolean, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Validate a float
	 *
	 * @reference http://php.net/manual/en/filter.constants.php
	 * @param $fltFloat Float to be validated
	 * @return mixed
	 */
	public function float($fltFloat){
		return filter_var($fltFloat, FILTER_VALIDATE_FLOAT);
	}

	/**
	 * Validate an integer, optionally you can pass in a min and max range for further validation
	 *
	 * @reference http://php.net/manual/en/filter.constants.php
	 * @param $intInteger Integer to be validated
	 * @param $intRangeMin Lowest acceptable integer value
	 * @param $intRangeMax Highest acceptable integer value
	 * @return mixed
	 */
	public function integer($intInteger,$intRangeMin = null,$intRangeMax = null){

		if(!is_null($intRangeMin) || !is_null($intRangeMax)){
			$arrOptions = array('options' => array());

			if(!is_null($intRangeMin)){
				$arrOptions['options']['min_range'] = $intRangeMin;
			}

			if(!is_null($intRangeMax)){
				$arrOptions['options']['max_range'] = $intRangeMax;
			}

			return filter_var($intInteger, FILTER_VALIDATE_INT,$arrOptions);
		}

		return filter_var($intInteger, FILTER_VALIDATE_INT);
	}

	/**
	 * Validate a sting, this will ensure the is is not an object, resource or boolean value
	 *
	 * @param $mxdString String to be validated
	 * @return bool
	 */
	public function string($mxdString){
		return (is_object($mxdString) || is_resource($mxdString) || is_bool($mxdString)) ? false : $mxdString;
	}

	/**
	 * Validate a telephone number, this function if very universal phone number validator also allow for ext|ext.|,|; with upto 4 digit extension.
	 * Optional spacing, brackets and dashes throughout
	 *
	 * @param $mxdPhoneNumber Phone number to be validated
	 * @return bool|mixed
	 */
	public function telephone($mxdPhoneNumber){

		$blOut = false;

		if(preg_match("/^(\+?(\([0-9\-\s]+\)|[0-9\-\s]+){6,16}((ext\.?|\,|\;)\s?[0-9]{1,4})?)$/i",$mxdPhoneNumber,$arrMatches)){
			$blOut = true;
			$mxdPhoneNumber = str_replace(array("+ ","  ","--","( "," )"," (",") ","(",")"),array("+"," ","-","(",")","(",")"," (",") "),$mxdPhoneNumber);
		}

		return ($blOut) ? $mxdPhoneNumber : false;
	}

	/**
	 * Validate a UK postcode
	 *
	 * @param $strPostcode Postcode to be validated
	 * @return bool|mixed|string
	 */
	public function postcode($strPostcode){

		// Permitted letters depend upon their position in the postcode.
		$arrLetterCombos = array(
			0 => "[abcdefghijklmnoprstuwyz]",// Character 1
			1 => "[abcdefghklmnopqrstuvwxy]",// Character 2
			2 => "[abcdefghjkstuw]",// Character 3
			3 => "[abehmnprvwxy]",// Character 4
			4 => "[abdefghjlnpqrstuwxyz]"// Character 5
		);

		$arrExpressions = array(
			// Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA with a space Or AN, ANN, AAN, AANN with no whitespace
			0 => sprintf('^(%s{1}%s{0,1}[0-9]{1,2})([[:space:]]{0,})([0-9]{1}%s{2})?$',$arrLetterCombos[0],$arrLetterCombos[1],$arrLetterCombos[4]),
			// Expression for postcodes: ANA NAA Or ANA with no whitespace
			1 => sprintf('^(%s{1}[0-9]{1}%s{1})([[:space:]]{0,})([0-9]{1}%s{2})?$',$arrLetterCombos[0],$arrLetterCombos[2],$arrLetterCombos[4]),
			// Expression for postcodes: AANA NAA Or AANA With no whitespace
			2 => sprintf('^(%s{1}%s[0-9]{1}%s)([[:space:]]{0,})([0-9]{1}%s{2})?$',$arrLetterCombos[0],$arrLetterCombos[1],$arrLetterCombos[3],$arrLetterCombos[4]),
			// Exception for the special postcode GIR 0AA Or just GIR
			3 => '^(gir)([[:space:]]{0,})?(0aa)?$',
			// Standard BFPO numbers
			4 => '^(bfpo)([[:space:]]{0,})([0-9]{1,4})$',
			// c/o BFPO numbers
			5 => '^(bfpo)([[:space:]]{0,})(c\/o([[:space:]]{0,})[0-9]{1,3})$',
			// Overseas Territories
			6 => '^([a-z]{4})([[:space:]]{0,})(1zz)$',
			// Anquilla
			7 => '^(ai\-2640)$'
		);

		$blValid = false;
		$strPostcodeOut = strtolower($strPostcode);

		//Check the string against the six types of postcodes
		foreach($arrExpressions as $strRegExp){
			if(preg_match(sprintf('/%s/i',$strRegExp),$strPostcodeOut,$arrMatches)){

				//Load new postcode back into the form element
				$strPostcodeOut = strtoupper($arrMatches[1]);
				if(isset($arrMatches[3])){
					$strPostcodeOut .= ' '.strtoupper($arrMatches[3]);
				}

				//Take account of the special BFPO c/o format
				$strPostcodeOut = preg_replace('/C\/O/', 'c/o ', $strPostcodeOut);

				$blValid = true;
				break;
			}
		}

		if($blValid){
			return $strPostcodeOut;
		}else{
			return false;
		}
	}

	/**
	 * Validate some data using an Regular Expression
	 *
	 * @param $mxdData Data to be validated
	 * @param $strRegX Expression used to validate the data
	 * @return bool
	 */
	public function regx($mxdData,$strRegX){
		return (preg_match($strRegX,$mxdData,$arrMatches)) ? $mxdData : false;
	}
}