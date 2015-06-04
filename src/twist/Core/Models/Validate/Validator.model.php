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

namespace Twist\Core\Models\Validate;

class Validator{

	protected $arrChecks = array();
	protected $arrTypes = array('email','domain','url','ip','boolean','float','integer','string','telephone','postcode');
	protected $arrTestResults = array();

	public function checkEmail($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'email',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkDomain($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'domain',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkURL($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'url',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkIP($strKey,$blValidateIPV6 = false,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'ip',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0,
			'ipv6' => ($blValidateIPV6 == true || $blValidateIPV6 == 1) ? 1 : 0,
		);
	}

	public function checkBoolean($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'boolean',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkFloat($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'float',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkInteger($strKey,$intRangeMin = null,$intRangeMax = null,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'integer',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0,
			'min_range' => $intRangeMin,
			'max_range' => $intRangeMax
		);
	}

	public function checkString($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'string',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkTelephone($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'telephone',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkPostcode($strKey,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'postcode',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	public function checkRegX($strKey,$strExpression,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'regx',
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0,
			'expression' => $strExpression
		);
	}

	/**
	 * Validate an array of data against an array of checks. Very useful for validating $_GET and $_POST arrays.
	 *
	 * The checks array should consist of a key (the field name you want to check) and an array of checks consisting of any of the below options:
	 * blank = 1|0
	 * type = email|url|ip|boolean|float|integer|string|telephone|postcode
	 * required = 1|0
	 *
	 * Note: When checking a multi-dimensional array the key can contain '/' for eg. 'user/name' for the array $arrData['user']['name']
	 *
	 * Return Keys per field
	 * status - If the field passed the test
	 * type - Contains the type of problem encountered pass|blank|invalid|missing
	 * message - The message contains a basic human readable description of the problem
	 *
	 * @param $arrData
	 * @return array
	 */
	public function test(&$arrData){

		$arrChecks = $this->arrChecks;

		$this->arrTestResults = array(
			'status' => true,
			'results' => array()
		);

		foreach($arrChecks as $strKey => $arrEachCheck){

			$mxdTestValue = null;

			if(array_key_exists($strKey,$arrData)){
				$mxdTestValue = $arrData[$strKey];
			}elseif(strstr($strKey,'/')){
				$mxdTestValue = \Twist::framework() -> tools() -> arrayParse($strKey,$arrData);
			}

			//First check to see that the key is present
			if(!is_null($mxdTestValue)){

				//Trim the spaces from either side of the input before testing
				if(array_key_exists('trim',$arrEachCheck) && $arrEachCheck['trim'] == 1){
					$mxdTestValue = trim($mxdTestValue);
				}

				if(trim($mxdTestValue) == '' && array_key_exists('blank',$arrEachCheck) && $arrEachCheck['blank'] == 0){
					$this->testResult($strKey,false,sprintf("%s cannot be blank",$this->keyToText($strKey)),'blank');
				}elseif(array_key_exists('type',$arrEachCheck) && in_array($arrEachCheck['type'],$this->arrTypes)){

					//Call the test function and get the result
					switch($arrEachCheck['type']){
						case'integer':
							$mxdTestResult = \Twist::Validate()->$arrEachCheck['type']($mxdTestValue,$arrEachCheck['min_range'],$arrEachCheck['max_range']);
							break;
						case'ip':
							$mxdTestResult = \Twist::Validate()->$arrEachCheck['type']($mxdTestValue,$arrEachCheck['ipv6']);
							break;
						case'regx':
							$mxdTestResult = \Twist::Validate()->$arrEachCheck['type']($mxdTestValue,$arrEachCheck['expression']);
							break;
						default:
							$mxdTestResult = \Twist::Validate()->$arrEachCheck['type']($mxdTestValue);
							break;
					}

					if($mxdTestResult !== false){

						//On successful match put in a sanitised version of the original data
						if(array_key_exists($strKey,$arrData)){
							$arrData[$strKey] = $mxdTestResult;
						}elseif(strstr($strKey,'/')){
							//Todo the callback update
						}

						$this->testResult($strKey,true,sprintf("%s has passed all checks",$this->keyToText($strKey)));
					}else{
						$this->testResult($strKey,false,sprintf("%s contains incorrectly formatted data",$this->keyToText($strKey)),'invalid');
					}

				}elseif(array_key_exists('type',$arrEachCheck) && !in_array($arrEachCheck['type'],$this->arrTypes)){
					throw new \Exception(sprintf("%s (%s) has an invalid validation type '%s' specified.",$this->keyToText($strKey),$strKey,$arrEachCheck['type']));
				}else{
					$this->testResult($strKey,true,sprintf("%s has been passed in",$this->keyToText($strKey)));
				}

			}elseif(array_key_exists('required',$arrEachCheck) && $arrEachCheck['required'] == 0){
				$this->testResult($strKey,true,sprintf("%s has not been passed in but is not required",$this->keyToText($strKey)));
			}else{
				$this->testResult($strKey,false,sprintf("%s has not been passed in",$this->keyToText($strKey)),'missing');
			}
		}

		return $this->arrTestResults;
	}

	protected function keyToText($strKey){
		return ucwords(str_replace('_',' ',$strKey));
	}

	public function success(){
		return (count($this->arrTestResults)) ? $this->arrTestResults['status'] : false;
	}

	private function testResult($strKey,$blStatus,$strMessage,$strErrorType = 'pass'){

		$this->arrTestResults['results'][$strKey] = array(
			'status' => $blStatus,
			'type' => $strErrorType,
			'message' => $strMessage
		);

		if($blStatus == false){
			$this->arrTestResults['status'] = false;
		}
	}
}