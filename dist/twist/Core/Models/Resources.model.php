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
	 * @return string Processed HTML output for the resource view tag
	 */
	public function viewResource($strReference,$arrParameters = array()){

		$this->loadLibraryManifest();
		$strOut = '';

		if($strReference === 'core-uri'){
			return sprintf('%s/%sCore/Resources/',rtrim(SITE_URI_REWRITE,'/'),ltrim(TWIST_FRAMEWORK_URI,'/'));
		}

		$blURI = (array_key_exists('uri',$arrParameters)) ? true : false;
		$blOnlyJS = (array_key_exists('js',$arrParameters)) ? true : false;
		$blOnlyCSS = (array_key_exists('css',$arrParameters)) ? true : false;

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

				$intCountCSS = count($arrResource['css']);
				$intCountJS = count($arrResource['js']);

				$strURI = ($strReference == '') ? $arrResource['uri'] : sprintf('%s/%s',$arrResource['uri'],$strReference);
				$strPath = ($strReference == '') ? $arrResource['path'] : sprintf('%s/%s',$arrResource['path'],$strReference);

				if($blURI && (($blOnlyJS == $blOnlyCSS && ($intCountCSS+$intCountJS) > 1) || ($blOnlyCSS && !$blOnlyJS && $intCountCSS > 1) || ($blOnlyJS && !$blOnlyCSS && $intCountJS > 1))) {

					trigger_error("Ambiguous resource: more than one URI is output, uri parameter cannot be used here", E_USER_NOTICE);
					$strOut = 'ambiguous-resource';
				}elseif($blURI && ($blOnlyJS == $blOnlyCSS || $blOnlyCSS) && $intCountCSS){

					$strOut = $strURI.'/'.array_pop($arrResource['css']);
				}elseif($blURI && ($blOnlyJS == $blOnlyCSS || $blOnlyJS) && $intCountJS){

					$strOut = $strURI.'/'.array_pop($arrResource['css']);
				}else{

					if(($blOnlyJS == $blOnlyCSS || $blOnlyCSS == true) && $intCountCSS){
						$strOut .= $this->processCSS($arrResource['css'],$strPath,$strURI,$blInline,$mxdAsyncType);
					}

					if(($blOnlyJS == $blOnlyCSS || $blOnlyJS == true) && $intCountJS){
						$strOut .= $this->processJS($arrResource['js'],$strPath,$strURI,$blInline,$mxdAsyncType);
					}
				}
			}
		}

		return $strOut;
	}

	/**
	 * Outputs a CSS resource to the HTML using the view tag {css:}. The option to load the resources inline or asynchronously can be used.
	 * The first part of the tag is the resource path e.g 'packages/Lavish/Resources/css/base.css', the tag for this would be {css:packages/Lavish/Resources/css/base.css}.
	 *
	 * You can override CSS files that are located in twist or an installed package by placing a copy of the file and path in your apps folder. The override file would be included in the page rather than the original file, the example override file would need ot be created here [app/packages/Lavish/Resources/css/base.css].
	 * Other parameters can be included in the tag, you can use a single or combination of parameters (some examples below):
	 *
	 * inline = 1
	 * Output the resource directly to the page, this will stop any additional js/css files being loaded into the page.
	 *
	 * async = async|defer
	 * Define the type of asynchronously loading, the choice of async or defer can be optionally set.
	 *
	 * An example of the tag with all the above parameters in use {css:packages/Lavish/Resources/css/base.css,inline=1,async=async}
	 * @param $strReference
	 * @param array $arrParameters
	 * @return string Processed HTML output for the CSS view tag
	 */
	public function viewCSS($strReference,$arrParameters = array()){

		$strOut = '';

		if(substr($strReference,-4) != '.css'){
			$strReference .= '.css';
		}

		$arrFileInfo = $this->locateFile($strReference);
		if(!is_null($arrFileInfo['path'])){

			$blInline = (array_key_exists('inline',$arrParameters)) ? true : false;
			$mxdAsyncType = (array_key_exists('async',$arrParameters) && in_array($arrParameters['async'],array('async','defer'))) ? $arrParameters['async'] : null;

			$strOut = $this->processCSS(array($arrFileInfo['file']),$arrFileInfo['path'],$arrFileInfo['uri'],$blInline,$mxdAsyncType);
		}

		return $strOut;
	}

	/**
	 * Outputs a JS resource to the HTML using the view tag {js:}. The option to load the resources inline or asynchronously can be used.
	 * The first part of the tag is the resource path e.g 'packages/Lavish/Resources/js/base.js', the tag for this would be {js:packages/Lavish/Resources/js/base.js}.
	 *
	 * You can override JS files that are located in twist or an installed package by placing a copy of the file and path in your apps folder. The override file would be included in the page rather than the original file, the example override file would need ot be created here [app/packages/Lavish/Resources/js/base.js].
	 * Other parameters can be included in the tag, you can use a single or combination of parameters (some examples below):
	 *
	 * inline = 1
	 * Output the resource directly to the page, this will stop any additional js/css files being loaded into the page.
	 *
	 * async = async|defer
	 * Define the type of asynchronously loading, the choice of async or defer can be optionally set.
	 *
	 * An example of the tag with all the above parameters in use {js:packages/Lavish/Resources/js/base.js,inline=1,async=async}
	 * @param $strReference
	 * @param array $arrParameters
	 * @return string Processed HTML output for the JS view tag
	 */
	public function viewJS($strReference,$arrParameters = array()){

		$strOut = '';

		if(substr($strReference,-3) != '.js'){
			$strReference .= '.js';
		}

		$arrFileInfo = $this->locateFile($strReference);
		if(!is_null($arrFileInfo['path'])){

			$blInline = (array_key_exists('inline',$arrParameters)) ? true : false;
			$mxdAsyncType = (array_key_exists('async',$arrParameters) && in_array($arrParameters['async'],array('async','defer'))) ? $arrParameters['async'] : null;

			$strOut = $this->processJS(array($arrFileInfo['file']),$arrFileInfo['path'],$arrFileInfo['uri'],$blInline,$mxdAsyncType);
		}

		return $strOut;
	}

	/**
	 * Outputs a Image resource to the HTML using the view tag {img:}. The options to output the image as a URI or as a base64 encoded string are also available.
	 * The first part of the tag is the image path e.g 'packages/Lavish/Resources/images/user.png', the tag for this would be {img:packages/Lavish/Resources/images/user.png}.
	 *
	 * You can override Image files that are located in twist or an installed package by placing a copy of the file and path in your apps folder. The override file would be included in the page rather than the original file, the example override file would need ot be created here [app/packages/Lavish/Resources/images/user.png].
	 * Other parameters can be included in the tag, you can use a single or combination of parameters (some examples below):
	 *
	 * uri = 1
	 * Output the image URI in plain text, no IMG tag will be output
	 *
	 * base64 = 1
	 * Output the image as a base64 encoded string, when used in conjunction with uri the base64 string will be output instead of the URI.
	 *
	 * All other parameters passed in will be output as attributes of the IMG tag, for example title='Hello World'
	 *
	 * An example of the tag with the title and id parameters set {img:packages/Lavish/Resources/images/user.png,title='Hello World',id='user-77'}
	 * @param $strReference
	 * @param array $arrParameters
	 * @return string
	 */
	public function viewImage($strReference,$arrParameters = array()){

		$strOut = '';

		$arrFileInfo = $this->locateFile($strReference);
		if(!is_null($arrFileInfo['path'])){

			if(array_key_exists('base64',$arrParameters)){
				$resImage = \Twist::Image()->load(sprintf('%s/%s',$arrFileInfo['path'],$arrFileInfo['file']));
				$strImageSRC = $resImage->outputBase64();
				unset($resImage);
			}else{
				$strImageSRC = sprintf('%s/%s',$arrFileInfo['uri'],$arrFileInfo['file']);
			}

			if(array_key_exists('uri',$arrParameters)){
				$strOut = $strImageSRC;
			}else{

				$strAttributes = '';
				foreach($arrParameters as $strKey => $mxdValue){
					if(!in_array(strtolower($strKey),array('src','base64','uri'))){
						$strAttributes .= sprintf(' %s="%s"',strtolower($strKey),htmlentities($mxdValue));
					}
				}

				$strOut = sprintf('<img src="%s"%s>',$strImageSRC,$strAttributes);
			}
		}

		return $strOut;
	}

	/**
	 * Locate the resource file by its path, can be app/*, packages/*, twist/* or any path in the document root.
	 * If requesting a file in either twist or packages it can be over-ridden by placing a corresponding file in the apps folder, for example:
	 *
	 * [packages/Lavish/Resources/css/base.css] is over-ridden by [app/packages/Lavish/Resources/css/base.css]
	 *
	 * @param $dirPath
	 * @return array An array of teh file name and the path and URI to the file
	 */
	protected function locateFile($dirPath){

		$arrOut = array('file' => null,'path' => null,'uri' => null);

		//First check to see if an over-ride exists in the app folder
		$dirAppPath = sprintf('%s/%s',TWIST_APP,ltrim($dirPath,'/'));

		if(!file_exists($dirAppPath)){

			//If not then we now need to see if we can find the file in its requested location
			$arrRequestParts = explode('/',ltrim($dirPath,'/'));
			$strType = array_shift($arrRequestParts);
			$strRequestFile = implode('/',$arrRequestParts);

			if(count($arrRequestParts)){
				if($strType == 'app'){
					$dirAppPath = sprintf('%s/%s',TWIST_APP,$strRequestFile);
				}elseif($strType == 'packages'){
					$dirAppPath = sprintf('%s/%s',TWIST_PACKAGES,$strRequestFile);
				}elseif($strType == 'twist'){
					$dirAppPath = sprintf('%s/%s',TWIST_FRAMEWORK,$strRequestFile);
				}else{
					$dirAppPath = sprintf('%s/%s',TWIST_DOCUMENT_ROOT,ltrim($dirPath,'/'));
				}
			}

			if(file_exists($dirAppPath)){
				$arrOut['file'] = basename($dirAppPath);
				$arrOut = $this->applyPath($arrOut,dirname($dirAppPath));
			}
		}else{
			$arrOut['file'] = basename($dirAppPath);
			$arrOut = $this->applyPath($arrOut,dir($dirAppPath));
		}

		return $arrOut;
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