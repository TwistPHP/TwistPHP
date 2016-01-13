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

namespace Twist\Core\Utilities;

/**
 * Generate and send full multi-lingual multi-part HTML emails with the ability to add attachments. Fully customisable with Char encoding, message encoding,
 * priority and sensitivity. The ability to parse raw email source and turn it into usable data using a single method call.
 */
class Email extends Base{

	public function create(){
		return new \Twist\Core\Models\Email\Create();
	}

	public function parseSource($strEmailSource){
		$resSourceParser = new \Twist\Core\Models\Email\SourceParser();
		return $resSourceParser->processEmailSource($strEmailSource);
	}
}