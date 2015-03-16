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

	class XML extends PackageBase{

		protected $resDOM = null;
		protected $strRawXML = '';
		protected $arrRawData = array();
		protected $arrXmlData = array();

		public function __construct(){ }

		/**
		 * Load the raw XML data into the system and expand into an array
		 * @param $strData
		 */
		public function loadRawData($strData){

			//Get the contents of the file and turn into an array
			$this->strRawXML = $strData;
			$this->processXML();
			$this->arrXmlData = $this->expandRawArray();
		}

		/**
		 * Load the raw XML from a file or feed and expand into an array
		 * @param $strLocalFilePath
		 */
		public function loadFile($strLocalFilePath){

			//Get the contents of the file and turn into an array
			$this->strRawXML = file_get_contents($strLocalFilePath);
			$this->processXML();

			//Expand the raw array and get teh results
			$arrResult = $this->expandRawArray();

			//Output the result set to the class array
			$this->arrXmlData = $arrResult['data'];
		}

		/**
		 * Get the resulting array once the XML has been loaded into the system
		 * @return array
		 */
		public function getArray(){
			return $this->arrXmlData;
		}

		/**
		 * Process the XML into an initial linear (raw) array
		 */
		protected function processXML(){

			//If the xml data is a string turn it into an array
			if(is_string($this->strRawXML)){

				//Parse the raw XML into a raw XML Array (needs to be processed further)
				$resXML = xml_parser_create();
				xml_parse_into_struct($resXML, $this->strRawXML, $this->arrRawData);
				xml_parser_free($resXML);
			}

			//echo "<pre>".print_r($this->arrRawData,true)."</pre>";
		}

		protected $arrLevelMarker = array();
		protected $intKeyPosition = 0;

		/**
		 * Expand the linear (raw) array into a usable multi-level array
		 * @param $intCurrentKey
		 * @return array
		 */
		protected function expandRawArray($intCurrentKey = -1){

			$arrXmlData = array();
			$intCloseLevel = null;
			$blEndLoop = false;

			foreach($this->arrRawData as $intKey => $arrEachElement){

				//Only start processing once reach current array location
				//When completing an 'open' a skip value will be set (current array key)
				//The previous foreach loop will then ignore all of the child elements that have just been processed
				if($intKey > $intCurrentKey){

					//Check to see if a close level has been set, is bigger than current level return to previous iteration
					if(!is_null($intCloseLevel) && $intCloseLevel >= $arrEachElement['level']){
						$blEndLoop = true;
						$intKey = $intKey -1;
					}else{

						//Null the close level as it is not relevant at this point
						$intCloseLevel = null;

						$arrAttributes = array();

						//Process the attributes, lower case all the keys
						if(array_key_exists('attributes',$arrEachElement)){
							foreach($arrEachElement['attributes'] as $strKey => $strValue){
								$arrAttributes[strtolower($strKey)] = $strValue;
							}
						}

						switch($arrEachElement['type']){

							/**
							 * Process opening Tags
							 */
							case'open':

								$arrOpenContent = array();

								//If their is no data then don't add any to the field 'content'
								if(array_key_exists('value',$arrEachElement) && trim($arrEachElement['value']) != ''){
									$arrOpenContent[] = $arrEachElement['value'];
								}

								//Jump into the opening tag until the corresponding closing tag is found
								$arrChildData = $this->expandRawArray($intKey);

								//Grab the child data and update the current process location
								$arrOpenContent = array_merge($arrOpenContent,$arrChildData['data']);
								$intCurrentKey = $arrChildData['skip'];

								$arrXmlData[] = array(
									'level' => $arrEachElement['level'],
									'tag' => strtolower($arrEachElement['tag']),
									'attributes' => $arrAttributes,
									'content' => $arrOpenContent,
								);

								break;

							/**
							 * Process closing tags
							 */
							case'close':

								$intCloseLevel = $arrEachElement['level'];
								break;

							/**
							 * Process content data
							 */
							case'cdata':

								if(array_key_exists('value',$arrEachElement) && trim($arrEachElement['value']) != ''){
									$arrXmlData[] = $arrEachElement['value'];
								}

								break;

							/**
							 * Process a complete tag as a whole
							 */
							case'complete':

								$arrOpenContent = array();

								//If their is no data then don't add any to the field 'content'
								if(array_key_exists('value',$arrEachElement) && trim($arrEachElement['value']) != ''){
									$arrOpenContent[] = $arrEachElement['value'];
								}

								$arrXmlData[] = array(
									'level' => $arrEachElement['level'],
									'tag' => strtolower($arrEachElement['tag']),
									'attributes' => $arrAttributes,
									'content' => $arrOpenContent,
								);

								break;
						}
					}
				}

				//Exit the loop, this is used when the corresponding 'close' tag is found
				if($blEndLoop == true){
					break;
				}
			}

			//Send back the data and the Skip Key
			return array('data' => $arrXmlData,'skip' => $intKey);
		}


		/**
		 * Turn an array of data into XML
		 * @param $arrItems
		 * @param $strTab
		 * @return string
		 */
		public function covertArray($arrItems,$strTab = ""){

			$strXml = '';
			foreach($arrItems as $strKey => $mxdValue){

				if(is_array($mxdValue)){

					if(preg_match("#([0-9]+)#",$strKey,$arrResults)){

						$strXml .= $this->covertArray($mxdValue,$strTab);
					}else{

						$strXml .= sprintf("%s<%s>\n%s%s</%s>\n",
							$strTab,
							$strKey,
							$this->covertArray($mxdValue,$strTab."\t"),
							$strTab,
							$strKey
						);
					}

				}elseif($mxdValue == ''){

					$strXml .= sprintf("%s<%s/>\n",
						$strTab,
						$strKey
					);
				}else{

					$strXml .= sprintf("%s<%s>%s</%s>\n",
						$strTab,
						$strKey,
						htmlentities($mxdValue),
						$strKey
					);
				}
			}

			return $strXml;
		}


		/**** NEW CLASS SETTINGS - In preparation for V2 ****/

		public function xmlToArray($strXML){

			//Process the response into a usable array
			$objXML = simplexml_load_string($strXML);

			$strJSON = json_encode($objXML);
			$arrData = json_decode($strJSON, true);

			return $arrData;
		}

		public function arrayToXML($arrData,$strRootNode = 'node'){

			//Start the function off by outputting the XML dom header
			$strXml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";

			$strParameters = $this->processAttributes($arrData);
			$strComment = $this->processComment($arrData);

			$strXml .= sprintf("<%s%s>\n%s%s</%s>",
				$strRootNode,
				$strParameters,
				$strComment,
				processEachItem($arrData),
				$strRootNode
			);

			return $strXml;
		}

		/**
		 * Process the Attributes where they exist
		 */
		protected function processAttributes(&$arrData){

			$strParameters = '';

			if(array_key_exists('@attributes',$arrData)){
				foreach($arrData['@attributes'] as $strKey => $strValue){
					$strParameters .= sprintf(' %s="%s"',$strKey,$strValue);
				}
				unset($arrData['@attributes']);
			}

			return $strParameters;
		}

		/**
		 * Process the Comment attribute where they exist
		 */
		protected function processComment(&$arrData){

			//Remove the comment from the array, only allow comments in the root node
			$strComment = '';

			if(array_key_exists('@comment',$arrData)){
				$strComment = sprintf("<!-- %s -->\n",$arrData['@comment']);
				unset($arrData['@comment']);
			}

			return $strComment;
		}

		/**
		 * Recursively go through the array and process each item
		 */
		protected function processEachItem($arrData,$strTab="\t",$strPreviousKey=""){

			$strXml = "";

			foreach($arrData as $strKey => $mxdData){

				$strParameters = $this->processAttributes($mxdData);
				$strComment = $this->processComment($mxdData);

				//Detect for empty rows
				if(!is_array($mxdData) && $mxdData == ""){
					$strXml .= sprintf("%s<%s%s/>\n",$strTab,$strKey,$strParameters);
				}else{

					//If this is a key of 0-9 use the previous key
					if(preg_match("#([0-9]+)#",$strKey,$arrResults)){

						$strXml .= sprintf("%s<%s%s>%s%s%s</%s>\n",
							$strTab,
							$strPreviousKey,
							$strParameters,
							$strTab,
							(is_array($mxdData)) ? "\n".$this->processEachItem($mxdData,$strTab."\t",$strKey) : $mxdData,
							$strTab,
							$strPreviousKey
						);

					}else{

						//If the next array is a 0-9 key then do not contain the items
						if(is_array($mxdData) && array_key_exists('0',$mxdData)){
							$strXml .= sprintf("%s",$this->processEachItem($mxdData,$strTab,$strKey));
						}else{

							//Use standard output
							$strXml .= sprintf("%s<%s%s>%s</%s>\n",
								$strTab,
								$strKey,
								$strParameters,
								(is_array($mxdData)) ? "\n".$this->processEachItem($mxdData,$strTab."\t",$strKey) : $mxdData,
								$strKey
							);
						}
					}
				}
			}

			return $strXml;
		}
	}
