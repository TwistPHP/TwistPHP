<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
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

namespace Twist\Core\Utilities;
use \Twist\Core\Controllers\BaseAJAX;
use \Twist\Core\Models\Route\Meta;
use \Twist\Classes\Instance;

/**
 * Simply setup a website with multiple pages in minutes. Create restricted areas with login pages and dynamic sections with wild carded URI's.
 * Just a couple lines of code and you will be up and running.Maintenance
 */
class Route extends Base{

	protected $bl404 = true;

	protected $strMainDomain = null;
	protected $arrAliasDomains = array();
	protected $blDisabled = false;

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
	protected $dirBaseViewDir = null;
	protected $blIgnoreBaseView = false;
	protected $blForceBaseView = false;
	protected $arrBypassMaintenanceMode = array();
	protected $strBaseURI = null;
	protected $strPackageURI = null;
	protected $strPageTitle = '';
	protected $intCacheTime = 3600;
	protected $blDebugMode = false;
	protected $resView = null;
	protected $strMetaInstanceKey = 'twist_route_meta';
	protected $strModelInstanceKey = 'twist_route_model';
	protected $strControllerDirectory = null;

	/**
	 * Start up an instance of Routes, pass in the main domain name for the instance. Routes registered to this instance will only be served if the domain or IP matches those entered as an alias or main domain.
	 * If null is passed in these routes will be served only if a domain/alias match has not been found.
	 * @param null|string $strMainListener Domain or IP address to listen for
	 */
	public function __construct($strMainListener = null){

		$this->strMainDomain = $strMainListener;
		$this->resView = \Twist::View();
		Instance::storeObject($this->strMetaInstanceKey,new Meta());

		$this->blDebugMode = (\Twist::framework()->setting('DEVELOPMENT_MODE') && \Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR'));

		$strControllerPath = TWIST_APP_CONTROLLERS;
		if(file_exists($strControllerPath)){
			$this->setControllerDirectory($strControllerPath);
		}
	}

	/**
	 * Add an alias domain or IP address to listen on, routes registered to this instance will only be served if the domain or IP matches those entered as an alias or main domain.
	 * @param string $strAliasListener Domain or IP address to listen for
	 */
	public function aliasDomain($strAliasListener){
		$this->arrAliasDomains[] = $strAliasListener;
	}

	/**
	 * Disable this instance of routes from being served, this can be used as a temporary measure if you need to stop a particular domain from being served.
	 */
	public function disable(){
		$this->blDisabled = true;
	}

	/**
	 * Get the details of which domains/IPs this route instance is listening for
	 * @return array Passed back in the array are domain, aliases, enabled
	 */
	public function listeners(){
		return array(
			'domain' => $this->strMainDomain,
			'aliases' => $this->arrAliasDomains,
			'enabled' => ($this->blDisabled) ? false : true
		);
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
			$this->strBaseView = (substr($dirViewFile,0,1) == '/') ? $dirViewFile : sprintf('%s/%s',rtrim($this->resView->getDirectory(),'/'),$dirViewFile);
			$this->dirBaseViewDir = dirname($this->strBaseView);
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
	 * Set/Get the package URI, used only when creating or working with an framework interface
	 * @param $strInterface
	 */
	public function packageURI($strPackage = null){

		if(!is_null($strPackage)){
            $strPath = sprintf('%s/%s',rtrim(TWIST_PACKAGES,'/'),ltrim($strPackage,'/'));
            $this->strPackageURI = '/'.ltrim(rtrim(str_replace(TWIST_DOCUMENT_ROOT,"",$strPath),'/'),'/');
		}

		return $this->strPackageURI;
	}

	/**
	 * Disable the use of the base view for this page (can be called during the processing of the page)
	 */
	public function baseViewIgnore(){
		$this->blIgnoreBaseView = true;
	}

	/**
	 * Force the base view set in routes to over-ride any route specific base view
	 * This function is called by baseController's _baseView() to ensure a custom base view is used nomatter what
	 */
	public function baseViewForce(){
		$this->blForceBaseView = true;
	}

	/**
	 * Enable debug mode, this will override the debug/development settings in Twist Settings
	 */
	public function debugMode($blEnabled = true){
		$this->blDebugMode = $blEnabled;
	}

	/**
	 * Allow the route to bypass maintenance mode when maintenance mode is enabled
	 * @param $strURI URI of the route to allow
	 */
	public function bypassMaintenanceMode($strURI){

		$blWildCard = (strstr($strURI,'%'));
		$strURI = rtrim(str_replace('%','',$strURI),'/').'/';

		if(!array_key_exists($strURI,$this->arrBypassMaintenanceMode)){
			$this->arrBypassMaintenanceMode[$strURI] = $blWildCard;
		}
	}

	/**
	 * @return null|\Twist\Core\Models\Route\Meta
	 */
	public function meta(){
		return (Instance::isObject($this->strMetaInstanceKey)) ? Instance::retrieveObject($this->strMetaInstanceKey) : null;
	}

	/**
	 * @return null|Object Returns an object of the registered route model, registered in the URI using {model:\App\Models\User}
	 */
	public function model(){
		return (Instance::isObject($this->strModelInstanceKey)) ? Instance::retrieveObject($this->strModelInstanceKey) : null;
	}

	/**
	 * Get all the registered routes and return as an array
	 */
	public function getAll(){

		return array(
			'ANY' => $this->arrRoutes,
			'GET' => $this->arrRoutesGET,
			'PUT' => $this->arrRoutesPUT,
			'POST' => $this->arrRoutesPOST,
			'DELETE' => $this->arrRoutesDELETE
		);
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
	 * Get an array of restricted routes and the restriction overrides (these are found in the unrestricted sub array)
	 * @return array An array with tow sub arrays 'restrcited' and 'unrestricted'
	 */
	public function getRestrictions(){
		return array(
			'restricted' => $this->arrRestrict,
			'unrestricted' => $this->arrUnrestricted
		);
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

		if(!\Twist::framework()->setting('ROUTE_CASE_SENSITIVE')){
			$strURI = strtolower($strURI);
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

		if(!\Twist::framework()->setting('ROUTE_CASE_SENSITIVE')){
			$strURI = strtolower($strURI);
		}

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
	 * Serve a file form a particular route, you can change the name of the file upon download and restrict the download bandwidth (very helpful if you have limited bandwidth)
	 *
	 * @param $strURI
	 * @param $dirFilePath Full path to the file that will be served
	 * @param null $strServeName Name of the file to be served
	 * @param null $intLimitDownloadSpeed Download speed for the end user in KB
	 */
	public function file($strURI,$dirFilePath,$strServeName = null,$intLimitDownloadSpeed = null){
		$this->addRoute($strURI,'file',array('file' => $dirFilePath, 'name' => $strServeName, 'speed' => $intLimitDownloadSpeed),false,false,array());
	}

	/**
	 * Serve all contents of a folder on a virtual route, the folder begin served dose not need to be publicly accessible,
	 * also restriction can be applied to the virtual route if user login is required to access.
	 *
	 * @param $strURI
	 * @param $dirFolderPath Full path to the folder to be served
	 * @param bool $blForceDownload Force the file to be downloaded to be browser
	 * @param null $intLimitDownloadSpeed Download speed for the end user in KB
	 */
	public function folder($strURI,$dirFolderPath,$blForceDownload = false,$intLimitDownloadSpeed = null){
		$this->addRoute($strURI,'folder',array('folder' => $dirFolderPath,'force-download' => $blForceDownload,'speed' => $intLimitDownloadSpeed),false,false,array());
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
	 * @param null $mxdController
	 */
	public function ajax($strURI,$mxdController){
		$arrController = (is_array($mxdController)) ? $mxdController : array($mxdController);
		$this->addRoute($strURI,'ajax',$arrController,false,false,array());
	}

	/**
	 * Add a pre-defined package route (interface) (provided by installed packages) that will be called upon a any request (HTTP METHOD) to the given URI.
	 * The URI can be made dynamic by adding a '%' symbol at the end.
	 *
	 * @param $strURI
	 * @param $strPackage
	 * @param bool $mxdBaseView
	 * @param bool $mxdCache
	 * @param array $arrData
	 */
	public function package($strURI,$strPackage,$mxdBaseView = true,$mxdCache = false,$arrData = array()){
		$this->addRoute($strURI,'package',$strPackage,$mxdBaseView,$mxdCache,$arrData);
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
	 * Pass in a PHP function to be parsed by the route
	 * @param $strURI
	 * @param $resFunction
	 * @param bool $mxdBaseView
	 */
	public function any($strURI,$resFunction,$mxdBaseView = true){
		$this->addRoute($strURI,'function',$resFunction,$mxdBaseView);
	}

	/**
	 * Pass in a PHP function to be parsed by the route upon a GET request
	 * @param $strURI
	 * @param $resFunction
	 * @param bool $mxdBaseView
	 */
	public function get($strURI,$resFunction,$mxdBaseView = true){
		$this->addRoute($strURI,'function',$resFunction,$mxdBaseView,false,array(),'GET');
	}

	/**
	 * Pass in a PHP function to be parsed by the route upon a POST request
	 * @param $strURI
	 * @param $resFunction
	 * @param bool $mxdBaseView
	 */
	public function post($strURI,$resFunction,$mxdBaseView = true){
		$this->addRoute($strURI,'function',$resFunction,$mxdBaseView,false,array(),'POST');
	}

	/**
	 * Pass in a PHP function to be parsed by the route upon a PUT request
	 * @param $strURI
	 * @param $resFunction
	 * @param bool $mxdBaseView
	 */
	public function put($strURI,$resFunction,$mxdBaseView = true){
		$this->addRoute($strURI,'function',$resFunction,$mxdBaseView,false,array(),'PUT');
	}

	/**
	 * Pass in a PHP function to be parsed by the route upon a DELETE request
	 * @param $strURI
	 * @param $resFunction
	 * @param bool $mxdBaseView
	 */
	public function delete($strURI,$resFunction,$mxdBaseView = true){
		$this->addRoute($strURI,'function',$resFunction,$mxdBaseView,false,array(),'DELETE');
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
		if(substr($strURI,-1) == '%' || $strType == 'package'){
			$blWildCard = true;
			$strURI = str_replace('%','',$strURI);
		}

		$strTrailingSlash = (\Twist::framework()->setting('SITE_TAILING_SLASH')) ? '/' : '';
		$strURI = rtrim($strURI,'/').$strTrailingSlash;

		$regxMatchURI = $strModel = null;
		if(strstr($strURI,'/{') && strstr($strURI,'}')){

			$regxMatchURI = $strURI;

			/**
			 * Turn the URI '/my/{model:App\Models\User}/uri'
			 * Into the Regx '#^(?<twist_uri>\/my\/(?<tmodel_user>[^\/]+)\/uri)#i'
			 * If Wildcard '#^(?<twist_uri>\/my\/(?<tmodel_user>[^\/]+)\/uri)(?<twist_wildcard>.*)#i'
			 */
			if(preg_match("/\{model\:([a-z0-9\_\\\]+)\}/i", $strURI, $arrModelDetails)){
				$strModel = $arrModelDetails[1];
				$arrParts = explode("\\",$strModel);
				$regxMatchURI = str_replace($arrModelDetails[0],sprintf("(?<tmodel_%s>[^\/]+)",array_pop($arrParts)),$regxMatchURI);
			}

			/**
			 * Turn the URI '/my/{page}/uri'
			 * Into the Regx '#^(?<twist_uri>\/my\/(?<tparam_page>[^\/]+)\/uri)#i'
			 * If Wildcard '#^(?<twist_uri>\/my\/(?<tparam_page>[^\/]+)\/uri)(?<twist_wildcard>.*)#i'
			 */
			$regxMatchURI = sprintf("#^(?<twist_uri>%s)%s#%s",
				str_replace(array("/","{","}"),array("\\/","(?<tparam_",">[^\/]+)"),$regxMatchURI),
				($blWildCard) ? '(?<twist_wildcard>.*)' : '$',
				\Twist::framework()->setting('ROUTE_CASE_SENSITIVE') ? '' : 'i'
			);
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
			'model' => $strModel,
			'base_view' => $mxdBaseView,
			'wildcard' => $blWildCard,
			'cache' => ($mxdCache === false) ? false : true,
			'cache_key' => null,
			'cache_life' => ($mxdCache === true) ? $this->intCacheTime : ($mxdCache !== false) ? $mxdCache : 0
		);

		if(!\Twist::framework()->setting('ROUTE_CASE_SENSITIVE')){
			$strURI = strtolower($strURI);
		}

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

			$strFullBaseURI = sprintf('%s%s',rtrim(SITE_URI_REWRITE,'/'),$this->baseURI());

			foreach($arrRoutesDataRef as $strURI => $arrEachRoute){

				$arrEachRoute['registered_uri'] = sprintf("%s%s",$strFullBaseURI,str_replace('//','/',$strURI));
				$arrEachRoute['base_uri'] = $strFullBaseURI;
				$arrEachRoute['base_url'] = sprintf("%s://%s%s",\Twist::framework()->setting('SITE_PROTOCOL'),\Twist::framework()->setting('SITE_HOST'),$strFullBaseURI);
				$arrEachRoute['url'] = sprintf("%s://%s%s%s",\Twist::framework()->setting('SITE_PROTOCOL'),\Twist::framework()->setting('SITE_HOST'),$strFullBaseURI,str_replace('//','/',$strURI));
				$arrEachRoute['cache_key'] = str_replace('/','+',trim(sprintf("%s%s",$strFullBaseURI,str_replace('//','/',$strURI)),'/'));

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
		$strCacheData = \Twist::Cache('twist/utility/route')->read($strPageCacheKey,true);
		$intCreatedTime = \Twist::Cache('twist/utility/route')->created($strPageCacheKey);
		$intExpiryTime = \Twist::Cache('twist/utility/route')->expiry($strPageCacheKey);

		if(!is_null($strCacheData)){

			$mxdModifiedTime = gmdate('D, d M Y H:i:s ', $intCreatedTime) . 'GMT';
			$strETag = sha1($strPageCacheKey . $mxdModifiedTime);

			$blModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
			$blNoneMatch = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;

			if($blNoneMatch === $strETag || $blModifiedSince === $mxdModifiedTime){
				header('HTTP/1.1 304 Not Modified');
				die();
			}elseif($blModifiedSince === false && $blNoneMatch === false){

				header("Cache-Control: max-age=".($intExpiryTime-$intCreatedTime));
				header("Last-Modified: $mxdModifiedTime");
				header("ETag: \"{$strETag}\"");

				//Enable GZip compression output, only when no other data has been output to the screen
				$arrOBStatus = ob_get_status();
				if($arrOBStatus['buffer_used'] == 0 && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false){
					ob_start('ob_gzhandler');
				}

				//Output the cached page here
				echo $strCacheData;
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

		\Twist::Cache('twist/utility/route')->write($strPageCacheKey,$strPageData,$intCacheTime);
		$intCreatedTime = \Twist::Cache('twist/utility/route')->created($strPageCacheKey);

		$mxdModifiedTime = gmdate('D, d M Y H:i:s ', $intCreatedTime) . 'GMT';
		$strEtag = sha1($strPageCacheKey . $mxdModifiedTime);

		header("Cache-Control: max-age=".$intCacheTime);
		header("Last-Modified: $mxdModifiedTime");
		header("ETag: \"{$strEtag}\"");
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
	public function current($strReturnKey = null){

		$strModelValue = null;
		$arrOut = $arrUriParameters = array();
		$arrMethodRoutes = $this->currentMethodRoutes();

		if(count($arrMethodRoutes) || count($this->arrRoutes)){

			$blCaseSensitive = \Twist::framework()->setting('ROUTE_CASE_SENSITIVE');

			$arrPartsURI = explode('?',$_SERVER['REQUEST_URI']);
			$arrPartsURI[0] = (!$blCaseSensitive) ? strtolower($arrPartsURI[0]) : $arrPartsURI[0];
			$strPageCacheKey = str_replace('/','+',trim($arrPartsURI[0],'/'));

			$arrPartsURI[0] = (!in_array($this->strBaseURI,array(null,'/'))) ? str_replace($this->strBaseURI,'',$arrPartsURI[0]) : $arrPartsURI[0];

			$strTrailingSlash = (\Twist::framework()->setting('SITE_TAILING_SLASH')) ? '/' : '';

			//Get the current URI to be used, added a URI key as teh regx version has 2 different variations a real URI and a param uri (the key)
			$strCurrentURI = $strCurrentURIKey = rtrim( str_replace(rtrim(SITE_URI_REWRITE,'/'),'',$arrPartsURI[0]), '/').$strTrailingSlash;

			$strRouteDynamic = '';
			$arrRouteParts = array();
			$blMatched = false;

			//Lower case the URI key that is used to match the URI (Only when running in insensitive mode)
			if(!$blCaseSensitive) {
				$strCurrentURIKey = strtolower($strCurrentURIKey);
				$strCurrentURI = strtolower($strCurrentURI);
			}

			//Only go into these wildcard and regx matches is there is not exact match found
			if(!array_key_exists($strCurrentURIKey,$arrMethodRoutes) && !array_key_exists($strCurrentURIKey,$this->arrRoutes)){

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
							if(strstr($strKey,'tparam_')){
								$arrUriParameters[str_replace('tparam_','',$strKey)] = $strValue;
							}

							if(strstr($strKey,'tmodel_')){
								$strModelValue = $strValue;
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

				$arrOut['title'] = \Twist::framework()->setting('SITE_NAME');
				$arrOut['uri'] = sprintf('%s/%s',rtrim($strCurrentURI,'/'),ltrim($strRouteDynamic,'/'));
				$arrOut['vars'] = $arrUriParameters;
				$arrOut['dynamic'] = $strRouteDynamic;
				$arrOut['parts'] = $arrRouteParts;

				//Create an instance of the model passing in the URL value
				if(!is_null($strModelValue)){
					$strModelClass = '\\'.ltrim($arrOut['model'],'\\');
					Instance::storeObject($this->strModelInstanceKey,new $strModelClass($strModelValue));
				}

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

		return ( !is_null( $strReturnKey ) && array_key_exists( $strReturnKey, $arrOut ) ) ? $arrOut[$strReturnKey] : $arrOut;
	}

	public function currentRestriction($strCurrentURI){

		$blRestrict = false;
		$arrFoundMatched = $arrMatch = array();

		$strCurrentURI = ($this->strBaseURI == '/') ? $strCurrentURI : str_replace($this->strBaseURI,'',$strCurrentURI);
		$strFullLoginURI = str_replace('//','/',sprintf('%s/login',$this->strBaseURI));

		//Lower case the URI key that is used to match the URI (Only when running in insensitive mode)
		if(!\Twist::framework()->setting('ROUTE_CASE_SENSITIVE')){
			$strCurrentURI = strtolower($strCurrentURI);
		}

		if(\Twist::Database()->checkSettings()){

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

				//Check all the unrestricted pages for exact matches and wildcards
				if($this->findURI($this->arrUnrestricted,$strCurrentURI)){

					$arrMatch = array(
						'login_required' => false,
						'allow_access' => true,
						'login_uri' => $strFullLoginURI,
						'status' => 'Ignored, unrestricted page'
					);

				}else{
					//If no unrestricted matches are found then continue
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

	/**
	 * Register the upload server, this will automatically call the Twist Upload Controller unless an override has been specified
	 * @param string $strURI
	 * @param null $strControllerOverride
	 */
	public function upload($strURI = '/upload/%',$strControllerOverride = null){
		\Twist::define('UPLOAD_ROUTE_URI',$strURI);
		$this->controller($strURI,(is_null($strControllerOverride)) ? 'Twist\Core\Controllers\Upload' : $strControllerOverride,false);
	}

	/**
	 * Register the framework manager on a URI so that it can be accessed
	 * @param string $strURI
	 */
	public function manager($strURI = '/manager/%'){
		\Twist::define('MANAGER_ROUTE_URI',$strURI);
		$this->package($strURI,'Twist\Core\Routes\Manager');
	}

	/**
	 * Register the image placholder server, this will automatically call the Twist Placeholder Controller unless an override has been specified
	 * @param string $strURI
	 * @param null $strControllerOverride
	 */
	public function placeholder($strURI = '/placeholder/%',$strControllerOverride = null){
		\Twist::define('PLACEHOLDER_ROUTE_URI',$strURI);
		$this->controller($strURI,(is_null($strControllerOverride)) ? 'Twist\Core\Controllers\Placeholder' : $strControllerOverride,false);
	}

	/*
	 * Register the resource server if the twist folder is installed above the document root
	 */
	protected function resourceServer(){

		if(TWIST_ABOVE_DOCUMENT_ROOT){
			$this->folder('/twist/Core/Resources%',sprintf('%s/Core/Resources',TWIST_FRAMEWORK));
		}
	}

	public function processController($arrRoute){

		$strOut = '';

		if(is_array($arrRoute['item']) && count($arrRoute['item']) >= 1){

			if(!strstr($arrRoute['item'][0],'\\')){
				$strControllerClass = sprintf('\\App\\Controllers\\%s', $arrRoute['item'][0]);
				$strControllerFile = sprintf('%s/%s.controller.php',$this->strControllerDirectory,$arrRoute['item'][0]);

				if(file_exists($strControllerFile)){
					require_once sprintf('%s/%s.controller.php',$this->strControllerDirectory,$arrRoute['item'][0]);
				}
			}else{
				$strControllerClass = $arrRoute['item'][0];
			}

			if(count($arrRoute['item']) > 1){
				$strControllerFunction = $arrRoute['item'][1];
			}elseif(count($arrRoute['vars']) && array_key_exists('function',$arrRoute['vars'])){
				$strControllerFunction = $arrRoute['vars']['function'];
			}else{
				$strControllerFunction = (count($arrRoute['parts'])) ? $arrRoute['parts'][0] : '_index';
			}

			$objController = new $strControllerClass();

			if(in_array("_extended", get_class_methods($objController))){

				//Register the route and route data
				$objController->_extended($this,$arrRoute);

				$arrAliases = $objController->_getAliases();
				$arrReplacements = $objController->_getReplacements();

				$blCaseSensitive = \Twist::framework()->setting('ROUTE_CASE_SENSITIVE');

				$arrControllerFunctions = array();
				foreach(get_class_methods($objController) as $strFunctionName){
					if(array_key_exists($strFunctionName, $arrReplacements)){
						$arrControllerFunctions[($blCaseSensitive) ? $arrReplacements[$strFunctionName] : strtolower($arrReplacements[$strFunctionName])] = $strFunctionName;
					}else{
						$arrControllerFunctions[($blCaseSensitive) ? $strFunctionName : strtolower($strFunctionName)] = $strFunctionName;
					}
				}

				//Correct the case of the function to match
				$strControllerFunction = ($blCaseSensitive) ? $strControllerFunction : strtolower($strControllerFunction);

				//Lower the case of all aliases if we are in case insensitive mode
				if(!$blCaseSensitive){
					$arrAliases = array_change_key_case($arrAliases, CASE_LOWER);
				}

				//Update the request if an alias has been registered
				if(array_key_exists($strControllerFunction,$arrAliases)){
					$strControllerFunction = ($blCaseSensitive) ? $arrAliases[$strControllerFunction] : strtolower($arrAliases[$strControllerFunction]);
				}

				//Create a method function key as well in the correct case
				$strRequestMethodFunction = (substr($strControllerFunction,0,1) == '_') ? sprintf('_%s%s', strtoupper($_SERVER['REQUEST_METHOD']), ltrim($strControllerFunction,'_')) : sprintf('%s%s', strtoupper($_SERVER['REQUEST_METHOD']), $strControllerFunction);

				//Lower the case of all aliases if we are in case insensitive mode
				if(!$blCaseSensitive){
					$strRequestMethodFunction = strtolower($strRequestMethodFunction);
				}

				if(array_key_exists($strRequestMethodFunction, $arrControllerFunctions)){

					$strControllerFunction = $arrControllerFunctions[$strRequestMethodFunction];
					$strOut = $objController->$strControllerFunction();

				}elseif(array_key_exists($strControllerFunction, $arrControllerFunctions)){

					$strControllerFunction = $arrControllerFunctions[$strControllerFunction];
					$strOut = $objController->$strControllerFunction();
				}else{

					//Check for method fallback before calling the standard fallback
					$strRequestMethodFunction = sprintf('_%sfallback', strtoupper($_SERVER['REQUEST_METHOD']));
					$strControllerFunction = '_fallback';

					if(array_key_exists($strRequestMethodFunction, $arrControllerFunctions)){
						$strControllerFunction = $arrControllerFunctions[$strRequestMethodFunction];
						$strOut = $objController->$strControllerFunction();
					}else{
						$strOut = $objController->$strControllerFunction();
					}
				}
			}else{
				throw new \Exception(sprintf("Controller '%s' must extend BaseController", $strControllerClass));
			}
		}else{
			\Twist::respond(500);
		}

		return $strOut;
	}

	/**
	 * Check to see if a URI is present in an array od URIs, the key must be the URI and the value can either be 1 (denotes wildcard check enabled) or 0 (no wildcard check)
	 * @param $arrURIs
	 * @param $strCurrentURI
	 * @return bool
	 */
	protected function findURI($arrURIs,$strCurrentURI){

		$blMatchFound = false;

		//Check all the URIs for an exact matches, wildcard checks will be done if value set to 1s
		if(count($arrURIs)){
			foreach($arrURIs as $strEachURI => $blWildCard){

				$strUriExpression = sprintf("#^(%s[\/]?)%s#%s", str_replace('/','\/',rtrim($strEachURI, '/')), $blWildCard ? '' : '$',(\Twist::framework()->setting('ROUTE_CASE_SENSITIVE')) ? '' : 'i');
				if(rtrim($strCurrentURI,'/') == rtrim($strEachURI,'/')  || ($blWildCard && preg_match($strUriExpression, $strCurrentURI, $arrMatches))){
					$blMatchFound = true;
					break;
				}
			}
		}

		return $blMatchFound;
	}

	/**
	 * Serve is used to active the routes system after all routes have been set
	 * @param $blExitOnComplete Exit script once the page has been served
	 * @throws \Exception
	 */
	public function serve($blExitOnComplete = true){

		//Register the resource server if and when required
		$this->resourceServer();
		\Twist::recordEvent('Routes prepared');

		$arrRoute = $this->current();

		if(count($arrRoute)){
						
			$arrRoute['request_method'] = strtoupper($_SERVER['REQUEST_METHOD']);

			//First of all check for a package interface and do that
			if($arrRoute['type'] == 'package'){
				\Twist::framework()->package()->route($arrRoute['item'], $arrRoute['registered_uri'], $arrRoute['base_view']);
				die();
			}else{

				//Maintenance mode is automatically bypassed by root level users
				if(\Twist::framework()->setting('MAINTENANCE_MODE') && (is_null(\Twist::User()->currentLevel()) || \Twist::User()->currentLevel() > 0)){

					//Check to see if the current route is allowed to bypass
					if($this->findURI($this->arrBypassMaintenanceMode,$arrRoute['uri']) === false){
						\Twist::respond(503,'The site is currently undergoing maintenance, please check back shortly!');
					}
				}

				//Detect if there are any restrctons, if none then skip this step
				if(count($this->arrRestrict)){
					$arrRestriction = $this->currentRestriction($arrRoute['uri']);
				}else{
					$arrRestriction = array('login_required' => false,'allow_access' => true);
				}

				if($arrRestriction['login_required']){
					\Twist::User()->setAfterLoginRedirect();
					\Twist::redirect(str_replace('//', '/', $arrRestriction['login_uri']));
				}elseif($arrRestriction['allow_access'] == false){
					\Twist::respond(403);
				}else{

					$this->meta()->title(\Twist::framework()->setting('SITE_NAME'));
					$this->meta()->description(\Twist::framework()->setting('SITE_DESCRIPTION'));
					$this->meta()->author(\Twist::framework()->setting('SITE_AUTHOR'));
					$this->meta()->keywords(\Twist::framework()->setting('SITE_KEYWORDS'));

					//Load the page from cache
					$this->loadPageCache($arrRoute['cache_key']);

					$arrTags = $arrRoute;
					$arrTags['response'] = '';
					$arrTags['response_item'] = $arrRoute['item'];
					$arrTags['response_type'] = $arrRoute['type'];

					$arrTags['request'] = ($arrRoute['uri'] == '') ? '/' : $arrRoute['uri'];
					$arrTags['request_item'] = ltrim($arrRoute['dynamic'], '/');

					$arrTags['base_uri'] = rtrim(SITE_URI_REWRITE,'/').$this->strBaseURI;
					$arrTags['package_uri'] = $this->strPackageURI;
					$arrTags['request_uri'] = sprintf('%s%s',$this->strBaseURI, $arrRoute['uri']);

					$arrTags['query_string'] = http_build_query( $_GET );

					\Twist::framework()->hooks()->register('TWIST_VIEW_TAG', 'meta', $this->meta()->getTags());
					\Twist::framework()->hooks()->register('TWIST_VIEW_TAG', 'route', $arrTags);

					\Twist::recordEvent('Route found');

					//Run through all the serve types, this has been made into a separate function
					//So that it can be extended by other systems
					$arrTags = $this->serveTypes($arrRoute,$arrTags);

					\Twist::recordEvent('Route processed');

					if($arrRoute['type'] == 'ajax'){
						header( 'Cache-Control: no-cache, must-revalidate' );
						header( 'Expires: Wed, 24 Sep 1986 14:20:00 GMT' );
						header( 'Content-type: application/json' );
						header( sprintf( 'Content-length: %d', function_exists('mb_strlen') ? mb_strlen( $arrTags['response'] ) : strlen( $arrTags['response'] ) ) );

						echo $arrTags['response'];
					}else{
						//Update the Meta and Route tags to be used in the base template
						\Twist::framework()->hooks()->register('TWIST_VIEW_TAG', 'meta', $this->meta()->getTags());
						\Twist::framework()->hooks()->register('TWIST_VIEW_TAG', 'route', $arrTags);

						if($this->blIgnoreBaseView){
							$strPageOut = $arrTags['response'];
						}elseif(!is_null($this->strBaseView) && ($this->blForceBaseView || $arrRoute['base_view'] === true)){
							//Set the directory back to the original base as we may be in a package interface using the original site base
							$this->resView->setDirectory($this->dirBaseViewDir);
							$strPageOut = $this->resView->build($this->strBaseView, $arrRoute['data']);
						}elseif(!is_null($arrRoute['base_view']) && !is_bool($arrRoute['base_view'])){

							$strCustomView = sprintf('%s/%s', $this->resView->getDirectory(), $arrRoute['base_view']);
							if(file_exists($strCustomView)){
								$strPageOut = $this->resView->build($arrRoute['base_view'], $arrRoute['data']);
							}else{
								throw new \Exception(sprintf("The custom base view (%s) for the route %s '%s' does not exist", $arrRoute['base_view'], $arrRoute['type'], $arrRoute['uri']));
							}
						}else{
							$strPageOut = $arrTags['response'];
						}

						\Twist::recordEvent('Route base processed');

						//Cache the page if cache is enabled for this route
						if($arrRoute['cache'] == true && $arrRoute['cache_life'] > 0){
							$this->storePageCache($arrRoute['cache_key'], $strPageOut, $arrRoute['cache_life']);
							\Twist::recordEvent('Route cache stored');
						}

						//Output the Debug window to the screen when in debug mode (Do not output when its an ajax request)
						if($this->blDebugMode && !(TWIST_AJAX_REQUEST || $arrRoute['type'] == 'ajax')){
							if(strstr($strPageOut, '</body>')) {
								$strPageOut = str_replace( '</body>', \Twist::framework()->debug()->window( $arrRoute ) . '</body>', $strPageOut );
							}else{
								$strPageOut .= \Twist::framework()->debug()->window($arrRoute);
							}
						}

						//Enable GZip compression output, only when no other data has been output to the screen
						$arrOBStatus = ob_get_status();
						if(\Twist::framework()->setting('GZIP_COMPRESSION') && $arrOBStatus['buffer_used'] == 0 && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false){

							//Not very elegant but get the current buffer contents and clear the buffer
							$strOBContents = ob_get_contents();
							ob_end_clean();

							ini_set('zlib.output_compression_level', \Twist::framework()->setting('GZIP_COMPRESSION_LEVEL'));
							ob_start('ob_gzhandler');

							//Re-output the buffer where all the contents can be GZIP compressed
							if($strOBContents != ''){
								echo $strOBContents;
							}
						}

						//Output the page
						echo $strPageOut;
					}

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

	protected function serveTypes($arrRoute,$arrTags){

		switch($arrRoute['type']){
			case'view':
				$arrTags['response'] = $this->resView->build($arrRoute['item'], $arrRoute['data']);
				break;
			case'file':
				\Twist::File()->serve($arrRoute['item']['file'],$arrRoute['item']['name'],null,null,$arrRoute['item']['speed'],false);
				break;
			case'folder':

				$strFilePath = sprintf('%s/%s',$arrRoute['item']['folder'],$arrRoute['dynamic']);

				if(file_exists($strFilePath)){

					//For security do not allow PHP files to be served.
					if(substr($arrRoute['dynamic'],'-3') !== 'php') {

						$strMimeType = ($arrRoute['item']['force-download']) ? null : \Twist::File()->mimeType($strFilePath);
						\Twist::File()->serve($strFilePath, basename($strFilePath), $strMimeType, null, $arrRoute['item']['speed'], false);
					}else{
						\Twist::respond(403,'Unsupported file extension, PHP files are disallowed through this method');
					}
				}else{
					\Twist::respond(404);
				}

				break;
			case'function':
				$arrTags['response'] = $arrRoute['item']();
				break;
			case'ajax':
				if(!TWIST_AJAX_REQUEST){
					\Twist::respond(403,'Unsupported HTTP protocol used to request this URI');
				}else{
					try{
						$arrTags['response'] = $this->processController($arrRoute);
					}catch(\Exception $resException){
						//Response with the relevant error message
						$resControllerAJAX = new BaseAJAX();

						$resControllerAJAX->_ajaxFail();
						$resControllerAJAX->_ajaxMessage($resException->getMessage());

						$arrTags['response'] = $resControllerAJAX->_ajaxRespond();
					}
				}
				break;
			case'controller':
				$arrTags['response'] = $this->processController($arrRoute);
				break;
			case'package':
				//Should never get here -- See at the top of this function call (more efficient)
				break;
			case'redirect-permanent':
				\Twist::redirect($arrRoute['item'], true);
				break;
			case'redirect':
				\Twist::redirect($arrRoute['item']);
				break;
		}

		return $arrTags;
	}
}