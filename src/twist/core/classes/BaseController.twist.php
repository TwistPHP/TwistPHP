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

	namespace TwistController;

	class BaseController{

		protected $arrAliasURIs = array();
		protected $arrReplaceURIs = array();

		public function _extended(){
			return true;
		}

		public function _default(){
			\TwistPHP\Error::errorPage(404);
			return false;
		}

		public function _fallback(){
			\TwistPHP\Error::errorPage(404);
			return false;
		}

		protected function _aliasURI($strURI,$strFunctionName){
			$this->arrAliasURIs[$strURI] = $strFunctionName;
		}

		public function _getAliases(){
			return $this->arrAliasURIs;
		}

		protected function _replaceURI($strURI,$strFunctionName){
			$this->arrReplaceURIs[$strFunctionName] = $strURI;
		}

		public function _getReplacements(){
			return $this->arrReplaceURIs;
		}

		protected function _callFunction($strCallFunctionName){

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
				\TwistPHP\Error::errorPage(404);
				return false;
			}
		}

		protected function _route(){
			return $_SERVER['TWIST_ROUTE'];
		}

		protected function _title($strTitle = null){
			return (is_null($strTitle)) ? $_SERVER['TWIST_ROUTE_TITLE'] : $_SERVER['TWIST_ROUTE_TITLE'] = $strTitle;
		}

		protected function _description($strDescription = null){
			return (is_null($strDescription)) ? $_SERVER['TWIST_ROUTE_DESCRIPTION'] : $_SERVER['TWIST_ROUTE_DESCRIPTION'] = $strDescription;
		}

		protected function _author($strAuthor = null){
			return (is_null($strAuthor)) ? $_SERVER['TWIST_ROUTE_AUTHOR'] : $_SERVER['TWIST_ROUTE_AUTHOR'] = $strAuthor;
		}

		protected function _keywords($strKeywords = null){
			return (is_null($strKeywords)) ? $_SERVER['TWIST_ROUTE_KEYWORDS'] : $_SERVER['TWIST_ROUTE_KEYWORDS'] = $strKeywords;
		}

<<<<<<< HEAD
	protected function _var($strVarKey = null){

		if(is_null($strVarKey)){
			return $_SERVER['TWIST_ROUTE']['vars'];
		}else{
			return (array_key_exists($strVarKey,$_SERVER['TWIST_ROUTE']['vars'])) ? $_SERVER['TWIST_ROUTE']['vars'][$strVarKey] : null;
=======
		protected function _uriParameter($strParameterKey = null){

			if(is_null($strParameterKey)){
				return $_SERVER['TWIST_ROUTE']['current']['uri_parameters'];
			}else{
				return (array_key_exists($strParameterKey,$_SERVER['TWIST_ROUTE']['current']['uri_parameters'])) ? $_SERVER['TWIST_ROUTE']['current']['uri_parameters'][$strParameterKey] : null;
			}
>>>>>>> andi
		}
	}