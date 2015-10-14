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

	namespace Twist\Core\Models;

	/**
	 * Detection based on the User Agent string to work out the type of a device be it Desktop, Mac, Tablet or Mobile. OS and Browser details are also detected.
	 * @package Twist\Core\Models\UserAgent
	 */
	class UserAgent{

		/**
		 * Get the User Agent string form the PHP Server array
		 * @return string User Agent Header
		 */
		public static function get(){
			return $_SERVER['HTTP_USER_AGENT'];
		}

		/**
		 * Detect the Device Type, OS Name, Name/Version and the Browser Name based on a user agent string that is either passed in or detected from the server headers
		 * @param null $strUserAgent The user agent header to analise, null will detect the users current user-agent
		 * @return array Information about the device device,os,version,browser
		 */
		public static function detect($strUserAgent = null){

			$arrOut = array('device' => 'Unknown','os' => 'Unknown','version' => 'Unknown','browser' => 'Unknown');
			$strUserAgent = (is_null($strUserAgent)) ? self::get() : $strUserAgent;

			$arrOSDetections = array(
				'/windows nt 10/i'      =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 10'),
				'/windows nt 6.3/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 8.1'),
				'/windows nt 6.2/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 8'),
				'/windows nt 6.1/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 7'),
				'/windows nt 6.0/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows Vista'),
				'/windows nt 5.2/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows Server 2003/XP x64'),
				'/windows nt 5.1/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows XP'),
				'/windows xp/i'         =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows XP'),
				'/windows nt 5.0/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 2000'),
				'/windows me/i'         =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows ME'),
				'/win98/i'              =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 98'),
				'/win95/i'              =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 95'),
				'/win16/i'              =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 3.11'),
				'/macintosh|mac os x/i' =>  array('device' => 'Mac','os' => 'OSX','version' => 'Mac OS X'),
				'/mac_powerpc/i'        =>  array('device' => 'Mac PowerPC','os' => 'MacOS','version' => 'Mac OS 9'),
				'/linux/i'              =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Linux'),
				'/ubuntu/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Ubuntu'),
				'/fedora/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Fedora'),
				'/kubuntu/i'            =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Kubuntu'),
				'/debian/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Debian'),
				'/CentOS/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'CentOS'),
				'/Mandriva.([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)/i'    =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Mandriva'),
				'/SUSE.([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)/i'        =>  array('device' => 'Desktop','os' => 'Linux','version' => 'SUSE'),
				'/Dropline/i'           =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Slackware (Dropline GNOME)'),
				'/ASPLinux/i'           =>  array('device' => 'Desktop','os' => 'Linux','version' => 'ASPLinux'),
				'/Red Hat/i'            =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Red Hat'),
				'/iphone/i'             =>  array('device' => 'Phone','os' => 'iOS','version' => 'iPhone'),
				'/ipod/i'               =>  array('device' => 'Device','os' => 'iOS','version' => 'iPod'),
				'/ipad/i'               =>  array('device' => 'Tablet','os' => 'iOS','version' => 'iPad'),
				'/android/i'            =>  array('device' => 'Phone','os' => 'Android','version' => 'Android'),
				'/blackberry/i'         =>  array('device' => 'Phone','os' => 'BlackBerry','version' => 'BlackBerry'),
				'/webos/i'              =>  array('device' => 'Phone','os' => 'Phone','version' => 'Generic Phone')
			);

			//Loop through all the detections to find the OS
			foreach($arrOSDetections as $strRegX => $arrDevice){
				if(preg_match($strRegX, $strUserAgent)){
					$arrOut = $arrDevice;
					//break; Removed the break to enable run to the end, first match is not always the correct match
				}
			}

			$arrBrowserDetections = array(
				'/mobile/i'     =>  'Handheld Browser',
				'/msie/i'       =>  'Internet Explorer',
				'/firefox/i'    =>  'Firefox',
				'/safari/i'     =>  'Safari',
				'/chrome/i'     =>  'Chrome',
				'/opera/i'      =>  'Opera',
				'/netscape/i'   =>  'Netscape',
				'/maxthon/i'    =>  'Maxthon',
				'/konqueror/i'  =>  'Konqueror'
			);

			foreach($arrBrowserDetections as $strRegX => $strBrowser){
				if(preg_match($strRegX, $strUserAgent)){
					$arrOut['browser'] = $strBrowser;
					//break; Removed the break to enable run to the end, first match is not always the correct match
				}
			}

			return $arrOut;
		}
	}