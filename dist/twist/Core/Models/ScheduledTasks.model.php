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

	namespace Twist\Core\Models;

	/**
	 * SheduledTask Handler
	 * @package Twist\Core\Models
	 */
	class ScheduledTasks{

		protected static $intProcessorStarted = 0;
		protected static $intMaxRuntime = 70;
		protected static $intPauseBetweenChecks = 1;

		public static function processor(){

			self::pulse();
			self::debug("== Starting TwistCron Manager ==");

			$arrRunningTasks = array();
			self::checkZombies();
			$arrActiveTasks = self::activeTasks();

			if(count($arrActiveTasks)){

				self::debug();
				self::debug("* ".count($arrActiveTasks)." task found");
				self::debug();

				self::$intProcessorStarted = time();

				//Start all the tasks
				foreach($arrActiveTasks as $arrEachTask){
					$intProcessorID = self::callTask($arrEachTask['id']);
					$arrRunningTasks[$intProcessorID] = $arrEachTask['id'];
				}

				//Monitor running processes
				$arrChildProcesses = \Twist::Command()->childProcesses();
				while(count($arrChildProcesses) > 0){

					foreach($arrChildProcesses as $arrEachProcess){

						if($arrEachProcess['running'] === false){
							//Process a finished command
							self::logTask($arrRunningTasks[$arrEachProcess['pid']],$arrEachProcess['pid']);
						}
					}

					$arrChildProcesses = \Twist::Command()->childProcesses();

					//Sleep, we don't want the loop to be very CPU intensive, give the crons time to complete.
					sleep(self::$intPauseBetweenChecks);

					//If the process has run for more than the alloted max runtime kill the process
					if((time() - self::$intProcessorStarted) > self::$intMaxRuntime){
						self::debug("# Aborted, scheduler timeout reached");
						break;
					}
				}

				//If there are still processes left that are un-finished do what with them?
				if(count($arrChildProcesses)){
					//Kill or Leave?
					self::debug();
					self::debug("* ".count($arrChildProcesses)." unfinished tasks, left running");
					self::debug();

					foreach($arrChildProcesses as $arrEachProcess){
						$resTask = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($arrRunningTasks[$arrEachProcess['pid']],'id');
						$resTask->set('status','zombie');
						$resTask->commit();
					}
				}
			}

			self::debug("== Finished TwistCron Manager ==");
		}

		/**
		 * Log a pulse and store it for 24Hours + 10 minutes, keep the last 60 records on file
		 */
		protected static function pulse(){

			$arrPulse = \Twist::Cache('ScheduledTasks')->read('Pulse');

			if(is_null($arrPulse)){
				$arrPulse = array(time());
			}else{
				$arrPulse[] = time();
			}

			if(count($arrPulse) > 60){
				array_shift($arrPulse);
			}

			\Twist::Cache('ScheduledTasks')->write('Pulse',$arrPulse,86400+600);
		}

		/**
		 * Get the Scheduler Pulse Information
		 * @return array
		 */
		public static function pulseInfo(){

			$arrPulse = $arrPulseTemp = \Twist::Cache('ScheduledTasks')->read('Pulse');

			$intLastPulse = is_array($arrPulseTemp) && count($arrPulseTemp) ? array_pop($arrPulseTemp) : 0;
			$intPrevious1Pulse = is_array($arrPulseTemp) && count($arrPulseTemp) ? array_pop($arrPulseTemp) : 0;
			$intPrevious2Pulse = is_array($arrPulseTemp) && count($arrPulseTemp) ? array_pop($arrPulseTemp) : 0;

			$intFreq1 = ($intLastPulse - $intPrevious1Pulse);
			$intFreq2 = ($intPrevious1Pulse - $intPrevious2Pulse);

			return array(
				'active' => is_array($arrPulse) && count($arrPulse) && ($intFreq1 == $intFreq2 || $intFreq1 == ($intFreq2-1) || $intFreq1 == ($intFreq2+1)),
				'status' => (is_array($arrPulse) && count($arrPulse)) ? (($intFreq1 == $intFreq2 || $intFreq1 == ($intFreq2-1) || $intFreq1 == ($intFreq2+1)) ? 'Active' : 'Detecting...') : 'Inactive',
				'last_pulse' => $intLastPulse,
				'frequency' => $intFreq1,
				'history' => $arrPulse
			);
		}

		public static function get($intTaskID){
			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($intTaskID,'id',true);
		}

		public static function getAll(){
			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->all();
		}

		public static function checkZombies(){

			foreach(\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->find('zombie','status') as $arrEachTask){

				//Check to see if a Zombie task is still alive
				if(posix_getsid($arrEachTask['pid']) === false){

					//Mark the Zombie task as finished, dont log a runtime (for now)
					$resTask = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($arrEachTask['id'],'id');
					$resTask->set('status','finished');
					$resTask->set('pid',0);
					$resTask->commit();
				}
			}
		}

		public static function activeTasks(){

			$arrRun = array();
			$arrRun[] = 1;

			$intMinute = (int) date('i');
			$intHour = (int) date('H');

			if($intMinute == 0 || !($intMinute&1)){
				$arrRun[] = 2;
			}

			if(in_array($intMinute,array(0,5,10,15,20,25,30,35,40,45,50,55))){
				$arrRun[] = 5;
			}

			if(in_array($intMinute,array(0,10,20,30,40,50))){
				$arrRun[] = 10;
			}

			if(in_array($intMinute,array(0,15,30,45))){
				$arrRun[] = 15;
			}

			if(in_array($intMinute,array(0,20,40))){
				$arrRun[] = 20;
			}

			if(in_array($intMinute,array(0,30))){
				$arrRun[] = 30;
			}

			if(in_array($intMinute,array(0))){
				$arrRun[] = 60;
			}

			if($intHour == 0 || !($intHour&1)){
				$arrRun[] = 120;
			}

			if(in_array($intHour,array(0,4,8,12,16,20))){
				$arrRun[] = 240;
			}

			if(in_array($intHour,array(0,6,12,18))){
				$arrRun[] = 360;
			}

			if(in_array($intHour,array(0,12))){
				$arrRun[] = 720;
			}

			if($intHour == 0 && $intMinute == 0){
				$arrRun[] = 1440;
			}

			//Add the current time for those that run at a specific time, remove leading 0 to support both variations of time format
			$arrRun[] = date('H:i');
			$arrRun[] = ltrim(date('H:i'),'0');

			$arrOut = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->find($arrRun,'frequency');

			foreach($arrOut as $intKey => $arrEachTask){
				if($arrEachTask['enabled'] == '0' || $arrEachTask['status'] == 'zombie' || $arrEachTask['status'] == 'running'){
					unset($arrOut[$intKey]);
				}
			}

			return $arrOut;
		}

		public static function callTask($intTaskID){

			$resTask = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($intTaskID,'id');

			//@TODO - Non-Twist tasks have to be built in here
			$strPHPBin = (!empty(\Twist::framework()->setting('PHP_BIN'))) ? \Twist::framework()->setting('PHP_BIN') : 'php';

			self::debug("- Start: ".$resTask->get('description'));
			$strCommand = sprintf('twist_cron_child=%d %s '.rtrim(TWIST_PUBLIC_ROOT,'/').'/index.php',$intTaskID,$strPHPBin);

			$intPID = \Twist::Command()->executeChild($strCommand);

			//Store the last run of the current task in the database
			$resTask->set('last_run',date('Y-m-d H:i:s'));
			$resTask->set('status','running');
			$resTask->set('pid',$intPID);
			$resTask->commit();

			return $intPID;
		}

		public static function logTask($intTaskID,$intPID){

			$resTask = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($intTaskID,'id');

			//Get the process results
			$arrResult = \Twist::Command()->childResult($intPID);

			//Store the last run of the current task in the database
			$resTask->set('runtime',(time() - strtotime($resTask->get('last_run'))));
			$resTask->set('status','finished');
			$resTask->set('pid',0);
			$resTask->commit();

			self::debug("- End: ".$resTask->get('description')." [Runtime: ".$resTask->get('runtime')." sec]");

			//Log the result, this replaces the previous result
			\Twist::Cache('ScheduledTasks/logs')->write('task-'.$intTaskID,$arrResult,86400);

			//Log a result history if required, only keep the amount stated by history
			if($resTask->get('history') > 0){

				$arrHistory = \Twist::Cache('ScheduledTasks/history')->read('task-'.$intTaskID);
				$arrHistory[] = $arrResult['output'];

				if(count($arrHistory) > $resTask->get('history')){
					array_shift($arrHistory);
				}

				\Twist::Cache('ScheduledTasks/history')->write('task-'.$intTaskID,$arrHistory,86400);
			}

			//Send the result via email if required
			if($resTask->get('email') != '' && trim($arrResult['output']) != ''){

				\Twist::Email()->send(
					$resTask->get('email'),
					'Twist Scheduled Task ['.$intTaskID.']: Report',
					"Debug report for Twist Scheduled Task [{$intTaskID}]\n\n".$arrResult['output'],
					'report@'.\Twist::framework()->setting('SITE_HOST'),
					false
				);
			}
		}

		public static function lastResult($intTaskID){
			return \Twist::Cache('ScheduledTasks/logs')->read('task-'.$intTaskID);
		}

		public static function history($intTaskID){
			return \Twist::Cache('ScheduledTasks/history')->read('task-'.$intTaskID);
		}

		protected static function debug($strMessage = ""){
			echo $strMessage."\n";
		}

		/**
		 * Called by a child twist task being run by he master twist cron
		 * @param $intTaskID
		 */
		public static function runTask($intTaskID){

			$arrTask = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($intTaskID,'id',true);

			ini_set('max_execution_time',0);
			ignore_user_abort(true);
			set_time_limit(0);

			if(count($arrTask)){
				require_once (string) rtrim(TWIST_PUBLIC_ROOT,'/').'/'.$arrTask['command'];
			}else{
				echo "Error Invalid Task";
			}
		}

		/**
		 * Create a new task in the system
		 * @param $strDescription
		 * @param $strFrequency
		 * @param $strCommand
		 * @param int $intKeepHistory
		 * @param string $strEmail
		 * @param bool $blEnabled
		 * @param string $strPackageSlug
		 * @return bool|int
		 * @throws \Exception
		 */
		public static function createTask($strDescription, $strFrequency, $strCommand, $intKeepHistory = 0, $strEmail = '', $blEnabled = true, $strPackageSlug = ''){

			$resSchedule = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->create();
			$resSchedule->set('description',$strDescription);
			$resSchedule->set('frequency',$strFrequency);
			$resSchedule->set('command',$strCommand);
			$resSchedule->set('history',$intKeepHistory);
			$resSchedule->set('email',$strEmail);
			$resSchedule->set('enabled',($blEnabled) ? '1' : '0');
			$resSchedule->set('status','new');
			$resSchedule->set('package_slug',$strPackageSlug);

			return $resSchedule->commit();
		}

		/**
		 * Edit an existing task in the system, the package name is non editable as this is an automated feature
		 * @param $intTaskID
		 * @param $strDescription
		 * @param $strFrequency
		 * @param $strCommand
		 * @param int $intKeepHistory
		 * @param string $strEmail
		 * @param bool $blEnabled
		 * @return bool|int
		 * @throws \Exception
		 */
		public static function editTask($intTaskID,$strDescription, $strFrequency, $strCommand, $intKeepHistory = 0, $strEmail = '', $blEnabled = true){

			$resSchedule = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($intTaskID);
			$resSchedule->set('description',$strDescription);
			$resSchedule->set('frequency',$strFrequency);
			$resSchedule->set('command',$strCommand);
			$resSchedule->set('history',$intKeepHistory);
			$resSchedule->set('email',$strEmail);
			$resSchedule->set('enabled',($blEnabled) ? '1' : '0');

			return $resSchedule->commit();
		}

		/**
		 * Delete a particular task by its ID
		 * @param $intTaskID
		 * @return bool
		 */
		public static function deleteTask($intTaskID){
			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->delete($intTaskID,'id');
		}

		/**
		 * Remove all the scheduled tasks for a particular package
		 * @param $strPackageSlug
		 * @return bool
		 */
		public static function deletePackageTasks($strPackageSlug){
			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->delete($strPackageSlug,'package_slug',null);
		}
	}