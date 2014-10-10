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

	class ICSCalendar{

		protected $strType = 'VCALENDAR';
		protected $arrData = array();
		protected $arrEvents = array();
		protected $strReturnCode = "\r\n";

		public function __construct(){

			if(is_null($this->version())){
				$this->version('2.0');
			}

			if(is_null($this->prodID())){
				$this->prodID('-//TwistPHP//ICS//EN');
			}
		}

		public function type(){
			return $this->strType;
		}

		public function prodID($mxdProdID = null){

			if(!is_null($mxdProdID)){
				$this->arrData['PRODID'] = $this->sanitizeRawData($mxdProdID);
			}

			return (array_key_exists('PRODID',$this->arrData)) ? $this->arrData['PRODID'] : null;
		}

		public function version($mxdVersion = null){

			if(!is_null($mxdVersion)){
				$this->arrData['VERSION'] = $this->sanitizeRawData($mxdVersion);
			}

			return (array_key_exists('VERSION',$this->arrData)) ? $this->arrData['VERSION'] : null;
		}

		public function event($intUID = null){

			$arrEvents = $this->arrEvents;
			$this->arrEvents = array();

			foreach($arrEvents as $resEachEvent){
				$this->arrEvents[$resEachEvent->uid()] = $resEachEvent;
			}

			if(is_null($intUID)){
				$resEvent = new ICSEvent();
				$this->arrEvents[$resEvent->uid()] = $resEvent;
			}else{
				$resEvent = $this->arrEvents[$intUID];
			}

			return $resEvent;
		}

		public function getRaw(){

			if($this->validateCalendar()){

				$strOut = sprintf("BEGIN:VCALENDAR%s",$this->strReturnCode);

				//Output all the values for the event
				foreach($this->arrData as $strKey => $mxdValue){
					$strOut .= sprintf("%s:%s%s",$strKey,$mxdValue,$this->strReturnCode);
				}

				//Add the events in here
				foreach($this->arrEvents as $resEvent){
					$strOut .= $resEvent->getRaw();
				}

				$strOut .= sprintf("END:VCALENDAR%s",$this->strReturnCode);
			}else{
				$strOut = "Calendar validation failed, you are missing some key parameters";
			}

			return $strOut;
		}

		protected function validateCalendar(){

			$blValidEvent = true;

			return $blValidEvent;
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

		public function setData($strKey,$mxdData){
			$this->arrData[strtoupper(trim($strKey))] = $this->sanitizeRawData($mxdData);
		}

		protected function serve(){

			$strFilename = '';

			header("Content-type: text/calendar");
			header('Content-Disposition: attachment; filename="' . $strFilename . '"');
		}

	}