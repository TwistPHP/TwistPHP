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

	namespace Twist\Core\Controllers;
	use Twist\Core\Models\Route\Meta;
	use Twist\Classes\Error;

	/**
	 * Base Controller should be used as an extension to every route controller class that is made with the exception of those controllers that use BaseControllerAJAX and BaseControllerUser.
	 * @package Twist\Core\Classes
	 */
	class Base{

		protected $arrMessages = array();
		protected $arrAliasURIs = array();
		protected $arrReplaceURIs = array();
		protected $arrRoute = array();

        /**
         * @var \Twist\Core\Utilities\Route
         */
		protected $resRoute = null;

        /**
         * A function that is called by Routes both to ensure that the controller has been extended and so that we can pass in resources and information required by the controller.
         *
         * @param \Twist\Core\Utilities\Route $resRoute
         * @param $arrRouteData
         * @return bool
         */
		final public function _extended($resRoute,$arrRouteData){

			//Can be used to modify the baseView etc
			$this->resRoute = $resRoute;

			//Store the route data for use later
			$this->arrRoute = $arrRouteData;

			$this->_baseCalls();

			return true;
		}

		/**
		 * This function is called by _extended, replace this function putting calls such as alias and replaces
		 * and is only needed if creating a new expendable Base Controller such as baseUser and baseAJAX.
		 */
		protected function _baseCalls(){
			//Leave empty - this is to be extended only!
		}

		/**
		 * Default response from any controller, this function can be replaced in a controller to do what is required.
		 * The default response is a 404 page.
		 *
		 * @return bool
		 */
		public function _default(){
			return $this->_404();
		}

		/**
		 * The main response of the controller, treat this function as though it where an index.php file.
		 * As default the responses returned is that of _default.
		 *
		 * @return bool
		 */
		public function _index(){
			return $this->_default();
		}

		/**
		 * This is that function that will be called in the even that Routes was unable to find a exact controller response.
		 *
		 * @return bool
		 */
		public function _fallback(){
			return $this->_404();
		}

        /**
         * Over-ride the base view for the current page only.
         *
         * @return null|string
         */
		public function _baseView($mxdBaseView = null){

            if(!is_null($mxdBaseView)){
                $this->resRoute->baseViewForce();
            }

			return $this->resRoute->baseView($mxdBaseView);
		}

        /**
         * Ignore the base view for the current page only.
         */
		public function _baseViewIgnore(){
			$this->resRoute->baseViewIgnore();
		}

        /**
         * Set the timeout for the current page (Some pages may have allot to do so this can be done per page).
         *
         * @param int $intTimeout
         */
		public function _timeout($intTimeout = 30){
			set_time_limit($intTimeout);
		}

        /**
         * Ignore user abort, when in use the scrip will carry on processing until complete even if the user closes the browser window or stops the request from loading.
         *
         * @param bool $blIgnore
         */
		public function _ignoreUserAbort($blIgnore = true){
			ignore_user_abort($blIgnore);
		}

		/**
		 * Register an alias URI for a response function for instance if you had thankYou() as the function name you could register 'thank-you' as an alias URI.
		 * All aliases must be registered from within a __construct function in your controller. Adding an alias means that the original thankYou() will still be callable by routes.
		 *
		 * @param $strURI
		 * @param $strFunctionName
		 */
        protected function _aliasURI($strURI,$strFunctionName){
			$this->arrAliasURIs[$strURI] = $strFunctionName;
		}

		/**
		 * Get an array of all the aliases registered for this controller.
		 *
		 * @return array
		 */
        public function _getAliases(){
			return $this->arrAliasURIs;
		}

		/**
		 * Register an replace URI for a response function for instance if you had thankYou() as the function name you could register 'thank-you' as an replace URI.
		 * All replaces must be registered from within a __construct function in your controller. Adding a replace means that the original thankYou() will no-longer be callable by routes.
		 *
		 * @param $strURI
		 * @param $strFunctionName
		 */
        protected function _replaceURI($strURI,$strFunctionName){
			$this->arrReplaceURIs[$strFunctionName] = $strURI;
		}

		/**
		 * Get an array of all the replacements registered for this controller.
		 *
		 * @return array
		 */
        public function _getReplacements(){
			return $this->arrReplaceURIs;
		}

		/**
		 * Function to call any controller response with the correct method prefix if any has been setup. If the response function is not found a 404 page will be output.
		 *
		 * @param $strCallFunctionName Name of the function to be called
		 * @return bool
		 */
        final protected function _callFunction($strCallFunctionName){

			$arrControllerFunctions = array();
			foreach(get_class_methods($this) as $strFunctionName){
				$arrControllerFunctions[strtolower($strFunctionName)] = $strFunctionName;
			}

			$strRequestMethodFunction = sprintf('%s%s',strtolower($_SERVER['REQUEST_METHOD']),strtolower($strCallFunctionName));

			if(array_key_exists($strRequestMethodFunction,$arrControllerFunctions)){
				return $this->$arrControllerFunctions[$strRequestMethodFunction]();
			}elseif(array_key_exists(strtolower($strCallFunctionName),$arrControllerFunctions)){
				return $this->$arrControllerFunctions[strtolower($strCallFunctionName)]();
			}else{
				return $this->_404();
			}
		}

		/**
		 * Returns either a single item (if key passed in) from the route array otherwise returns the whole array.
		 *
		 * @param null|string $strReturnKey
		 * @return array
		 */
        final protected function _route($strReturnKey = null){
			return array_key_exists($strReturnKey, $this->arrRoute) ? $this->arrRoute[$strReturnKey] : $this->arrRoute;
		}

		/**
		 * Process files that have been uploaded and return an array of uploaded data, this is to help when a browser does not support teh pure AJAX uploader.
		 *
		 * @param $strFileKey
		 * @param string $strType
		 * @return array|mixed
		 */
		public function _upload($strFileKey,$strType = 'file'){

			$arrOut = array();

			if(count($_FILES) && array_key_exists($strFileKey,$_FILES)){
				$resUpload = new \Twist\Core\Controllers\Upload();

				if(is_array($_FILES[$strFileKey]['name'])){
					foreach($_FILES[$strFileKey]['name'] as $intKey => $mxdValue){
						$arrOut[] = json_decode($resUpload->$strType($strFileKey,$intKey),true);
					}
				}else{
					$arrOut = json_decode($resUpload->$strType($strFileKey),true);
				}
			}

			return $arrOut;
		}

		/**
		 * Halts all scripts and outputs a 404 page to the screen.
		 *
		 * @return bool
		 */
		final public function _404(){
			return $this->_error(404);
		}

		/**
		 * Halts all scripts and outputs the desired error page by response code (for example 404 or 403) to the screen.
		 *
		 * @param int $intError HTTP Response code of the error page to be output
		 * @return bool
		 */
		final public function _error($intError){
			return $this->_response($intError);
		}

		/**
		 * Halts all scripts and outputs the desired error page by response code (for example 404 or 403) to the screen.
		 *
		 * @param $intError HTTP Response code of the error page to be output
		 * @param null $strCustomDescription Custom description to be included in the response page
		 * @return bool
		 */
		final public function _response($intError,$strCustomDescription = null){
			Error::errorPage($intError,$strCustomDescription);
			return false;
		}

		/**
		 * Add an error message, the messages can be output using the {messages:error} template tag, you can also output all messages using {messages:all}.
		 *
		 * @param $strMessage
		 * @param null $strKey
		 */
		public function _errorMessage($strMessage,$strKey = null){
			\Twist::errorMessage($strMessage,$strKey);
		}

		/**
		 * Add an warning message, the messages can be output using the {messages:warning} template tag, you can also output all messages using {messages:all}.
		 *
		 * @param $strMessage
		 * @param null $strKey
		 */
		public function _warningMessage($strMessage,$strKey = null){
			\Twist::warningMessage($strMessage,$strKey);
		}

		/**
		 * Add an notice message, the messages can be output using the {messages:notice} template tag, you can also output all messages using {messages:all}.
		 *
		 * @param $strMessage
		 * @param null $strKey
		 */
		public function _noticeMessage($strMessage,$strKey = null){
			\Twist::noticeMessage($strMessage,$strKey);
		}

		/**
		 * Add an success message, the messages can be output using the {messages:success} template tag, you can also output all messages using {messages:all}.
		 *
		 * @param $strMessage
		 * @param null $strKey
		 */
		public function _successMessage($strMessage,$strKey = null){
			\Twist::successMessage($strMessage,$strKey);
		}

		/**
		 * Returns the Meta object so that page titles, keywords and other meta items can all be updated before being output to the base template.
		 *
		 * @return \Twist\Core\Models\Route\Meta
		 */
        public function _meta(){
			return $this->resRoute->meta();
		}

		/**
		 * Returns the Model object which is only set when {model:App\My\Model} is defined in your URI, From here all functions of the model can be called.
		 *
		 * @return null|Object
		 */
        public function _model(){
			return $this->resRoute->model();
		}

		/**
		 * Get a Route URI var from the route vars, passing in null will return the whole array of route vars.
		 *
		 * @param null $strVarKey
		 * @return array|null
		 */
        protected function _var($strVarKey = null){

			if(is_null($strVarKey)){
				return $this->arrRoute['vars'];
			}else{
				return (array_key_exists($strVarKey,$this->arrRoute['vars'])) ? $this->arrRoute['vars'][$strVarKey] : null;
			}
		}

		/**
		 * Process a view template file and return the output.
		 * @param $dirView
		 * @param null $arrViewTags
		 * @param bool $blRemoveUnusedTags
		 * @return string
		 */
        protected function _view($dirView,$arrViewTags = null,$blRemoveUnusedTags = false){
			return \Twist::View()->build($dirView,$arrViewTags,$blRemoveUnusedTags);
		}

		/**
		 * @alias _view
		 */
        protected function _render($dirView,$arrViewTags = null,$blRemoveUnusedTags = false){
			return $this->_view($dirView,$arrViewTags,$blRemoveUnusedTags);
		}
	}
