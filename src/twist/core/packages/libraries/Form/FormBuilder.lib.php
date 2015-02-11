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

class FormBuilder{

	public $arrDetails = array();
	public $arrFields = array();
	public $arrGroups = array();
	public $arrOrder = array();

	public function __construct(){

		$this->arrDetails = array(
			'id' => uniqid(),
			'submit' => null,
			'cancel' => null,
			'method' => 'post',
			'encrypt' => 'text/plain'
		);
	}

	public function id($mxdUID){
		$this->arrDetails['id'] = $mxdUID;
	}

	public function method($strMethod){
		$this->arrDetails['method'] = $strMethod;
	}

	public function addField($strTitle,$strName,$strType,$intMaxLength = null,$arrAttributes = array()){

		//TITLE, NAME, TYPE, [MAXLENGTH, [ATTRIBUTES]]
		$this->storeField($strTitle,$strName,$strType,$intMaxLength,$arrAttributes);

		if($strType == 'file'){
			$this->arrDetails['encrypt'] = 'multipart/form-data';
		}
	}

	public function addGroup($strGroupName,$mxdGroupID){

		$this->arrGroups[$mxdGroupID] = array(
			'name' => $strGroupName,
			'required' => 0
		);
	}

	public function addGroupField($intGroupID,$strTitle,$strName,$strType,$intMaxLength = null,$arrAttributes = array()){

		//Add fields to a group - GROUP ID, TITLE, NAME, TYPE, [MAXLENGTH]
		$this->storeField($strTitle,$strName,$strType,$intMaxLength,$arrAttributes,$intGroupID);
	}

	public function requiredFields(){
		//@todo Speak to andi about if this should be a multi or single param
		//return $this->updateField($strName,'required',1);
	}

	public function requiredGroups(){
		//@todo Speak to andi about if this should be a multi or single param
		//return $this->updateGroup($strName,'required',1);
	}

	public function addFieldPrefix($strName,$strPrefix){
		return $this->updateField($strName,'prefix',$strPrefix);
	}

	public function addFieldSuffix($strName,$strSuffix){
		return $this->updateField($strName,'suffix',$strSuffix);
	}

	public function addSumbit($strName,$strSuccessRedirect){
		$this->arrDetails['submit'] = array('name' => $strName,'redirect' => $strSuccessRedirect);
	}

	public function addCancel($strName,$strCancelRedirect){
		$this->arrDetails['cancel'] = array('name' => $strName,'redirect' => $strCancelRedirect);
	}

	public function saveSubmissions(){
		//@todo Save in a database table
	}

	public function render(){
		//@todo Render the form
	}

	protected function storeField($strTitle,$strName,$strType,$intMaxLength = null,$arrAttributes = array(),$intGroupID = null){

		$this->arrFields[] = array(
			'title' => $strTitle,
			'name' => $strName,
			'type' => $strType,
			'max-length' => $intMaxLength,
			'attributes' => $arrAttributes,
			'group' => $intGroupID,
			'prefix' => null,
			'suffix' => null,
			'required' => 0,
		);

		$this->arrOrder[] = $strName;
	}

	protected function updateField($strName,$strKey,$strValue){

		$blOut = false;
		if(array_key_exists($this->arrFields,$strName)){
			$this->arrFields[$strName][$strKey] = $strValue;
			$blOut = true;
		}

		return $blOut;
	}

	protected function updateGroup($mxdGroupID,$strKey,$strValue){

		$blOut = false;
		if(array_key_exists($this->arrFields,$mxdGroupID)){
			$this->arrGroups[$mxdGroupID][$strKey] = $strValue;
			$blOut = true;
		}

		return $blOut;
	}
}