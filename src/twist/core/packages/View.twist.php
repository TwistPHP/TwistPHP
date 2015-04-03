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
use \Twist\Core\Classes\BasePackage;
use \Twist\Core\Classes\Instance;

/**
 * Bring HTML to life with conditional tags, allowing interaction with PHP without using PHP code. Include functionality of additional views in just a fue chars
 */
class View extends BasePackage{

	protected $strInstanceKey = '';
	protected $dirViews = '';
	protected $dirElements = '';
	protected $arrElementData = array();
	protected $arrElementParams = array();
	protected $dirCurrentView = null;
	protected $blDebugMode = false;

	public function __construct($strInstanceKey){
	    $this->strInstanceKey = $strInstanceKey;
	    $this->setDirectory();
	    $this->setElementsDirectory();
	    $this->blDebugMode = (\Twist::framework()->setting('DEVELOPMENT_MODE') && \Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR'));
	}

	/**
	 * @deprecated
	 * @alias get
	 */
	protected function getTemplateFile($strTemplate){
		return $this->get($strTemplate);
	}

	/**
	 * @deprecated
	 * @alias setViewsDirectory
	 */
	public function setTemplatesDirectory($dirCustomTemplates = null){
		$this->setDirectory($dirCustomTemplates);
	}

	/**
	 * @deprecated
	 * @alias getDirectory
	 */
	public function getTemplatesDirectory(){
		return $this->dirViews;
	}

	/**
	 * @deprecated
	 * Set the element directory to the default or provide a new directory
	 * @param $dirCustomElements Path to a custom elements directory
	 */
	public function setElementsDirectory($dirCustomElements = null){
		$this->dirElements = (is_null($dirCustomElements)) ? $this->dirViews : $dirCustomElements;
	}

	/**
	 * @deprecated
	 * Get the current element directory/path that is in use by the template package
	 * @return directory Returns the current element path
	 */
	public function getElementsDirectory(){
		return $this->dirElements;
	}

	/**
	 * @deprecated
	 * @alias getTags
	 */
	public function getTemplateTags($strTemplate,$blIsFile = true,$blDiscover = false){
		return $this->getTags($strTemplate,$blIsFile,$blDiscover);
	}




	/**
	 * Kills the current instance of the View class, help to keep your RAM free
	 */
	public function kill(){
	    Instance::removeObject(($this->strInstanceKey == 'twist') ? 'pkgView' : sprintf('pkgView-%s',$this->strInstanceKey));
	}

	/**
	 * Set the View directory to default or provide a new directory
	 * @param $dirCustomViews Path to a custom View directory
	 */
	public function setDirectory($dirCustomViews = null){
	    $this->dirViews = (is_null($dirCustomViews)) ? DIR_APP_VIEWS : $dirCustomViews;
	    $this->dirElements =  $this->dirViews;
	}

	/**
	 * Get the current View directory/path that is in use by the View package
	 * @return directory Returns the current View path
	 */
	public function getDirectory(){
		return $this->dirViews;
	}

	/**
	 * Build the View with the array of tags supplied
	 *
	 * @param $dirView
	 * @param $arrViewTags
	 * @param $blRemoveUnusedTags
	 * @return string
	 */
	public function build($dirView,$arrViewTags = null,$blRemoveUnusedTags = false) {

	    $strViewDataOut = null;
	    $this->validDataTags($arrViewTags);
	    $this->dirCurrentView = $dirView;

	    $dirFullViewPath = (!is_file($dirView)) ? sprintf("%s%s",$this->dirViews,$dirView) : $dirView;
		$strCacheKey = str_replace(array(BASE_LOCATION,'twist/interfaces','twist/core'),array('','twist-interface','twist-core'),$dirFullViewPath);
		$arrViewData = \Twist::Cache('pkgView')->retrieve($strCacheKey);

	    //Detect if the file has changed, if changed remove cache and rebuild
	    if(!is_null($arrViewData) && $arrViewData['html_hash'] !== \Twist::File()->hash($dirFullViewPath,'md5')){
		    \Twist::Cache('pkgView')->remove($strCacheKey);
		    $arrViewData = null;
	    }

	    if(is_null($arrViewData)){
		    $arrViewData = array();

		    $arrViewData['html_raw'] = $this->get($dirFullViewPath);
		    $arrViewData['html_hash'] = \Twist::File()->hash($dirFullViewPath,'md5');
		    $arrViewData['tags'] = $this->getTags($arrViewData['html_raw'],false);

		    \Twist::Cache('pkgView')->store($strCacheKey,$arrViewData,$this->framework()->setting('TEMPLATE_PRE_PROCESS_CACHE'));
	    }

	    foreach($arrViewData['tags'] as $strEachTag){
	        $arrViewData['html_raw'] = $this->processTag($arrViewData['html_raw'],$strEachTag,$arrViewTags);
	    }

	    //Remove all un-used View tags
	    if($blRemoveUnusedTags){
	        $arrViewData['html_raw'] = $this->removeUnusedTags($arrViewData['html_raw']);
	    }

	    if($this->blDebugMode){
	        \Twist::framework()->debug()->log('View','usage',array('instance' => $this->strInstanceKey,'file' => $dirView,'tags' => $arrViewData['tags']));
	    }

	    return $arrViewData['html_raw'];
	}

	/**
	 * Replace tags in raw View data with the array of tags supplied
	 *
	 * @param $strRawViewData
	 * @param $arrViewTags
	 * @param $blRemoveUnusedTags
	 * @return string
	 */
	public function replace($strRawViewData,$arrViewTags = null,$blRemoveUnusedTags = false) {

	    $strViewDataOut = null;
	    $this->validDataTags($arrViewTags);
	    $this->dirCurrentView = null;

	    //Check that the raw View data is not null or blank
	    if(!is_null($strRawViewData) && $strRawViewData != ''){

	        $arrLiveTags = $this->getTags($strRawViewData,false);

	        foreach($arrLiveTags as $strEachTag){
	            $strRawViewData = $this->processTag($strRawViewData,$strEachTag,$arrViewTags);
	        }

	        //No tags found, return raw View data
	        $strViewDataOut = $strRawViewData;

	        //Remove all un-used View tags
	        if($blRemoveUnusedTags){
	            $strViewDataOut = $this->removeUnusedTags($strViewDataOut);
	        }

	    }else{

	        throw new \Exception('Raw View data is empty.');
	    }

	    return $strViewDataOut;
	}

	/**
	 * Get all the tags of a given View and return them as an array
	 *
	 * @param $strView
	 * @param $blIsFile
	 * @param $blDiscover
	 * @return array
	 */
	public function getTags($strView,$blIsFile = true,$blDiscover = false){

	    $arrOut = array();

	    //Get the raw View data
	    $strRawViewData = ($blIsFile) ? $this->get($strView) : $strView;

	    //Grab all the tags out of the View
	    preg_match_all("#\{([^\{\}\n]+)\}#i",$strRawViewData,$arrViewTags);

	    //Check their is an array of tags before returning them
	    if(is_array($arrViewTags) && count($arrViewTags) > 0){
	        $arrOut = $arrViewTags[1];
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
	 * Removes all tags that remain in the View after use
	 *
	 * @param $strViewData
	 * @return string
	 */
	public function removeUnusedTags($strViewData){

	    $arrTags = array();
	    $arrViewTags = $this->getTags($strViewData,false);

	    if(is_array($arrViewTags) && count($arrViewTags) > 0){

	        foreach($arrViewTags as $strTag){
	            $strViewData = $this->replaceTag($strViewData,$strTag,'');
	        }
	    }

	    return $strViewData;
	}
	
	/**
	 * Get the raw View data form the View file
	 *
	 * @param $strViewFullPath
	 * @return string
	 */
	protected function get($strView){

	    $strRawViewDataOut = null;

	    if(!is_file($strView)){
	        //Try using the designated View directory
	        $strView = sprintf("%s%s",$this->dirViews,$strView);
	    }

	    //Check to see if the View file exists
	    if(is_file($strView)){

	        if(filesize($strView)){

	            //if($this -> framework() -> setting('TEMPLATE_BASE_OVERRIDE') || strstr(realpath($strView),BASE_LOCATION)){
	                $strRawViewDataOut = file_get_contents($strView);
	            //}else{
	           //     throw new \Exception(sprintf("View file '%s' is outside of your Document Root.",$strView),11107);
	           // }
	        }else{
	            $strRawViewDataOut = '';
	            trigger_error(sprintf("Twist [11101]: View file '%s' contains no data.",$strView), E_USER_NOTICE);
	            //throw new \Exception(sprintf("View file '%s' contains no data.",$strView),11101);
	        }
	    }else{
	        throw new \Exception(sprintf("View file '%s' was not found or does not exist.",$strView),11102);
	    }

	    return $strRawViewDataOut;
	}

	/**
	 * Decide weather the View data tags are valid or not
	 *
	 * @param $arrViewTags
	 * @return boolean
	 */
	protected function validDataTags($arrViewTags){

	    $blOut = false;

	    //Check to see if the tags are set to null
	    if(is_null($arrViewTags)){
	        $blOut = true;
	    }else{

	        //If the tags contain an array then they can be used
	        if(is_array($arrViewTags)){
	            $blOut = true;
	        }else{
	            throw new \Exception('View tags are an invalid format, must be and array or null.',11103);
	        }
	    }

	    return $blOut;
	}

	/**
	 * Process each individual tag from the View one by one
	 *
	 * @param $strRawView
	 * @param $strTag
	 * @param $arrData
	 * @return mixed
	 */
	protected function processTag($strRawView,$strTag,$arrData = array()){

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
	                $strRawView = $this->runTags($strRawView,$strTag,$arrTagParts[0],$arrTagParts[1],$arrData);
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

	                $strRawView = $this->replaceTag($strRawView,$strTag,$strOut);
	            }

	        }else{

	            //Grab the tag data and precess
	            $arrTagParts = explode(':',$strTag);

	            //Grab the parts and rebuild the string
	            $strType = $arrTagParts[0];
	            unset($arrTagParts[0]);
	            $strReference = implode(':',$arrTagParts);

	            $strRawView = $this->runTags($strRawView,$strTag,$strType,$strReference,$arrData);
	        }
	    }

	    return $strRawView;
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
	 * Run the tag processing on each tag that was found in the View and process them accordingly (Snipit module is required to process multi-dimensional tag arrays)
	 *
	 * @param $strRawView
	 * @param $strTag
	 * @param $strType
	 * @param $strReference
	 * @param $arrData
	 * @return mixed
	 */
	public function runTags($strRawView,$strTag,$strType,$strReference,$arrData = array(),$blReturnArray = false){

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
					$strRawView = $this->replaceTag($strRawView,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}

				break;

			case'element':

				$strOut = $this->processElement($strReference,$arrData);
				$strRawView = $this->replaceTag($strRawView,$strTag,$strOut,$strFunction);
				break;

			case'template':
			case'view':

				$strView = $strReference;

				//Allow the use of "structure_view" which will determine the current structure view
				if($strReference == 'structure_view' || $strReference == 'structure_template'){
					$arrStructure = \Twist::Structure() -> getCurrent();
					$strView = $arrStructure['view']['tpl_file'];
				}

				if(substr($strView,0,1) == '.'){
					$strView = sprintf('%s/%s',dirname($this->dirCurrentView),$strView);
				}

				$strTagData = $this->build($strView,$arrData);
				$strRawView = $this->replaceTag($strRawView,$strTag,$strTagData,$strFunction);
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
					$strRawView = $this->replaceTag($strRawView,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}

				break;

			case'post':
			case'raw-post':

				$arrResult = $this->processArrayItem($strReference,$_POST,$blReturnArray);

				if($arrResult['status'] == true){
					//Protect against XSS attacks
					$arrResult['return'] = ($strType == 'raw-post') ? $arrResult['return'] : htmlspecialchars($arrResult['return']);
					$strRawView = $this->replaceTag($strRawView,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}
				break;

			case'setting':

				$strOut = \Twist::framework()->setting(strtoupper($strReference));
				$strRawView = $this->replaceTag($strRawView,$strTag,$strOut,$strFunction);
				break;

			case'server':

				$arrResult = $this->processArrayItem($strReference,$_SERVER,$blReturnArray);

				if($arrResult['status'] == true){
					$strRawView = $this->replaceTag($strRawView,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}

				break;

			case'cookie':

				$arrResult = $this->processArrayItem(strtoupper($strReference),$_COOKIE,$blReturnArray);

				if($arrResult['status'] == true){
					//Protect against XSS attacks
					$arrResult['return'] = htmlspecialchars($arrResult['return']);
					$strRawView = $this->replaceTag($strRawView,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}
				break;

			case'static':
				$strReference = trim($strReference,'"');
				$strReference = trim($strReference,"'");
				$strRawView = $this->replaceTag($strRawView,$strTag,$strReference,$strFunction);
				break;

			case'date':
				$strRawView = $this->replaceTag($strRawView,$strTag,\Twist::DateTime()->date($strReference),$strFunction);
				break;

			case'uri':
				$urlTraversed = \Twist::framework()->tools()->traverseURI($strReference);
				$strRawView = $this->replaceTag($strRawView,$strTag,$urlTraversed,$strFunction);
				break;

			case'twist':

				switch($strReference){

					case'version':
						$strRawView = $this->replaceTag($strRawView,$strTag,TWIST_VERSION,$strFunction);
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

						$resClass = \Twist\Core\Classes\Instance::retrieveObject($arrExtensions[$strType]['instance']);
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

					$strRawView = $this->replaceTag($strRawView,$strTag,$strReplacementData,$strFunction);
				}

				//if(count($this->arrCustomTags) && array_key_exists($strType,$this->arrCustomTags) && array_key_exists($strReference,$this->arrCustomTags[$strType])){
				//	$strRawView = $this->replaceTag($strRawView,$strTag,$this->arrCustomTags[$strType][$strReference]);
				//}
				break;

		}

		return $strRawView;
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

	        //if(\Twist::framework()->setting('TEMPLATE_BASE_OVERRIDE') || strstr(realpath($dirElement),BASE_LOCATION)){
	            ob_start();
	            include $dirElement;
	            $strOut = ob_get_contents();
	            ob_end_clean();

	            if($this->blDebugMode){
		            \Twist::framework()->debug()->log('View','usage',array('instance' => $this->strInstanceKey,'file' => $dirElement,'tags' => array()));
	            }

	        //}else{
	        //    throw new \Exception(sprintf("Element file '%s' is outside of your Document Root.",$dirElement),11109);
	        //}
	    }else{
	        throw new \Exception(sprintf("Twist element '%s' was not found!",strtolower($dirElement)));
	    }

	    return $strOut;
	}

	/**
	 * Replace the tag in the View data with the provided content
	 *
	 * @param $strRawView
	 * @param $strTag
	 * @param $strData
	 * @param $strFunction
	 * @param $mxdRawData
	 * @return mixed
	 */
	protected function replaceTag($strRawView,$strTag,$strData,$strFunction = null,$mxdRawData = array()){

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
	            trigger_error(sprintf("Twist View: function '%s' is disabled",$strFunction),E_USER_NOTICE);
	        }
	    }

	    //Build the tag replace expression
	    $strExpression = sprintf("{%s}",$strTag);

	    //If the expression is equal to the raw View return the data as is otherwise replace the tag with the value
	    if($strExpression == $strRawView){
	        $strRawView = $strData;
	    }else{
	        $strRawView = str_replace($strExpression,$strData,$strRawView);
	    }

	    return $strRawView;
	}
}