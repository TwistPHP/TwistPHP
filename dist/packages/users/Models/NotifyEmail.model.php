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

	namespace Packages\notifications\Models;

	class NotifyEmail{

		public static function send( $intUserID, $strTitle, $strHTML, $strEmailAddressCC = '', $strFrom = '' ){

			$arrUser = \Twist::User()->get( $intUserID )->get();

			//Only send to enabled users
			if($arrUser['enabled'] == '1'){

				$strName = trim($arrUser['firstname'] . ' ' . $arrUser['surname']);

				$resEmail = \Twist::Email() -> create();
				$resEmail -> addTo( $arrUser['email'], $strName );

				if($strEmailAddressCC != ''){
					$resEmail -> addCc($strEmailAddressCC);
				}

				if($strFrom == ''){
					$strFrom = 'noreply@'.\Twist::framework()->setting('SITE_HOST');
				}

				$resEmail -> setFrom( $strFrom );
				$resEmail -> setSubject( $strTitle );
				$resEmail -> setBodyHTML( $strHTML );
				return $resEmail -> send();
			}

			//If a user is disabled/suspended also return true so that item will be deleted
			return true;
		}


		public static function sendDirect( $strEmailAddress, $strTitle, $strHTML, $strEmailAddressCC = '', $strFrom = '' ) {

			$resEmail = \Twist::Email() -> create();
			$resEmail -> addTo( $strEmailAddress );

			if($strEmailAddressCC != ''){
				$resEmail -> addCc($strEmailAddressCC);
			}

			if($strFrom == ''){
				$strFrom = 'noreply@'.\Twist::framework()->setting('SITE_HOST');
			}

			$resEmail -> setFrom( $strFrom );
			$resEmail -> setSubject( $strTitle );
			$resEmail -> setBodyHTML( $strHTML );
			return $resEmail -> send();
		}
	}
