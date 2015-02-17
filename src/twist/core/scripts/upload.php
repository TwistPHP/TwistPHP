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

	require_once sprintf('%s/../../framework.php',dirname(__FILE__));

	if(is_array($_FILES) && count($_FILES)){
		$arrOut = Twist::File()->upload('');
	}else{
		$arrOut = Twist::File()->uploadPUT();
	}

	//Now if the file upload was successful process the asset (if required)
	if($arrOut['status'] == true && (array_key_exists('HTTP_TWIST_PROCESS',$_SERVER) && $_SERVER['HTTP_TWIST_PROCESS'] == 'asset' || array_key_exists('twist_process',$_GET) && $_GET['twist_process'] == 'asset')){

		$intAssetID = Twist::Asset()->add($arrOut['file']['path'],1);
		$arrAsset = Twist::Asset()->get($intAssetID);

		//Add 2 additional parameters to the output
		$arrOut['uri'] = $arrAsset['data'];
		$arrOut['support'] = $arrAsset['support'];
		$arrOut['type'] = $arrAsset['type']['slug'];
		$arrOut['asset_id'] = $intAssetID;
	}

	echo json_encode($arrOut);