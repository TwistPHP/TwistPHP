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
	 * Simply format data and time strings, generate human readable age of any given date. For example 120 will become "2 minutes" provide a timestamp and the age can be presented as "10 minutes ago". Work with date ranges and producing on screen calendars.
	 */
	class DateTime extends Base{

		protected $fltYearDays = 356.2425;
		protected $strTimeSource = null;

		/**
		 * Get the default time source method if required
		 */
		public function __construct(){
			$this->strTimeSource = \Twist::framework()->setting('DATETIME_SOURCE');
		}

		/**
		 * Get the current timestamp from the system, depending on framework settings this will either be from MySQL connection of natively from PHP.
		 * @return int Returns the timestamp in seconds
		 */
		public function time(){

			//Get the time from the required source
			switch($this->strTimeSource){

				case'mysql':

					$resResult = \Twist::Database()->query("SELECT UNIX_TIMESTAMP() AS `timestamp`");

					if($resResult->status()){
						$arrDate = $resResult->row();
						return $arrDate['timestamp'];
					}else{
						return time();
					}
					break;

				case'php':
				default:
					return time();
					break;
			}
		}

		/**
		 * Get the current date using the time function with the the framework defined method of getting the time. (Either natively from PHP or from the MySQL connection).
		 *
		 * @related time
		 * @reference http://php.net/manual/en/function.date.php
		 *
		 * @param string $strFormat Format the datetime (using PHP date format notation)
		 * @param integer $intTimestamp Provide a custom timestamp to process the date
		 * @return string Returns the date as a string
		 */
		public function date($strFormat = 'Y-m-d H:i:s',$intTimestamp = null){

			if(is_null($intTimestamp)){
				$intTimestamp = $this->time();
			}

			return date($strFormat,$intTimestamp);
		}

		/**
		 * Determine if a provided timestamp is in the future when compared to the current timestamp of 'time'
		 *
		 * @related inPast
		 *
		 * @param integer $intTimestamp Timestamp for comparison
		 * @return boolean Returns true if future timestamp
		 */
		public function inFuture($intTimestamp){
			return $this->time() - $intTimestamp < 0;
		}

		/**
		 * Determine if a provided timestamp is in the past when compared to the current timestamp of 'time'
		 *
		 * @related inFuture
		 *
		 * @param integer $intTimestamp Timestamp for comparison
		 * @return boolean Returns true if past timestamp
		 */
		public function inPast($intTimestamp){
			return $this->time() - $intTimestamp > 0;
		}

		/**
		 * Get a nicely formatted string for the age of a timestamp eg. 'A moment ago' or 'In 3 Hours'
		 *
		 * @related prettyTime
		 *
		 * @param integer $intTimestamp Timestamp for conversion
		 * @return string Returns a formatted human readable time
		 */
		public function prettyAge($intTimestamp){

			//Convert date stings into seconds if required
			if(!is_int($intTimestamp)){
				$intTimestamp = strtotime($intTimestamp);
			}

			$intSecondsDifference = $this->time() - $intTimestamp;

			$blFuture = ($intSecondsDifference < 0);
			$intMonthSeconds = ($this->fltYearDays / 12) * 86400;
			$intSecondsDifference = abs($intSecondsDifference);

			if($intSecondsDifference < 60 ){
				return $blFuture ? 'In a moment' : 'A moment ago';
			}elseif($intSecondsDifference < 120){
				return ($blFuture) ? 'In an minute' : 'A minute ago';
			}elseif($intSecondsDifference < 3600){
				$strOut = ceil($intSecondsDifference / 60).' minutes';
			}elseif($intSecondsDifference < 7200){
				return ($blFuture) ? 'In an hour' : 'An hour ago';
			}elseif($intSecondsDifference < 86400){
				$strOut = ceil($intSecondsDifference / 3600).' hours';
			}elseif($intSecondsDifference < 172800){
				return ($blFuture) ? 'Tomorrow' : 'Yesterday';
			}elseif($intSecondsDifference < $intMonthSeconds){
				$strOut = ceil($intSecondsDifference / 86400).' days';
			}elseif($intSecondsDifference < $intMonthSeconds * 2){
				return ($blFuture) ? 'In a month' : 'A month ago';
			}elseif($intSecondsDifference < $this->fltYearDays * 86400){
				$strOut = ceil( $intSecondsDifference / $intMonthSeconds ).' months';
			}elseif($intSecondsDifference < $this->fltYearDays * 86400 * 2){
				return ($blFuture) ? 'In a year' : 'A year ago';
			}else{
				$strOut = ceil($intSecondsDifference / ($this->fltYearDays * 86400)).' years';
			}

			return ($blFuture) ? 'In '.$strOut : $strOut.' ago';
		}

		/**
		 * Turn a number of seconds into a nicely formatted string eg. '1 Day 2 Hours' or 1d 2h
		 *
		 * @related prettyAge
		 *
		 * @param integer $intSeconds Time in seconds for conversion
		 * @param bool $blShortLabels Use short labels (y, mo, w) rather than full labels (year, month, week)
		 * @return string
		 */
		public function prettyTime($intSeconds,$blShortLabels = false){

			$strUptime = '';
			$arrLimits = array($this->fltYearDays * 86400, ($this->fltYearDays / 84) * 604800, 604800, 86400, 3600, 60);
			$arrLimitLabels = $blShortLabels ? array('y', 'mo', 'w', 'd', 'h', 'm') : array('year','month','week','day','hour','minute');

			foreach($arrLimits as $intLimitIndex => $strLimitValue){

				if($intSeconds >= $arrLimits[$intLimitIndex]){

					if($blShortLabels){
						$strUptime .= floor($intSeconds / $arrLimits[$intLimitIndex]) . $arrLimitLabels[$intLimitIndex];
					}else{
						$strUptime .= floor($intSeconds / $arrLimits[$intLimitIndex]) . ' ' . $arrLimitLabels[$intLimitIndex] . ((floor($intSeconds / $arrLimits[$intLimitIndex]) === 1) ? '' : 's');
					}

					$intSeconds -= floor($intSeconds / $arrLimits[$intLimitIndex]) * $arrLimits[$intLimitIndex];

					if($intSeconds === 0){
						return $strUptime;
					}else{
						$strUptime .= ' ';
					}

				}elseif($strUptime !== ''){

					if($blShortLabels){
						$strUptime .= '0'.$arrLimitLabels[$intLimitIndex];
					}else{
						$strUptime .= '0 '.$arrLimitLabels[$intLimitIndex].'s ';
					}

					if($intSeconds !== 0){
						$strUptime .= ' ';
					}
				}
			}

			if($blShortLabels){
				return $strUptime.$intSeconds.'s';
			}else{
				return $strUptime.$intSeconds.' seconds';
			}

		}

		/**
		 * @alias prettyAge
		 * @param $intTimestamp
		 * @return string
		 */
		public function getAge($intTimestamp){ return $this->prettyAge($intTimestamp); }

		/**
		 * @alias prettyTime
		 * @param $intTimestamp
		 * @return string
		 */
		public function getTimePeriod($intTimestamp){ return $this->prettyTime($intTimestamp); }

		/**
		 * Get the age of a person in years from their date of birth
		 *
		 * @param string $dateDOB Date of birth as a date string
		 * @return integer Returns age in years
		 */
		public function getPersonAge($dateDOB){

			//Get the timestamp of the DOB
			$intDOB = strtotime($dateDOB);

			//Get the year of the DOB
			$intYearBorn = date('Y',$intDOB);
			//Get the month of the DOB
			$intMonthBorn = date('m',$intDOB);
			//Get the day of the DOB
			$intDayBorn = date('d',$intDOB);
			//Get the number of years since the DOB
			$intAgeYears = date('Y') - $intYearBorn;

			if(date('m') < $intMonthBorn){
				$intAgeYears = $intAgeYears - 1;
			}elseif(date('m') == $intMonthBorn){
				if(date('d') < $intDayBorn){
					$intAgeYears = $intAgeYears - 1;
				}
			}

			return $intAgeYears;
		}

		/**
		 * Get an array of every X day between two given dates
		 *
		 * @param string $dateStart Start date of the range
		 * @param string $dateEnd End date of the range
		 * @param integer $intWeekdayNumber
		 * @return array Returns and array of dates
		 */
		public function getDayBetweenDates($dateStart, $dateEnd, $intWeekdayNumber){

			$intStartDate = strtotime($dateStart);
			$intEndDate = strtotime($dateEnd);

			$arrDates = array();

			do{
				if(date("w", $intStartDate) != $intWeekdayNumber){
					$intStartDate += (24 * 3600); // add 1 day
				}
			}while(date("w", $intStartDate) != $intWeekdayNumber);


			while($intStartDate <= $intEndDate){
				$arrDates[] = date('Y-m-d', $intStartDate);
				$intStartDate += (7 * 24 * 3600); // add 7 days
			}

			return $arrDates;
		}
	}