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
	use TwistPHP\ModuleBase;

	class ICS{

		public function __construct(){
			require_once sprintf('%s/libraries/ICS/Calendar.lib.php',DIR_FRAMEWORK_PACKAGES);
			require_once sprintf('%s/libraries/ICS/Event.lib.php',DIR_FRAMEWORK_PACKAGES);
		}

		public function createCalendar(){
			return new ICSCalendar();
		}

		public function createEvent(){
			return new ICSEvent();
		}

		public function loadFile($strICSFile){

			$resObject = null;

			if(file_exists($strICSFile) || strstr($strICSFile,'http')){
				$strRawData = file_get_contents($strICSFile);
				$resObject = $this->parseRawData($strRawData);
			}

			return $resObject;
		}

		protected function parseRawData($strRawData){

			//Clean up the line breaks
			$resObject = $strType = null;
			$strRawData = str_replace(array("\r\n","\r"),"\n",$strRawData);

			if(strstr($strRawData,'BEGIN:VCALENDAR')){
				$strType = 'calendar';
				$resObject = $this->createCalendar();
			}elseif(strstr($strRawData,'BEGIN:VEVENT')){
				$strType = 'event';
				$resObject = $this->createEvent();
			}

			$arrLines = explode("\n",$strRawData);

			foreach($arrLines as $strEachLine){

				switch($strEachLine){
					case'BEGIN:VCALENDAR':
						$resCurrentItem = $resObject;
						break;
					case'BEGIN:VEVENT':
						if($strType == 'calendar'){
							$resCurrentItem = $resObject->event();
						}else{
							$resCurrentItem = $resObject;
						}
						break;

					case'END:VCALENDAR':
					case'END:VEVENT':
						break;

					default:

						$strExplodeChar = ':';
						if(preg_match("#^[A-Z\-]+;#i",$strEachLine,$arrMatches)){
							$strExplodeChar = ';';
						}

						//Get the row parts
						$arrRowParts = explode($strExplodeChar,$strEachLine);

						//Extract the key and data from the array
						$strKey = $arrRowParts[0];
						unset($arrRowParts[0]);
						$mxdValue = implode($strExplodeChar,$arrRowParts);

						//Set the data into the event or calendar
						$resCurrentItem->setData($strKey,$mxdValue);
						break;
				}
			}

			return $resObject;
		}
	}