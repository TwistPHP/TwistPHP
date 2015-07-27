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

class Upload extends Base{

	public function _construct(){
		$this->resRoute->debugMode(false);
	}

	/**
	 * Upload a file to the site and place in the uploads directory
	 * @param null $strFileKey
	 * @param null $intIndex
	 * @return string
	 */
	public function file($strFileKey = null,$intIndex = null){

		$this->resRoute->debugMode(false);

		if(is_array($_FILES) && count($_FILES)){
			$arrOut = \Twist::File()->upload($strFileKey,null,$intIndex);
		}else{
			$arrOut = \Twist::File()->uploadPUT();
		}

		//Get info about the file type
		$arrInfo = \Twist::File()->mimeTypeInfo($arrOut['file']['path']);

		$arrOut['uri'] = str_replace(TWIST_DOCUMENT_ROOT,'',$arrOut['file']['path']);
		$arrOut['uri_preview'] = $arrInfo['icon'];
		$arrOut['uri_icon'] = $arrInfo['icon'];

		//Output the file type
		$arrOut['file_type'] = $arrInfo['name'];

		//Value to be posted in a form for this file type
		$arrOut['form_value'] = $arrOut['uri'];

		return json_encode($arrOut);
	}

	/**
	 * Upload an asset to the system and create relevant thumbnails of images.
	 * @param null $strFileKey
	 * @param null $intIndex
	 * @return string
	 */
	public function asset($strFileKey = null,$intIndex = null){

		$this->resRoute->debugMode(false);

		$arrOut = json_decode($this->file($strFileKey,$intIndex),true);

		//Now if the file upload was successful process the asset (if required)
		if($arrOut['status']){

			$intAssetID = \Twist::Asset()->add($arrOut['file']['path'],1);
			$arrAsset = \Twist::Asset()->get($intAssetID);

			//Get info about the file type
			$arrInfo = \Twist::File()->mimeTypeInfo($arrOut['file']['path']);

			//Add 2 additional parameters to the output
			$arrOut['uri'] = $arrAsset['uri'];
			$arrOut['uri_preview'] = $arrInfo['icon'];
			$arrOut['uri_icon'] = $arrInfo['icon'];

			//Output the file type
			$arrOut['file_type'] = $arrInfo['name'];

			//Value to be posted in a form for this file type
			$arrOut['form_value'] = $intAssetID;

			//Additional data that only Assets returns
			$arrOut['support'] = $arrAsset['support'];
			$arrOut['asset_id'] = $intAssetID;
		}

		return json_encode($arrOut);
	}
}