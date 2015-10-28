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

			$arrOut = array('device' => 'Unknown','os' => 'Unknown','version' => 'Unknown','fa-os-icon' => 'fa-desktop','browser' => 'Unknown','fa-browser-icon' => 'fa-question-circle');
			$strUserAgent = (is_null($strUserAgent)) ? self::get() : $strUserAgent;

			$arrOSDetections = array(
				'/windows nt 10/i'      =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 10','fa-os-icon' => 'fa-windows'),
				'/windows nt 6.3/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 8.1','fa-os-icon' => 'fa-windows'),
				'/windows nt 6.2/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 8','fa-os-icon' => 'fa-windows'),
				'/windows nt 6.1/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 7','fa-os-icon' => 'fa-windows'),
				'/windows nt 6.0/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows Vista','fa-os-icon' => 'fa-windows'),
				'/windows nt 5.2/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows Server 2003/XP x64','fa-os-icon' => 'fa-windows'),
				'/windows nt 5.1/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows XP','fa-os-icon' => 'fa-windows'),
				'/windows xp/i'         =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows XP','fa-os-icon' => 'fa-windows'),
				'/windows nt 5.0/i'     =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 2000','fa-os-icon' => 'fa-windows'),
				'/windows me/i'         =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows ME','fa-os-icon' => 'fa-windows'),
				'/win98/i'              =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 98','fa-os-icon' => 'fa-windows'),
				'/win95/i'              =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 95','fa-os-icon' => 'fa-windows'),
				'/win16/i'              =>  array('device' => 'Desktop','os' => 'Windows','version' => 'Windows 3.11','fa-os-icon' => 'fa-windows'),
				'/macintosh|mac os x/i' =>  array('device' => 'Mac','os' => 'OSX','version' => 'Mac OS X','fa-os-icon' => 'fa-apple'),
				'/mac_powerpc/i'        =>  array('device' => 'Mac PowerPC','os' => 'MacOS','version' => 'Mac OS 9','fa-os-icon' => 'fa-apple'),
				'/linux/i'              =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Linux','fa-os-icon' => 'fa-linux'),
				'/ubuntu/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Ubuntu','fa-os-icon' => 'fa-linux'),
				'/fedora/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Fedora','fa-os-icon' => 'fa-linux'),
				'/kubuntu/i'            =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Kubuntu','fa-os-icon' => 'fa-linux'),
				'/debian/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Debian','fa-os-icon' => 'fa-linux'),
				'/CentOS/i'             =>  array('device' => 'Desktop','os' => 'Linux','version' => 'CentOS','fa-os-icon' => 'fa-linux'),
				'/Mandriva.([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)/i'    =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Mandriva','fa-os-icon' => 'fa-linux'),
				'/SUSE.([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)/i'        =>  array('device' => 'Desktop','os' => 'Linux','version' => 'SUSE','fa-os-icon' => 'fa-linux'),
				'/Dropline/i'           =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Slackware (Dropline GNOME)','fa-os-icon' => 'fa-linux'),
				'/ASPLinux/i'           =>  array('device' => 'Desktop','os' => 'Linux','version' => 'ASPLinux','fa-os-icon' => 'fa-linux'),
				'/Red Hat/i'            =>  array('device' => 'Desktop','os' => 'Linux','version' => 'Red Hat','fa-os-icon' => 'fa-linux'),
				'/iphone/i'             =>  array('device' => 'Phone','os' => 'iOS','version' => 'iPhone','fa-os-icon' => 'fa-apple'),
				'/ipod/i'               =>  array('device' => 'Device','os' => 'iOS','version' => 'iPod','fa-os-icon' => 'fa-apple'),
				'/ipad/i'               =>  array('device' => 'Tablet','os' => 'iOS','version' => 'iPad','fa-os-icon' => 'fa-apple'),
				'/android/i'            =>  array('device' => 'Phone','os' => 'Android','version' => 'Android','fa-os-icon' => 'fa-android'),
				'/blackberry/i'         =>  array('device' => 'Phone','os' => 'BlackBerry','version' => 'BlackBerry','fa-os-icon' => 'fa-mobile'),
				'/webos/i'              =>  array('device' => 'Phone','os' => 'Phone','version' => 'Generic Phone','fa-os-icon' => 'fa-mobile')
			);

			//Loop through all the detections to find the OS
			foreach($arrOSDetections as $strRegX => $arrDevice){
				if(preg_match($strRegX, $strUserAgent)){
					$arrOut = $arrDevice;
					//break; Removed the break to enable run to the end, first match is not always the correct match
				}
			}

			$arrBrowserDetections = array(
				'/mobile/i'     =>  array('title' => 'Handheld Browser','fa-browser-icon' => 'fa-mobile'),
				'/msie/i'       =>  array('title' => 'Internet Explorer','fa-browser-icon' => 'fa-internet-explorer'),
				'/firefox/i'    =>  array('title' => 'Firefox','fa-browser-icon' => 'fa-firefox'),
				'/safari/i'     =>  array('title' => 'Safari','fa-browser-icon' => 'fa-safari'),
				'/chrome/i'     =>  array('title' => 'Chrome','fa-browser-icon' => 'fa-chrome'),
				'/opera/i'      =>  array('title' => 'Opera','fa-browser-icon' => 'fa-opera'),
				'/netscape/i'   =>  array('title' => 'Netscape','fa-browser-icon' => 'fa-desktop'),
				'/maxthon/i'    =>  array('title' => 'Maxthon','fa-browser-icon' => 'fa-desktop'),
				'/konqueror/i'  =>  array('title' => 'Konqueror','fa-browser-icon' => 'fa-desktop')
			);

			foreach($arrBrowserDetections as $strRegX => $arrBrowserInfo){
				if(preg_match($strRegX, $strUserAgent)){
					$arrOut['browser'] = $arrBrowserInfo['title'];
					$arrOut['fa-browser-icon'] = $arrBrowserInfo['fa-icon'];
					//break; Removed the break to enable run to the end, first match is not always the correct match
				}
			}

			return $arrOut;
		}
	}