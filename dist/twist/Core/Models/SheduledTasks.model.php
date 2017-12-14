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

		public static function getAll(){
			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->all();
		}

		public static function activeTasks(){

			$arrRun = array();
			$arrRun[] = 1;

			$intMinute = date('m');
			$intHour = date('h');

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

			return \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->find($arrRun,'frequency');
		}

		public static function run($intTaskID){

			$arrTask = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($intTaskID,'id');


		}

		public static function log($intTaskID){

			$arrTask = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'scheduled_tasks')->get($intTaskID,'id');

		}

	}