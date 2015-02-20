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

	namespace Twist\Core;

	/**
	 * Usage and Performance stats of the the framework. Page load and creation timings can be accessed from here.
	 */
	final class Status{

		protected $arrTimes = array();

		public function enable(){

		}

		protected function getMicroTime($strCustomStart = null){
			$mxdMicroTime = (!is_null($strCustomStart)) ? $strCustomStart : microtime();
			$arrMicroTimeParts = explode(" ",$mxdMicroTime);
			$intMicroTime = $arrMicroTimeParts[1] + $arrMicroTimeParts[0];
			return $intMicroTime;
		}

		public function timerStart($strReference){

			if(array_key_exists($strReference,$this->arrTimes)){
				trigger_error(sprintf("Error, Timer for '%s' already started",$strReference),E_TWIST_NOTICE);
			}else{
				$this->arrTimes[$strReference] = array(
					'time' => \Twist::DateTime()->time(),
					'start_microtime' => $this->getMicroTime( (in_array($strReference,array('TwistPHPBoot','TwistPageLoad'))) ? $_SERVER['TWIST_BOOT'] : null ),
					'end_microtime' => 0,
					'total_time' => 0
				);
			}
		}

		public function timerStop($strReference){

			$this->arrTimes[$strReference]['end_microtime'] = $this->getMicroTime();
			$this->arrTimes[$strReference]['total_time'] = ($this->arrTimes[$strReference]['end_microtime'] - $this->arrTimes[$strReference]['start_microtime']);

			if(strstr($this->arrTimes[$strReference]['total_time'],'E')){
				$this->arrTimes[$strReference]['total_time'] = 0;
			}

			return $this->arrTimes[$strReference];
		}

		public function timerClear($strReference){
			unset($this->arrTimes[$strReference]);
		}

		public function timerResults($strReference){
			return $this->arrTimes[$strReference];
		}

		public static function performanceLog(){

			//echo "<pre>".print_r($this->arrTimes,true)."</pre>";

			\Twist::framework() -> status() -> timerStop('TwistPHP');
			$arrTimerStatus = \Twist::framework() -> status() -> timerResults('TwistPHP');
		}
	}