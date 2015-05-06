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

	class BaseController{

		protected $arrAliasURIs = array();
		protected $arrReplaceURIs = array();
		protected $resMeta = null;
		protected $arrRoute = array();

		final public function _extended($arrRoute,Meta $resMeta = null){

			$this->arrRoute = $arrRoute;
			$this->resMeta = $resMeta;

			return true;
		}

		public function _default(){
			Error::errorPage(404);
			return false;
		}

		public function _index(){
			return $this->_default();
		}

		public function _fallback(){
			Error::errorPage(404);
			return false;
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
				Error::errorPage(404);
				return false;
			}
		}

        final protected function _route($strReturnKey = null){
			return array_key_exists($strReturnKey, $this->arrRoute) ? $this->arrRoute[$strReturnKey] : $this->arrRoute;
		}

		/**
		 * Meta object that
		 * @return Meta
		 */
        final public function _meta(){
			return $this->resMeta;
		}

		/**
		 * @deprecated
		 * @param $strTitle
		 */
        final protected function _title($strTitle){
			$this->_meta()->title($strTitle);
		}

		/**
		 * @deprecated
		 * @param $strDescription
		 */
        final protected function _description($strDescription){
			$this->_meta()->description($strDescription);
		}

		/**
		 * @deprecated
		 * @param $strAuthor
		 */
        final protected function _author($strAuthor){
			$this->_meta()->author($strAuthor);
		}

		/**
		 * @deprecated
		 * @param $strKeywords
		 */
        final protected function _keywords($strKeywords){
			return (is_null($strKeywords)) ? $_SERVER['TWIST_ROUTE_KEYWORDS'] : $this->_meta()->keywords($strKeywords);
		}

        final protected function _var($strVarKey = null){

			if(is_null($strVarKey)){
				return $this->_route('vars');
			}else{
				return (array_key_exists($strVarKey,$this->_route('vars'))) ? $_SERVER['TWIST_ROUTE']['vars'][$strVarKey] : null;
			}
		}

        final protected function _view($dirView,$arrViewTags = null,$blRemoveUnusedTags = false){
			return \Twist::View()->build($dirView,$arrViewTags,$blRemoveUnusedTags);
		}

        final protected function _render($dirView,$arrViewTags = null,$blRemoveUnusedTags = false){
			return $this->_view($dirView,$arrViewTags,$blRemoveUnusedTags);
		}
	}
