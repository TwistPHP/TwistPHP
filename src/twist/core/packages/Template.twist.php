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
use TwistPHP\Instance;

/**
 * Bring HTML to life with conditional template tags, allowing interaction with PHP without using PHP code. Include functionality of additional templates in just a fue chars
 */
class Template extends ModuleBase{

    protected $strInstanceKey = '';
    protected $dirTemplates = '';
    protected $dirElements = '';
    protected $arrElementData = array();
    protected $arrElementParams = array();
    protected $dirCurrentTemplate = null;

    public function __construct($strInstanceKey){
        $this->strInstanceKey = $strInstanceKey;
        $this->setTemplatesDirectory();
        $this->setElementsDirectory();
    }

    /**
     * Kills the current instance of the template class, help to keep your RAM free
     */
    public function kill(){
        Instance::removeObject(($this->strInstanceKey == 'twist') ? 'pkgTemplate' : sprintf('pkgTemplate-%s',$this->strInstanceKey));
    }

    /**
     * Set the template directory to default or provide a new directory
     * @param $dirCustomTemplates Path to a custom template directory
     */
    public function setTemplatesDirectory($dirCustomTemplates = null){
        $this->dirTemplates = (is_null($dirCustomTemplates)) ? DIR_TEMPLATES : $dirCustomTemplates;
    }

    /**
     * Set the element directory to the default or provide a new directory
     * @param $dirCustomElements Path to a custom elements directory
     */
    public function setElementsDirectory($dirCustomElements = null){
	    $this->dirElements = (is_null($dirCustomElements)) ? $this->dirTemplates : $dirCustomElements;
    }

    /**
     * Get the current template directory/path that is in use by the template package
     * @return directory Returns the current template path
     */
    public function getTemplatesDirectory(){
        return $this->dirTemplates;
    }

    /**
     * Get the current element directory/path that is in use by the template package
     * @return directory Returns the current element path
     */
    public function getElementsDirectory(){
        return $this->dirElements;
    }

    /**
     * Build the template with the array of tags supplied
     *
     * @param $dirTemplate
     * @param $arrTemplateTags
     * @param $blRemoveUnusedTags
     * @return string
     */
    public function build($dirTemplate,$arrTemplateTags = null,$blRemoveUnusedTags = false) {

        $strTemplateDataOut = null;
        $this->validDataTags($arrTemplateTags);
	    $this->dirCurrentTemplate = $dirTemplate;

	    $dirFullTemplatePath = (!is_file($dirTemplate)) ? sprintf("%s%s",$this->dirTemplates,$dirTemplate) : $dirTemplate;
		$strCacheKey = str_replace(array(BASE_LOCATION,'twist/interfaces','twist/core'),array('','twist-interface','twist-core'),$dirFullTemplatePath);
		$arrTemplateData = \Twist::Cache('pkgTemplate')->retrieve($strCacheKey);

	    //Detect if the file has changed, if changed remove cache and rebuild
	    if(!is_null($arrTemplateData) && $arrTemplateData['html_hash'] !== \Twist::File()->hash($dirFullTemplatePath,'md5')){
		    \Twist::Cache('pkgTemplate')->remove($strCacheKey);
		    $arrTemplateData = null;
	    }

	    if(is_null($arrTemplateData)){
		    $arrTemplateData = array();

		    $arrTemplateData['html_raw'] = $this->getTemplateFile($dirFullTemplatePath);
		    $arrTemplateData['html_hash'] = \Twist::File()->hash($dirFullTemplatePath,'md5');
		    $arrTemplateData['tags'] = $this->getTemplateTags($arrTemplateData['html_raw'],false);

		    \Twist::Cache('pkgTemplate')->store($strCacheKey,$arrTemplateData,$this->framework()->setting('TEMPLATE_PRE_PROCESS_CACHE'));
	    }

        foreach($arrTemplateData['tags'] as $strEachTag){
	        $arrTemplateData['html_raw'] = $this->processTag($arrTemplateData['html_raw'],$strEachTag,$arrTemplateTags);
        }

        //Remove all un-used template tags
        if($blRemoveUnusedTags){
	        $arrTemplateData['html_raw'] = $this->removeUnusedTags($arrTemplateData['html_raw']);
        }

        if($this->framework() -> setting('DEVELOPMENT_MODE')){
            $this->framework() -> debug() -> log('Template','usage',array('instance' => $this->strInstanceKey,'file' => $dirTemplate,'tags' => $arrTemplateData['tags']));
        }

        //$this->arrStats[] = array('file' => $strTemplate,'tags' => $arrLiveTags);
        //$this->arrStats[] = array('file' => $strTemplate,'tags' => $arrLiveTags,'data' => $arrTemplateTags);

        return $arrTemplateData['html_raw'];
    }

