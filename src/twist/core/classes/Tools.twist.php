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

	namespace TwistPHP;

	final class Tools{

		/**
		 * Similar to print_r but corrects issues such as booleans, also give more useful information about the data
         *
		 * @param $arrData
		 * @param string $strIndent
		 * @return string
		 */
		public function arrayPrint($arrData,$strIndent = ''){

			$strOut = "";

			foreach($arrData as $strKey => $mxdValue){
				if(is_array($mxdValue)){
					$strOut .= $strIndent."[".$strKey."] => Array\n";
					$strOut .= $strIndent."(\n";
					$strOut .= $this->arrayPrint($mxdValue,$strIndent."\t");
					$strOut .= $strIndent.")";
					$strOut .= "\n";
				}else{
					$strOut .= $strIndent."[".$strKey."] => ".((is_bool($mxdValue)) ? sprintf("%b",$mxdValue) : $mxdValue);
					$strOut .= "\n";
				}
			}

			return ($strIndent == '') ? sprintf('<pre>%s</pre>',$strOut) : $strOut;
		}

		/**
         * Transform an associative array into a multidimensional array using a key to define the structure
         *
		 * @param $arrIn Array to transform
		 * @param null $strMultiDimensionalKey Key in the array to use to define a structure
		 * @param string $strSplitChar Structure separator
		 * @return array
		 */
		public function array2dTo3d( $arrIn, $strMultiDimensionalKey = null, $strSplitChar = '/' ) {

			$arrOut = array();

			foreach( $arrIn as $mxdRowKey => $mxdRowValue ) {
				$arrNewRow = array();
				$strRowKey = is_null( $strMultiDimensionalKey ) ? $mxdRowKey : $mxdRowValue[$strMultiDimensionalKey];

				if( strstr( $strRowKey, $strSplitChar ) ) {
					$arrNewRow = $mxdRowValue;
					$arrParts = explode( $strSplitChar, $strRowKey );
					while( count( $arrParts ) ) {
						$strLastRowKey = array_pop( $arrParts );
						$arrNewRow = array( $strLastRowKey => $arrNewRow );
					}
				} else {
					$arrNewRow[$strRowKey] = $mxdRowValue;
				}

				$arrOut = $this -> arrayMergeRecursive( $arrOut, $arrNewRow );
			}

			return $arrOut;
		}

		/**
         * Collapse a multidimensional array into a single associative array
         *
		 * @param $arrIn Array to transform
		 * @param string $strJoinChar Structure separator
		 * @param null $mxdPreviousKey Previous key encountered (used in the recursive process)
		 * @return array
		 */
		public function array3dTo2d( $arrIn, $strJoinChar = '/', $mxdPreviousKey = null ) {

			$arrOut = array();

			foreach( $arrIn as $mxdKey => $mxdValue ) {
				$mxdCurrentKey = ( !is_null( $mxdPreviousKey ) ) ? $mxdPreviousKey . $strJoinChar . $mxdKey : $mxdKey;

				if( is_array( $mxdValue ) ) {
					$arrMoreData = $this -> array3dTo2d( $mxdValue, $strJoinChar, $mxdCurrentKey );
					$arrOut = array_merge( $arrOut, $arrMoreData );
				} else {
					$arrOut[$mxdCurrentKey] = $mxdValue;
				}
			}

			return $arrOut;
		}

		/**
         * Fully merge two multidimensional arrays
         *
		 * @param $arrPrimary Primary array
		 * @param $arrSecondary Secondary array
		 * @return mixed
		 */
		public function arrayMergeRecursive($arrPrimary,$arrSecondary){

			foreach($arrSecondary as $strKey => $mxdValue){

				if(array_key_exists($strKey,$arrPrimary)){

					if(is_array($mxdValue)){

						//Only time anything is different is when we process a sub-array
						$arrPrimary[$strKey] = $this->arrayMergeRecursive($arrPrimary[$strKey],$mxdValue);
					}else{
						$arrPrimary[$strKey] = $mxdValue;
					}
				}else{
					$arrPrimary[$strKey] = $mxdValue;
				}
			}

			return $arrPrimary;
		}

		/**
		 * Return a value in an array using multi dimensional key to parse the structure of the array
         *
		 * @param $strKey Location of the value in the array
		 * @param $arrData Array to parse
		 * @param string $strSplitChar Structure separator
		 * @return null $mxdOut
		 */
		public function arrayParse($strKey,$arrData,$strSplitChar='/'){

			$mxdOut = null;
			$arrParts = explode($strSplitChar,$strKey);

			$strKey = array_shift($arrParts);

			if(array_key_exists($strKey,$arrData)){

				if(count($arrParts)){
					$mxdOut = $this->arrayParse( implode( $strSplitChar, $arrParts ), $arrData[$strKey] );
				}else{
					$mxdOut = $arrData[$strKey];
				}
			}

			return $mxdOut;
		}

		/**
		 * Remove an item from a multi-dimensional array using a key, the split char indicates a change in array level
		 * @param $strKey
		 * @param $arrData
		 * @param string $strSplitChar
		 * @return array Returns either the original array or the array with the item removed
		 */
		public function arrayParseUnset($strKey,$arrData,$strSplitChar='/'){

			$arrCollapsedArray = $this->array3dTo2d($arrData,$strSplitChar);

			if(array_key_exists($strKey,$arrCollapsedArray)){
				unset($arrCollapsedArray[$strKey]);
				$arrData = $this->array2dTo3d($arrCollapsedArray,null,$strSplitChar);
			}

			return $arrData;
		}

		/**
		 * @param $arrData
		 * @param $strKeyField
		 * @param bool $blGroup
		 * @return array|bool
		 */
		public function arrayReindex( $arrData, $strKeyField, $blGroup = false ) {

			$arrOut = array();

			if( is_array( $arrData ) ) {
				if( count( $arrData ) ) {
					foreach( $arrData as $arrEachItem ) {
						if( !array_key_exists( $strKeyField, $arrEachItem ) ) {
							$arrOut = false;
							trigger_error( sprintf( 'Cannot reindex the array by "%s": field doesn\'t exist', $strKeyField ), E_USER_WARNING );
							break;
						} else {
							if( $arrEachItem[$strKeyField] == '' ) {
								$arrOut[] = $arrEachItem;
							} else {
								$strNewKey = $arrEachItem[$strKeyField];
								if( $blGroup ){
									if( !array_key_exists( $strNewKey, $arrOut ) ) {
										$arrOut[$strNewKey] = array();
									}
									$arrOut[$strNewKey][] = $arrEachItem;
								} else {
									$arrOut[$strNewKey] = $arrEachItem;
								}
							}
						}
					}
				} else {
					$arrOut = $arrData;
				}
			} else {
				$arrOut = false;
				trigger_error( 'Parameter 1 must contain a valid array', E_USER_WARNING );
			}

			return $arrOut;
		}

		/**
		 * Create a blank multidimensional array using a URI-style string and populate the last item with a value
		 * @param $strStructure
		 * @param string $strSplit
		 * @param null $strFinalValue
		 * @return array|null
		 */
		public function ghostArray( $strStructure, $strSplit = '/', $strFinalValue = null ) {
			foreach( array_reverse( explode( $strSplit, $strStructure ) ) as $strPart ) {
				$strFinalValue = array( $strPart => $strFinalValue );
			}

			return $strFinalValue;
		}

		/**
		 * @param $arrStructure
		 * @param string $strIDField
		 * @param string $strParentIDField
		 * @param string $strChildrenKey
		 * @return array
		 */
		public function arrayRelationalTree( $arrStructure, $strIDField = 'id', $strParentIDField = 'parent_id', $strChildrenKey = 'children' ) {

			$arrTree = array();

			foreach( $arrStructure as $arrStructureItem ) {
				if( $arrStructureItem[$strParentIDField] == '' ) {
					$arrTree[0][] = $arrStructureItem;
				} else {
					$arrTree[$arrStructureItem[$strParentIDField]][] = $arrStructureItem;
				}
			}

			return $this->buildRelationalTree( $arrTree, $arrTree[0], $strIDField, $strChildrenKey );
		}

		private function buildRelationalTree( &$arrList, $arrParents, $strIDField, $strChildrenKey ) {

			$arrTempTree = array();

			foreach( $arrParents as $arrChild ) {
				if( isset( $arrList[$arrChild[$strIDField]] ) ) {
					$arrChild[$strChildrenKey] = $this->buildRelationalTree( $arrList, $arrList[$arrChild[$strIDField]], $strIDField, $strChildrenKey );
				}
				$arrTempTree[] = $arrChild;
			}

			return $arrTempTree;
		}

		public function varDump(){
			ob_start();
			call_user_func_array('var_dump',func_get_args());
			return ob_get_clean();
		}

		/**
		 * Traverse the current URI in $_SERVER['REQUEST_URI'] or pass in a starting URI
		 * @param $urlRelativePath
		 * @param $urlStartingURI
		 * @return string Return the traversed URI
		 */
		public function traverseURI($urlRelativePath,$urlStartingURI = null){

			if($urlRelativePath == '/') {
				return $urlRelativePath;
			}

			$urlCurrentURI = trim((is_null($urlStartingURI)) ? $_SERVER['REQUEST_URI'] : $urlStartingURI,'/');
			$urlOut = rtrim($urlRelativePath,'/');

			if(substr($urlRelativePath,0,2) == './'){

				//THIS
				$urlOutTemp = trim($urlOut,'/');

				if(substr($urlOutTemp,0,2) == './'){
					$urlOutTemp = substr($urlOutTemp,2);
				}

				$arrCurrentParts = (strstr($urlCurrentURI,'/')) ? explode('/',$urlCurrentURI) : array($urlCurrentURI);
				array_pop($arrCurrentParts);
				$urlCurrentURI = implode('/',$arrCurrentParts);

				$urlOut = sprintf('/%s/%s',$urlCurrentURI,$urlOutTemp);

			}elseif(substr($urlRelativePath,0,3) == '../'){

				//UP
				$urlOutTemp = trim($urlOut,'/');

				$arrCurrentParts = (strstr($urlCurrentURI,'/')) ? explode('/',$urlCurrentURI) : array($urlCurrentURI);
				$arrRedirectParts = (strstr($urlOutTemp,'/')) ? explode('/',$urlOutTemp) : array($urlOutTemp);

				foreach($arrRedirectParts as $intKey => $strEachPart){
					if($strEachPart == '..' && count($arrCurrentParts) > 0){
						array_pop($arrCurrentParts);
						array_shift($arrRedirectParts);
					}else{
						break;
					}
				}

				if(count($arrRedirectParts) > 0){
					array_pop($arrCurrentParts);
				}

				$arrUriParts = array_merge($arrCurrentParts,$arrRedirectParts);
				$urlOut = sprintf('/%s',implode('/',$arrUriParts));

			}elseif(!strstr($urlRelativePath,':') && substr($urlRelativePath,0,2) != '//' && substr($urlRelativePath,0,1) != '/'){

				//CHILD
				$urlOutTemp = trim($urlOut,'/');
				$urlOut = sprintf('/%s/%s',$urlCurrentURI,$urlOutTemp);
			}

			//Otherwise do a full redirect
			if(\Twist::framework()->setting('SITE_TRAILING_SLASH')){
				$urlOut .= '/';
			}else{
				$urlOut = rtrim($urlOut,'/');
			}

			return $urlOut;
		}
	}