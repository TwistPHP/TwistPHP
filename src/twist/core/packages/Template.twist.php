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
 * @link       http://twistphp.com/
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
    protected $strTemplatesDir = '';
    protected $strElementsDir = '';
    protected $arrElementData = array();
    protected $arrElementParams = array();

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
     * @param null $strCustomTemplatesDir
     */
    public function setTemplatesDirectory($strCustomTemplatesDir = null){
        $this->strTemplatesDir = (is_null($strCustomTemplatesDir)) ? DIR_TEMPLATES : $strCustomTemplatesDir;
    }

    /**
     * Set the element directory to the default or provide a new directory
     * @param null $strCustomElementsDir
     */
    public function setElementsDirectory($strCustomElementsDir = null){

        if( is_null( $strCustomElementsDir ) ) {
            $this->strElementsDir = sprintf( '%selements/', $this->strTemplatesDir );
        } else {
            $this->strElementsDir = $strCustomElementsDir;
        }
    }

    /**
     * Get the current template directory in use
     * @return string
     */
    public function getTemplatesDirectory(){
        return $this->strTemplatesDir;
    }

    /**
     * Get the current element directory in use
     * @return string
     */
    public function getElementsDirectory(){
        return $this->strElementsDir;
    }

    /**
     * Build the template with the array of tags supplied
     *
     * @param string $strTemplateFullPath
     * @param array $arrTemplateTags
     * @param boolean $blRemoveUnusedTags
     * @return string
     */
    public function build($strTemplate,$arrTemplateTags = null,$blRemoveUnusedTags = false) {

        $strTemplateDataOut = null;
        $this->validDataTags($arrTemplateTags);

        //Get the raw template data
        $strRawTemplateData = $this->getTemplateFile($strTemplate);
        $arrLiveTags = $this->getTemplateTags($strTemplate);

        foreach($arrLiveTags as $strEachTag){
            $strRawTemplateData = $this->processTag($strRawTemplateData,$strEachTag,$arrTemplateTags);
        }

        //No tags found, return raw template data
        $strTemplateDataOut = $strRawTemplateData;

        //Remove all un-used template tags
        if($blRemoveUnusedTags){
            $strTemplateDataOut = $this->removeUnusedTags($strTemplateDataOut);
        }


        if($this->framework() -> setting('DEVELOPMENT_MODE')){
            $this->framework() -> debug() -> log('Template','usage',array('instance' => $this->strInstanceKey,'file' => $strTemplate,'tags' => $arrLiveTags));
        }

        //$this->arrStats[] = array('file' => $strTemplate,'tags' => $arrLiveTags);
        //$this->arrStats[] = array('file' => $strTemplate,'tags' => $arrLiveTags,'data' => $arrTemplateTags);

        return $strTemplateDataOut;
    }

    /**
     * Replace tags in raw template data with the array of tags supplied
     *
     * @param string $strRawTemplateData
     * @param array $arrTemplateTags
     * @param boolean $blRemoveUnusedTags
     * @return string
     */
    public function replace($strRawTemplateData,$arrTemplateTags = null,$blRemoveUnusedTags = false) {

        $strTemplateDataOut = null;
        $this->validDataTags($arrTemplateTags);

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
     * @param string $strTemplate
     * @param boolean $blIsFile
     * @param boolean $blDiscover
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
     * @param string $strTemplateData
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
     * @param string $strTemplateFullPath
     * @return string
     */
    protected function getTemplateFile($strTemplate){

        $strRawTemplateDataOut = null;

        if(!is_file($strTemplate)){
            //Try using the designated template directory
            $strTemplate = sprintf("%s%s",$this->strTemplatesDir,$strTemplate);
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
     * @param array $arrTemplateTags
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

                    $mxdValue1 = (!is_null($arrValue1Parts)) ? $this->runTags($strTempTag1,$strTempReplace1,$arrValue1Parts[0],$arrValue1Parts[1],$arrData) : $arrConditions[2][$intKey];
                    $mxdValue2 = (!is_null($arrValue2Parts)) ? $this->runTags($strTempTag2,$strTempReplace2,$arrValue2Parts[0],$arrValue2Parts[1],$arrData) : $arrConditions[4][$intKey];

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
     * @param $mxdValue
     * @return bool|int|mixed|null|string
     */
    protected function detectType($mxdValue){

        if(!is_bool($mxdValue)){

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
                $blOut = ($mxdValue1 === $mxdValue2) ? true : false;
                break;
            case'!==':
                $blOut = ($mxdValue1 !== $mxdValue2) ? true : false;
                break;
            case'==':
                $blOut = ($mxdValue1 == $mxdValue2 || ($mxdValue1 == '' && $mxdValue2 === 'twst-undefined-variable') || ($mxdValue2 == '' && $mxdValue1 === 'twst-undefined-variable')) ? true : false;
                break;
            case'<':
                $blOut = ($mxdValue1 < $mxdValue2) ? true : false;
                break;
            case'>':
                $blOut = ($mxdValue1 > $mxdValue2) ? true : false;
                break;
            case'<=':
                $blOut = ($mxdValue1 <= $mxdValue2) ? true : false;
                break;
            case'>=':
                $blOut = ($mxdValue1 >= $mxdValue2) ? true : false;
                break;
            case'!=':
                $blOut = ($mxdValue1 != $mxdValue2) ? true : false;
                break;
            case'*':
                $blOut = (in_array($mxdValue1,$mxdValue2)) ? true : false;
                break;
            case'^=':
                $blOut = (substr($mxdValue1,0,strlen($mxdValue2)) == $mxdValue2) ? true : false;
                break;
            case'*=':
                $blOut = (strpos($mxdValue1,$mxdValue2) !== false) ? true : false;
                break;
            case'$=':
                $blOut = (substr($mxdValue1,strlen($mxdValue1)-strlen($mxdValue2),strlen($mxdValue2)) == $mxdValue2) ? true : false;
                break;
        }

        return $blOut;
    }

    /**
     * Run the tag processing on each tag that was found in the template and process them accordingly (Snipit module is required to process multi-dimensional tag arrays)
     * @param $strRawTemplate
     * @param $strTag
     * @param $strType
     * @param $strReference
     * @param $arrData
     * @return mixed
     */
    public function runTags($strRawTemplate,$strTag,$strType,$strReference,$arrData = array()){

        $strFunction = null;

        if(preg_match("#(.*)\[(.*)\:(.*)\]#",$strTag,$srtMatchResults)){
            $strFunction = $srtMatchResults[1];
            $strType = $srtMatchResults[2];
            $strReference = $srtMatchResults[3];
        }

        switch($strType){

            case'data':

                $strTempData = '';
                $blFound = false;

                if(is_array($arrData)){

                    if(strstr($strReference,'/')){

                        $mxdTempData = $this -> framework() -> tools() -> arrayParse($strReference,$arrData);
                        $blFound = (is_null($mxdTempData)) ? false : true;

                        $strTempData = (is_array($mxdTempData)) ? print_r($mxdTempData,true) : $mxdTempData;

                        //Do what normally happens
                    }elseif(array_key_exists($strReference,$arrData)){

                        $blFound = true;
                        $strTempData = (is_array($arrData[$strReference])) ? print_r($arrData[$strReference],true) : $arrData[$strReference];
                    }

                    if($blFound){
                        $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strTempData,$strFunction);
                    }
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

                $strTagData = $this->build($strTemplate,$arrData);
                $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strTagData,$strFunction);
                break;

            /**
             * PHP Global VARS GET, POST, SERVER, COOKIE
             */
            case'get':
            case'raw-get':
                if(array_key_exists($strReference,$_GET)){
                    //Protect against XSS attacks
                    $strData = ($strType == 'raw-get') ? $_GET[$strReference] : htmlspecialchars($_GET[$strReference]);
                    $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strData,$strFunction);
                }
                break;

            case'post':
            case'raw-post':
                if(array_key_exists($strReference,$_POST)){
                    //Protect against XSS attacks
                    $strData = ($strType == 'raw-post') ? $_POST[$strReference] : htmlspecialchars($_POST[$strReference]);
                    $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strData,$strFunction);
                }
                break;

            case'setting':

                $strOut = \Twist::framework()->setting(strtoupper($strReference));
                $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strOut,$strFunction);
                break;

            case'server':
                if(array_key_exists(strtoupper($strReference),$_SERVER)){
                    $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$_SERVER[strtoupper($strReference)],$strFunction);
                }
                break;

            case'cookie':
                if(array_key_exists($strReference,$_COOKIE)){
                    //Protect against XSS attacks
                    $strData = htmlspecialchars($_COOKIE[$strReference]);
                    $strRawTemplate = $this->replaceTag($strRawTemplate,$strTag,$strData,$strFunction);
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

                        $strClassName = $arrExtensions[$strType]['class'];
                        $strFunctionName = $arrExtensions[$strType]['function'];

                        $objClass = new $strClassName();
                        $strReplacementData = $objClass -> $strFunctionName($strReference);

                    }elseif(array_key_exists('function',$arrExtensions[$strType])){

                        $strFunctionName = $arrExtensions[$strType]['function'];
                        $strReplacementData = call_user_func($strFunctionName,$strReference);
                    }else{

                        if(strstr($strReference,'/')){
                            $mxdTempData = $this->framework()->tools()->arrayParse($strReference,$arrExtensions[$strType]);
                            $strReplacementData = (is_array($mxdTempData)) ? print_r($mxdTempData,true) : $mxdTempData;
                        }else{
                            if(array_key_exists($strReference,$arrExtensions[$strType])){
                                $strReplacementData = (is_array($arrExtensions[$strType][$strReference])) ? print_r($arrExtensions[$strType][$strReference],true) : $arrExtensions[$strType][$strReference];
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
     * @param $strElement
     * @param null $arrData
     * @return string
     * @throws Exception
     */
    public function processElement($strElement,$arrData = null){

        $strOut = '';
        $this->arrElementData = $arrData;

        //If their are parameters then pass them through
        if(strstr($strElement,',')){
            $arrParts = explode(',',$strElement);
            $strElement = $arrParts[0];

            //Build the param array from the remaining parts
            unset($arrParts[0]);
            $this->arrElementParams = array_values( $arrParts );
        }

        //Check to see if it is a full path or partial path
        if(!strstr($strElement,'/') || (!file_exists($strElement) && file_exists(sprintf('%s%s',$this->strElementsDir,strtolower($strElement))))){
            $strElement = sprintf('%s%s',$this->strElementsDir,strtolower($strElement));
        }

        if(file_exists($strElement)){

            if($this -> framework() -> setting('TEMPLATE_BASE_OVERRIDE') || strstr(realpath($strElement),BASE_LOCATION)){
                ob_start();
                include $strElement;
                $strOut = ob_get_contents();
                ob_end_clean();
            }else{
                throw new \Exception(sprintf("Element file '%s' is outside of your Document Root.",$strElement),11109);
            }
        }else{
            throw new \Exception(sprintf("Twist element '%s' was not found!",strtolower($strElement)));
        }

        return $strOut;
    }

    /**
     * Replace the tag in the template data with the provided content
     *
     * @param $strRawTemplate
     * @param $strTag
     * @param $strData
     * @return mixed
     */
    protected function replaceTag($strRawTemplate,$strTag,$strData,$strFunction = null){

        if(!is_null($strFunction)){

            $arrAllowedFunctions = array(
                'sha1','md5',
                'urlencode','urldecode',
                'base64_encode','base64_decode',
                'htmlentities','htmlspecialchars',
                'json_encode','json_decode',
                'strip_tags',
                'addslashes','stripslashes',
                'round','ceil','floor',
                'strtolower','strtoupper',
                'ucfirst','ucwords',
                'prettytime','bytestosize',
                'date'
            );

            if(in_array($strFunction,$arrAllowedFunctions) || $strFunction == 'escape'){
                if($strFunction == 'date'){

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