    /**
     * Replace tags in raw template data with the array of tags supplied
     *
     * @param $strRawTemplateData
     * @param $arrTemplateTags
     * @param $blRemoveUnusedTags
     * @return string
     */
    public function replace($strRawTemplateData,$arrTemplateTags = null,$blRemoveUnusedTags = false) {

        $strTemplateDataOut = null;
        $this->validDataTags($arrTemplateTags);
	    $this->dirCurrentTemplate = null;

        //Check that the raw template data is not null or blank
        if(!is_null($strRawTemplateData) && $strRawTemplateData != ''){

            $arrLiveTags = $this->getTemplateTags($strRawTemplateData,false);

            foreach($arrLiveTags as $strEachTag){
                $strRawTemplateData = $this->processTag($strRawTemplateData,$strEachTag,$arrTemplateTags);
            }

            //No tags found, return raw template data
            $strTemplateDataOut = $strRawTemplateData;

            //Remove all un-used template tags
            if($blRemoveUnusedTags){
                $strTemplateDataOut = $this->removeUnusedTags($strTemplateDataOut);
            }

        }else{

            throw new \Exception('Raw template data is empty.');
        }

        return $strTemplateDataOut;
    }

    /**
     * Get all the tags of a given template and return them as an array
     *
     * @param $strTemplate
     * @param $blIsFile
     * @param $blDiscover
     * @return array
     */
    public function getTemplateTags($strTemplate,$blIsFile = true,$blDiscover = false){

        $arrOut = array();

        //Get the raw template data
        $strRawTemplateData = ($blIsFile) ? $this->getTemplateFile($strTemplate) : $strTemplate;

        //Grab all the tags out of the template
        preg_match_all("#\{([^\{\}\n]+)\}#i",$strRawTemplateData,$arrTemplateTags);

        //Check their is an array of tags before returning them
        if(is_array($arrTemplateTags) && count($arrTemplateTags) > 0){
            $arrOut = $arrTemplateTags[1];
        }

        //If Discovery mode is enabled, we can run through the conditional tags and pick out more tags
        if($blDiscover){

            foreach($arrOut as $strEachTag){
                if(preg_match("#^([^?]+)\?([\w\W]+\:[\w\W]+)$#",$strEachTag,$arrItems)){

                    //For the time being only match tags used in the results as ones in the condition are not displayed on the page
                    preg_match_all("#(\'([^\']*)\'|\"([^\"]*)\"|([\d]+)|([\w\.\-\_\/]+:[\w\.\_\-\/]+)):?#",$arrItems[2],$arrResults);

                    //Check result 'true'
                    if($arrResults[5][0] != ''){
                        $arrOut[] = $arrResults[5][0];
                    }

                    //Check result 'false'
                    if($arrResults[5][1] != ''){
                        $arrOut[] = $arrResults[5][1];
                    }
                }
            }
        }

        return $arrOut;
    }

