<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
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

	namespace Twist\Core\Models;

	/**
	 * A set of useful tools that have been designed to be used throughout the framework, these tools are to stop repartition of common code and data processing algorithms.
	 * @package Twist\Core\Models
	 */
	final class Tools{

		/**
		 * Similar to print_r but corrects issues such as booleans, also give more useful information about the data
		 *
		 * @param array $arrData
		 * @param string $strIndent
		 * @return string
		 */
		public function arrayPrint( $arrData, $strIndent = '' ) {

			$strOut = "";

			foreach( $arrData as $strKey => $mxdValue ) {
				if( is_array( $mxdValue ) ) {
					$strOut .= $strIndent . "[" . $strKey . "] => Array\n";
					$strOut .= $strIndent . "(\n";
					$strOut .= $this -> arrayPrint( $mxdValue, $strIndent . "\t" );
					$strOut .= $strIndent . ")";
					$strOut .= "\n";
				} else {
					$strOut .= $strIndent . "[" . $strKey . "] => " . ( is_bool( $mxdValue ) ? sprintf( "%b", $mxdValue ) : $mxdValue );
					$strOut .= "\n";
				}
			}

			return ( $strIndent === '' ) ? sprintf( '<pre>%s</pre>', $strOut ) : $strOut;
		}

		/**
		 * Return a value in an array using multi dimensional key to parse the structure of the array
		 *
		 * @param string $strKey Location of the value in the array
		 * @param array $arrData Array to parse
		 * @param string $strSplitChar Structure separator
		 * @return null $mxdOut
		 */
		public function arrayParse( $strKey, $arrData, $strSplitChar = '/' ) {

			$mxdOut = null;
			$arrParts = explode( $strSplitChar, $strKey );

			$strKey = array_shift( $arrParts );

			if( is_array( $arrData ) && array_key_exists( $strKey, $arrData ) ) {

				if( count( $arrParts ) ) {
					$mxdOut = $this -> arrayParse( implode( $strSplitChar, $arrParts ), $arrData[$strKey] );
				} else {
					$mxdOut = $arrData[$strKey];
				}
			}

			return $mxdOut;
		}

		/**
		 * Remove an item from a multi-dimensional array using a key, the split char indicates a change in array level
		 *
		 * @param string $strKey
		 * @param array $arrData
		 * @param string $strSplitChar
		 * @return array Returns either the original array or the array with the item removed
		 */
		public function arrayParseUnset( $strKey, $arrData, $strSplitChar = '/' ) {

			$arrCollapsedArray = $this -> array3dTo2d( $arrData, $strSplitChar );

			if( array_key_exists( $strKey, $arrCollapsedArray ) ) {
				unset( $arrCollapsedArray[$strKey] );
				$arrData = $this -> array2dTo3d( $arrCollapsedArray, null, $strSplitChar );
			}

			return $arrData;
		}

		/**
		 * Collapse a multidimensional array into a single associative array
		 *
		 * @param array $arrIn Array to transform
		 * @param string $strJoinChar Structure separator
		 * @param mixed $mxdPreviousKey Previous key encountered (used in the recursive process)
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
		 * Transform an associative array into a multidimensional array using a key to define the structure
		 *
		 * @param array $arrIn Array to transform
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
		 * Fully merge two multidimensional arrays and return the new merged array.
		 *
		 * @param array $arrPrimary Primary array
		 * @param array $arrSecondary Secondary array
		 * @return mixed
		 */
		public function arrayMergeRecursive( $arrPrimary, $arrSecondary ) {

			foreach( $arrSecondary as $strKey => $mxdValue ) {

				if( is_array($arrPrimary) && array_key_exists( $strKey, $arrPrimary ) ) {

					if( is_array($arrPrimary[$strKey]) && is_array( $mxdValue ) ) {

						//Only time anything is different is when we process a sub-array
						$arrPrimary[$strKey] = $this -> arrayMergeRecursive( $arrPrimary[$strKey], $mxdValue );
					} else {
						$arrPrimary[$strKey] = $mxdValue;
					}
				} else {
					$arrPrimary[$strKey] = $mxdValue;
				}
			}

			return $arrPrimary;
		}

		/**
		 * Re-index an array of data, provide the key field to be used to re-index the array. The key must exists with a value within the array.
		 *
		 * Example of original data:
		 * array(
		 *     0 => array('id' => 1, 'name' => 'Home', 'slug' => 'home'),
		 *     1 => array('id' => 2, 'name' => 'About', 'slug' => 'about'),
		 *     2 => array('id' => 3, 'name' => 'Contact', 'slug' => 'contact'),
		 * )
		 *
		 * Example of data after being re-indexed by the key 'slug':
		 * array(
		 *     'home' => array('id' => 1, 'name' => 'Home', 'slug' => 'home'),
		 *     'about' => array('id' => 2, 'name' => 'About', 'slug' => 'about'),
		 *     'contact' => array('id' => 3, 'name' => 'Contact', 'slug' => 'contact'),
		 * )
		 *
		 * @param array $arrData
		 * @param string $strKeyField
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
							if( $arrEachItem[$strKeyField] === '' ) {
								$arrOut[] = $arrEachItem;
							} else {
								$strNewKey = $arrEachItem[$strKeyField];
								if( $blGroup ) {
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
		 *
		 * @param        $strStructure
		 * @param string $strSplit
		 * @param null   $strFinalValue
		 * @return array|null
		 */
		public function ghostArray( $strStructure, $strSplit = '/', $strFinalValue = null ) {
			foreach( array_reverse( explode( $strSplit, $strStructure ) ) as $strPart ) {
				$strFinalValue = array( $strPart => $strFinalValue );
			}

			return $strFinalValue;
		}

		/**
		 * @param        $arrStructure
		 * @param string $strIDField
		 * @param string $strParentIDField
		 * @param string $strChildrenKey
		 * @return array
		 */
		public function arrayRelationalTree( $arrStructure, $strIDField = 'id', $strParentIDField = 'parent_id', $strChildrenKey = 'children') {

			$arrTree = array();

			foreach( $arrStructure as $arrStructureItem ) {
				if( !array_key_exists($strParentIDField,$arrStructureItem) || $arrStructureItem[$strParentIDField] === '' || is_null($arrStructureItem[$strParentIDField]) ) {
					$arrTree[0][] = $arrStructureItem;
				} else {
					$arrTree[$arrStructureItem[$strParentIDField]][] = $arrStructureItem;
				}
			}

			return $this -> buildRelationalTree( $arrTree, $arrTree[0], $strIDField, $strChildrenKey );
		}

		/**
		 * @param array $arrList
		 * @param array $arrParents
		 * @param string $strIDField
		 * @param string $strChildrenKey
		 * @return array
		 */
		private function buildRelationalTree( &$arrList, $arrParents, $strIDField, $strChildrenKey ) {

			$arrTempTree = array();

			foreach( $arrParents as $arrChild ) {
				if( isset( $arrList[$arrChild[$strIDField]] ) ) {
					$arrChild[$strChildrenKey] = $this -> buildRelationalTree( $arrList, $arrList[$arrChild[$strIDField]], $strIDField, $strChildrenKey );
				}
				$arrTempTree[] = $arrChild;
			}

			return $arrTempTree;
		}

		/**
		 * Var dump some data and return the output as a string of text (and not to the screen). Accepts all the same parameters as the regular PHP vardump function.
		 * @reference http://php.net/manual/en/function.var-dump.php
		 * @return string
		 */
		public function varDump() {
			ob_start();
			call_user_func_array( 'var_dump', func_get_args() );

			return ob_get_clean();
		}

		/**
		 * Traverse the current URI in $_SERVER['REQUEST_URI'] or pass in a starting URI
		 *
		 * @param string $urlRelativePath
		 * @param string$urlStartingURI
		 * @param bool $blPreserveQueryString Keep/merge the current query string with the query string of the redirect
		 * @return string Return the traversed URI
		 */
		public function traverseURI( $urlRelativePath, $urlStartingURI = null, $blPreserveQueryString = false ) {

			$strQueryString = $strCurrentQueryString = '';

			//Fix when a dot is passed in
			if( $urlRelativePath === '.' ) {
				$urlRelativePath = './';
			}

			//Remove the query string from the redirect to help with comparisons
			if( strstr( $urlRelativePath, '?' ) ) {
				list( $urlRelativePath, $strQueryString ) = explode( '?', $urlRelativePath );
			}

			//If the redirect id / no further processing required
			if( $urlRelativePath === '/' ) {
				return ( $strQueryString === '' ) ? $urlRelativePath : sprintf( '%s?%s', $urlRelativePath, $strQueryString );
			}

			//Remove the query string form the users current location (may be required on redirect if enabled)
			$urlCurrentURI = ( is_null( $urlStartingURI ) ) ? $_SERVER['REQUEST_URI'] : $urlStartingURI;
			if( strstr( $urlCurrentURI, '?' ) ) {
				list( $urlCurrentURI, $strCurrentQueryString ) = explode( '?', $urlCurrentURI );
			}

			//Append the old query string to the new one if enabled
			if( $blPreserveQueryString ) {
				$strQueryString = ( $strQueryString === '' ) ? $strCurrentQueryString : sprintf( '%s&%s', $strCurrentQueryString, $strQueryString );
			}

			//Start processing the traversal
			$urlCurrentURI = ltrim( $urlCurrentURI, '/' );
			$urlOut = rtrim( $urlRelativePath, '/' );

			if( substr( $urlRelativePath, 0, 2 ) === './' ) {

				$arrCurrentParts = ( strstr( $urlCurrentURI, '/' ) ) ? explode( '/', $urlCurrentURI ) : array( $urlCurrentURI );
				array_pop( $arrCurrentParts );
				$urlCurrentURI = implode( '/', $arrCurrentParts );

				$urlOut = ($urlCurrentURI == '') ? sprintf( '/%s', substr( $urlRelativePath, 2 ) ) : sprintf( '/%s/%s', $urlCurrentURI, substr( $urlRelativePath, 2 ) );

			} elseif( substr( $urlRelativePath, 0, 3 ) === '../' ) {

				//UP
				$urlOutTemp = trim( $urlOut, '/' );

				$arrCurrentParts = ( strstr( $urlCurrentURI, '/' ) ) ? explode( '/', $urlCurrentURI ) : array( $urlCurrentURI );
				$arrRedirectParts = ( strstr( $urlOutTemp, '/' ) ) ? explode( '/', $urlOutTemp ) : array( $urlOutTemp );

				foreach( $arrRedirectParts as $intKey => $strEachPart ) {
					if( $strEachPart === '..' && count( $arrCurrentParts ) > 0 ) {
						array_pop( $arrCurrentParts );
						array_shift( $arrRedirectParts );
					} else {
						break;
					}
				}

				if( count( $arrRedirectParts ) > 0 ) {
					array_pop( $arrCurrentParts );
				}

				$arrUriParts = array_merge( $arrCurrentParts, $arrRedirectParts );
				$urlOut = sprintf( '/%s', implode( '/', $arrUriParts ) );

			} elseif( !strstr( $urlRelativePath, ':' ) && substr( $urlRelativePath, 0, 2 ) != '//' && substr( $urlRelativePath, 0, 1 ) != '/' ) {

				//CHILD
				$urlOutTemp = trim( $urlOut, '/' );
				$urlOut = ($urlCurrentURI == '') ? sprintf( '/%s', $urlOutTemp ) : sprintf( '/%s/%s', $urlCurrentURI, $urlOutTemp );
			}

			//Otherwise do a full redirect
			if( \Twist::framework() -> setting( 'SITE_TRAILING_SLASH' ) ) {
				$urlOut .= '/';
			} else {
				$urlOut = rtrim( $urlOut, '/' );

				if( $urlOut === '' ) {
					$urlOut = '/';
				}
			}

			return ( $strQueryString === '' ) ? $urlOut : sprintf( '%s?%s', $urlOut, $strQueryString );
		}

		/**
		 * Generate a random string, default length is set in the framework settings
		 * @param null $intStringLength Default value is set in "RANDOM_STRING_LENGTH" setting
		 * @param null $mxdCharset
		 * @param bool $blIgnoreSimilarChars True will not use the chars "015IJLSOijlos"
		 * @return string
		 */
		public function randomString( $intStringLength = null, $mxdCharset = null, $blIgnoreSimilarChars = true ) {

			$strOut = '';
			$intStringLength = ( !is_null( $intStringLength ) && $intStringLength > 0 ) ? $intStringLength : \Twist::framework() -> setting( 'RANDOM_STRING_LENGTH' );

			if($blIgnoreSimilarChars){
				$strChars = '2346789ABCDEFGHKMNPQRTUVWXYZabcdefghkmnpqrtuvwxyz';
			}else{
				$strChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghiklmnopqrstuvwxyz';
			}

			if( $mxdCharset === true ) {
				$strChars .= '!@£$%^&*()_-=+[]{};:|<>?/';
			} elseif( !is_null( $mxdCharset ) ) {
				$strChars = $mxdCharset;
			}

			$arrChars = preg_split( '//u', $strChars, -1, PREG_SPLIT_NO_EMPTY );

			for( $intChar = 0; $intChar < $intStringLength; $intChar++ ) {
				$intRand = mt_rand( 0, count( $arrChars ) - 1 );
				$strOut .= $arrChars[$intRand];
			}

			return $strOut;
		}

		/**
		 * Generate a URL-friendly slug of a string
		 *
		 * @param string $strRaw           The input string
		 * @param array  $arrExistingSlugs Array of existing slugs to avoid collisions with
		 * @param null   $intMaxLength     Max length for the returned slug
		 * @return mixed|string
		 */
		public function slug( $strRaw, $arrExistingSlugs = array(), $intMaxLength = null ) {
			$strSlug = preg_replace( '~[^\\pL\d]+~u', '-', $strRaw );
			while( strstr( $strSlug, '--' ) ) {
				$strSlug = str_replace( '--', '-', $strSlug );
			}
			$strSlug = iconv( 'utf-8', 'us-ascii//TRANSLIT', trim( $strSlug, '-' ) );
			$strSlug = preg_replace( '~[^-\w]+~', '', strtolower( $strSlug ) );
			$strSlug = strlen( $strSlug ) ? $strSlug : '-';

			if( !is_null( $intMaxLength ) ) {
				$strSlug = substr( $strSlug, 0, $intMaxLength );
			}

			if( in_array( $strSlug, $arrExistingSlugs ) ) {
				$intUniq = 1;
				do {
					$intUniq++;
					$strTestSlug = sprintf( '%s-%d', ( is_null( $intMaxLength ) ? $strSlug : substr( $strSlug, 0, -( strlen( $intUniq ) + 1 ) ) ), $intUniq );
				} while( in_array( $strTestSlug, $arrExistingSlugs ) );
				$strSlug = $strTestSlug;
			}

			return $strSlug;
		}

		/**
		 * Create a 'zipped' string of characters (useful for sha1() + uniqid() to avoid similar-looking uniqid()'s)
		 *
		 * @param string $strString1
		 * @param string $strString2
		 * @return string
		 */
		public function zipStrings( $strString1, $strString2 ) {
			$strOut = '';

			$arrString1Chars = str_split( $strString1 );
			$arrString2Chars = str_split( $strString2 );

			if( count( $arrString1Chars ) < count( $arrString2Chars ) ) {
				$intSmallerArray = count( $arrString1Chars );
				$arrLargerArray = $arrString2Chars;
			} else {
				$intSmallerArray = count( $arrString2Chars );
				$arrLargerArray = $arrString1Chars;
			}

			for( $intChar = 0; $intChar < $intSmallerArray; $intChar++ ) {
				$strOut .= $arrString1Chars[$intChar] . $arrString2Chars[$intChar];
			}

			$strOut .= substr( implode( '', $arrLargerArray ), $intSmallerArray );

			return $strOut;
		}
	}