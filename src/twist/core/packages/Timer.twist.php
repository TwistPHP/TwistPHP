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
	use \Twist\Core\Classes\PackageBase;

	/**
	 * Timer to capture the time taken
	 */
	class Timer extends PackageBase{

		protected $strInstanceKey = '';
		protected $arrTimer = array();

		public function __construct($strInstanceKey){
			$this->strInstanceKey = $strInstanceKey;
		}

		protected function getMicroTime($strCustomStart = null){
			$mxdMicroTime = (!is_null($strCustomStart)) ? $strCustomStart : microtime();
			$arrMicroTimeParts = explode(" ",$mxdMicroTime);
			$intMicroTime = $arrMicroTimeParts[1] + $arrMicroTimeParts[0];
			return $intMicroTime;
		}

		/**
		 * Start a new timer, pass in a unique key to reference the timer with
		 * @param $strReference
		 */
		public function start(){

			//if(count($this->arrTimer)){
			//	trigger_error(sprintf("Error, Timer for '%s' already started",$this->strInstanceKey),E_TWIST_NOTICE);
			//}else{
				$this->arrTimer = array(
					'time' => \Twist::DateTime()->time(),
					'start' => $this->getMicroTime( (in_array($this->strInstanceKey,array('TwistPageLoad'))) ? $_SERVER['TWIST_BOOT'] : null ),
					'end' => 0,
					'total' => 0,
					'log' => array()
				);
			//}
		}

		/**
		 * Stop the timer, this timer cannot that be used any further
		 * @param $strReference
		 * @return mixed
		 */
		public function stop(){
			$this->arrTimer['end'] = $this->getMicroTime();
			$this->arrTimer['total'] = ($this->arrTimer['end'] - $this->arrTimer['start']);

			if(strstr($this->arrTimer['total'],'E')){
				$this->arrTimer['total'] = 0;
			}

			return $this->arrTimer;
		}

		/**
		 * Clear the timer results from the system
		 * @param $strReference
		 */
		public function clear(){
			$this->arrTimer = array();
		}

		/**
		 * Get the full results from any given timer
		 * @param $strReference
		 * @return mixed
		 */
		public function results(){
			return $this->arrTimer;
		}

		/**
		 * Get the timers' current length but do not stop the timer
		 * @param $strReference
		 * @return int
		 */
		public function log($strLogKey){
			$intTotalTime = ($this->getMicroTime() - $this->arrTimer['start']);
			$this->arrTimer['log'][$strLogKey] = (strstr($intTotalTime,'E')) ? 0 : $intTotalTime;
			return $this->arrTimer['log'][$strLogKey];
		}
	}