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

namespace Twist\Core\Utilities;

use \Twist\Classes\Instance;
use \Twist\Classes\Exception;

/**
 * Bring HTML to life with conditional tags, allowing interaction with PHP without using PHP code. Include functionality of additional views in just a fue chars
 */
class View extends Base{

	protected $strInstanceKey = '';
	protected $dirViews = '';
	protected $dirElements = '';
	protected $arrViewData = array();
	protected $arrViewParams = array();
	protected $dirCurrentView = null;
	protected $strCurrentTag = null;
	protected $blDebugMode = false;

	public function __construct($strInstanceKey){
	    $this->strInstanceKey = $strInstanceKey;
	    $this->setDirectory();
	    $this->blDebugMode = (\Twist::framework()->setting('DEVELOPMENT_MODE') && \Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR'));
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
	    $this->dirViews = (is_null($dirCustomViews)) ? TWIST_APP_VIEWS : $dirCustomViews;
	    $this->dirElements =  $this->dirViews;
	}

	/**
	 * Get the current View directory/path that is in use by the View utility
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

		//Backup current view path
		$strTempCurrentView = $this->dirCurrentView;

		//If their are parameters then pass them through
		if(strstr($dirView,',')){
			$arrParts = explode(',',$dirView);
			$dirView = $arrParts[0];

			//Build the param array from the remaining parts
			unset($arrParts[0]);
			$this->arrViewParams = array_values( $arrParts );
		}

		$dirFullViewPath = $this->parseViewPath($dirView);

		if(substr($dirFullViewPath,-4) == '.php'){
			$arrViewData['html_raw'] = $this->processElement($dirFullViewPath,$arrViewTags);
		}else{
			$strCacheKey = ltrim(str_replace(array(TWIST_FRAMEWORK_VIEWS,TWIST_PACKAGES,TWIST_APP),array('core','packages','app'),$dirFullViewPath),'/');
			$arrViewData = \Twist::Cache('twist/utility/views')->read($strCacheKey);

			//Detect if the file has changed, if changed remove cache and rebuild
			if(!is_null($arrViewData) && $arrViewData['html_hash'] !== \Twist::File()->hash($dirFullViewPath,'md5')){
				\Twist::Cache('twist/utility/views')->remove($strCacheKey);
				$arrViewData = null;
			}

			if(is_null($arrViewData)){
				$arrViewData = array();

				$arrViewData['html_raw'] = $this->get($dirFullViewPath);
				$arrViewData['html_hash'] = \Twist::File()->hash($dirFullViewPath,'md5');
				$arrViewData['tags'] = $this->getTags($arrViewData['html_raw'],false);

				\Twist::Cache('twist/utility/views')->write($strCacheKey,$arrViewData,\Twist::framework()->setting('VIEW_PRE_PROCESS_CACHE'));
			}

			foreach($arrViewData['tags'] as $strEachTag){
				$arrViewData['html_raw'] = $this->processTag($arrViewData['html_raw'],$strEachTag,$arrViewTags);
			}

			//Remove all un-used View tags
			if($blRemoveUnusedTags){
				$arrViewData['html_raw'] = $this->removeUnusedTags($arrViewData['html_raw']);
			}
		}

	    if($this->blDebugMode){
	        \Twist::framework()->debug()->log('View','usage',array('instance' => $this->strInstanceKey,'file' => $dirView,'tags' => (array_key_exists('tags',$arrViewData)) ? $arrViewData['tags'] : array()));
	    }

		//Restore the current view path
		$this->dirCurrentView = $strTempCurrentView;

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

		//Backup current view path
		$strTempCurrentView = $this->dirCurrentView;
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

		//Restore the current view path
		$this->dirCurrentView = $strTempCurrentView;

	    return $strViewDataOut;
	}

	/**
	 * For elements to get the element data when required
	 * @return array
	 */
	protected function getData(){
		return $this->arrViewData;
	}

	/**
	 * Get the parameters passed in after the element file. These must be comma separated.
	 * @return array
	 */
	protected function getParameters(){
		return $this->arrViewParams;
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
	protected function processElement($dirView,$arrData = null){

		$this->arrViewData = $arrData;

		ob_start();
		include $dirView;
		$strOut = ob_get_contents();
		ob_end_clean();

		return $strOut;
	}

	/**
	 * Get all the tags of a given View and return them as an array
	 *
	 * @param $strView
	 * @param $blIsFile
	 * @param $blDiscover
	 * @return array
	 */
	public function getTags($mxdView,$blIsFile = true,$blDiscover = false){

	    $arrOut = array();

		//Get the raw View data
		if($blIsFile){
			$mxdView = $this->parseViewPath($mxdView);
			$strRawViewData = $this->get($mxdView);
		}else{
			$strRawViewData = $mxdView;
		}

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

	protected function parseViewPath($dirView){

		if(substr($dirView,0,1) == '/'){
			$dirFullViewPath = $dirView;
		}elseif(substr($dirView,0,2) == './'){
			$dirFullViewPath = (is_null($this->dirCurrentView)) ? sprintf('%s/%s',rtrim($this->dirViews,'/'),substr($dirView,2)) : sprintf('%s/%s',dirname($this->dirCurrentView),substr($dirView,2));
		}elseif(substr($dirView,0,3) == '../'){
			$dirFullViewPath = sprintf('%s/%s',dirname($this->dirCurrentView),ltrim($dirView,'/'));
		}else{
			$dirFullViewPath = sprintf("%s/%s",rtrim($this->dirViews,'/'),ltrim($dirView,'/'));

			if(!is_file($dirFullViewPath)){
				$dirFullViewPath = sprintf('%s/%s',dirname($this->dirCurrentView),ltrim($dirView,'/'));
			}
		}

		$strOverridePath = null;

		if(substr($dirFullViewPath,0,strlen(TWIST_FRAMEWORK_VIEWS)) == TWIST_FRAMEWORK_VIEWS){

			//Maybe detect if the usr has created an over-ride for the package override first?

			//If the current system is a package
			if(substr($this->dirViews,0,strlen(TWIST_PACKAGES)) == TWIST_PACKAGES){
				//Framework View - check {PACKAGE_VIEWS}/twist/core/view
				$strOverridePath = sprintf('%s/Twist/Core/Views/%s',rtrim($this->dirViews,'/'),ltrim(substr($dirFullViewPath,strlen(TWIST_FRAMEWORK_VIEWS)-1),'/'));
			}else{
				//Framework View - check TWIST_APP/twist/core/view
				$strOverridePath = sprintf('%s/Twist/Core/Views/%s',rtrim(TWIST_APP,'/'),ltrim(substr($dirFullViewPath,strlen(TWIST_FRAMEWORK_VIEWS)-1),'/'));
			}

		}elseif(substr($dirFullViewPath,0,strlen(TWIST_PACKAGES)) == TWIST_PACKAGES){
			//Packages View - check TWIST_APP/packages (no -1 required for packages)
			$strOverridePath = sprintf('%s/Packages/%s',rtrim(TWIST_APP,'/'),ltrim(substr($dirFullViewPath,strlen(TWIST_PACKAGES)),'/'));
		}

		if(is_file($dirFullViewPath) || is_file($strOverridePath)){

			//Set the current view to the original full path before using a replacement
			$this->dirCurrentView = $dirFullViewPath;

			return (is_file($strOverridePath)) ? $strOverridePath : $dirFullViewPath;
		}else{

			if(!is_null($this->dirCurrentView)){
				$intLineNo = $this->locateTag($this->dirCurrentView,$this->strCurrentTag);
				throw new Exception(sprintf("View file <em>%s</em> was not found or does not exist in <em>%s</em>",str_replace(TWIST_DOCUMENT_ROOT,'/',$dirFullViewPath),str_replace(TWIST_DOCUMENT_ROOT,'/',$this->dirCurrentView)),11102,$this->dirCurrentView,$intLineNo);
			}else{
				//Will work as long as the error is called from build()
				$arrBacktrace = debug_backtrace();
				throw new Exception(sprintf("View file <em>%s</em> was not found or does not exist",str_replace(TWIST_DOCUMENT_ROOT,'/',$dirFullViewPath)),11102,$arrBacktrace[2]['file'],$arrBacktrace[2]['line']);
			}
		}
	}

	/**
	 * Get the raw View data form the View file
	 *
	 * @param $strViewFullPath
	 * @return string
	 */
	protected function get($strView){

	    $strRawViewDataOut = null;

	    //Check to see if the View file exists
	    if(is_file($strView)){

	        if(filesize($strView)){
		        $strRawViewDataOut = file_get_contents($strView);
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
	 * Locate the line number that a particular tag falls on
	 * @param $dirViewFile
	 * @param $strTag
	 * @return int|null|string
	 * @throws \Exception
	 */
	protected function locateTag($dirViewFile,$strTag){

		$intLineNo = null;
		$arrCode = explode("\n",$this->get($dirViewFile));

		foreach($arrCode as $intLineNo => $strLine){
			if(strstr($strLine,$strTag)){
				break;
			}
		}

		return ($intLineNo+1);
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
	                //This functionality could be made much more efficient in a future release
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

		            //Would crc32() be faster?
		            $strHash = md5($strRawView);
	                $strRawView = $this->runTags($strRawView,$strTag,$arrTagParts[0],$arrTagParts[1],$arrData);

		            if($strHash === md5($strRawView)){
			            //Remove un-used tag as the statement has been matched
			            $strRawView = $this->replaceTag($strRawView,$strTag,'');
		            }
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
		$arrParameters = array();
		$this->strCurrentTag = $strTag;

		if(strstr($strType,'[') && strstr($strReference,']')){

			$arrFunctionParts = explode('[',$strType);
			$strFunction = $arrFunctionParts[0];
			$strType = $arrFunctionParts[1];
			$strReference = rtrim($strReference,']');

		}elseif(preg_match("#(.*)\[(.*)\:(.*)\]#",$strTag,$srtMatchResults)){

			$strFunction = $srtMatchResults[1];
			$strType = $srtMatchResults[2];
			$strReference = $srtMatchResults[3];
		}

		//Explode parameters they must be set as key=value pairs comma separated. To pass a unassociated array in the values split by  pipe symbol '|'
		if(strstr($strReference,',')){

			$arrReferenceParams = explode(',', $strReference);
			$strReference = $arrReferenceParams[0];

			unset($arrReferenceParams[0]);
			foreach($arrReferenceParams as $mxdItem){
				if(strstr($mxdItem,'=')){
					//Update the parameters with custom values
					list($strKey,$mxdValue) = explode('=',$mxdItem);

					//See if the value is a tag to be processed, return the value of the tag
					if(strstr($mxdValue,':')){
						$arrParamTagParts = explode(':',$mxdValue);

						//A tag should only have 2 parts, only process is there are 2 parts (no more, no less)
						if(count($arrParamTagParts) == 2){
							$mxdValue = $this->runTags(sprintf('{%s}',$mxdValue),$mxdValue,$arrParamTagParts[0],$arrParamTagParts[1],$arrData);
						}
					}

					$mxdValue = $this->detectType($mxdValue);
					$arrParameters[$strKey] = (strstr($mxdValue,'|')) ? explode('|',$mxdValue) : $mxdValue;
				}else{
					$mxdItem = $this->detectType($mxdItem);
					$arrParameters[] = (strstr($mxdItem,'|')) ? explode('|',$mxdItem) : $mxdItem;
				}
			}
		}

		switch($strType){

			case'data':

				$arrResult = $this->processArrayItem($strReference,$arrData,$blReturnArray);

				if($arrResult['status'] == true){
					$strRawView = $this->replaceTag($strRawView,$strTag,$arrResult['return'],$strFunction,$arrResult['return_raw']);
				}

				break;

			case'element':
			case'template':
				//Log an error for the two deprecated template tags
				trigger_error(sprintf('TwistPHP, use of deprecated template tag "%s" in %s',$strType,$this->dirCurrentView),E_USER_DEPRECATED);

			case'view':

				$this->arrViewParams = $arrParameters;
				$arrData = is_array($arrData) ? array_merge($arrData,$arrParameters) : $arrParameters;

				$strTagData = $this->build($strReference,$arrData);
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

				if(array_key_exists($strReference,$_COOKIE)){
					$strOut = $_COOKIE[$strReference];
					$strRawView = $this->replaceTag($strRawView,$strTag,$strOut,$strFunction);
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

			case'model':

				//@todo Currently a little bit hacky but will add support for all models and params shortly
				//Currently only supports a model that has been initiated through routes.
				if(Instance::isObject('twist_route_model')){
					$resModel = Instance::retrieveObject('twist_route_model');

					if(method_exists($resModel,$strReference)){
						$strRawView = $this->replaceTag($strRawView,$strTag,$resModel->$strReference(),$strFunction);
					}
				}
				break;

			/**
			 * Use custom tags, set using \Twist::framework()->hooks()->register('TWIST_VIEW_TAGS','tag_key',array('key' => 'value'))
			 */
			default:

				//Get all registered extensions form the extension handler
				$arrExtensions = \Twist::framework()->hooks()->getAll('TWIST_VIEW_TAG');

				if(count($arrExtensions) && array_key_exists($strType,$arrExtensions)){

					$strReplacementData = '';

					if(array_key_exists('core',$arrExtensions[$strType])){

						$strClassName = $arrExtensions[$strType]['core'];

						$strReplacementData = \Twist::$strClassName($strReference,$arrParameters);

					}elseif(array_key_exists('module',$arrExtensions[$strType])){

						$strClassName = $arrExtensions[$strType]['module'];
						$strFunctionName = $arrExtensions[$strType]['function'];

						$strReplacementData = \Twist::$strClassName() -> $strFunctionName($strReference,$arrParameters);

					}elseif(array_key_exists('class',$arrExtensions[$strType])){

						$strClassName = sprintf('\\%s',$arrExtensions[$strType]['class']);
						$strFunctionName = $arrExtensions[$strType]['function'];

						$objClass = new $strClassName();
						$strReplacementData = $objClass -> $strFunctionName($strReference,$arrParameters);

					}elseif(array_key_exists('instance',$arrExtensions[$strType])){

						$resClass = Instance::retrieveObject($arrExtensions[$strType]['instance']);
						$strFunctionName = $arrExtensions[$strType]['function'];

						$strReplacementData = $resClass -> $strFunctionName($strReference,$arrParameters);

					}elseif(array_key_exists('function',$arrExtensions[$strType])){

						//@note Does not accept the params array at the moment, may deprecate this option
						$strFunctionName = $arrExtensions[$strType]['function'];
						$strReplacementData = call_user_func($strFunctionName,$strReference);
					}else{

						if(strstr($strReference,'/')){
							$mxdTempData = \Twist::framework()->tools()->arrayParse($strReference,$arrExtensions[$strType]);
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

		$this->strCurrentTag = null;

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

				$mxdTempData = \Twist::framework()->tools()->arrayParse($strKey,$arrData);

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
	            'strip_tags','nl2br',
	            'addslashes','stripslashes',
	            'count','round','ceil','floor','decimalise',
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

	            }elseif($strFunction == 'decimalise'){
		            $strData = number_format($strData,2,'.','');
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