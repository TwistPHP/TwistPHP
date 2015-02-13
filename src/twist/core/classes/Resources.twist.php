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

/**
 * Core resource handler for the framework, can be extended by installing the Official Resource module
 */
final class Resources{

	var $arrLibraries = array();

	public function __construct(){
		$this->loadLibraryManifest();
	}

	/**
	 * The main template function to output the correct details
	 * @param $strReference
	 * @return string
	 */
	public function templateExtension($strReference){

		$strOut = '';
		$arrParts = (strstr($strReference,',')) ? explode(',',$strReference) : array(0 => $strReference,1 => null);

		$strAsyncType = null;
		$blInline = false;

		if( count( $arrParts ) > 1 ) {
			if( $arrParts[count( $arrParts ) - 1] === 'inline' ) {
				$blInline = true;
				if( $arrParts[count( $arrParts ) - 2] === 'async'
					|| $arrParts[count( $arrParts ) - 2] === 'defer' ) {
					$strAsyncType = $arrParts[count( $arrParts ) - 2];
					if( count( $arrParts ) === 3 ) {
						$arrParts[1] = null;
					}
				} else if( count( $arrParts ) === 2 ) {
					$arrParts[1] = null;
				}
			} else if( $arrParts[count( $arrParts ) - 1] === 'async'
				|| $arrParts[count( $arrParts ) - 1] === 'defer' ) {
				$strAsyncType = $arrParts[count( $arrParts ) - 1];
				if( count( $arrParts ) === 2 ) {
					$arrParts[1] = null;
				}
			}
		}

		$arrResource = $this->getFromLibrary($arrParts[0],$arrParts[1]);
		$strModuleURI = sprintf('%score/resources/',FRAMEWORK_URI);
		$strModulePath = sprintf('%score/resources/',DIR_FRAMEWORK);

		//If the count is bigger than 0 then output the data
		if(count($arrResource)){

			if(count($arrResource['css'])){
				foreach($arrResource['css'] as $strEachItem){
					if( $blInline ) {
						if(substr($strEachItem,0,2) == '//'){
							$strOut .= sprintf( '%s ', file_get_contents($strEachItem) );
						} else {
							$strOut .= sprintf( '%s ', file_get_contents(sprintf("%s/%s/%s",$strModulePath,$arrParts[0],$strEachItem)) );
						}
					} else if( !is_null( $strAsyncType ) ) {
						if(substr($strEachItem,0,2) == '//'){
							//$strOut .= sprintf( "(function(d){var g=d.createElement('link'),s=d.getElementsByTagName('script')[0];g.type='text/css';g.rel='stylesheet';g.href='%s';s.parentNode.insertBefore(g,s);}(document));", $strEachItem );
							$strOut .= sprintf( "(function(d){var g=d.createElement('link');g.type='text/css';g.rel='stylesheet';g.href='%s';d.getElementsByTagName('head')[0].appendChild(g);}(document));", $strEachItem );
						} else {
							//$strOut .= sprintf( "(function(d){var g=d.createElement('link'),s=d.getElementsByTagName('script')[0];g.type='text/css';g.rel='stylesheet';g.href='%slibraries/%s/%s';s.parentNode.insertBefore(g,s);}(document));", $strModuleURI,$arrParts[0],$strEachItem );
							$strOut .= sprintf( "(function(d){var g=d.createElement('link');g.type='text/css';g.rel='stylesheet';g.href='%slibraries/%s/%s';d.getElementsByTagName('head')[0].appendChild(g);}(document));", $strModuleURI,$arrParts[0],$strEachItem );
						}
					} else {
						if(substr($strEachItem,0,2) == '//'){
							$strOut .= sprintf('<link href="%s" type="text/css" rel="stylesheet">',$strEachItem);
						}else{
							$strOut .= sprintf('<link href="%s/%s/%s" type="text/css" rel="stylesheet">',$strModuleURI,$arrParts[0],$strEachItem);
						}
					}
				}

				if( !is_null( $strAsyncType ) ) {
					$strOut = sprintf( '<script async>%s</script>', $strOut );
				} else if( $blInline ) {
					$strOut = sprintf( '<style type="text/css">%s</style>', $strOut );
				}
			}

			if(count($arrResource['js'])){
				foreach($arrResource['js'] as $strEachItem){
					//$strItemPath = (substr($strEachItem,0,2) == '//') ? $strEachItem : sprintf("%slibraries/%s/%s",$strModulePath,$arrParts[0],$strEachItem);

					if( $blInline ) {
						if(substr($strEachItem,0,2) == '//'){
							$strOut .= sprintf( '%s ', file_get_contents($strEachItem) );
						} else {
							$strOut .= sprintf( '%s ', file_get_contents(sprintf("%s/%s/%s",$strModulePath,$arrParts[0],$strEachItem) ) );
						}
					} else {
						if(substr($strEachItem,0,2) == '//'){
							$strOut .= sprintf('<script src="%s"%s></script>',$strEachItem,(is_null( $strAsyncType ) ? '' : sprintf( ' %s', $strAsyncType )));
						}else{
							$strOut .= sprintf('<script src="%s/%s/%s"%s></script>',$strModuleURI,$arrParts[0],$strEachItem,(is_null( $strAsyncType ) ? '' : sprintf( ' %s', $strAsyncType )));
						}
					}
				}

				if( $blInline ) {
					$strOut = sprintf( '<script%s>%s</script>',(is_null( $strAsyncType ) ? '' : sprintf( ' %s', $strAsyncType ) ), $strOut );
				}
			}
		}

		return $strOut;
	}

	/**
	 * Load in the library manifest file
	 */
	protected function loadLibraryManifest(){

		//Get the resource libraries
		$strJSON = file_get_contents(sprintf('%score/resources/manifest.json',DIR_FRAMEWORK));
		$this->arrLibraries = json_decode($strJSON,true);
	}

	/**
	 * Get the correct package form the library
	 * @param $strRequestedResource
	 * @param null $strRequestedVersion
	 * @return array
	 */
	protected function getFromLibrary( $strRequestedResource, $strRequestedVersion = null ) {
		$arrOut = array();

		if( array_key_exists( $strRequestedResource, $this -> arrLibraries ) ) {
			if( !is_null( $strRequestedVersion )
				&& array_key_exists( $strRequestedVersion, $this -> arrLibraries[$strRequestedResource] ) ) {
				$arrOut = $this -> arrLibraries[$strRequestedResource][$strRequestedVersion];
			} else if( is_null( $strRequestedVersion )
				|| ( !is_null( $strRequestedVersion )
					&& !array_key_exists( $strRequestedVersion, $this -> arrLibraries[$strRequestedResource] ) ) ) {
				foreach( $this -> arrLibraries[$strRequestedResource] as $arrResourceVersion ) {
					if( $arrResourceVersion['default'] ) {
						$arrOut = $arrResourceVersion;
						break;
					}
				}
			}
		}

		return $arrOut;
	}
}