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

/**
 * Simply setup a website with multiple pages in minutes. Create restricted areas with login pages and dynamic sections with wild carded URI's.
 * Just a couple lines of code and you will be up and running.
 */
class Route extends PackageBase{

	protected $bl404 = true;

	protected $arrRoutes = array();
	protected $arrRoutesGET = array();
	protected $arrRoutesPOST = array();
	protected $arrRoutesPUT = array();
	protected $arrRoutesDELETE = array();

	protected $arrWildCards = array();
	protected $arrRegxMatches = array();
	protected $arrRestrict = array();
	protected $arrUnrestricted = array();
	protected $strBaseView = null;
	protected $strBaseURI = null;
	protected $strInterfaceURI = null;
	protected $strPageTitle = '';
	protected $intCacheTime = 3600;
	protected $blDebugMode = false;
	protected $strInstance = '';
	protected $resView = null;
	protected $strControllerDirectory = null;

	public function __construct($strInstance){

		$this->strInstance = $strInstance;
		$this->resView = \Twist::View();
		$this->blDebugMode = (\Twist::framework()->setting('DEVELOPMENT_MODE') && \Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR'));

		$strControllerPath = DIR_APP_CONTROLLERS;
		if(file_exists($strControllerPath)){
			$this->setControllerDirectory($strControllerPath);
		}
	}

	/**
	 * Set a custom views directory to use for this routes instance, leaving blank will use the default views directory
	 * @param $dirViewPath Path to the view directory
	 */
	public function setDirectory($dirViewPath = null){
		$this->resView->setDirectory($dirViewPath);
	}

	/**
	 * Set a custom controller directory to use for this routes instance, leaving blank will use the default controllers directory
	 * @param $dirControllerPath Path to the controller directory
	 */
	public function setControllerDirectory($dirControllerPath = null){
		$this->strControllerDirectory = rtrim($dirControllerPath,'/');
	}

	/**
	 * Set a path to the base view that you wish to wrap the output of the route with
	 * @param $dirViewFile Path to the base view file, relative to your view directory (a full path can be used if required)
	 */
	public function baseView($dirViewFile = null){

		if(!is_null($dirViewFile)){
			$this->strBaseView = $dirViewFile;
		}

		return $this->strBaseView;
	}

	/**
	 * Set a base URI so that you can use routes in folders that are not your Doc Root
	 * @param $strBaseURI
	 */
	public function baseURI($strBaseURI = null){

		if(!is_null($strBaseURI)){
			$this->strBaseURI = '/'.ltrim(rtrim($strBaseURI,'/'),'/');

			if($this->strBaseURI == '/'){
				$this->strBaseURI = '';
			}
		}

		return $this->strBaseURI;
	}

	/**
	 * Set/Get the interface URI, used only when creating or working with an framework interface
	 * @param $strInterface
	 */
	public function interfaceURI($strInterface = null){

		if(!is_null($strInterface)){
			$strPath = sprintf('%s%s',DIR_FRAMEWORK_INTERFACES,$strInterface);
			$this->strInterfaceURI = '/'.ltrim(rtrim(str_replace(BASE_LOCATION,"",$strPath),'/'),'/');
		}

		return $this->strInterfaceURI;
	}

	/**
	 * Disable the use of the base view for this page (can be called during the processing of the page)
	 */
	public function baseViewIgnore(){
		$this->strBaseView = null;
	}

	/**
	 * Enable debug mode, this will override the debug/development settings in Twist Settings
	 */
	public function debugMode($blEnabled = true){
		$this->blDebugMode = $blEnabled;
	}

	/**
	 * Purge the instance of routes
	 */
	public function purge(){
		$this->arrRoutes = array();
		$this->arrRoutesGET = array();
		$this->arrRoutesPUT = array();
		$this->arrRoutesPOST = array();
		$this->arrRoutesDELETE = array();
	}

	/**
	 * Set the page title for the page (can be called during the processing of the page)
	 */
	public function pageTitle($strPageTitle){
		$this->strPageTitle = $strPageTitle;
	}

	protected function _restrictDefault($strURI,$strLoginURI){

		//Only allow page restrictions with a working database connection
		if(!\Twist::Database()->checkSettings()){
			throw new \Exception('TwistPHP: You must have a database connection enabled to restricted pages using Routes');
		}

		$blWildCard = strstr($strURI,'%');
		$strURI = rtrim(str_replace('%','',$strURI),'/').'/';

		if(!array_key_exists($strURI,$this->arrRestrict)){
			$this->arrRestrict[$strURI] = array(
				'wildcard' => $blWildCard,
				'login_uri' => '/'.ltrim(rtrim($strLoginURI,'/'),'/').'/',
				'level' => null,
				'group' => null
			);
		}

		return $strURI;
	}

	/**
	 * Restrict a page to logged in users only, place a '%' at the end of the URI will apply this restriction to all child pages as well as itself
	 *
	 * @note Restrict a URI without using '%' will only restrict the exact URI provided
	 * @param $strURI
	 * @param $strLoginURI
	 * @param $intLevel By default it restricts page to the lowest possible level (1)
	 */
	public function restrict($strURI,$strLoginURI,$intLevel = 1){

		$strURI = $this->_restrictDefault($strURI,$strLoginURI);
		$this->arrRestrict[$strURI]['level'] = $intLevel;
	}

	/**
	 * Add an exception to the restrictions applied
	 */
	public function unrestrict($strURI){

		$blWildCard = (strstr($strURI,'%'));
		$strURI = rtrim(str_replace('%','',$strURI),'/').'/';

		if(!array_key_exists($strURI,$this->arrUnrestricted)){
			$this->arrUnrestricted[$strURI] = $blWildCard;
		}
	}

	/**
	 * @related restrict
	 *
	 * @param $strURI
	 * @param $strLoginURI
	 */
	public function restrictMember($strURI,$strLoginURI){
		$this->restrict($strURI,$strLoginURI,\Twist::framework()->setting('USER_LEVEL_MEMBER'));
	}

	/**
	 * @related restrict
	 *
	 * @param $strURI
	 * @param $strLoginURI
	 */
	public function restrictAdvanced($strURI,$strLoginURI){
		$this->restrict($strURI,$strLoginURI,\Twist::framework()->setting('USER_LEVEL_ADVANCED'));
	}

	/**
	 * @related restrict
	 *
	 * @param $strURI
	 * @param $strLoginURI
	 */
	public function restrictAdmin($strURI,$strLoginURI){
		$this->restrict($strURI,$strLoginURI,\Twist::framework()->setting('USER_LEVEL_ADMIN'));
	}

	/**
	 * @related restrict
	 *
	 * @param $strURI
	 * @param $strLoginURI
	 */
	public function restrictSuperAdmin($strURI,$strLoginURI){
		$this->restrict($strURI,$strLoginURI,\Twist::framework()->setting('USER_LEVEL_SUPERADMIN'));
	}

	/**
	 * @related restrict
	 *
	 * @param $strURI
	 * @param $strLoginURI
	 */
	public function restrictRoot($strURI,$strLoginURI){
		$this->restrict($strURI,$strLoginURI,0);
	}

	/**
	 * @related restrict
	 *
	 * @param $strURI
	 * @param $strLoginURI
	 * @param $strGroupSlug
	 */
	public function restrictGroup($strURI,$strLoginURI,$strGroupSlug){

		$strURI = $this->_restrictDefault($strURI,$strLoginURI);

		if(is_null($strGroupSlug)){
			$this->arrRestrict[$strURI]['group'] = null;
		}else{

			if(is_null($this->arrRestrict[$strURI]['group'])){
				$this->arrRestrict[$strURI]['group'] = array();
			}

			$this->arrRestrict[$strURI]['group'][] = $strGroupSlug;
		}
	}

	/**
	 * Add a controller that will be called upon a any request (HTTP METHOD) to the given URI.
	 * The URI can be made dynamic by adding a '%' symbol at the end.
	 *
	 * @param $strURI
	 * @param $mxdController
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function controller($strURI,$mxdController,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$arrController = (is_array($mxdController)) ? $mxdController : array($mxdController);
		$this->addRoute($strURI,'controller',$arrController,$mxdBaseView,$mxdCache,$arrData);
	}

	/**
	 * Add a ajax server that will be called upon a any request (HTTP METHOD) to the given URI.
	 * The URI can be made dynamic by adding a '%' symbol at the end.
	 *
	 * @param $strURI
	 * @param null $strFunctionsFolder
	 * @param null $strViewsFolder
	 * @param null $strElementsFolder
	 */
	public function ajax($strURI,$strFunctionsFolder = null,$strViewsFolder = null,$strElementsFolder = null,$blUnrestrict=false){

		$arrCustomFields = array(
			'functions' => $strFunctionsFolder,
			'views' => $strViewsFolder,
			'elements' => $strElementsFolder
		);

		if($blUnrestrict){
			$this->unrestrict($strURI);
		}

		$this->addRoute($strURI,'ajax',null,false,false,$arrCustomFields);
	}

	/**
	 * Add a UI (User Interface) that will be called upon a any request (HTTP METHOD) to the given URI.
	 * The URI can be made dynamic by adding a '%' symbol at the end.
	 *
	 * @param $strURI
	 * @param $strInterface
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function ui($strURI,$strInterface,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'interface',$strInterface,$mxdBaseView,$mxdCache,$arrData);
	}

	/**
	 * Add a redirect that will be called upon a any request (HTTP METHOD) to the given URI.
	 * The URI can be made dynamic by adding a '%' symbol at the end.
	 *
	 * @param $strURI
	 * @param $strURL
	 * @param $blPermanent
	 */
	public function redirect($strURI,$strURL,$blPermanent = false){
		$this->addRoute($strURI,($blPermanent) ? 'redirect-permanent' : 'redirect',$strURL);
	}

	/**
	 * Add a view that will be called upon a any request (HTTP METHOD) to the given URI, using this call will not take precedence over a GET,POST,PUT or DELETE route.
	 * The URI can be made dynamic by adding a '%' symbol at the end.
	 *
	 * @param $strURI
	 * @param $dirView
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function view($strURI,$dirView,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'view',$dirView,$mxdBaseView,$mxdCache,$arrData);
	}

	/**
	 * Add a view that will only be called upon a GET request (HTTP METHOD) to the given URI
	 *
	 * @related view
	 *
	 * @param $strURI
	 * @param $dirView
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function getView($strURI,$dirView,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'view',$dirView,$mxdBaseView,$mxdCache,$arrData,'GET');
	}

	/**
	 * Add a view that will only be called upon a POST request (HTTP METHOD) to the given URI
	 *
	 * @related view
	 *
	 * @param $strURI
	 * @param $dirView
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function postView($strURI,$dirView,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'view',$dirView,$mxdBaseView,$mxdCache,$arrData,'POST');
	}

	/**
	 * Add a view that will only be called upon a PUT request (HTTP METHOD) to the given URI
	 *
	 * @related view
	 *
	 * @param $strURI
	 * @param $dirView
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function putView($strURI,$dirView,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'view',$dirView,$mxdBaseView,$mxdCache,$arrData,'PUT');
	}

	/**
	 * Add a view that will only be called upon a DELETE request (HTTP METHOD) to the given URI
	 *
	 * @related view
	 *
	 * @param $strURI
	 * @param $dirView
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function deleteView($strURI,$dirView,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'view',$dirView,$mxdBaseView,$mxdCache,$arrData,'DELETE');
	}

	/**
	 * Add a element that will be called upon a any request (HTTP METHOD) to the given URI, using this call will not take precedence over a GET,POST,PUT or DELETE route.
	 * The URI can be made dynamic by adding a '%' symbol at the end.
	 *
	 * @param $strURI
	 * @param $strElement
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function element($strURI,$strElement,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'element',$strElement,$mxdBaseView,$mxdCache,$arrData);
	}

	/**
	 * Add a element that will only be called upon a GET request (HTTP METHOD) to the given URI
	 *
	 * @related element
	 *
	 * @param $strURI
	 * @param $strElement
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function getElement($strURI,$strElement,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'element',$strElement,$mxdBaseView,$mxdCache,$arrData,'GET');
	}

	/**
	 * Add a element that will only be called upon a POST request (HTTP METHOD) to the given URI
	 *
	 * @related element
	 *
	 * @param $strURI
	 * @param $strElement
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function postElement($strURI,$strElement,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'element',$strElement,$mxdBaseView,$mxdCache,$arrData,'POST');
	}

	/**
	 * Add a element that will only be called upon a PUT request (HTTP METHOD) to the given URI
	 *
	 * @related element
	 *
	 * @param $strURI
	 * @param $strElement
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function putElement($strURI,$strElement,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'element',$strElement,$mxdBaseView,$mxdCache,$arrData,'PUT');
	}

	/**
	 * Add a element that will only be called upon a DELETE request (HTTP METHOD) to the given URI
	 *
	 * @related element
	 *
	 * @param $strURI
	 * @param $strElement
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function deleteElement($strURI,$strElement,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'element',$strElement,$mxdBaseView,$mxdCache,$arrData,'DELETE');
	}

	/**
	 * Add the route into the array of listeners, when the serve function if called the listeners array will be processed and a page will be served
	 *
	 * @param $strURI
	 * @param $strType
	 * @param $strItem
	 * @param $arrData
	 * @param bool $mxdCache
	 */
	protected function addRoute($strURI,$strType,$strItem,$mxdBaseView=true,$mxdCache=false,$arrData=array(),$strRequestMethod = null){

		$blWildCard = false;
		if(substr($strURI,-1) == '%' || $strType == 'interface'){
			$blWildCard = true;
			$strURI = str_replace('%','',$strURI);
		}

		$strTrailingSlash = ($this->framework()->setting('SITE_TAILING_SLASH')) ? '/' : '';
		$strURI = rtrim($strURI,'/').$strTrailingSlash;

		$regxMatchURI = null;
		if(strstr($strURI,'/{') && strstr($strURI,'}')){
			/**
			 * Turn the URI '/my/{page}/uri'
			 * Into the Regx '#^(?<twist_uri>\/my\/(?<tphp_page>[^\/]+)\/uri)#i'
			 * If Wildcard '#^(?<twist_uri>\/my\/(?<tphp_page>[^\/]+)\/uri)(?<twist_wildcard>.*)#i'
			 */
			$regxMatchURI = sprintf("#^(?<twist_uri>%s)%s#i",str_replace(array("/","{","}"),array("\\/","(?<tphp_",">[^\/]+)"),$strURI),($blWildCard) ? '(?<twist_wildcard>.*)' : '$');
		}

		$arrRouteData = array(
			'regx' => $regxMatchURI,
			'uri' => '',
			'registered_uri' => null,
			'registered_uri_current' => $strURI,
			'base_uri' => null,
			'base_url' => null,
			'url' => null,
			'method' => (is_null($strRequestMethod)) ? 'ANY' : $strRequestMethod,
			'type' => $strType,
			'item' => $strItem,
			'data' => $arrData,
			'base_view' => $mxdBaseView,
			'wildcard' => $blWildCard,
			'cache' => ($mxdCache === false) ? false : true,
			'cache_key' => null,
			'cache_life' => ($mxdCache === true) ? $this->intCacheTime : ($mxdCache !== false) ? $mxdCache : 0
		);

		switch($arrRouteData['method']){
			case'GET':
				$this->arrRoutesGET[$strURI] = $arrRouteData;
				break;
			case'POST':
				$this->arrRoutesPOST[$strURI] = $arrRouteData;
				break;
			case'PUT':
				$this->arrRoutesPUT[$strURI] = $arrRouteData;
				break;
			case'DELETE':
				$this->arrRoutesDELETE[$strURI] = $arrRouteData;
				break;
			default:
				$this->arrRoutes[$strURI] = $arrRouteData;
				break;
		}

		if($blWildCard && is_null($regxMatchURI)){

			//Add the plain wild cards
			$this->arrWildCards[] = $strURI;
			sort($this->arrWildCards);
		}elseif(!is_null($regxMatchURI)){

			//Add the regx matches including the regx wildcard matches
			$this->arrRegxMatches[$strURI] = $regxMatchURI;
			ksort($this->arrRegxMatches);
		}
	}

	/**
	 * Process all the routes and add in the baseURI where required
	 */
	protected function processRoutes(){
		$this->processRoutesArray($this->arrRoutesGET);
		$this->processRoutesArray($this->arrRoutesPOST);
		$this->processRoutesArray($this->arrRoutesPUT);
		$this->processRoutesArray($this->arrRoutesDELETE);
		$this->processRoutesArray($this->arrRoutes);
	}

	/**
	 * Process the routes Array by reference and adding in the current set baseURI
	 * @param $arrRoutesDataRef
	 */
	protected function processRoutesArray(&$arrRoutesDataRef){

		if(count($arrRoutesDataRef)){

			foreach($arrRoutesDataRef as $strURI => $arrEachRoute){

				$arrEachRoute['registered_uri'] = sprintf("%s%s",$this->baseURI(),str_replace('//','/',$strURI));
				$arrEachRoute['base_uri'] = $this->baseURI();
				$arrEachRoute['base_url'] = sprintf("%s://%s%s",$this->framework()->setting('SITE_PROTOCOL'),$this->framework()->setting('SITE_HOST'),$this->baseURI());
				$arrEachRoute['url'] = sprintf("%s://%s%s%s",$this->framework()->setting('SITE_PROTOCOL'),$this->framework()->setting('SITE_HOST'),$this->baseURI(),str_replace('//','/',$strURI));
				$arrEachRoute['cache_key'] = str_replace('/','+',trim(sprintf("%s%s",$this->baseURI(),str_replace('//','/',$strURI)),'/'));

				$arrRoutesDataRef[$strURI] = $arrEachRoute;
			}
		}
	}

	/**
	 * Load an existing page form the page cache, use the page key to find the cached page.
	 * @param $strPageCacheKey
	 */
	protected function loadPageCache($strPageCacheKey){

		//Get the page cache if exists
		$arrCacheInfo = \Twist::Cache('pkgRoute')->retrieve($strPageCacheKey,true);

		if(!is_null($arrCacheInfo)){

			$mxdModifiedTime = gmdate('D, d M Y H:i:s ', strtotime($arrCacheInfo['info']['create_date'])) . 'GMT';
			$strETag = sha1($strPageCacheKey . $mxdModifiedTime);

			$blModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
			$blNoneMatch = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;

			if((($blNoneMatch && $blNoneMatch == $strETag) || (!$blNoneMatch)) && ($blModifiedSince && $blModifiedSince == $mxdModifiedTime)){
				header('HTTP/1.1 304 Not Modified');
				die();
			}elseif(!$blModifiedSince && !$blNoneMatch){

				header("Cache-Control: max-age=".$arrCacheInfo['info']['life_time']);
				header("Last-Modified: $mxdModifiedTime");
				header("ETag: \"{$strETag}\"");

				//Output the cached page here
				echo $arrCacheInfo['data'];
				die();
			}
		}
	}

	/**
	 * Store a page into the page cache, use a unique page key so that the page can be found again later when required.
	 * @param $strPageCacheKey
	 * @param $strPageData
	 * @param $intCacheTime
	 */
	protected function storePageCache($strPageCacheKey,$strPageData,$intCacheTime = 3600){

		$mxdModifiedTime = gmdate('D, d M Y H:i:s ', \Twist::DateTime()->time()) . 'GMT';
		$strEtag = sha1($strPageCacheKey . $mxdModifiedTime);

		header("Cache-Control: max-age=".$intCacheTime);
		header("Last-Modified: $mxdModifiedTime");
		header("ETag: \"{$strEtag}\"");

		\Twist::Cache('pkgRoute')->store($strPageCacheKey,$strPageData,$intCacheTime);
	}

	/**
	 * Get all routes that are registered the to current request method, either GET,POST,PUT,DELETE. This function does not return teh ANY array
	 * @return array
	 */
	protected function currentMethodRoutes(){

		$this->processRoutesArray($this->arrRoutes);

		switch(strtoupper($_SERVER['REQUEST_METHOD'])){
			case'GET':
				$this->processRoutesArray($this->arrRoutesGET);
				return $this->arrRoutesGET;
				break;
			case'POST':
				$this->processRoutesArray($this->arrRoutesPOST);
				return $this->arrRoutesPOST;
				break;
			case'PUT':
				$this->processRoutesArray($this->arrRoutesPUT);
				return $this->arrRoutesPUT;
				break;
			case'DELETE':
				$this->processRoutesArray($this->arrRoutesDELETE);
				return $this->arrRoutesDELETE;
				break;
		}

		return array();
	}

	/**
	 * Detect the current active route from the users URI, uses the PHP variable $_SERVER['REQUEST_URI'] to achieve this.
	 * Wild card detection is also carried out here if you are on a wild carded domain.
	 *
	 * @return array Returns an array of data relating to the current route
	 */
	public function current(){

		$arrOut = $arrUriParameters = array();
		$arrMethodRoutes = $this->currentMethodRoutes();

		if(count($arrMethodRoutes) || count($this->arrRoutes)){

			$arrPartsURI = explode('?',$_SERVER['REQUEST_URI']);
			$strPageCacheKey = str_replace('/','+',trim($arrPartsURI[0],'/'));
			$arrPartsURI[0] = (!in_array($this->strBaseURI,array(null,'/'))) ? str_replace($this->strBaseURI,'',$arrPartsURI[0]) : $arrPartsURI[0];

			$strTrailingSlash = ($this->framework()->setting('SITE_TAILING_SLASH')) ? '/' : '';

			//Get the current URI to be used, added a URI key as teh regx version has 2 different variations a real URI and a param uri (the key)
			$strCurrentURI = $strCurrentURIKey = rtrim( str_replace(rtrim($this->framework()->setting('SITE_BASE'),'/'),'',$arrPartsURI[0]), '/').$strTrailingSlash;

			$strRouteDynamic = '';
			$arrRouteParts = array();
			$blMatched = false;

			//Only go into these wildcard and regx matches is there is not exact match found
			if(!array_key_exists($strCurrentURIKey,$arrMethodRoutes) && !array_key_exists($strCurrentURIKey,$this->arrRoutes)) {

				//Check the regX matched first if there are any to be checked
				if(count($this->arrRegxMatches)){

					$arrFoundRegxMatches = array();

					foreach($this->arrRegxMatches as $strMatchedURI => $regxUriExpression){
						if(preg_match($regxUriExpression, $strCurrentURI, $arrResult)){
							$arrFoundRegxMatches[$strMatchedURI] = array('match_uri' => $strMatchedURI, 'matches' => $arrResult);
						}
					}

					if(count($arrFoundRegxMatches)){

						krsort($arrFoundRegxMatches);
						$arrMatchResults = array_shift($arrFoundRegxMatches);

						foreach($arrMatchResults['matches'] as $strKey => $strValue){
							if(strstr($strKey,'tphp_')){
								$arrUriParameters[str_replace('tphp_','',$strKey)] = $strValue;
							}
						}

						if(array_key_exists('twist_wildcard',$arrMatchResults['matches'])){
							$strRouteDynamic = $arrMatchResults['matches']['twist_wildcard'];
							$arrRouteParts = (!in_array(trim($strRouteDynamic,'/'),array('','/'))) ? explode('/',trim($strRouteDynamic,'/')) : array();
						}

						$strCurrentURIKey = $arrMatchResults['match_uri'];
						$strCurrentURI = $arrMatchResults['matches']['twist_uri'];
						$blMatched = true;
					}
				}

				//If no route is found and there are wild cards then look up the wild cards
				if(!$blMatched && !array_key_exists($strCurrentURI,$arrMethodRoutes) && !array_key_exists($strCurrentURI,$this->arrRoutes) && count($this->arrWildCards)){

					$arrFoundWildCard = array();

					foreach($this->arrWildCards as $strWildCard){
						if(substr($strCurrentURI,0,strlen($strWildCard)) === $strWildCard){
							$arrFoundWildCard[strlen($strWildCard)] = $strWildCard;
						}
					}

					if(count($arrFoundWildCard)){

						arsort($arrFoundWildCard);
						$strWildCard = array_shift($arrFoundWildCard);

						$strRouteDynamic = substr($strCurrentURI,strlen($strWildCard),strlen($strCurrentURI)-strlen($strWildCard));
						$arrRouteParts = (!in_array(trim($strRouteDynamic,'/'),array('','/'))) ? explode('/',trim($strRouteDynamic,'/')) : array();

						$strCurrentURI = $strCurrentURIKey = $strWildCard;
						$blMatched = true;
					}
				}
			}

			//Use the Current URI Key here to ensure
			if(array_key_exists($strCurrentURIKey,$arrMethodRoutes) || array_key_exists($strCurrentURIKey,$this->arrRoutes)){

				$arrOut = array_key_exists($strCurrentURIKey,$arrMethodRoutes) ? $arrMethodRoutes[$strCurrentURIKey] : $this->arrRoutes[$strCurrentURIKey];

				$arrOut['cache_key'] = $strPageCacheKey;

				$arrOut['title'] = (is_null($arrOut['title'])) ? $this->framework() -> setting('SITE_NAME') : $arrOut['title'];
				$arrOut['uri'] = sprintf('%s/%s',rtrim($strCurrentURI,'/'),ltrim($strRouteDynamic,'/'));
				$arrOut['vars'] = $arrUriParameters;
				$arrOut['dynamic'] = $strRouteDynamic;
				$arrOut['parts'] = $arrRouteParts;

				//Now sanitise the registered_uri_current and the url
				if(!is_null($arrOut['regx'])){
					foreach($arrUriParameters as $strParamKey => $strParamValue){
						$strReplaceKey = sprintf("{%s}",$strParamKey);
						$arrOut['registered_uri_current'] = str_replace($strReplaceKey,$strParamValue,$arrOut['registered_uri_current']);
						$arrOut['url'] = str_replace($strReplaceKey,$strParamValue,$arrOut['url']);
					}
				}
			}
		}

		return $arrOut;
	}

	public function currentRestriction($strCurrentURI){

		$blRestrict = false;
		$arrFoundMatched = $arrMatch = array();

		$strCurrentURI = ($this->strBaseURI == '/') ? $strCurrentURI : str_replace($this->strBaseURI,'',$strCurrentURI);
		$strFullLoginURI = str_replace('//','/',sprintf('%s/login',$this->strBaseURI));
		//$strFullLoginURL = sprintf('%s/%s', $arrRoute['registered_uri'], ltrim($this->framework()->setting('USER_DEFAULT_LOGIN_URI'), '/'));

		if(\Twist::Database()->checkSettings()){

			\Twist::User()->logout();
			\Twist::User()->authenticate();

			$blLoggedIn = \Twist::User()->loggedIn();
			$intCurrentUserLevel = \Twist::User()->currentLevel();

			foreach($this->arrRestrict as $strRestrictURI => $arrRestrictedInfo){

				$strRestrictExpression = sprintf("#^(%s[\/]?)%s#", str_replace('/','\/',rtrim($strRestrictURI, '/')), $arrRestrictedInfo['wildcard'] ? '' : '$');

				//Check for an exact match
				if(rtrim($strRestrictURI,'/') == rtrim($strCurrentURI,'/')){

					$arrMatch = $arrRestrictedInfo;
					$blRestrict = true;
					break;

				}elseif(preg_match($strRestrictExpression, $strCurrentURI, $arrMatches)){
					$arrFoundMatched[] = $arrRestrictedInfo;
				}

				//Log all login pages to be un-restricted
				$this->arrUnrestricted[rtrim($arrRestrictedInfo['login_uri'],'/')] = true;
			}

			//No exact mach found and there is an array to be processed
			if($blRestrict == false && count($arrFoundMatched)){

				if(count($arrFoundMatched) == 1){
					$blRestrict = true;
					$arrMatch = $arrFoundMatched[0];
				}else{

					//Process Multi-Matches, find the highest level from the found matches, user must match or exceed this level (0 is God)
					$intHighestLevel = 0;
					foreach($arrFoundMatched as $arrEachMatch){
						if($arrEachMatch['level'] == 0 || $arrEachMatch['level'] > $intHighestLevel){
							$intHighestLevel = $arrEachMatch['level'];
							$arrMatch = $arrEachMatch;
							$blRestrict = true;

							if($intHighestLevel == 0){
								break;
							}
						}
					}
				}
			}

			//If a match is found
			if($blRestrict){

				$strFullLoginURI = str_replace('//','/',sprintf('%s/%s',$this->strBaseURI,ltrim($arrMatch['login_uri'],'/')));

				if(array_key_exists(rtrim($strCurrentURI,'/'),$this->arrUnrestricted)){
					$arrMatch = array(
						'login_required' => false,
						'allow_access' => true,
						'login_uri' => $strFullLoginURI,
						'status' => 'Ignored, unrestricted page'
					);
				}else{

					if($blLoggedIn){
						if($arrMatch['level'] > 0 && $intCurrentUserLevel >= $arrMatch['level'] || $intCurrentUserLevel == 0){
							$arrMatch['login_required'] = false;
							$arrMatch['allow_access'] = true;
							$arrMatch['status'] = 'User level sufficient, allow Access';
						}else{
							$arrMatch['login_required'] = false;
							$arrMatch['allow_access'] = false;
							$arrMatch['status'] = 'User level insufficient, Deny Access';
						}
					}else{
						$arrMatch['login_required'] = true;
						$arrMatch['allow_access'] = false;
						$arrMatch['status'] = 'User must be logged in to access restricted page';
					}
				}

				$arrMatch['login_uri'] = $strFullLoginURI;
			}else{
				$arrMatch = array(
					'login_required' => false,
					'allow_access' => true,
					'login_uri' => $strFullLoginURI,
					'status' => 'No restriction found'
				);
			}
		}else{
			$arrMatch = array(
				'login_required' => false,
				'allow_access' => true,
				'login_uri' => $strFullLoginURI,
				'status' => 'No restriction found'
			);
		}

		return $arrMatch;
	}

	public function getID($strURI){

		$intOut = null;
		$objDB = \Twist::Database();

		if($objDB->checkSettings()){

			$strSQL = sprintf("SELECT * FROM `%s`.`structure_routes` WHERE `uri` = '%s' LIMIT 1",
				DATABASE_NAME,
				$objDB->escapeString($strURI)
			);

			if($objDB->query($strSQL) && $objDB->getNumberRows()){
				$arrItems = $objDB->getArray();
				$intOut = $arrItems['id'];
			}
		}

		return $intOut;
	}

	/*
	 * Register the resource server if the twist folder is installed above the document root
	 */
	protected function resourceServer(){

		if(TWIST_ABOVE_DOCUMENT_ROOT){
			$this->controller('/twist/%','Twist\Core\Controllers\Resources',false);
		}
	}

	/**
	 * Serve is used to active the routes system after all routes have been set
	 * @param $blExitOnComplete Exit script once the page has been served
	 * @throws \Exception
	 */
	public function serve($blExitOnComplete = true){

		//Register the resource server if and when required
		$this->resourceServer();

		\Twist::Timer('TwistPageLoad')->log('Routes Prepared');

		$arrRoute = $this->current();
		if (count($arrRoute)) {

			//First of all check for an interface and do that
			if($arrRoute['type'] == 'interface'){
				\Twist::framework()->interfaces()->load($arrRoute['item'], $arrRoute['registered_uri'], $arrRoute['base_view']);
				die();
			}else{

				$arrRestriction = $this->currentRestriction($arrRoute['uri']);

				\Twist::User()->loginURL($arrRestriction['login_uri']);

				if($arrRestriction['login_required']){
					\Twist::User()->setAfterLoginRedirect();
					\Twist::redirect(str_replace('//', '/', $arrRestriction['login_uri']));
				}elseif($arrRestriction['allow_access'] == false){
					\Twist::respond(403);
				}else{

					//Pass all the current route info to the global server array
					$_SERVER['TWIST_ROUTE'] = $arrRoute;
					$_SERVER['TWIST_ROUTE_DYNAMIC'] = $arrRoute['dynamic'];
					$_SERVER['TWIST_ROUTE_PARTS'] = $arrRoute['parts'];
					$_SERVER['TWIST_ROUTE_URI'] = $arrRoute['uri'];
					$_SERVER['TWIST_ROUTE_TITLE'] = \Twist::framework()->setting('SITE_NAME');
					$_SERVER['TWIST_ROUTE_DESCRIPTION'] = \Twist::framework()->setting('SITE_DESCRIPTION');
					$_SERVER['TWIST_ROUTE_AUTHOR'] = \Twist::framework()->setting('SITE_AUTHOR');
					$_SERVER['TWIST_ROUTE_KEYWORDS'] = \Twist::framework()->setting('SITE_KEYWORDS');

					//Load the page from cache
					$this->loadPageCache($arrRoute['cache_key']);

					$arrTags = $arrRoute;
					$arrTags['response'] = '';
					$arrTags['response_item'] = $arrRoute['item'];
					$arrTags['response_type'] = $arrRoute['type'];

					$arrTags['request'] = ($arrRoute['uri'] == '') ? '/' : $arrRoute['uri'];
					$arrTags['request_item'] = ltrim($arrRoute['dynamic'], '/');

					$arrTags['base_uri'] = $this->strBaseURI;
					$arrTags['interface_uri'] = $this->strInterfaceURI;

					$this->framework()->module()->extend('View', 'route', $arrTags);

					switch ($arrRoute['type']) {
						case'view':
							$arrTags['response'] .= $this->resView->build($arrRoute['item'], $arrRoute['data']);
							break;
						case'element':
							$arrTags['response'] .= $this->resView->processElement($arrRoute['item'], $arrRoute['data']);
							break;
						case'controller':
							if (is_array($arrRoute['item']) && count($arrRoute['item']) >= 1) {

								if(!strstr($arrRoute['item'][0],'\\')){
									$strControllerClass = sprintf('\\Twist\\Controllers\\%s', $arrRoute['item'][0]);
									$strControllerFile = sprintf('%s/%s.controller.php',$this->strControllerDirectory,$arrRoute['item'][0]);

									if(file_exists($strControllerFile)){
										require_once sprintf('%s/%s.controller.php',$this->strControllerDirectory,$arrRoute['item'][0]);
									}
								}else{
									$strControllerClass = $arrRoute['item'][0];
								}

								if (count($arrRoute['item']) > 1) {
									$strControllerFunction = $arrRoute['item'][1];
								} elseif(count($arrRoute['vars']) && array_key_exists('function',$arrRoute['vars'])) {
									$strControllerFunction = $arrRoute['vars']['function'];
								} else {
									$strControllerFunction = (count($arrRoute['parts'])) ? $arrRoute['parts'][0] : '_default';
								}

								$objController = new $strControllerClass();

								if (in_array("_extended", get_class_methods($objController))) {

									$arrAliases = $objController->_getAliases();
									$arrReplacements = $objController->_getReplacements();

									$arrControllerFunctions = array();
									foreach (get_class_methods($objController) as $strFunctionName) {
										if (array_key_exists($strFunctionName, $arrReplacements)) {
											$arrControllerFunctions[strtolower($arrReplacements[$strFunctionName])] = $strFunctionName;
										} else {
											$arrControllerFunctions[strtolower($strFunctionName)] = $strFunctionName;
										}
									}

									//Merge in all the registered aliases if any exist
									$arrControllerFunctions = array_merge($arrControllerFunctions, $arrAliases);

									$strRequestMethodFunction = sprintf('%s%s', strtolower($_SERVER['REQUEST_METHOD']), strtolower($strControllerFunction));
									$strControllerFunction = strtolower($strControllerFunction);

									if (array_key_exists($strRequestMethodFunction, $arrControllerFunctions)) {

										$strControllerFunction = $arrControllerFunctions[$strRequestMethodFunction];
										$arrTags['response'] .= $objController->$strControllerFunction();

									} elseif (array_key_exists($strControllerFunction, $arrControllerFunctions)) {

										$strControllerFunction = $arrControllerFunctions[$strControllerFunction];
										$arrTags['response'] .= $objController->$strControllerFunction();

									} else {

										$strControllerFunction = '_fallback';
										$arrTags['response'] .= $objController->$strControllerFunction();
									}
								} else {
									throw new \Exception(sprintf("Controller '%s' must extend BaseController", $strControllerClass));
								}
							} else {
								\Twist::respond(500);
							}
							break;
						case'ajax':
							//Only allow ajax to make these requests
							if (TWIST_AJAX_REQUEST) {
								\Twist::AJAX()->server(
									$arrRoute['data']['functions'],
									$arrRoute['data']['views']
								);
							} else {
								\Twist::respond(405);
							}
							break;
						case'interface':
							//Should never get here -- See at the top of this function call (more efficient)
							break;
						case'redirect-permanent':
							\Twist::redirect($arrRoute['item'], true);
							break;
						case'redirect':
							\Twist::redirect($arrRoute['item']);
							break;
					}

					$arrTags['title'] = $_SERVER['TWIST_ROUTE_TITLE'];
					$arrTags['description'] = $_SERVER['TWIST_ROUTE_DESCRIPTION'];
					$arrTags['author'] = $_SERVER['TWIST_ROUTE_AUTHOR'];
					$arrTags['keywords'] = $_SERVER['TWIST_ROUTE_KEYWORDS'];

					$this->framework()->module()->extend('View', 'route', $arrTags);

					if (!is_null($this->strBaseView) && $arrRoute['base_view'] === true) {

						$strPageOut = $this->resView->build($this->strBaseView, $arrRoute['data']);
					} elseif (!is_bool($arrRoute['base_view'])) {

						$strCustomView = sprintf('%s/%s', $this->resView->getDirectory(), $arrRoute['base_view']);
						if (file_exists($strCustomView)) {
							$strPageOut = $this->resView->build($arrRoute['base_view'], $arrRoute['data']);
						} else {
							throw new \Exception(sprintf("The custom base view (%s) for the route %s '%s' does not exist", $arrRoute['base_view'], $arrRoute['type'], $arrRoute['uri']));
						}
					} else {
						$strPageOut = $arrTags['response'];
					}

					//Cache the page if cache is enabled for this route
					if($arrRoute['cache'] == true && $arrRoute['cache_life'] > 0) {
						$this->storePageCache($arrRoute['cache_key'], $strPageOut, $arrRoute['cache_life']);
					}

					//Output the Debug window to the screen when in debug mode
					if($this->blDebugMode){
						if(strstr($strPageOut,'</body>')){
							$strPageOut = str_replace('</body>',\Twist::framework()->debug()->window($arrRoute).'</body>',$strPageOut);
						}else{
							$strPageOut .= \Twist::framework()->debug()->window($arrRoute);
						}
					}

					//Output the page
					echo $strPageOut;

					//Exit the script, no further processing will be done
					if($blExitOnComplete){
						exit;
					}
				}
			}

		} elseif ($this->bl404) {
			\Twist::respond(404);
		}
	}
}