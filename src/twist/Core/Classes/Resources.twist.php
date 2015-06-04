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

namespace Twist\Core\Classes;

/**
 * Core resource handler for the framework, can be extended by installing the Official Resource module
 */
final class Resources{

	var $arrLibraries = array();
	protected $arrIncluded = array();

	public function __construct(){
		$this->loadLibraryManifest();
	}

	/**
	 * The main template function to output the correct details
	 * @param $strReference
	 * @return string
	 */
	public function viewExtension($strReference,$arrParameters = array()){

		$strOut = '';

		//Only allow each resource to be output once
		if(!array_key_exists($strReference,$this->arrIncluded)){

			$this->arrIncluded[$strReference] = $strReference;

			if(!count($arrParameters)){
				$arrParameters[0] = null;
			}

			$strAsyncType = null;
			$blInline = false;

			if($strReference === 'core-uri'){
				return sprintf('/%sCore/Resources/',ltrim(TWIST_FRAMEWORK_URI,'/'));
			}

			if( count( $arrParameters )) {
				if( $arrParameters[count( $arrParameters ) - 1] === 'inline' ) {
					$blInline = true;
					if( $arrParameters[count( $arrParameters ) - 2] === 'async'
						|| $arrParameters[count( $arrParameters ) - 2] === 'defer' ) {
						$strAsyncType = $arrParameters[count( $arrParameters ) - 2];
						if( count( $arrParameters ) === 2 ) {
							$arrParameters[0] = null;
						}
					} else if( count( $arrParameters ) === 1 ) {
						$arrParameters[0] = null;
					}
				} else if( $arrParameters[count( $arrParameters ) - 1] === 'async'
					|| $arrParameters[count( $arrParameters ) - 1] === 'defer' ) {
					$strAsyncType = $arrParameters[count( $arrParameters ) - 1];
					if( count( $arrParameters ) === 1 ) {
						$arrParameters[0] = null;
					}
				}
			}

			$arrResource = $this->getFromLibrary($strReference,$arrParameters[0]);

			//If the count is bigger than 0 then output the data
			if(count($arrResource)){

				$strModuleURI = $arrResource['uri'];
				$strModulePath = $arrResource['path'];

				if(count($arrResource['css'])){
					foreach($arrResource['css'] as $strEachItem){
						if( $blInline ) {
							if(substr($strEachItem,0,2) === '//'){
								$strOut .= sprintf( '%s ', file_get_contents($strEachItem) );
							} else {
								$strOut .= sprintf( '%s ', file_get_contents(sprintf("%s/%s/%s",$strModulePath,$strReference,$strEachItem)) );
							}
						} else if( !is_null( $strAsyncType ) ) {
							if(substr($strEachItem,0,2) === '//'){
								//$strOut .= sprintf( "(function(d){var g=d.createElement('link'),s=d.getElementsByTagName('script')[0];g.type='text/css';g.rel='stylesheet';g.href='%s';s.parentNode.insertBefore(g,s);}(document));", $strEachItem );
								$strOut .= sprintf( "(function(d){var g=d.createElement('link');g.type='text/css';g.rel='stylesheet';g.href='%s';d.getElementsByTagName('head')[0].appendChild(g);}(document));", $strEachItem );
							} else {
								//$strOut .= sprintf( "(function(d){var g=d.createElement('link'),s=d.getElementsByTagName('script')[0];g.type='text/css';g.rel='stylesheet';g.href='%slibraries/%s/%s';s.parentNode.insertBefore(g,s);}(document));", $strModuleURI,$arrParameters[0],$strEachItem );
								$strOut .= sprintf( "(function(d){var g=d.createElement('link');g.type='text/css';g.rel='stylesheet';g.href='%slibraries/%s/%s';d.getElementsByTagName('head')[0].appendChild(g);}(document));", $strModuleURI,$strReference,$strEachItem );
							}
						} else {
							if(substr($strEachItem,0,2) === '//'){
								$strOut .= sprintf('<link href="%s" type="text/css" rel="stylesheet" property="stylesheet">',$strEachItem);
							}else{
								$strOut .= sprintf('<link href="%s/%s/%s" type="text/css" rel="stylesheet" property="stylesheet">',$strModuleURI,$strReference,$strEachItem);
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
						//$strItemPath = (substr($strEachItem,0,2) == '//') ? $strEachItem : sprintf("%slibraries/%s/%s",$strModulePath,$arrParameters[0],$strEachItem);

						if( $blInline ) {
							if(substr($strEachItem,0,2) === '//'){
								$strOut .= sprintf( '%s ', file_get_contents($strEachItem) );
							} else {
								$strOut .= sprintf( '%s ', file_get_contents(sprintf("%s/%s/%s",$strModulePath,$strReference,$strEachItem) ) );
							}
						} else {
							if(substr($strEachItem,0,2) === '//'){
								$strOut .= sprintf('<script src="%s"%s></script>',$strEachItem,(is_null( $strAsyncType ) ? '' : sprintf( ' %s', $strAsyncType )));
							}else{
								$strOut .= sprintf('<script src="%s/%s/%s"%s></script>',$strModuleURI,$strReference,$strEachItem,(is_null( $strAsyncType ) ? '' : sprintf( ' %s', $strAsyncType )));
							}
						}
					}

					if( $blInline ) {
						$strOut = sprintf( '<script%s>%s</script>',(is_null( $strAsyncType ) ? '' : sprintf( ' %s', $strAsyncType ) ), $strOut );
					}
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
		$strJSON = file_get_contents(sprintf('%sCore/Resources/manifest.json',TWIST_FRAMEWORK));
		$this->arrLibraries = json_decode($strJSON,true);

		//Pre-process the paths and uris into the libraries
		foreach($this->arrLibraries as $strKey => $arrOptions){
			foreach($arrOptions as $strOptionKey => $arrOptionParameters){
				$this->arrLibraries[$strKey][$strOptionKey] = $this->applyPath($arrOptionParameters,sprintf('%sCore/Resources/',TWIST_FRAMEWORK));
			}
		}
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

	protected function applyPath($arrParameters,$dirResourcePath){

		$arrParameters['path'] = rtrim($dirResourcePath,'/');

		if(strstr(dirname($dirResourcePath),rtrim(TWIST_FRAMEWORK,'/'))){
			//Resource is in framework (this will work for frameworks in and out of the base)
			$arrParameters['uri'] = '/'.trim(TWIST_FRAMEWORK_URI.str_replace(TWIST_FRAMEWORK,'',$dirResourcePath),'/');
		}else{
			$arrParameters['uri'] = '/'.trim(str_replace(TWIST_DOCUMENT_ROOT,'',$dirResourcePath),'/');
		}

		return $arrParameters;
	}

	public function extendLibrary($dirManifest,$dirResourcePath){

		if(file_exists($dirManifest)){
			if(is_dir($dirResourcePath)){

				//Get the resource libraries
				$strJSON = file_get_contents($dirManifest);
				$arrExtendedLibraries = json_decode($strJSON,true);

				foreach($arrExtendedLibraries as $strKey => $arrOptions){
					if(array_key_exists($strKey,$this->arrLibraries)){

						foreach($arrOptions as $strSubKey => $arrParameters){
							if(array_key_exists($strSubKey,$this->arrLibraries[$strKey])){
								//@todo Decide if to keep original or overwrite - currently keeps original
							}else{
								$this->arrLibraries[$strKey][$strSubKey] = $this->applyPath($arrParameters,$dirResourcePath);
							}
						}
					}else{

						//Pre-process the paths and uris into the libraries
						foreach($arrOptions as $strOptionKey => $arrOptionParameters){
							$arrOptions[$strOptionKey] = $this->applyPath($arrOptionParameters,$dirResourcePath);
						}

						$this->arrLibraries[$strKey] = $arrOptions;
					}
				}
			}else{
				throw new \Exception(sprintf("TwistPHP: Error, extension resource directory '%s' does not exists loading",$dirManifest));
			}
		}else{
			throw new \Exception(sprintf("TwistPHP: Error loading resource manifest '%s' of and extension library",$dirManifest));
		}
	}
}