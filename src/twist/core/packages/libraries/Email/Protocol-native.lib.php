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
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP\Packages;

	class EmailNATIVE{

		protected $resConnection = null;
		protected $strLastMessage = '';
		protected $blConnected = true;

		protected $strTo = null;
		protected $strFrom = null;
		protected $strSubject = null;
		protected $strBody = null;
		protected $blUseFromParameter = false;

		public function setTimeout($intTimeout = 90){ return true; }

		public function getLastMessage(){ return ''; }

		public function connect($strHost,$intPort = 25){ return true; }

		public function connected(){ return $this->blConnected; }

		public function disconnect(){}

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