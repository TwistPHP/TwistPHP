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

namespace Twist\Core\Models;

/**
 * Core resource handler for the framework, output resources, App and Package JS and CSS files.
 */
class Resources{

	var $arrLibraries = array();
	protected $arrIncluded = array();

	/**
	 * Outputs framework resources in a view, multiple versions of a resource might be available to use. The option to load the resources inline or asynchronously can be used. Additional resources can be added to the library by packages or by following the online examples.
	 * Each unique resource request will only be output once per page to stop unnecessary content duplication. The first part of the tag is the resource key e.g 'jquery', if you want to include the default version use the following tag {resource:jquery}.
	 *
	 * Other parameters can be included in the tag, you can use a single or combination of parameters (some examples below):
	 *
	 * inline = 1
	 * Output the resource directly to the page, this will stop any additional js/css files being loaded into the page.
	 *
	 * async = async|defer
	 * Define the type of asynchronously loading, the choice of async or defer can be optionally set.
	 *
	 * version = 2.0.0
	 * The version of the resource that will be loaded, check the recourse library in the docs to find out what versions are available.
	 *
	 * An example of the tag with all the above parameters in use {resource:jquery,inline=1,async=async,version=2.0.0}
	 *
	 * @param $strReference
	 * @return string
	 */
	public function viewResource($strReference,$arrParameters = array()){

		$this->loadLibraryManifest();
		$strOut = '';

		if($strReference === 'core-uri'){
			return sprintf('%s/%sCore/Resources/',rtrim(SITE_URI_REWRITE,'/'),ltrim(TWIST_FRAMEWORK_URI,'/'));
		}

		$blInline = (array_key_exists('inline',$arrParameters)) ? true : false;
		$mxdAsyncType = (array_key_exists('async',$arrParameters) && in_array($arrParameters['async'],array('async','defer'))) ? $arrParameters['async'] : null;
		$mxdVersion = (array_key_exists('version',$arrParameters)) ? $arrParameters['version'] : null;

		$strIncludeKey = sprintf('%s,inline=%s,async=%s,version=%s',$strReference,($blInline) ? '1' : '0',$mxdAsyncType,$mxdVersion);

		//Only allow each unique resource to be output once per page
		if(!array_key_exists($strIncludeKey,$this->arrIncluded)){

			$this->arrIncluded[$strIncludeKey] = $strReference;
			$arrResource = $this->getFromLibrary($strReference,$mxdVersion);

			//If the count is bigger than 0 then output the data
			if(count($arrResource)){

				$strURI = ($strReference == '') ? $arrResource['uri'] : sprintf('%s/%s',$arrResource['uri'],$strReference);
				$strPath = ($strReference == '') ? $arrResource['path'] : sprintf('%s/%s',$arrResource['path'],$strReference);

				if(count($arrResource['css'])){
					$strOut .= $this->processCSS($arrResource['css'],$strPath,$strURI,$blInline,$mxdAsyncType);
				}

				if(count($arrResource['js'])){
					$strOut .= $this->processJS($arrResource['js'],$strPath,$strURI,$blInline,$mxdAsyncType);
				}
			}
		}

		return $strOut;
	}

	/**
	 * @param $strReference
	 * @param array $arrParameters
	 * @return string
	 */
	public function viewCSS($strReference,$arrParameters = array()){

		$strOut = '';

		if(substr($strReference,-4) != '.css'){
			$strReference .= '.css';
		}

		$dirLocalPath = $this->locateFile($strReference);

		if(file_exists($dirLocalPath)){

			$strURI = '';

			$blInline = (array_key_exists('inline',$arrParameters)) ? true : false;
			$mxdAsyncType = (array_key_exists('async',$arrParameters) && in_array($arrParameters['async'],array('async','defer'))) ? $arrParameters['async'] : null;

			$this->processCSS(array($strReference),$dirLocalPath,$strURI,$blInline,$mxdAsyncType);
		}

		return $strOut;
	}

