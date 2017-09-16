<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
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

	class ProtocolNative extends BaseProtocol{

		/**
		 * Hard coded to true so that the connection/login process will not be done upon sending
		 * @var bool
		 */
		protected $blConnected = true;

		public function useFromParam($blStatus = true){
			$this->blUseFromParameter = $blStatus;
		}

		public function send($strEmailSource){

			$strEmailSource = preg_replace('/To\: .*\r\n/im', '', $strEmailSource);
			$strEmailSource = preg_replace('/Subject\: .*\r\n/im', '', $strEmailSource);

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