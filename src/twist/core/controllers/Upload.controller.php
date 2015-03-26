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

namespace Twist\Core\Controllers;
use Twist\Core\Classes\BaseController;

class Upload extends BaseController{

	public function _default(){

		$arrRoute = $this->_route();

		//@todo finish this part of the upload script
		$arrFunctions = array('test','test2');

		//First of all check that the third party function exists
		if(in_array($arrRoute['dynamic'][0],$arrFunctions)){

			//Accept the uploaded file to the server
			$arrOut = json_decode($this->file());

			//We can then pass on the data to a third party function
			if($arrOut['status'] == true){

			}

			//We then return the output
			return json_encode($arrOut);
		}else{
			//Invalid function call, return fallback (404 page by default)
			return $this->_fallback();
		}
	}

	public function file(){

		if(is_array($_FILES) && count($_FILES)){
			$arrOut = \Twist::File()->upload('');
		}else{
			$arrOut = \Twist::File()->uploadPUT();
		}

		return json_encode($arrOut);
	}

	public function asset(){

		$arrOut = json_decode($this->file());

		//Now if the file upload was successful process the asset (if required)
		if($arrOut['status'] == true && (array_key_exists('HTTP_TWIST_PROCESS',$_SERVER) && $_SERVER['HTTP_TWIST_PROCESS'] == 'asset' || array_key_exists('twist_process',$_GET) && $_GET['twist_process'] == 'asset')){

			$intAssetID = \Twist::Asset()->add($arrOut['file']['path'],1);
			$arrAsset = \Twist::Asset()->get($intAssetID);

			//Add 2 additional parameters to the output
			$arrOut['uri'] = $arrAsset['data'];
			$arrOut['support'] = $arrAsset['support'];
			$arrOut['type'] = $arrAsset['type']['slug'];
			$arrOut['asset_id'] = $intAssetID;
		}

		return json_encode($arrOut);
	}
}