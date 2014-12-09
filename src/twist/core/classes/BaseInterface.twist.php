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

	namespace TwistInterface;

	class BaseInterface{

		protected $strInterfaceKey = null;
		protected $strBaseURI = null;
		protected $strBaseTemplate = null;
		protected $resRoute = null;

		public function __construct($strInterfaceKey){

			\Twist::Route()->purge();

			$this->strInterfaceKey = $strInterfaceKey;

			$this->resRoute = \Twist::Route($this->strInterfaceKey);
			$this->resRoute->interfaceURI($this->strInterfaceKey);

			$this->resRoute->setTemplatesDirectory(sprintf('%s/%s/templates/',rtrim(DIR_FRAMEWORK_INTERFACES,'/'),$this->strInterfaceKey));
			$this->resRoute->setElementsDirectory(sprintf('%s/%s/templates/',rtrim(DIR_FRAMEWORK_INTERFACES,'/'),$this->strInterfaceKey));
			$this->resRoute->setControllerDirectory(sprintf('%s/%s/controllers/',rtrim(DIR_FRAMEWORK_INTERFACES,'/'),$this->strInterfaceKey));
		}

		public function baseURI($strBaseURI){
			$this->strBaseURI = trim($strBaseURI,'/');
			$this->resRoute->baseURI($this->strBaseURI);
		}

		public function baseTemplate($strBaseTemplate){
			$this->strBaseTemplate = $strBaseTemplate;
			$this->resRoute->baseTemplate($strBaseTemplate);
		}

		protected function redirect($strURI,$strURL){
			$this->resRoute->redirect($strURI,$strURL);
		}

		protected function restrict($strURI,$strLoginURI,$mxdLevel = null){
			$this->resRoute->restrict($strURI,$strLoginURI,$mxdLevel);
		}

		protected function ajax($strURI,$strFunctionsFolder = null,$strTemplatesFolder = null,$strElementsFolder = null){
			$this->resRoute->ajax($strURI,$strFunctionsFolder,$strTemplatesFolder,$strElementsFolder);
		}

		protected function template($strURI,$strTemplate,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->template($strURI,$strTemplate,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function getTemplate($strURI,$strTemplate,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->getTemplate($strURI,$strTemplate,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function postTemplate($strURI,$strTemplate,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->postTemplate($strURI,$strTemplate,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function putTemplate($strURI,$strTemplate,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->putTemplate($strURI,$strTemplate,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function deleteTemplate($strURI,$strTemplate,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->deleteTemplate($strURI,$strTemplate,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function element($strURI,$strElement,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->element($strURI,$strElement,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function getElement($strURI,$strElement,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->getElement($strURI,$strElement,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function postElement($strURI,$strElement,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->postElement($strURI,$strElement,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function putElement($strURI,$strElement,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->putElement($strURI,$strElement,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function deleteElement($strURI,$strElement,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->deleteElement($strURI,$strElement,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function controller($strURI,$mxdController,$mxdBaseTemplate = true,$mxdCache = false,$arrData = array()){
			$this->resRoute->controller($strURI,$mxdController,$mxdBaseTemplate,$mxdCache,$arrData);
		}

		protected function moduleRequired($strModule){
			\Twist::framework()->module()->exists($strModule,true);
		}

		public function load(){
			throw new \Exception('A load function must be added to your interface class, the class must extend TwistInterface');
		}

		public function serve(){
			//Server all the pages of the CMS when requested
			$this->resRoute->serve();
		}
	}