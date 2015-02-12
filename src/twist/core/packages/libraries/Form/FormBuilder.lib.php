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

	const EOL = "\n";

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

	public function addField($strTitle,$strName,$strType,$intMaxLength = null,$mxdValue = null,$arrAttributes = array()){

		//TITLE, NAME, TYPE, [MAXLENGTH, [ATTRIBUTES]]
		$this->storeField($strTitle,$strName,$strType,$intMaxLength,$mxdValue,$arrAttributes);

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

	public function addGroupField($mxdGroupID,$strTitle,$strName,$strType,$intMaxLength = null,$mxdValue = null,$arrAttributes = array()){

		//Add fields to a group - GROUP ID, TITLE, NAME, TYPE, [MAXLENGTH]
		$this->storeField($strTitle,$strName,$strType,$intMaxLength,$mxdValue,$arrAttributes,$mxdGroupID);
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

		//https://gist.github.com/ahosgood/88d474c0b811ce469bc0
		//@todo store cache of the form, build in option to put in custom tags to allow cache form to be populated with data and pre-selects where required

		$resTemplate = \Twist::Template('pkgForm');
		$resTemplate->setTemplatesDirectory(sprintf('%s/templates/Form/',DIR_FRAMEWORK_PACKAGES));

		$arrFormTags = array(
			'id' => $this->arrDetails['id'],
			'method' => $this->arrDetails['method'],
			'action' => '.',
			'encrypt' => $this->arrDetails['encrypt'],
			'fields' => self::EOL
		);

		foreach($this->arrFields as $arrField){

			//No linebreak required after label so that the input buts up to the label
			$arrFormTags['fields'] .= $resTemplate->build('label.tpl',$arrField);

			$arrFieldTags = array(
				'name' => (is_null($arrField['group'])) ? $arrField['name'] : sprintf('%s[%s]',$arrField['group'],$arrField['name']),
				'type' => $arrField['type'],
				'value' => $arrField['value'],
				'attributes' => ($arrField['required']) ? ' required' : ''
			);

			foreach($arrField['attributes'] as $strAttributeName => $strAttributeValue){
				$arrFieldTags .= sprintf(' %s="%s"',$strAttributeName,$strAttributeValue);
			}

			switch($arrField['type']){

				case'select':

					$arrFieldTags['options'] = self::EOL;
					foreach($arrField['value'] as $strTitle => $strValue){

						$arrOptionData = array(
							'title' => $strTitle,
							'value' => $strValue,
							'attributes' => ''
						);

						$arrFieldTags['options'] .= $resTemplate->build('inputs/select-option.tpl',$arrOptionData).self::EOL;
					}

					$arrFormTags['fields'] .= $resTemplate->build('inputs/select.tpl',$arrFieldTags).self::EOL;
					break;

				default:

					if(!is_null($arrField['prefix'])){

					}
					if(!is_null($arrField['suffix'])){

					}

					$strTemplate = sprintf('inputs/%s.tpl',$arrField['type']);

					if(!file_exists($strTemplate)){
						$strTemplate = 'inputs/default.tpl';
					}

					$arrFormTags['fields'] .= $resTemplate->build($strTemplate,$arrFieldTags).self::EOL;
					break;
			}
		}

		//@todo look at buttons as the submit should always be present
		if(!is_null($this->arrDetails['submit'])){
			$arrFormTags['fields'] .= $resTemplate->build('button.tpl',$this->arrDetails['submit']).self::EOL;
		}

		if(!is_null($this->arrDetails['cancel'])){
			$arrFormTags['fields'] .= $resTemplate->build('button.tpl',$this->arrDetails['cancel']).self::EOL;
		}

		$arrFormTags['fields'] .= '<!-- TwistPHP required from fields -->'.self::EOL;
		$arrFormTags['fields'] .= $resTemplate->build('inputs/hidden.tpl',array('name' => 'twistphp[id]','type' => 'hidden','value' => $this->arrDetails['id'],'attributes' => '')).self::EOL;
		$arrFormTags['fields'] .= $resTemplate->build('inputs/hidden.tpl',array('name' => 'twistphp[redirect_success]','type' => 'hidden','value' => '','attributes' => '')).self::EOL;
		$arrFormTags['fields'] .= $resTemplate->build('inputs/hidden.tpl',array('name' => 'twistphp[redirect_cancel]','type' => 'hidden','value' => '','attributes' => '')).self::EOL;

		return $resTemplate->build('form.tpl',$arrFormTags);
	}

	protected function storeField($strTitle,$strName,$strType,$intMaxLength = null,$mxdValue = null,$arrAttributes = array(),$mxdGroupID = null){

		$this->arrFields[] = array(
			'title' => $strTitle,
			'name' => $strName,
			'type' => $strType,
			'max-length' => $intMaxLength,
			'attributes' => $arrAttributes,
			'value' => $mxdValue,
			'group' => $mxdGroupID,
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