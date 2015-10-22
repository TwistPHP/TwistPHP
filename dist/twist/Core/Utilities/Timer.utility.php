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
	 * Timer to capture the time taken
	 */
	class Timer extends Base{

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
		 * @param $intStartMicroTime Start time to use if not current time
		 */
		public function start($intStartMicroTime = null){
			$this->arrTimer = array(
				'time' => \Twist::DateTime()->time(),
				'start' => $this->getMicroTime($intStartMicroTime),
				'end' => 0,
				'total' => 0,
				'memory' => array(
					'start' => memory_get_usage(),
					'end' => 0,
					'peak' => 0,
					'limit' => ini_get('memory_limit'),
				),
				'log' => array()
			);
		}

		/**
		 * Stop the timer, this timer cannot that be used any further
		 * @param $strReference
		 * @return mixed
		 */
		public function stop(){
			$this->arrTimer['end'] = $this->getMicroTime();
			$this->arrTimer['total'] = ($this->arrTimer['end'] - $this->arrTimer['start']);
			$this->arrTimer['memory']['end'] = memory_get_usage();
			$this->arrTimer['memory']['peak'] = memory_get_peak_usage();

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
		 * @param $strLogTitle
		 * @return int
		 */
		public function log($strLogTitle){
			$intTotalTime = ($this->getMicroTime() - $this->arrTimer['start']);
			$this->arrTimer['log'][] = array(
				'title' => $strLogTitle,
				'time' => (strstr($intTotalTime,'E')) ? 0 : $intTotalTime,
				'memory' => memory_get_usage()
			);
			return $this->arrTimer['log'][count($this->arrTimer['log'])-1]['time'];
		}
	}