    /**
     * Removes all tags that remain in the template after use
     *
     * @param $strTemplateData
     * @return string
     */
    public function removeUnusedTags($strTemplateData){

        $arrTags = array();
        $arrTemplateTags = $this->getTemplateTags($strTemplateData,false);

        if(is_array($arrTemplateTags) && count($arrTemplateTags) > 0){

            foreach($arrTemplateTags as $strTag){
                $strTemplateData = $this->replaceTag($strTemplateData,$strTag,'');
            }
        }

        return $strTemplateData;
    }

    /**
     * Get the raw template data form teh template file
     *
     * @param $strTemplateFullPath
     * @return string
     */
    protected function getTemplateFile($strTemplate){

        $strRawTemplateDataOut = null;

        if(!is_file($strTemplate)){
            //Try using the designated template directory
            $strTemplate = sprintf("%s%s",$this->dirTemplates,$strTemplate);
        }

        //Check to see if the template file exists
        if(is_file($strTemplate)){

            if(filesize($strTemplate)){

                if($this -> framework() -> setting('TEMPLATE_BASE_OVERRIDE') || strstr(realpath($strTemplate),BASE_LOCATION)){
                    $strRawTemplateDataOut = file_get_contents($strTemplate);
                }else{
                    throw new \Exception(sprintf("Template file '%s' is outside of your Document Root.",$strTemplate),11107);
                }
            }else{
                $strRawTemplateDataOut = '';
                trigger_error(sprintf("Twist [11101]: Template file '%s' contains no data.",$strTemplate), E_USER_NOTICE);
                //throw new \Exception(sprintf("Template file '%s' contains no data.",$strTemplate),11101);
            }
        }else{
            throw new \Exception(sprintf("Template file '%s' was not found or does not exist.",$strTemplate),11102);
        }

        return $strRawTemplateDataOut;
    }

    /**
     * Decide weather the template data tags are valid or not
     *
     * @param $arrTemplateTags
     * @return boolean
     */
    protected function validDataTags($arrTemplateTags){

        $blOut = false;

        //Check to see if the tags are set to null
        if(is_null($arrTemplateTags)){
            $blOut = true;
        }else{

            //If the tags contain an array then they can be used
            if(is_array($arrTemplateTags)){
                $blOut = true;
            }else{
                throw new \Exception('Template tags are an invalid format, must be and array or null.',11103);
            }
        }

        return $blOut;
    }

