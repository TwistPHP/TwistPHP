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

	namespace Twist\Core\Models\ICS;

	class Event{

		protected $strType = 'VEVENT';
		protected $arrData = array();
		protected $strReturnCode = "\r\n";

		public function __construct(){

			$this->uid(uniqid());

			//If no creation date is set then set one
			if(!array_key_exists('DTSTAMP',$this->arrData)){
				$this->creationDate(strtotime(\Twist::DateTime()->date('Y-m-d H:i:s')));
			}
		}

		public function type(){
			return $this->strType;
		}

		public function uid($mxdUID = null){

			if(!is_null($mxdUID)){
				$this->arrData['UID'] = $mxdUID;
				$this->lastModified();
			}

			return (array_key_exists('UID',$this->arrData)) ? $this->arrData['UID'] : null;
		}

		public function allDay($strDate = null){

			if(!is_null($strDate)){
				$this->arrData['DTSTART;TZID=ALLDAY;VALUE=DATE'] = gmstrftime("%Y%m%d", $strDate);
				$this->arrData['DTEND;TZID=ALLDAY;VALUE=DATE'] = gmstrftime("%Y%m%d", $strDate);
				$this->lastModified();
			}

			return (array_key_exists('DTSTART;TZID=ALLDAY;VALUE=DATE',$this->arrData)) ? $this->arrData['DTSTART;TZID=ALLDAY;VALUE=DATE'] : null;
		}

		public function startDate($strStartDate = null){

			if(!is_null($strStartDate)){
				$this->arrData['DTSTART'] = gmstrftime("%Y%m%dT%H%M00Z", $strStartDate);
				$this->lastModified();
			}

			return (array_key_exists('DTSTART',$this->arrData)) ? $this->arrData['DTSTART'] : null;
		}

		public function endDate($strEndDate = null){

			if(!is_null($strEndDate)){
				$this->arrData['DTEND'] = gmstrftime("%Y%m%dT%H%M00Z", $strEndDate);
				$this->lastModified();
			}

			return (array_key_exists('DTEND',$this->arrData)) ? $this->arrData['DTEND'] : null;
		}

		public function title($strTitle = null){

			if(!is_null($strTitle)){
				$this->arrData['SUMMARY'] = $this->sanitizeRawData($strTitle);
				$this->lastModified();
			}

			return (array_key_exists('SUMMARY',$this->arrData)) ? $this->arrData['SUMMARY'] : null;
		}

		public function description($strDescription = null){

			if(!is_null($strDescription)){
				$this->arrData['DESCRIPTION'] = $this->sanitizeRawData($strDescription);
				$this->lastModified();
			}

			return (array_key_exists('DESCRIPTION',$this->arrData)) ? $this->arrData['DESCRIPTION'] : null;
		}

		public function location($strLocation = null){

			if(!is_null($strLocation)){
				$this->arrData['LOCATION'] = $this->sanitizeRawData($strLocation);
				$this->lastModified();
			}

			return (array_key_exists('LOCATION',$this->arrData)) ? $this->arrData['LOCATION'] : null;
		}

		public function url($strURL = null){

			if(!is_null($strURL)){
				$this->arrData['URL'] = $this->sanitizeRawData($strURL);
				$this->lastModified();
			}

			return (array_key_exists('URL',$this->arrData)) ? $this->arrData['URL'] : null;
		}

		public function creationDate($strCreationDate = null){

			if(!is_null($strCreationDate)){
				$this->arrData['DTSTAMP'] = gmstrftime("%Y%m%dT%H%M00Z", $strCreationDate);
				$this->lastModified();
			}

			return (array_key_exists('DTSTAMP',$this->arrData)) ? $this->arrData['DTSTAMP'] : null;
		}

		protected function lastModified(){
			$this->arrData['LAST-MODIFIED'] = gmstrftime("%Y%m%dT%H%M00Z", strtotime(\Twist::DateTime()->date('Y-m-d H:i:s')));
		}

		public function getRaw(){

			if($this->validateEvent()){

				$strOut = sprintf("BEGIN:VEVENT%s",$this->strReturnCode);

				//Output all the values for the event
				foreach($this->arrData as $strKey => $mxdValue){
					$strOut .= sprintf("%s:%s%s",$strKey,$mxdValue,$this->strReturnCode);
				}

				$strOut .= sprintf("END:VEVENT%s",$this->strReturnCode);
			}else{
				$strOut = "Event validation failed, you are missing some key parameters";
			}

			return $strOut;
		}

		protected function sanitizeRawData($mxdRawData){

			$mxdRawData = strip_tags($mxdRawData,'<br>');

			$mxdRawData = str_replace(
				array('<br />','<br/>','<br>',"\r\n","\r","\n","\t",'"'),
				array('\n','\n','\n','','','',' ','\"'),
				$mxdRawData
			);

			return trim($mxdRawData);
		}

		protected function validateEvent(){

			$blValidEvent = true;

			if(!array_key_exists('UID',$this->arrData) || $this->arrData['UID'] == ''){
				$blValidEvent = false;
			}

			if(!array_key_exists('DTSTAMP',$this->arrData) || $this->arrData['DTSTAMP'] == ''){
				$blValidEvent = false;
			}

			if(!(array_key_exists('DTSTART;TZID=ALLDAY;VALUE=DATE',$this->arrData) || (array_key_exists('DTSTART',$this->arrData) && $this->arrData['DTSTART'] != ''))){
				$blValidEvent = false;
			}

			if(!(array_key_exists('DTEND;TZID=ALLDAY;VALUE=DATE',$this->arrData) || (array_key_exists('DTEND',$this->arrData) && $this->arrData['DTEND'] != ''))){
				$blValidEvent = false;
			}

			if(!array_key_exists('SUMMARY',$this->arrData) || $this->arrData['SUMMARY'] == ''){
				$blValidEvent = false;
			}

			return $blValidEvent;
		}

		public function setData($strKey,$mxdData){
			$this->arrData[strtoupper(trim($strKey))] = $this->sanitizeRawData($mxdData);
		}

		public function serve($strFileName = 'event'){

			$strFileName = \Twist::File()->sanitizeName($strFileName);

			header("Content-type: text/calendar");
			header(sprintf('Content-Disposition: inline; filename="%s.ics"',$strFileName));

			echo $this->getRaw();
			die();
		}
	}