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

namespace Twist\Core\Helpers;

/**
 * Process and send full HTML emails with attachments and parse the raw source of email messages into a usable data array.
 */
class Email extends Base{

	/**
	 * Get an email object to custom create an outgoing email, add attachments, parse HTML bodies, base64 encode etc
	 * @return \Twist\Core\Models\Email\Create
	 */
	public function create(){
		return new \Twist\Core\Models\Email\Create();
	}

	/**
	 * Quick send, use this option to send an email with the basic 4 parameters to, subject, body, form. By default a HTML body is expected
	 * @param $strToAddress
	 * @param $strSubject
	 * @param $strBody
	 * @param $strFromAddress
	 * @param bool $blBodyIsHTML
	 * @return bool
	 * @throws \Exception
	 */
	public function send($strToAddress, $strSubject, $strBody, $strFromAddress,$blBodyIsHTML = true){

		$resEmail = self::create();

		$resEmail->addTo($strToAddress);
		$resEmail->setFrom($strFromAddress);
		$resEmail->setSubject($strSubject);

		if($blBodyIsHTML){
			$resEmail->setBodyHTML($strBody);
		}else{
			$resEmail->setBodyPlain($strBody);
		}

		return $resEmail->send(true);
	}

	public function parseSource($strEmailSource,$blShowHeaders = false){
		$resSourceParser = new \Twist\Core\Models\Email\SourceParser();
		return $resSourceParser->processEmailSource($strEmailSource,$blShowHeaders);
	}
}