    /**
     * Process each individual tag from the template one by one
     *
     * @param $strRawTemplate
     * @param $strTag
     * @param $arrData
     * @return mixed
     */
    protected function processTag($strRawTemplate,$strTag,$arrData = array()){

        if(strstr($strTag,':')){

            //Step 1 - Check to see if this is a conditional tag
            $blCondition = preg_match("#^([^?]+)\?([\w\W]+\:[\w\W]+)$#",$strTag,$arrItems);

            if($blCondition){

                //Step 2 - Match the Conditions with parenthesis and option type matching
                preg_match_all("#(\)|\()?([\w\d\:\-\_\.\'\"\[\]\/]+)([\=\!]{3}|[=\>\<\*\^\!\$]{2}|[\<\>\*]{1})([\w\d\:\-\_\.\'\"\/]+)(\)|\()?(&&|\|\|)?#",$arrItems[1],$arrConditions);

                $arrResults = array();
                $strParenthesisOpen = $strParenthesisClose = false;
                $blParenthesisResult = true;
                $intResultPointer = 0;
                $strPreviousConditions = $strPreviousParenthesisConditions = '';

                $strTempTag1 = '';
                $strTempTag2 = '';

                //Go through each condition one by one
                foreach($arrConditions[1] as $intKey => $strValue){

                    //Detect opening parenthesis
                    $strParenthesisOpen = (!$strParenthesisOpen && $strValue == '(') ? true : $strParenthesisOpen;

                    //Get the two values to be compared
                    //@todo Re-write the functionality to the below 4 connected functions
                    $arrValue1Parts = (strstr($arrConditions[2][$intKey],':')) ? explode(':',$arrConditions[2][$intKey]) : null;
                    $arrValue2Parts = (strstr($arrConditions[4][$intKey],':')) ? explode(':',$arrConditions[4][$intKey]) : null;

                    //Build the data to correctly decode each tag in condition 1
                    if(!is_null($arrValue1Parts)){
                        $strTempTag1 = sprintf('{%s}',$arrConditions[2][$intKey]);
                        $strTempReplace1 = $arrConditions[2][$intKey];

                    }

                    //Build the data to correctly decode each tag in condition 2
                    if(!is_null($arrValue2Parts)){
                        $strTempTag2 = sprintf('{%s}',$arrConditions[4][$intKey]);
                        $strTempReplace2 = $arrConditions[4][$intKey];
                    }

                    $mxdValue1 = (!is_null($arrValue1Parts)) ? $this->runTags($strTempTag1,$strTempReplace1,$arrValue1Parts[0],$arrValue1Parts[1],$arrData,true) : $arrConditions[2][$intKey];
                    $mxdValue2 = (!is_null($arrValue2Parts)) ? $this->runTags($strTempTag2,$strTempReplace2,$arrValue2Parts[0],$arrValue2Parts[1],$arrData,true) : $arrConditions[4][$intKey];

                    //Detect undefined parameters and match against 'undefined'
                    $mxdValue1 = ($mxdValue1 === $strTempTag1) ? 'twst-undefined-variable' : $mxdValue1;
                    $mxdValue2 = ($mxdValue2 === $strTempTag2) ? 'twst-undefined-variable' : $mxdValue2;

                    //Test the values with the condition
                    $blResult = $this->condition($mxdValue1,$arrConditions[3][$intKey],$mxdValue2);

                    //Detect closing parenthesis
                    $strParenthesisClose = ($arrConditions[5][$intKey] == ')');

                    //If the current parenthesis result is true and previous parenthesis condition is && or ''
                    //or If the current parenthesis result is false and the previous condition is OR Log Results
                    //or If not in parenthesis send result through
                    if(( in_array($strPreviousParenthesisConditions,array('&&','')) && $blParenthesisResult )
                        || ( $strPreviousParenthesisConditions == '||' && !$blParenthesisResult )
                        || ( $strParenthesisOpen == false && $blParenthesisResult )){

                        $blParenthesisResult = $blResult;
                    }

                    //If the parenthesis is not open or has opened and closed then log the result and rest vars
                    if($strParenthesisOpen == false || ($strParenthesisOpen && $strParenthesisClose)){

                        if(in_array($strPreviousConditions,array('&&','')) && (!array_key_exists($intResultPointer,$arrResults) || $arrResults[$intResultPointer] == true)){
                            $arrResults[$intResultPointer] = $blParenthesisResult;
                        }elseif($strPreviousConditions == '||'){
                            $intResultPointer++;
                            $arrResults[$intResultPointer] = $blParenthesisResult;
                        }

                        $strParenthesisOpen = $strParenthesisClose = false;
                        $blParenthesisResult = true;
                        $strPreviousParenthesisConditions = '';
                    }

                    //Set the previous condition
                    if($strParenthesisOpen){
                        $strPreviousParenthesisConditions = $arrConditions[6][$intKey];
                    }else{
                        $strPreviousConditions = $arrConditions[6][$intKey];
                    }
                }

                //Run through the results and see if conditions have been met
                $blOut = false;
                foreach($arrResults as $blStatus){
                    $blOut = ($blStatus) ? true : $blOut;
                }

                //Step 4 - Grab the result parameters
                preg_match_all("#(\'([^\']*)\'|\"([^\"]*)\"|([\d]+)|([\w\.\-\_\/]+:[\w\.\_\-\/]+)):?#",$arrItems[2],$arrResults);

                if($arrResults[5][($blOut)?0:1] != ''){
                    $arrTagParts = explode(':',$arrResults[5][($blOut)?0:1]);
                    $strRawTemplate = $this->runTags($strRawTemplate,$strTag,$arrTagParts[0],$arrTagParts[1],$arrData);
                }else{
                    $intConditionResult = ($blOut)?0:1;

                    if(!is_null($arrResults[2][$intConditionResult])){
                        $strOut = $arrResults[2][$intConditionResult];
                    }elseif(!is_null($arrResults[3][$intConditionResult])){
                        $strOut = $arrResults[3][$intConditionResult];
                    }elseif(!is_null($arrResults[4][$intConditionResult])){
                        $strOut = $arrResults[4][$intConditionResult];
                    }else{
                        $strOut = '';
                    }

                    $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strOut);
                }

            }else{

                //Grab the tag data and precess
                $arrTagParts = explode(':',$strTag);

                //Grab the parts and rebuild the string
                $strType = $arrTagParts[0];
                unset($arrTagParts[0]);
                $strReference = implode(':',$arrTagParts);

                $strRawTemplate = $this->runTags($strRawTemplate,$strTag,$strType,$strReference,$arrData);
            }
        }

