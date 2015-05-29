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
	use Twist\Core\Models\Route\Meta;
	use Twist\Core\Classes\Error;

	class BaseController{

		protected $arrMessages = array();
		protected $arrAliasURIs = array();
		protected $arrReplaceURIs = array();
		protected $resMeta = null;
		protected $arrRoute = array();
		protected $arrRouteVars = array();

		final public function _extended($arrRoute,Meta $resMeta = null){

			$this->arrRoute = $arrRoute;
			$this->arrRouteVars = $arrRoute['vars'];
			$this->resMeta = $resMeta;

			//Preset the array of messages
			\Twist::framework()->package()->extend('View', 'messages', array( 'all' => '','error' => '','warning' => '','notice' => '','success' => '' ));

			return true;
		}

		public function _default(){
			return $this->_404();
		}

		public function _index(){
			return $this->_default();
		}

		public function _fallback(){
			return $this->_404();
		}

		public function _timeout($intTimeout = 30){
			set_time_limit($intTimeout);
		}

		public function _ignoreUserAbort($blIgnore = true){
			ignore_user_abort($blIgnore);
		}

        final protected function _aliasURI($strURI,$strFunctionName){
			$this->arrAliasURIs[$strURI] = $strFunctionName;
		}

        final public function _getAliases(){
			return $this->arrAliasURIs;
		}

        final protected function _replaceURI($strURI,$strFunctionName){
			$this->arrReplaceURIs[$strFunctionName] = $strURI;
		}

        final public function _getReplacements(){
			return $this->arrReplaceURIs;
		}

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

        final protected function _route($strReturnKey = null){
			return array_key_exists($strReturnKey, $this->arrRoute) ? $this->arrRoute[$strReturnKey] : $this->arrRoute;
		}

		final public function _404(){
			return $this->_error(404);
		}

		final public function _error($intError){
			return $this->_response($intError);
		}

		final public function _response($intError){
			Error::errorPage($intError);
			return false;
		}public function _errorMessage($strMessage){
		$this->_message($strMessage,'error');
	}

		public function _warningMessage($strMessage){
			$this->_message($strMessage,'warning');
		}

		public function _noticeMessage($strMessage){
			$this->_message($strMessage,'notice');
		}

		public function _successMessage($strMessage){
			$this->_message($strMessage,'success');
		}

		private function _message($strMessage,$strType){

			$this->arrMessages[] = array(
				'type' => $strType,
				'html' => $this->_view(sprintf('%s/messages/%s.tpl',TWIST_FRAMEWORK_VIEWS,$strType),array('type' => $strType,'message' => $strMessage))
			);

			$arrOut = array( 'all' => '','error' => '','warning' => '','notice' => '','success' => '' );

			foreach($this->arrMessages as $arrEachMessage){

				if($arrEachMessage['type'] == 'error'){
					$arrOut['error'] .= $arrEachMessage['html'];
				}elseif($arrEachMessage['type'] == 'warning'){
					$arrOut['warning'] .= $arrEachMessage['html'];
				}elseif($arrEachMessage['type'] == 'notice'){
					$arrOut['notice'] .= $arrEachMessage['html'];
				}elseif($arrEachMessage['type'] == 'success'){
					$arrOut['success'] .= $arrEachMessage['html'];
				}

				$arrOut['all'] .= $arrEachMessage['html'];
			}

			\Twist::framework()->package()->extend('View', 'messages', $arrOut);
		}

		/**
		 * Meta object that
		 * @return Meta
		 */
        final public function _meta(){
			return $this->resMeta;
		}

        final protected function _var($strVarKey = null){

			if(is_null($strVarKey)){
				return $this->arrRouteVars;
			}else{
				return (array_key_exists($strVarKey,$this->arrRouteVars)) ? $this->arrRouteVars[$strVarKey] : null;
			}
		}

        final protected function _view($dirView,$arrViewTags = null,$blRemoveUnusedTags = false){
			return \Twist::View()->build($dirView,$arrViewTags,$blRemoveUnusedTags);
		}

        final protected function _render($dirView,$arrViewTags = null,$blRemoveUnusedTags = false){
			return $this->_view($dirView,$arrViewTags,$blRemoveUnusedTags);
		}
	}
