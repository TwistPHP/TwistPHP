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
	 * Execute bash command line commands on the server and get back nicely formatted result array.
	 */
	class Command extends Base{

		protected $intProcessID = 1000;
		protected $arrProcesses = array();

		/**
		 * Pass in the bash command to be executed on the server, the result will be formatted as an array with overall status, return code and error messages in an error array.
		 * You can override the current working directory, by default the current working directory is you document root. Commands can either be written utilising full path stings or they can be relative to the current working directory.
		 * @param string $strCommand Correctly formatted bash command
		 * @param string $dirCurrentWorkingDirectory Override of your current working directory
		 * @return array Formatted array of data containing the fields command, status, output, errors, return
		 */
		public function execute($strCommand,$dirCurrentWorkingDirectory = null){
			return $this->childResult($this->executeChild($strCommand,$dirCurrentWorkingDirectory));
		}

		/**
		 * Get a list of all the child processes running in this current PHP instance
		 * @return array
		 */
		public function childProcesses(){

			$arrOut = array();
			foreach($this->arrProcesses as $intPID => $arrProcessInfo){

				$arrOut[$intPID] = array(
					'pid' => $intPID,
					'command' => $arrProcessInfo['command'],
					'started' => $arrProcessInfo['started'],
					'running' => $this->childRunning($intPID)
				);
			}

			return $arrOut;
		}

		/**
		 * Pass in the bash command to be executed on the server, an PID (Process ID) will be return, to get the result use the resultChild function call.
		 * You can override the current working directory, by default the current working directory is you document root. Commands can either be written utilising full path stings or they can be relative to the current working directory.
		 * @param string $strCommand Correctly formatted bash command
		 * @param string $dirCurrentWorkingDirectory Override of your current working directory
		 * @return int PID (Process ID) to be used with resultChild
		 */
		public function executeChild($strCommand,$dirCurrentWorkingDirectory = null){

			$arrDescriptorSpec = array(
				0 => array("pipe", "r"),//Input Pipe
				1 => array("pipe", "w"),//Output to a Pipe
				//2 => array("file", "/tmp/error-output.txt", "a"),//Output to a file
				2 => array("pipe", "w")//Output to a Pipe
			);

			$dirCurrentWorkingDirectory = (is_null($dirCurrentWorkingDirectory) || !is_dir($dirCurrentWorkingDirectory)) ? TWIST_DOCUMENT_ROOT : $dirCurrentWorkingDirectory;
			$mxdEnvironmentsVars = null;
			$strAdditionalInput = '';

			//$this->arrProcesses[$this->intProcessID]['resource'] = proc_open($strCommand, $arrDescriptorSpec, $this->arrProcesses[$this->intProcessID]['pipes'], $dirCurrentWorkingDirectory, $mxdEnvironmentsVars);

			//if(is_resource($this->arrProcesses[$this->intProcessID]['resource'])){
			//	fwrite($this->arrProcesses[$this->intProcessID]['pipes'][0], $strAdditionalInput);
			//	fclose($this->arrProcesses[$this->intProcessID]['pipes'][0]);
			//}

			$resProcess = proc_open($strCommand, $arrDescriptorSpec, $arrPipes, $dirCurrentWorkingDirectory, $mxdEnvironmentsVars);

			if(is_resource($resProcess)){
				fwrite($arrPipes[0], $strAdditionalInput);
				fclose($arrPipes[0]);

				$arrStats = proc_get_status($resProcess);

				$this->arrProcesses[$arrStats['pid']] = array(
					'pid' => $arrStats['pid'],
					'command' => $strCommand,
					'pipes' => $arrPipes,
					'resource' => $resProcess,
					'started' => date('Y-m-d H:i:s')
				);

				return $arrStats['pid'];
			}

			return false;
		}

		/**
		 * Get the status of a child process by its PID (Process ID)
		 * @param $intPID
		 * @return array|bool
		 */
		public function childStatus($intPID){

			if(array_key_exists($intPID,$this->arrProcesses)){
				return proc_get_status($this->arrProcesses[$intPID]['resource']);
			}

			return false;
		}

		/**
		 * Get a boolean status to say if the process is still running (returns true) or if the results are ready to be collected (returns false).
		 * @param $intPID
		 * @return bool|mixed
		 */
		public function childRunning($intPID){

			$arrData = $this->childStatus($intPID);
			return (is_array($arrData)) ? $arrData['running'] : false;
		}

		/**
		 * Us the internal twist process ID to get the result. It will be formatted as an array with overall status, return code and error messages in an error array.
		 * @param $intPID
		 * @return array
		 */
		public function childResult($intPID){

			$arrOut = array(
				'command' => '',
				'status' => false,
				'output' => null,
				'errors' => null,
				'return' => null
			);

			if(array_key_exists($intPID,$this->arrProcesses)){

				$arrOut['command'] = $this->arrProcesses[$intPID]['command'];

				if(is_resource($this->arrProcesses[$intPID]['resource'])){

					$arrOut['output'] = stream_get_contents($this->arrProcesses[$intPID]['pipes'][1]);
					fclose($this->arrProcesses[$intPID]['pipes'][1]);

					$arrOut['errors'] = stream_get_contents($this->arrProcesses[$intPID]['pipes'][2]);
					fclose($this->arrProcesses[$intPID]['pipes'][2]);

					$arrOut['return'] = (int) proc_close($this->arrProcesses[$intPID]['resource']);
				}

				//Return the status, error is return code is bigger than 1
				$arrOut['status'] = ($arrOut['return'] > 1) ? false : true;

				//Remove the process from the process holder
				unset($this->arrProcesses[$intPID]);
			}

			return $arrOut;
		}

		public function isEnabled(){

			if(ini_get('safe_mode')){
				return false;
			}else{
				$strDisabled = ini_get('disable_functions');

				if($strDisabled != ''){
					$arrDisabled = explode(',', $strDisabled);
					$arrDisabled = array_map('trim', $arrDisabled);
					return !in_array('proc_open', $arrDisabled);
				}
			}

			return true;
		}
	}