        return $strRawTemplate;
    }

    /**
     * Detect and correct the type of the inputs contents
     *
     * @param $mxdValue
     * @return bool|int|mixed|null|string
     */
    protected function detectType($mxdValue){

        if(!is_bool($mxdValue) && !is_array($mxdValue)){

            //Get the length of the original string and strip containing quote marks
            $intLength = strlen($mxdValue);

            //Clean the string up
            $mxdValue = ($mxdValue == "''" || $mxdValue == '""') ? '' : $mxdValue;
            $mxdValue = preg_replace('/^([\'|\"]{1})([\W\w]+)([\'|\"]{1})$/', '$2', $mxdValue);

            //If the length has stayed the same it is not a string and type needs correcting
            $blDetect = ($intLength == strlen($mxdValue));

            if($blDetect && $mxdValue == 'null'){
                $mxdValue = null;
            }elseif($blDetect && $mxdValue == 'undefined'){
                $mxdValue = 'twst-undefined-variable';
            }elseif($blDetect && $mxdValue == 'true'){
                $mxdValue = true;
            }elseif($blDetect && $mxdValue == 'false'){
                $mxdValue = false;
            }elseif($blDetect && preg_match('#^[0-9]+\.[0-9]+$#',$mxdValue)){
                settype( $mxdValue , 'float' );
            }elseif($blDetect && is_numeric($mxdValue)){
                settype( $mxdValue , 'integer' );
            }
        }

        return $mxdValue;
    }

    /**
     * Run the logical comparison between to sets of data
     *
     * @param $mxdValue1
     * @param $strCondition
     * @param $mxdValue2
     * @return bool
     */
    protected function condition($mxdValue1,$strCondition,$mxdValue2){

        $blOut = false;

        //Sanitise and detect type of each variable
        $mxdValue1 = $this->detectType($mxdValue1);
        $mxdValue2 = $this->detectType($mxdValue2);

        switch($strCondition){
            case'===':
                $blOut = ($mxdValue1 === $mxdValue2);
                break;
            case'!==':
                $blOut = ($mxdValue1 !== $mxdValue2);
                break;
            case'==':
                $blOut = ($mxdValue1 == $mxdValue2 || ($mxdValue1 == '' && $mxdValue2 === 'twst-undefined-variable') || ($mxdValue2 == '' && $mxdValue1 === 'twst-undefined-variable'));
                break;
            case'<':
                $blOut = ($mxdValue1 < $mxdValue2);
                break;
            case'>':
                $blOut = ($mxdValue1 > $mxdValue2);
                break;
            case'<=':
                $blOut = ($mxdValue1 <= $mxdValue2);
                break;
            case'>=':
                $blOut = ($mxdValue1 >= $mxdValue2);
                break;
            case'!=':
                $blOut = ($mxdValue1 != $mxdValue2);
                break;
            case'*':
                $blOut = in_array($mxdValue2,$mxdValue1);
                break;
            case'^=':
                $blOut = (substr($mxdValue1,0,strlen($mxdValue2)) == $mxdValue2);
                break;
            case'*=':
                $blOut = (strpos($mxdValue1,$mxdValue2) !== false);
                break;
            case'$=':
                $blOut = (substr($mxdValue1,strlen($mxdValue1)-strlen($mxdValue2),strlen($mxdValue2)) == $mxdValue2);
                break;
        }

        return $blOut;
    }

	/**
	 * Run the tag processing on each tag that was found in the template and process them accordingly (Snipit module is required to process multi-dimensional tag arrays)
	 *
	 * @param $strRawTemplate
	 * @param $strTag
	 * @param $strType
	 * @param $strReference
	 * @param $arrData
	 * @return mixed
	 */
	public function runTags($strRawTemplate,$strTag,$strType,$strReference,$arrData = array(),$blReturnArray = false){

		$strFunction = null;

		if(preg_match("#(.*)\[(.*)\:(.*)\]#",$strTag,$srtMatchResults)){
			$strFunction = $srtMatchResults[1];
			$strType = $srtMatchResults[2];
			$strReference = $srtMatchResults[3];
		}

		switch($strType){

			case'data':

				$arrResult = $this->processArrayItem($strReference,$arrData,$blReturnArray);

				if($arrResult['status'] == true){
					$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}

				break;

			case'element':

				$strOut = $this->processElement($strReference,$arrData);
				$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strOut,$strFunction);
				break;

			case'template':

				$strTemplate = $strReference;

				//Allow the use of "structure_template" which will determine the current structure template
				if($strReference == 'structure_template'){
					$arrStructure = \Twist::Structure() -> getCurrent();
					$strTemplate = $arrStructure['template']['tpl_file'];
				}

				if(substr($strTemplate,0,1) == '.'){
					$strTemplate = sprintf('%s/%s',dirname($this->dirCurrentTemplate),$strTemplate);
				}

				$strTagData = $this->build($strTemplate,$arrData);
				$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strTagData,$strFunction);
				break;

			/**
			 * PHP Global VARS GET, POST, SERVER, COOKIE
			 */
			case'get':
			case'raw-get':

				$arrResult = $this->processArrayItem($strReference,$_GET,$blReturnArray);

				if($arrResult['status'] == true){
					//Protect against XSS attacks
					$arrResult['return'] = ($strType == 'raw-get') ? $arrResult['return'] : htmlspecialchars($arrResult['return']);
					$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}

				break;

			case'post':
			case'raw-post':

				$arrResult = $this->processArrayItem($strReference,$_POST,$blReturnArray);

				if($arrResult['status'] == true){
					//Protect against XSS attacks
					$arrResult['return'] = ($strType == 'raw-post') ? $arrResult['return'] : htmlspecialchars($arrResult['return']);
					$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}
				break;

			case'setting':

				$strOut = \Twist::framework()->setting(strtoupper($strReference));
				$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strOut,$strFunction);
				break;

			case'server':

				$arrResult = $this->processArrayItem($strReference,$_SERVER,$blReturnArray);

				if($arrResult['status'] == true){
					$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}

				break;

			case'cookie':

				$arrResult = $this->processArrayItem(strtoupper($strReference),$_COOKIE,$blReturnArray);

				if($arrResult['status'] == true){
					//Protect against XSS attacks
					$arrResult['return'] = htmlspecialchars($arrResult['return']);
					$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}
				break;

			case'static':
				$strReference = trim($strReference,'"');
				$strReference = trim($strReference,"'");
				$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strReference,$strFunction);
				break;

			case'date':
				$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,\Twist::DateTime()->date($strReference),$strFunction);
				break;

			case'uri':
				$urlTraversed = \Twist::framework()->tools()->traverseURI($strReference);
				$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$urlTraversed,$strFunction);
				break;

			case'twist':

				switch($strReference){

					case'version':
						$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,TWIST_VERSION,$strFunction);
						break;
				}

				break;

			/**
			 * Use custom tags, set using registerTag('tagType',array('key' => 'value'))
			 */
			default:

				//Get all registered extensions form the extension handler
				$arrExtensions = $this -> __extensions();

				if(count($arrExtensions) && array_key_exists($strType,$arrExtensions)){

					$strReplacementData = '';

					if(array_key_exists('module',$arrExtensions[$strType])){

						$strClassName = $arrExtensions[$strType]['module'];
						$strFunctionName = $arrExtensions[$strType]['function'];

						$strReplacementData = \Twist::$strClassName() -> $strFunctionName($strReference);

					}elseif(array_key_exists('class',$arrExtensions[$strType])){

						$strClassName = sprintf('\\%s',$arrExtensions[$strType]['class']);
						$strFunctionName = $arrExtensions[$strType]['function'];

						$objClass = new $strClassName();
						$strReplacementData = $objClass -> $strFunctionName($strReference);

					}elseif(array_key_exists('instance',$arrExtensions[$strType])){

						$resClass = \TwistPHP\Instance::retrieveObject($arrExtensions[$strType]['instance']);
						$strFunctionName = $arrExtensions[$strType]['function'];

						$strReplacementData = $resClass -> $strFunctionName($strReference);

					}elseif(array_key_exists('function',$arrExtensions[$strType])){

						$strFunctionName = $arrExtensions[$strType]['function'];
						$strReplacementData = call_user_func($strFunctionName,$strReference);
					}else{

						if(strstr($strReference,'/')){
							$mxdTempData = $this->framework()->tools()->arrayParse($strReference,$arrExtensions[$strType]);
							$strReplacementData = (is_array($mxdTempData) && $blReturnArray == false) ? print_r($mxdTempData,true) : $mxdTempData;
						}else{
							if(array_key_exists($strReference,$arrExtensions[$strType])){
								$strReplacementData = (is_array($arrExtensions[$strType][$strReference]) && $blReturnArray == false) ? print_r($arrExtensions[$strType][$strReference],true) : $arrExtensions[$strType][$strReference];
							}
						}
					}

					$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strReplacementData,$strFunction);
				}

				//if(count($this->arrCustomTags) && array_key_exists($strType,$this->arrCustomTags) && array_key_exists($strReference,$this->arrCustomTags[$strType])){
				//	$strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$this->arrCustomTags[$strType][$strReference]);
				//}
				break;

		}

		return $strRawTemplate;
	}

	/**
	 * Find an item within an array of data, return the round status and the return value.
	 *
	 * @param $strKey Key that can contain / to move though an arrays structure
	 * @param $arrData Array of data to be searched
	 * @param $blReturnArray Option to define if an array or string must be retured
	 * @return array The results of the search with status
	 */
	protected function processArrayItem($strKey,$arrData,$blReturnArray=false){

		$arrResponse = array('status' => false,'return' => '','return_raw' => '');

		if(is_array($arrData)){

			if(strstr($strKey,'/')){

				$mxdTempData = $this -> framework() -> tools() -> arrayParse($strKey,$arrData);

				$arrResponse['status'] = (is_null($mxdTempData)) ? false : true;
				$arrResponse['return'] = (is_array($mxdTempData) && $blReturnArray == false) ? print_r($mxdTempData,true) : $mxdTempData;
				$arrResponse['return_raw'] = $mxdTempData;

				//Do what normally happens
			}elseif(array_key_exists($strKey,$arrData)){

				$arrResponse['status'] = true;
				$arrResponse['return'] = (is_array($arrData[$strKey]) && $blReturnArray == false) ? print_r($arrData[$strKey],true) : $arrData[$strKey];
				$arrResponse['return_raw'] = $arrData[$strKey];
			}
		}

		return $arrResponse;
	}

    /**
     * For elements to get the element data when required
     * @return array
     */
    protected function getData(){
        return $this->arrElementData;
    }

    /**
     * Get the parameters passed in after the element file. These must be comma separated.
     * @return array
     */
    protected function getParameters(){
        return $this->arrElementParams;
    }

    /**
     * Process element tag, return the data captured from the output of the element.
     * Additional parameters are exploded of the end of the Element var, these parameters are comma separated.
     * To retrieve the parameters use $this->getParameters(); in your element.
     *
     * @param $dirElement
     * @param $arrData
     * @return string
     * @throws \Exception
     */
    public function processElement($dirElement,$arrData = null){

        $strOut = '';
        $this->arrElementData = $arrData;

        //If their are parameters then pass them through
        if(strstr($dirElement,',')){
            $arrParts = explode(',',$dirElement);
	        $dirElement = $arrParts[0];

            //Build the param array from the remaining parts
            unset($arrParts[0]);
            $this->arrElementParams = array_values( $arrParts );
        }

        //Check to see if it is a full path or partial path
        $dirElement = (!is_file($dirElement)) ? sprintf("%s%s",$this->dirElements,$dirElement) : $dirElement;

        if(file_exists($dirElement)){

            if($this -> framework() -> setting('TEMPLATE_BASE_OVERRIDE') || strstr(realpath($dirElement),BASE_LOCATION)){
                ob_start();
                include $dirElement;
                $strOut = ob_get_contents();
                ob_end_clean();
            }else{
                throw new \Exception(sprintf("Element file '%s' is outside of your Document Root.",$dirElement),11109);
            }
        }else{
            throw new \Exception(sprintf("Twist element '%s' was not found!",strtolower($dirElement)));
        }

        return $strOut;
    }

    /**
     * Replace the tag in the template data with the provided content
     *
     * @param $strRawTemplate
     * @param $strTag
     * @param $strData
     * @param $strFunction
     * @param $mxdRawData
     * @return mixed
     */
    protected function replaceTag($strRawTemplate,$strTag,$strData,$strFunction = null,$mxdRawData = array()){

        if(!is_null($strFunction)){

            $arrAllowedFunctions = array(
                'sha1','md5',
                'urlencode','urldecode',
                'base64_encode','base64_decode',
                'htmlentities','htmlspecialchars',
                'json_encode','json_decode',
                'strip_tags',
                'addslashes','stripslashes',
                'count','round','ceil','floor',
                'strlen','strtolower','strtoupper',
                'ucfirst','ucwords',
                'prettytime','bytestosize',
                'date'
            );

            if(in_array($strFunction,$arrAllowedFunctions) || $strFunction == 'escape'){

                if(in_array($strFunction,array('count','json_encode',))){
	                //This is used when processing arrays
	                $strData = call_user_func($strFunction,$mxdRawData);
                }elseif($strFunction == 'date'){

                    $strDateFormat = 'Y-m-d H:i:s';

                    if(strstr($strTag,',')){
                        $arrParts = explode(',',$strTag);
                        unset($arrParts[0]);
                        $strDateFormat = rtrim(implode(',',$arrParts),']');
                    }

                    $strData = date($strDateFormat,strtotime($strData));

                }elseif(function_exists($strFunction)){
                    $strData = call_user_func($strFunction,$strData);
                }elseif($strFunction == 'escape'){
                    $strData = htmlspecialchars($strData);
                }elseif($strFunction == 'prettytime'){
                    $strData = \Twist::DateTime() -> getAge($strData);
                }elseif($strFunction == 'bytestosize'){
                    $strData = \Twist::File() -> bytesToSize($strData);
                }
            }else{
                trigger_error(sprintf("Twist Template: function '%s' is disabled",$strFunction),E_USER_NOTICE);
            }
        }

        //Build the tag replace expression
        $strExpression = sprintf("{%s}",$strTag);

        //If the expression is equal to the raw template return the data as is otherwise replace the tag with the value
        if($strExpression == $strRawTemplate){
            $strRawTemplate = $strData;
        }else{
            $strRawTemplate = str_replace($strExpression,$strData,$strRawTemplate);
        }

        return $strRawTemplate;
    }
}