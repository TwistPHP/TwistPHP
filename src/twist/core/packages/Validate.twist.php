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
 * @link       http://twistphp.com
 *
 */

namespace TwistPHP\Packages;
use TwistPHP\ModuleBase;

class Validate extends ModuleBase{

	public function __construct(){
		require_once sprintf('%s/libraries/Validate/Validator.lib.php',DIR_FRAMEWORK_PACKAGES);
	}

	/**
	 * Get a validator object, form here you can define all your validator checks and then test your data against the checks
	 * @return Validator
	 */
	public function createTest(){
		return new Validator();
	}

	public function email($strEmailAddress){
		return filter_var($strEmailAddress, FILTER_VALIDATE_EMAIL);
	}

	public function url($strURL){
		return filter_var($strURL, FILTER_VALIDATE_URL);
	}

	public function ip($mxdIPAddress,$blValidateIPV6 = false){
		$strFilterFlag = ($this->boolean($blValidateIPV6)) ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4;
		return filter_var($mxdIPAddress, FILTER_VALIDATE_IP,$strFilterFlag);
	}

	public function boolean($blBoolean){
		return filter_var($blBoolean, FILTER_VALIDATE_BOOLEAN);
	}

	public function float($fltFloat){
		return filter_var($fltFloat, FILTER_VALIDATE_FLOAT);
	}

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

	public function string($mxdString){
		return (is_object($mxdString) || is_resource($mxdString) || is_bool($mxdString)) ? false : $mxdString;
	}

	public function telephone($mxdPhoneNumber){

		$blOut = false;

		//A much more universal phone number validator also allow for ext|ext.|,|; with upto 4 digit extension. Optional spacing, brackets and dashes throughout
		if(preg_match("/^(\+?(\([0-9\-\s]+\)|[0-9\-\s]+){6,16}((ext\.?|\,|\;)\s?[0-9]{1,4})?)$/i",$mxdPhoneNumber,$arrMatches)){
			$blOut = true;
			$mxdPhoneNumber = str_replace(array("+ ","  ","--","( "," )"," (",") ","(",")"),array("+"," ","-","(",")","(",")"," (",") "),$mxdPhoneNumber);
		}

		return ($blOut) ? $mxdPhoneNumber : false;
	}

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

	public function regx($mxdData,$strRegX){
		return (preg_match($strRegX,$mxdData,$arrMatches)) ? $mxdData : false;
	}
}