<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
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

namespace Twist\Core\Utilities;

/**
 * Execute bash command line commands on the server and get back nicely formatted result array.
 */
class Command extends Base{

	/**
	 * Pass in the bash command to be executed on the server, the result will be formatted as an array with overall status, return code and error messages in an error array.
	 * You can override the current working directory, by default the current working directory is you document root. Commands can either be written utilising full path stings or they can be relative to the current working directory.
	 * @param string $strCommand Correctly formatted bash command
	 * @param string $dirCurrentWorkingDirectory Override of your current working directory
	 * @return array Formatted array of data containing the fields command, status, output, errors, return
	 */
	public function execute($strCommand,$dirCurrentWorkingDirectory = null){

		$arrDescriptorSpec = array(
			0 => array("pipe", "r"),//Input Pipe
			1 => array("pipe", "w"),//Output to a Pipe
			//2 => array("file", "/tmp/error-output.txt", "a"),//Output to a file
			2 => array("pipe", "w")//Output to a Pipe
		);

		$dirCurrentWorkingDirectory = (is_null($dirCurrentWorkingDirectory) || !is_dir($dirCurrentWorkingDirectory)) ? TWIST_DOCUMENT_ROOT : $dirCurrentWorkingDirectory;
		$mxdEnvironmentsVars = null;
		$strAdditionalInput = '';

		$resProcess = proc_open($strCommand, $arrDescriptorSpec, $arrPipes, $dirCurrentWorkingDirectory, $mxdEnvironmentsVars);

		$arrOut = array(
			'command' => $strCommand,
			'status' => false,
			'output' => null,
			'errors' => null,
			'return' => null
		);

		if(is_resource($resProcess)){

			fwrite($arrPipes[0], $strAdditionalInput);
			fclose($arrPipes[0]);

			$arrOut['output'] = stream_get_contents($arrPipes[1]);
			fclose($arrPipes[1]);

			$arrOut['errors'] = stream_get_contents($arrPipes[2]);
			fclose($arrPipes[2]);

			$arrOut['return'] = (int) proc_close($resProcess);
		}

		//Return the status, error is return code is bigger than 1
		$arrOut['status'] = ($arrOut['return'] > 1) ? false : true;

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