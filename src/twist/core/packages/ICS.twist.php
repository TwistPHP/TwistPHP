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

	namespace Twist\Core\Packages;
	use \Twist\Core\ModuleBase;

	/**
	 * ICS Calendar and Event Handler, can import, edita nd create ics files that are compatible with Google Calendars and iCAl/Mac Calendar
	 * @package TwistPHP\Packages
	 */
	class ICS{

		/**
		 * Loads in the two returnable object classes
		 */
		public function __construct(){
			require_once sprintf('%s/libraries/ICS/Calendar.lib.php',DIR_FRAMEWORK_PACKAGES);
			require_once sprintf('%s/libraries/ICS/Event.lib.php',DIR_FRAMEWORK_PACKAGES);
		}

		/**
		 * Create a new instance of the ICSCalendar object, allowing the creation of an calendar ICS file
		 *
		 * @return_object ICSCalendar core/packages/libraries/ICS/Calendar.lib.php
		 * @return object Returns the ICS Calendar Object
		 */
		public function createCalendar(){
			return new ICSCalendar();
		}

		/**
		 * Create a new instance of the ICSEvent object, allowing the creation of an event ICS file
		 *
		 * @return_object ICSEvent core/packages/libraries/ICS/Event.lib.php
		 * @return object Returns the ICS Event Object
		 */
		public function createEvent(){
			return new ICSEvent();
		}

		/**
		 * Load in an existing ICS file in to be converted into an usable ICS Event/Calendar object
		 *
		 * @param $dirICSFile Path of the ICS file to be imported
		 * @return_object ICSCalendar core/packages/libraries/ICS/Calendar.lib.php
		 * @return_object ICSEvent core/packages/libraries/ICS/Event.lib.php
		 * @return null|object Returns NULL or either the ICS Event or Calendar Object
		 */
		public function loadFile($dirICSFile){

			$resObject = null;

			if(file_exists($dirICSFile) || strstr($dirICSFile,'http')){
				$strRawData = file_get_contents($dirICSFile);
				$resObject = $this->parseRawData($strRawData);
			}

			return $resObject;
		}

		/**
		 * Turns the raw ICS data into an object and returns
		 *
		 * @param $strRawData
		 * @return_object ICSCalendar core/packages/libraries/ICS/Calendar.lib.php
		 * @return_object ICSEvent core/packages/libraries/ICS/Event.lib.php
		 * @return null|object Returns NULL or either the ICS Event or Calendar Object
		 */
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