	/**
	 * @param $strReference
	 * @param array $arrParameters
	 * @return string
	 */
	public function viewJS($strReference,$arrParameters = array()){

		$strOut = '';

		if(substr($strReference,-3) != '.js'){
			$strReference .= '.js';
		}

		$dirLocalPath = $this->locateFile($strReference);

		if(file_exists($dirLocalPath)){

			$strURI = '';

			$blInline = (array_key_exists('inline',$arrParameters)) ? true : false;
			$mxdAsyncType = (array_key_exists('async',$arrParameters) && in_array($arrParameters['async'],array('async','defer'))) ? $arrParameters['async'] : null;

			$this->processJS(array($strReference),$dirLocalPath,$strURI,$blInline,$mxdAsyncType);
		}

		return $strOut;
	}

	protected function locateFile($dirPath){

		//First check to see if an over-ride exists in the app folder
		$dirLocalPath = sprintf('%s/%s',TWIST_APP,ltrim($dirPath,'/'));

		if(!file_exists($dirLocalPath)){

			//If not then we now need to see if we can find the file in its requested location
			$arrRequestParts = explode('/',ltrim($dirPath,'/'));
			$strType = array_shift($arrRequestParts);
			$strRequestFile = implode('/',$arrRequestParts);

			if(count($arrRequestParts)){
				if($strType == 'app'){
					$dirLocalPath = sprintf('%s/%s',TWIST_APP,$strRequestFile);
				}elseif($strType == 'packages'){
					$dirLocalPath = sprintf('%s/%s',TWIST_PACKAGES,$strRequestFile);
				}elseif($strType == 'twist'){
					$dirLocalPath = sprintf('%s/%s',TWIST_FRAMEWORK,$strRequestFile);
				}else{
					$dirLocalPath = sprintf('%s/%s',TWIST_DOCUMENT_ROOT,ltrim($dirPath,'/'));
				}
			}
		}

		return $dirLocalPath;
	}

	/**
	 * Process the JS files and output them in the desired HTML format
	 * @param $arrFiles
	 * @param $strPath
	 * @param $strURI
	 * @param bool $blInline
	 * @param null|string $mxdAsyncType
	 * @return string
	 */
	protected function processJS($arrFiles,$strPath,$strURI,$blInline = false,$mxdAsyncType = null){

		$strOut = '';

		foreach($arrFiles as $strEachItem){
			if($blInline){
				if(substr($strEachItem,0,2) === '//'){
					$strOut .= sprintf( '%s ', file_get_contents($strEachItem) );
				}else{
					$strOut .= sprintf( '%s ', file_get_contents(sprintf("%s/%s",$strPath,$strEachItem) ) );
				}
			}else{
				if(substr($strEachItem,0,2) === '//'){
					$strOut .= sprintf('<script src="%s"%s></script>',$strEachItem,(is_null( $mxdAsyncType ) ? '' : sprintf( ' %s', $mxdAsyncType )));
				}else{
					$strOut .= sprintf('<script src="%s/%s"%s></script>',$strURI,$strEachItem,(is_null( $mxdAsyncType ) ? '' : sprintf( ' %s', $mxdAsyncType )));
				}
			}
		}

		if($blInline){
			$strOut = sprintf( '<script%s>%s</script>',(is_null( $mxdAsyncType ) ? '' : sprintf( ' %s', $mxdAsyncType ) ), $strOut );
		}

		return $strOut;
	}

