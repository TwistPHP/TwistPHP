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

	class ProtocolNative{

		protected $resConnection = null;
		protected $strLastMessage = '';
		protected $blConnected = true;

		protected $strTo = null;
		protected $strFrom = null;
		protected $strSubject = null;
		protected $strBody = null;
		protected $blUseFromParameter = false;

		public function setTimeout($intTimeout = 90){ //TODO: $intTimeout not used
			return true;
		}

		public function getLastMessage(){ return ''; }

		public function connect($strHost,$intPort = 25){ //TODO: $strHost and $intPort not used
			return true;
		}

		public function connected(){ return $this->blConnected; }

		public function disconnect(){}

		public function login($strEmailAddress,$strPassword){ //TODO: $strEmailAddress and $strPassword not used
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

			$strAdditionalParam = null;

			if($this->blUseFromParameter){
				ini_set('sendmail_from', $this->strFrom);
				$strAdditionalParam = sprintf('-f%s',$this->strFrom);
			}

			$blOut = mail($this->strTo,$this->strSubject,$this->strBody,$strEmailSource,$strAdditionalParam);
			$this->strTo = $this->strSubject = $this->strBody = $this->strFrom = null;
			return $blOut;
		}
	}