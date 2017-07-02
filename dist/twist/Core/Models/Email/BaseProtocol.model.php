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

	namespace Twist\Core\Models\Email;

	class BaseProtocol{

		protected $resConnection = null;
		protected $strLastMessage = '';
		protected $strMessageLog = '';
		protected $strErrorMessage = '';
		protected $intErrorNo = 0;
		protected $blConnected = false;
		protected $intTimeout = 30;

		protected $strTo = null;
		protected $strFrom = null;
		protected $strSubject = null;
		protected $strBody = null;
		protected $blUseFromParameter = false;

		public function setTimeout($intTimeout = 30){
			$this->intTimeout = $intTimeout;
			return true;
		}

		public function getLastMessage(){
			return $this->strLastMessage;
		}

		public function getMessageLog(){
			return $this->strMessageLog;
		}

		protected function setError($intErrorNo, $strErrorMessage){
			$this->intErrorNo = $intErrorNo;
			$this->strErrorMessage = $strErrorMessage;
		}

		public function getError(){
			return array(
				'code' => $this->intErrorNo,
				'message' => $this->strErrorMessage
			);
		}

		public function connect($strHost,$intPort = 25){
			return true;
		}

		public function connected(){
			return $this->blConnected;
		}

		public function disconnect(){
			return true;
		}

		public function login($strEmailAddress,$strPassword){
			return true;
		}

		public function useFromParam($blStatus = true){
			$this->blUseFromParameter = $blStatus;
		}

		public function from($strFromAddress){
			$this->strFrom = $strFromAddress;
			return true;
		}

		public function to($strToAddress){
			$this->strTo = $strToAddress;
			return true;
		}

		public function subject($strSubject){
			$this->strSubject = $strSubject;
			return true;
		}

		public function body($strToAddress){
			$this->strBody = $strToAddress;
			return true;
		}

		public function send($strEmailSource){
			return true;
		}
	}