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
use TwistPHP\ModuleBase;

/**
 * Execute bash command line commands on the server and get back nicely formatted result array.
 */
class Command extends ModuleBase{

	/**
	 * Pass in the bash command to be executed on the server, the result will be formatted as an array with overall status, return code and error messages in an error array
	 * @param $strCommand Correctly formatted bash command
	 * @return array
	 */
	public function execute($strCommand){

		$arrDescriptorSpec = array(
			0 => array("pipe", "r"),//Input Pipe
			1 => array("pipe", "w"),//Output to a Pipe
			//2 => array("file", "/tmp/error-output.txt", "a"),//Output to a file
			2 => array("pipe", "w")//Output to a Pipe
		);

		$strCurrentWorkingDirectory = getcwd();
		$mxdEnvironmentsVars = null;
		$strAdditionalInput = '';

		$resProcess = proc_open($strCommand, $arrDescriptorSpec, $arrPipes, $strCurrentWorkingDirectory, $mxdEnvironmentsVars);

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

			$arrOut['errors'] = stream_get_contents($arrPipes[1]);
			fclose($arrPipes[1]);

			$arrOut['output'] = stream_get_contents($arrPipes[2]);
			fclose($arrPipes[2]);

			$arrOut['return'] = (int) proc_close($resProcess);
		}

		//Return the status, error is return code is bigger than 1
		$arrOut['status'] = ($arrOut['return'] > 1) ? false : true;

		return $arrOut;
	}

}