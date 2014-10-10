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

	namespace TwistPHP;

	final class Tools{

		/**
		 * Similar to print_r but corrects issues such as booleans, also give more useful information about the data
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
		 * @param $arrIn
		 * @param null $strMultiDimensionalKey
		 * @param string $strSplitChar
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
		 * @param $arrIn
		 * @param string $strJoinChar
		 * @param null $mxdPreviousKey
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
		 * @param $arrPrimary
		 * @param $arrSecondary
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
		 * Parse an array of data looking for the result of a multi dimentional key.
		 * The key should be formatted using '/' for instance 'user/name'
		 * @param $strKey
		 * @param $arrData
		 * @param string $strSplitChar
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

		function varDump(){
			ob_start();
			call_user_func_array('var_dump',func_get_args());
			return ob_get_clean();
		}
	}