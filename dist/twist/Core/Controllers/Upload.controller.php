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

namespace Twist\Core\Controllers;

/**
 *  The route controller for the File Upload manager that is used by the inline file and asset uploader.
 *  This route can be easily added to your site by calling the Twist::Route()->upload() alias function.
 * @package Twist\Core\Controllers
 */
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
		$arrOut = $this->storeFile($strFileKey,$intIndex);

		//Now if the file upload was successful process the asset (if required)
		if($arrOut['status']){

			//Get info about the file type
			$arrInfo = \Twist::File()->mimeTypeInfo($arrOut['file']['path']);

			$arrOut['uri'] = str_replace(TWIST_DOCUMENT_ROOT,'',$arrOut['file']['path']);
			$arrOut['uri_preview'] = $arrInfo['icon'];
			$arrOut['uri_icon'] = $arrInfo['icon'];

			//Output the file type
			$arrOut['file_type'] = $arrInfo['name'];

			//Value to be posted in a form for this file type
			$arrOut['form_value'] = $arrOut['uri'];
		}

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
		$arrOut = $this->storeFile($strFileKey,$intIndex);

		//Now if the file upload was successful process the asset (if required)
		if($arrOut['status']){

			//Get the asset group to be used for the upload
			$arrAssetGroup = array();
			$intAssetGroup = \Twist::framework()->setting('ASSET_DEFAULT_GROUP');

			//Allow for a URI to be registered such as: Twist::Route()->upload('/account/upload/{function}/{asset_group}');
			if($this->_var('asset_group') != null && $this->_var('asset_group') != ''){
				$arrAssetGroup = \Twist::Asset()->getGroupBySlug($this->_var('asset_group'));
			}else{
				$arrDynamicRoute = $this->_route('dynamic');

				if(count($arrDynamicRoute)){
					if(count($arrDynamicRoute) > 1 && $arrDynamicRoute[0] == 'asset'){
						$arrAssetGroup = \Twist::Asset()->getGroupBySlug($arrDynamicRoute[1]);
					}elseif($arrDynamicRoute[0] !== 'asset'){
						$arrAssetGroup = \Twist::Asset()->getGroupBySlug($arrDynamicRoute[0]);
					}
				}
			}

			if(count($arrAssetGroup)){
				$intAssetGroup = $arrAssetGroup['id'];
			}

			$intAssetID = \Twist::Asset()->add($arrOut['file']['path'],$intAssetGroup);
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

	protected function storeFile($strFileKey = null,$intIndex = null){

		if(is_array($_FILES) && count($_FILES)){
			$arrOut = \Twist::File()->upload($strFileKey,null,$intIndex);
		}else{
			$arrOut = \Twist::File()->uploadPUT();
		}

		return $arrOut;
	}
}