	/**
	 * Process the CSS files and output them in the desired HTML format
	 * @param $arrFiles
	 * @param $strPath
	 * @param $strURI
	 * @param bool $blInline
	 * @param null|string $mxdAsyncType
	 * @return string
	 */
	protected function processCSS($arrFiles,$strPath,$strURI,$blInline = false,$mxdAsyncType = null){

		$strOut = '';

		foreach($arrFiles as $strEachItem){
			if($blInline){
				if(substr($strEachItem,0,2) === '//'){
					$strOut .= sprintf('%s ',file_get_contents($strEachItem));
				}else{
					$strOut .= sprintf('%s ',file_get_contents(sprintf("%s/%s",$strPath,$strEachItem)));
				}
			}else if(!is_null($mxdAsyncType)){
				if(substr($strEachItem,0,2) === '//'){
					$strOut .= sprintf("(function(d){var g=d.createElement('link');g.type='text/css';g.rel='stylesheet';g.href='%s';d.getElementsByTagName('head')[0].appendChild(g);}(document));", $strEachItem);
				}else{
					$strOut .= sprintf("(function(d){var g=d.createElement('link');g.type='text/css';g.rel='stylesheet';g.href='%s/%s';d.getElementsByTagName('head')[0].appendChild(g);}(document));", $strURI,$strEachItem);
				}
			}else{
				if(substr($strEachItem,0,2) === '//'){
					$strOut .= sprintf('<link href="%s" type="text/css" rel="stylesheet" property="stylesheet">',$strEachItem);
				}else{
					$strOut .= sprintf('<link href="%s/%s" type="text/css" rel="stylesheet" property="stylesheet">',$strURI,$strEachItem);
				}
			}
		}

		if(!is_null( $mxdAsyncType)){
			$strOut = sprintf('<script async>%s</script>', $strOut);
		}else if($blInline){
			$strOut = sprintf('<style type="text/css">%s</style>', $strOut);
		}

		return $strOut;
	}

	/**
	 * Load in the library manifest file
	 */
	protected function loadLibraryManifest(){

		if(!count($this->arrLibraries)){

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

	/**
	 * Apply the correct URI to a resource based on its resource directory path.
	 * @param $arrParameters
	 * @param $dirResourcePath
	 * @return mixed
	 */
	protected function applyPath($arrParameters,$dirResourcePath){

		$arrParameters['path'] = rtrim($dirResourcePath,'/');

		if(strstr(dirname($dirResourcePath),rtrim(TWIST_FRAMEWORK,'/'))){
			//Resource is in framework (this will work for frameworks in and out of the base)
			$arrParameters['uri'] = rtrim(SITE_URI_REWRITE,'/').'/'.trim(TWIST_FRAMEWORK_URI.str_replace(TWIST_FRAMEWORK,'',$dirResourcePath),'/');
		}else{
			$arrParameters['uri'] = rtrim(SITE_URI_REWRITE,'/').'/'.trim(str_replace(TWIST_DOCUMENT_ROOT,'',$dirResourcePath),'/');
		}

		return $arrParameters;
	}

	/**
	 * Extend the resource library with a whole new set of resources. This function can be called if you want to put some custom rescources into the system that the site or package can use.
	 * The resources will then become accessible via the {resource:} view tag.
	 * @param $dirManifest
	 * @param $dirResourcePath
	 * @throws \Exception
	 */
	public function extendLibrary($dirManifest,$dirResourcePath){

		$this->loadLibraryManifest();

		if(file_exists($dirManifest)){
			if(is_dir($dirResourcePath)){

				//Get the resource libraries
				$strJSON = file_get_contents($dirManifest);
				$arrExtendedLibraries = json_decode($strJSON,true);
				$blResourceOverride = \Twist::framework()->setting('RESOURCE_VERSION_OVERRIDE');

				foreach($arrExtendedLibraries as $strKey => $arrOptions){
					if(array_key_exists($strKey,$this->arrLibraries)){

						foreach($arrOptions as $strSubKey => $arrParameters){
							if(array_key_exists($strSubKey,$this->arrLibraries[$strKey])){
								//If over-ride enabled allow the extending library to over-ride the current file when the version numbers match
								$this->arrLibraries[$strKey][$strSubKey] = ($blResourceOverride) ? $this->applyPath($arrParameters,$dirResourcePath) : $this->arrLibraries[$strKey][$strSubKey];
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