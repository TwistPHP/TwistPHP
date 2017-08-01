<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html GPL License
 * @link       https://twistphp.com
 */

namespace Twist\Core\Models\Validate;

class Validator{

	protected $arrChecks = array();
	protected $arrTypes = array('compare','email','domain','url','ip','boolean','float','integer','string','telephone','postcode');
	protected $arrTestResults = array();

	public function checkCompare($strKey,$strKey2,$blAllowBlank = false,$blRequired = true,$blTrim = true){

		$this->arrChecks[$strKey] = array(
			'type' => 'compare',
			'key2' => $strKey2,
			'blank' => ($blAllowBlank == true || $blAllowBlank == 1) ? 1 : 0,
			'required' => ($blRequired == true || $blRequired == 1) ? 1 : 0,
			'trim' => ($blTrim == true || $blTrim == 1) ? 1 : 0
		);
	}

	/**
	 * @alias checkCompare
	 * @param      $strKey
	 * @param      $strKey2
	 * @param bool $blAllowBlank
	 * @param bool $blRequired
	 * @param bool $blTrim
	 */
	public function checkComparison($strKey,$strKey2,$blAllowBlank = false,$blRequired = true,$blTrim = true){
		$this->checkCompare($strKey,$strKey2,$blAllowBlank,$blRequired,$blTrim);
	}

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
	 * type = compare|email|url|ip|boolean|float|integer|string|telephone|postcode
	 * required = 1|0
	 *
	 * Note: When checking a multi-dimensional array the key can contain '/' for eg. 'user/name' for the array $arrData['user']['name']
	 *
	 * Return Keys per field
	 * status - If the field passed the test
	 * type - Contains the type of problem encountered pass|blank|invalid|missing
	 * message - The message contains a basic human readable description of the problem
	 *
	 * @param array $arrData
	 * @return array
	 * @throws \Exception
	 */
	public function test(&$arrData){

		$arrChecks = $this->arrChecks;

		$this->arrTestResults = array(
			'status' => true,
			'results' => array()
		);

		foreach($arrChecks as $strKey => $arrEachCheck){

			$mxdTestValue = $mxdTestValue2 = null;

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

					//If blank is allowed do not try and validate the data
					if(trim($mxdTestValue) == '' && array_key_exists('blank',$arrEachCheck) && $arrEachCheck['blank'] == 1){
						$mxdTestResult = $mxdTestValue;
					}else{
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
							case'compare':

								$mxdTestValue2 = null;

								//Detect value two for the comparison
								if(array_key_exists($arrEachCheck['key2'],$arrData)){
									$mxdTestValue2 = $arrData[$arrEachCheck['key2']];
								}elseif(strstr($arrEachCheck['key2'],'/')){
									$mxdTestValue2 = \Twist::framework() -> tools() -> arrayParse($arrEachCheck['key2'],$arrData);
								}

								//Trim the spaces from either side of the input before testing
								if(array_key_exists('trim',$arrEachCheck) && $arrEachCheck['trim'] == 1){
									$mxdTestValue2 = trim($mxdTestValue2);
								}

								$mxdTestResult = \Twist::Validate()->$arrEachCheck['type']($mxdTestValue,$mxdTestValue2);
								break;
							default:
								$mxdTestResult = \Twist::Validate()->$arrEachCheck['type']($mxdTestValue);
								break;
						}
					}

					//On successful match put in a sanitised version of the original data
					if($mxdTestResult !== false){

						if($arrEachCheck['type'] == 'compare'){

							//If it is a compare ensure the test value is applied back to the array key1 and not the result
							$mxdTestResult = $mxdTestValue;

							//Also apply the value to key2
							if(array_key_exists($arrEachCheck['key2'],$arrData)){
								$arrData[$arrEachCheck['key2']] = $mxdTestValue2;
							}elseif(strstr($arrEachCheck['key2'],'/')){
								//TODO: The callback update
							}
						}

						//apply the value to key1
						if(array_key_exists($strKey,$arrData)){
							$arrData[$strKey] = $mxdTestResult;
						}elseif(strstr($strKey,'/')){
							//TODO: The callback update
						}

						$this->testResult($strKey,true,sprintf("%s has passed all checks",$this->keyToText($strKey)));
					}else{

						if($arrEachCheck['type'] == 'compare'){
							$this->testResult($strKey,false,sprintf("%s does not match %s",$this->keyToText($strKey),$this->keyToText($arrEachCheck['key2'])),'invalid');
						}else{
							$this->testResult($strKey,false,sprintf("%s contains incorrectly formatted data",$this->keyToText($strKey)),'invalid');
						}
					}

				}elseif(array_key_exists('type',$arrEachCheck) && !in_array($arrEachCheck['type'],$this->arrTypes)){
					throw new \Exception(sprintf("%s (%s) has an invalid validation type '%s' specified.",$this->keyToText($strKey),$strKey,$arrEachCheck['type']));
				}else{
					$this->testResult($strKey,true,sprintf("%s has been selected",$this->keyToText($strKey)));
				}

			}elseif(array_key_exists('required',$arrEachCheck) && $arrEachCheck['required'] == 0){
				$this->testResult($strKey,true,sprintf("%s has not been selected but is not required",$this->keyToText($strKey)));
			}else{
				$this->testResult($strKey,false,sprintf("%s has not been selected",$this->keyToText($strKey)),'missing');
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

		//Allow new items to be added or successful items to be overridden by failed items
		if(!array_key_exists($strKey,$this->arrTestResults['results']) || (array_key_exists($strKey,$this->arrTestResults['results']) && $this->arrTestResults['results'][$strKey]['status'] && $blStatus === false)){

			$this->arrTestResults['results'][$strKey] = array(
				'status' => $blStatus,
				'type' => $strErrorType,
				'message' => $strMessage
			);
		}

		if($blStatus == false){
			$this->arrTestResults['status'] = false;
		}
	}
}
