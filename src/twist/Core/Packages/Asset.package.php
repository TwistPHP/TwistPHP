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

	namespace Twist\Core\Packages;
	use \Twist\Core\Classes\BasePackage;

	/**
	 * Asset management package that allows the addition and grouping of assets. Assets can be images, videos, files, links and other data that can be uploaded to a website/service.
	 * @package TwistPHP\Packages
	 */
	class Asset extends BasePackage{

		protected $resTemplate = null;
		protected $strAssetDirectory = null;

		public $arrTypes = array();
		public $arrTypeSlugs = array();
		public $arrGroups = array();
		public $arrGroupSlugs = array();
		public $arrGroupTree = array();

		public function __construct(){

			$this->resTemplate = \Twist::View('pkgAsset');
			$this->resTemplate->setDirectory( sprintf('%s/asset/',TWIST_FRAMEWORK_VIEWS));

			$this->strAssetDirectory = TWIST_APP_ASSETS;

			//Create the asset directory if it not exist
			if(!file_exists($this->strAssetDirectory)){
				mkdir($this->strAssetDirectory);
			}

			//Pre-cache the types and groups ready to be used
			$this->arrTypes = $this->framework()->tools()->arrayReindex(\Twist::Database()->getAll(sprintf('%sasset_types',TWIST_DATABASE_TABLE_PREFIX)),'id');
			$this->arrTypeSlugs = $this->framework()->tools()->arrayReindex($this->arrTypes,'slug');

			$this->arrGroups = $this->framework()->tools()->arrayReindex(\Twist::Database()->getAll(sprintf('%sasset_groups',TWIST_DATABASE_TABLE_PREFIX)),'id');
			$this->arrGroupSlugs = $this->framework()->tools()->arrayReindex($this->arrGroups,'slug');
			$this->arrGroupTree = $this->framework()->tools()->arrayRelationalTree($this->arrGroups,'id','parent');
		}

		/**
		 * Get an asset by Asset ID, this will also expand the asset to include a sub array of its type and group information
		 *
		 * @param $intAssetID ID of the required asset
		 * @return array Returns an array of the assets information
		 */
		public function get($intAssetID){

			$arrAsset = \Twist::Database()->get(sprintf('%sassets',TWIST_DATABASE_TABLE_PREFIX),$intAssetID);
			$arrAsset = (count($arrAsset)) ? $this->expand($arrAsset) : array();

			return $arrAsset;
		}

		/**
		 * Get all the assets in the asset system, this will also expand the asset to include a sub array of its type and group information
		 *
		 * @related get
		 * @return array Returns a multi-dimensional array of all the assets in the system
		 */
		public function getAll($strOrderBy='added',$strOrderDirection='DESC'){

			$arrOut = array();
			$arrAssets = \Twist::Database()->getAll(sprintf('%sassets',TWIST_DATABASE_TABLE_PREFIX),$strOrderBy,$strOrderDirection);

			if(count($arrAssets)){
				foreach($arrAssets as $arrEachAsset){
					$arrOut[$arrEachAsset['id']] = $this->expand($arrEachAsset);
				}
			}

			return $arrOut;
		}

		/**
		 * Get all assets that of a asset group by Asset Group ID, this will also expand the asset to include a sub array of its type and group information
		 *
		 * @related get
		 * @param $intGroupID ID of the required asset group
		 * @return array Returns a multi-dimensional array of the groups assets
		 */
		public function getByGroup($intGroupID,$strOrderBy='added',$strOrderDirection='DESC'){

			$arrOut = array();
			$arrAssets = \Twist::Database()->find(sprintf('%sassets',TWIST_DATABASE_TABLE_PREFIX),$intGroupID,'group_id',$strOrderBy,$strOrderDirection);

			if(count($arrAssets)){
				foreach($arrAssets as $arrEachAsset){
					$arrOut[$arrEachAsset['id']] = $this->expand($arrEachAsset);
				}
			}

			return $arrOut;
		}

		/**
		 * Get all assets of a particular type by Asset Type ID, this will also expand the asset to include a sub array of its type and group information
		 *
		 * @related get
		 * @param $intTypeID ID of the required asset type
		 * @return array Returns a multi-dimensional array of assets
		 */
		public function getByType($intTypeID,$strOrderBy='added',$strOrderDirection='DESC'){

			$arrOut = array();
			$arrAssets = \Twist::Database()->find(sprintf('%sassets',TWIST_DATABASE_TABLE_PREFIX),$intTypeID,'type_id',$strOrderBy,$strOrderDirection);

			if(count($arrAssets)){
				foreach($arrAssets as $arrEachAsset){
					$arrOut[$arrEachAsset['id']] = $this->expand($arrEachAsset);
				}
			}

			return $arrOut;
		}

		/**
		 * Expand the assets default array of date to include extra data such as detailed type/group information
		 *
		 * @param $arrAsset Default asset array before expansion
		 * @return array Expanded asset array
		 */
		private function expand($arrAsset){

			$strFileName = \Twist::File()->name($arrAsset['data']);
			$arrNameData = explode('-',$strFileName);
			unset($arrNameData[0]);

			$arrAsset['path'] = sprintf('%s%s',TWIST_APP_ASSETS,$arrAsset['data']);
			$arrAsset['uri'] = str_replace(TWIST_DOCUMENT_ROOT,'',$arrAsset['path']);
			$arrAsset['filename'] = $strFileName;
			$arrAsset['original_filename'] = implode('-',$arrNameData);

			$arrAsset['type'] = $this->getType($arrAsset['type_id']);

            //Replace standard type icon with the new method of detection
            $arrMimeType = \Twist::File()->mimeTypeInfo($arrAsset['data']);
            $arrAsset['icon'] = $arrAsset['type']['icon'] = $arrMimeType['icon'];

			$arrAsset['group'] = $this->getGroup($arrAsset['group_id']);
			$arrAsset['support'] = $this->getSupportingContent($arrAsset);

			return $arrAsset;
		}

		/**
		 * Get all the supporting content for this asset, this includes thumbnails and alternative sizes/formats. If none are found the default icon set will be returned.
		 *
		 * @param $arrAsset Default asset array before expansion
		 * @return array Returns array of supporting content
		 */
		public function getSupportingContent($arrAsset){

			$arrOut = array();
			$arrSupport = \Twist::Database()->find(sprintf('%sasset_support',TWIST_DATABASE_TABLE_PREFIX),$arrAsset['id'],'asset_id');

			if(count($arrSupport)){
				foreach($arrSupport as $arrEachItem){
					$arrOut[$arrEachItem['type']] = $arrEachItem['data'];
				}
			}else{
				$arrOut = $this->getDefaultSupportingContent($arrAsset);
			}

			return $arrOut;
		}

		/**
		 * Get all the default content icons the the assets type
		 *
		 * @related getSupportingContent
		 * @param $arrAsset Default asset array before expansion
		 * @return array Returns array of default content icons
		 */
		public function getDefaultSupportingContent($arrAsset){

			$arrOut = array(
				'square-thumb-512' => $arrAsset['type']['icon'],
				'square-thumb-256' => $arrAsset['type']['icon'],
				'square-thumb-128' => $arrAsset['type']['icon'],
				'square-thumb-64' => $arrAsset['type']['icon'],
				'square-thumb-32' => $arrAsset['type']['icon'],
				'thumb-512' => $arrAsset['type']['icon'],
				'thumb-256' => $arrAsset['type']['icon'],
				'thumb-128' => $arrAsset['type']['icon'],
				'thumb-64' => $arrAsset['type']['icon'],
				'thumb-32' => $arrAsset['type']['icon']
			);

			return $arrOut;
		}

		/**
		 * Get an array of asset type information by its asset type ID
		 *
		 * @param $intTypeID ID of the required asset type
		 * @return array Returns an array of the asset type information
		 */
		public function getType($intTypeID){
			return (array_key_exists($intTypeID,$this->arrTypes)) ? $this->arrTypes[$intTypeID] : array();
		}

		/**
		 * Get an array of asset type information by its asset type slug
		 *
		 * @related getType
		 * @param $strTypeSlug Slug of the required asset type
		 * @return array Returns an array of the asset type information
		 */
		public function getTypeBySlug($strTypeSlug){
			return (array_key_exists($strTypeSlug,$this->arrTypeSlugs)) ? $this->arrTypeSlugs[$strTypeSlug] : array();
		}

		/**
		 * Get an array of asset group information by its asset group ID
		 *
		 * @param $intGroupID ID of the required asset group
		 * @return array Returns an array of the asset group information
		 */
		public function getGroup($intGroupID){
			return (array_key_exists($intGroupID,$this->arrGroups)) ? $this->arrGroups[$intGroupID] : array();
		}

		/**
		 * Get an array of asset group information by its asset group slug
		 *
		 * @related getGroup
		 * @param $strGroupSlug Slug of the required asset type
		 * @return array Returns an array of the asset group information
		 */
		public function getGroupBySlug($strGroupSlug){
			return (array_key_exists($strGroupSlug,$this->arrGroupSlugs)) ? $this->arrGroupSlugs[$strGroupSlug] : array();
		}

		/**
		 * Get a multi-dimensional array of all the asset groups registered in the system
		 *
		 * @related getGroup
		 * @return array Returns a multi-dimensional array of asset groups
		 */
		public function getGroups(){
			return $this->arrGroups;
		}

		/**
		 * Get a multi-dimensional array in a parent/child configuration of all the asset groups registered in the system
		 *
		 * @related getGroup
		 * @return array Returns a multi-dimensional parent/child array of the groups assets
		 */
		public function getGroupTree(){
			return $this->arrGroupTree;
		}

		/**
		 * Add a new group to the asset groups table, the asset groups will allow you slit/categorise your assets into manageable groups.
		 * @related getGroup
		 *
		 * @param $strDescription Description of the group
		 * @param $srtSlug Slug of the group, used to reference the group
		 * @return int ID of the newly created group
		 */
		public function addGroup($strDescription,$srtSlug){

			//Create the asset group record in the database
			$resRecord = \Twist::Database()->createRecord(sprintf('%sasset_groups',TWIST_DATABASE_TABLE_PREFIX));
			$resRecord->set('description',$strDescription);
			$resRecord->set('slug',$srtSlug);
			$resRecord->set('created',\Twist::DateTime()->date('Y-m-d H:i:s'));

			return $resRecord->commit();
		}

		/**
		 * Update a asset group, change the group description and slug without affecting the assets contained within the group.
		 *
		 * @param $intGroupID ID of the asset group to be updated
		 * @param $strDescription Description of the group
		 * @param $srtSlug Slug of the group, used to reference the group
		 * @return bool Returns the status of the update
		 */
		public function editGroup($intGroupID,$strDescription,$srtSlug){

			//Create the asset group record in the database
			$resRecord = \Twist::Database()->getRecord(sprintf('%sasset_groups',TWIST_DATABASE_TABLE_PREFIX),$intGroupID);
			$resRecord->set('description',$strDescription);
			$resRecord->set('slug',$srtSlug);
			$resRecord->set('created',\Twist::DateTime()->date('Y-m-d H:i:s'));

			return $resRecord->commit();
		}

		/**
		 * Add an asset to the system, the asset type will be detected automatically. The asset group must be passed in as a group ID.
		 * In the first parameter you can either pass in a string i.e URL, Youtube Link, Co-ordinates or a full path to a file i.e /my/file/to/add/file.ext
		 *
		 * @param $mxdData
		 * @param $intGroupID Initial group for the asset to be added
		 * @param $strTitle Title of the asset
		 * @param $strDescription Description for the asset
		 * @param $blActive Default status of the asset once created in the system
		 * @return int Returns the ID of the newly added asset
		 */
		public function add($mxdData,$intGroupID,$strTitle='',$strDescription='',$blActive=true){

			$intTypeID = $intSize = $intOut = 0;
			$intWidth = $intHeight = null;
			$intUserID = \Twist::User()->currentID();
			$strAssetPath = $strThumbnailPath = '';
			$arrSupportingAssets = array();

			if(file_exists($mxdData)){

				//A file, type will be determined
				$strExtension = \Twist::File()->extension($mxdData);
				$intSize = filesize($mxdData);
				$arrType = array();

				$arrGroupInfo = $this->getGroup($intGroupID);
				$strAssetGroupDir = sprintf('%s/%s',$this->strAssetDirectory,$arrGroupInfo['slug']);

				//Create the asset group directory if it not exist
				if(!file_exists($strAssetGroupDir.'/original')){
					mkdir($strAssetGroupDir.'/original',0777,true);
				}

				$strTitle = \Twist::File()->sanitizeName(\Twist::File()->name($mxdData));

				//The filename for the asset
				$strFileName = sprintf('%s-%s',\Twist::DateTime()->time(),\Twist::File()->name($mxdData));
				$strFileName = \Twist::File()->sanitizeName($strFileName);

				$strAssetPath = sprintf('%s/original/%s',$strAssetGroupDir,$strFileName);

				\Twist::File()->move($mxdData,$strAssetPath);

				foreach($this->arrTypes as $arrEachType){
					$arrExtensions = explode(',',strtolower($arrEachType['file_extensions']));
					if(in_array(strtolower($strExtension),$arrExtensions)){
						$arrType = $arrEachType;
						$intTypeID = $arrEachType['id'];
						break;
					}
				}

				//If the type is an image get the width and height
				if(count($arrType) && $arrType['slug'] == 'image'){
					list($intWidth, $intHeight, $type, $attr) = getimagesize($strAssetPath);

					//Create standard thumbnails if any are set to be created
					$strSizeList = \Twist::framework()->setting('ASSET_THUMBNAIL_SIZES');
					if($strSizeList !== ''){
						$arrSizes = (strstr($strSizeList,',')) ? explode(',',$strSizeList) : array($strSizeList);

						rsort($arrSizes);

						foreach($arrSizes as $intEachSize){
							$intEachSize = trim($intEachSize);
							$dirThumbPath = sprintf('%s/thumb-%d',$strAssetGroupDir,$intEachSize);
							\Twist::File()->recursiveCreate($dirThumbPath);

							$objImage = \Twist::Image()->load($strAssetPath);
							$objImage->resizeMaxDimension($intEachSize);
							$objImage->save(sprintf('%s/%s',$dirThumbPath,$strFileName));

							$arrSupportingAssets[sprintf('thumb-%d',$intEachSize)] = str_replace(TWIST_DOCUMENT_ROOT,'',sprintf('%s/%s',$dirThumbPath,$strFileName));
						}
					}

					//Create square thumbnails if any are set to be created
					$strSizeList = \Twist::framework()->setting('ASSET_THUMBNAIL_SQUARE_SIZES');
					if($strSizeList !== ''){
						$arrSizes = (strstr($strSizeList,',')) ? explode(',',$strSizeList) : array($strSizeList);

						rsort($arrSizes);

						foreach($arrSizes as $intEachSize){
							$intEachSize = trim($intEachSize);
							$dirThumbPath = sprintf('%s/square-thumb-%d',$strAssetGroupDir,$intEachSize);
							\Twist::File()->recursiveCreate($dirThumbPath);

							$objImage = \Twist::Image()->load($strAssetPath);
							$objImage->resizeCover($intEachSize);
							$objImage->save(sprintf('%s/%s',$dirThumbPath,$strFileName));

							$arrSupportingAssets[sprintf('square-thumb-%d',$intEachSize)] = str_replace(TWIST_DOCUMENT_ROOT,'',sprintf('%s/%s',$dirThumbPath,$strFileName));
						}
					}
				}

				$strAssetPath = ltrim(str_replace(TWIST_APP_ASSETS,'',$strAssetPath),'/');

			}elseif(strstr($mxdData,'http://') || strstr($mxdData,'https://') || strstr($mxdData,'ftp://') || strstr($mxdData,'smb://') || strstr($mxdData,'mailto:')){
				//Youtube video or link
				$arrType = (strstr($mxdData,'youtube')) ? $this->getTypeBySlug('youtube') : $this->getTypeBySlug('url');
				$intTypeID = (count($arrType)) ? $arrType['id'] : 0;
				$strAssetPath = $mxdData;
			}elseif(preg_match("#^\-?[0-9]+\.\[0-9]+\,\-?[0-9]+\.\[0-9]+\$#",$mxdData)){
				//Google map coordinates, currently no detection for with marker
				$arrType = $this->getTypeBySlug('gmap');
				$intTypeID = (count($arrType)) ? $arrType['id'] : 0;
				$strAssetPath = $mxdData;
			}

			//Create the asset record in the database
			$resRecord = \Twist::Database()->createRecord(sprintf('%sassets',TWIST_DATABASE_TABLE_PREFIX));
			$resRecord->set('title',$strTitle);
			$resRecord->set('description',$strDescription);
			$resRecord->set('type_id',$intTypeID);
			$resRecord->set('group_id',$intGroupID);
			$resRecord->set('data',$strAssetPath);
			$resRecord->set('size',$intSize);
			$resRecord->set('width',$intWidth);
			$resRecord->set('height',$intHeight);
			$resRecord->set('enabled',($blActive) ? '1' : '0');
			$resRecord->set('user',$intUserID);
			$resRecord->set('added',\Twist::DateTime()->date('Y-m-d H:i:s'));
			$intOut = $resRecord->commit();

			if(count($arrSupportingAssets)){

				foreach($arrSupportingAssets as $strType => $strURI){
					//Create the asset record in the database
					$resSupportingRecord = \Twist::Database()->createRecord(sprintf('%sasset_support',TWIST_DATABASE_TABLE_PREFIX));
					$resSupportingRecord->set('asset_id',$intOut);
					$resSupportingRecord->set('type',$strType);
					$resSupportingRecord->set('data',$strURI);
					$resSupportingRecord->commit();
				}
			}

			return $intOut;
		}

		/**
		 * Upload an asset to the system (utilises 'add' to store the asset once uploaded), the asset type will be detected automatically. An asset group must be provided.
		 *
		 * @related add
		 * @param $strFileKey File upload key from the $_FILES array
		 * @param $intGroupID Initial group for the asset to be added
		 * @param $strTitle Title of the asset
		 * @param $strDescription Description for the asset
		 * @param $blActive Default status of the asset once created in the system
		 * @return int Returns the ID of the newly uploaded/added asset
		 */
		public function upload($strFileKey,$intGroupID,$strTitle='',$strDescription='',$blActive=true){

			//Upload the file to the system
			$intOut = 0;
			$arrFile = \Twist::File()->upload($strFileKey,\Twist::DateTime()->time());

			//Store the file as an asset
			$intOut = $this->add($arrFile['file']['path'],$intGroupID,$strTitle,$strDescription,$blActive);

			return $intOut;
		}

		/**
		 * Import an asset into the system (utilises 'add' to store the asset once uploaded), the asset type will be detected automatically. An asset group must be provided.
		 *
		 * @related add
		 * @param $mxdFile A filepath or URL to import
		 * @param $intGroupID Initial group for the asset to be added
		 * @param $strTitle Title of the asset
		 * @param $strDescription Description for the asset
		 * @param $blActive Default status of the asset once created in the system
		 * @return int Returns the ID of the newly uploaded/added asset
		 */

		public function import($mxdFile,$intGroupID,$strTitle='',$strDescription='',$blActive=true){

			$strTempFile = tempnam(sys_get_temp_dir(), 'asset-import');
			file_get_contents($strTempFile,$mxdFile);

			//Store the file as an asset
			$intOut = $this->add($mxdFile,$intGroupID,$strTitle,$strDescription,$blActive);

			return $intOut;
		}

		/**
		 * Edit the title and description of an asset by its asset ID
		 *
		 * @param $intAssetID ID of the asset to be updated
		 * @param $strTitle Title to be stored for the provided asset ID
		 * @param $strDescription Description to be stored for the provided asset ID
		 * @return bool Returns that status of the update
		 */
		public function edit($intAssetID,$strTitle,$strDescription=''){

			$resRecord = \Twist::Database()->getRecord(sprintf('%sasset',TWIST_DATABASE_TABLE_PREFIX),$intAssetID);
			$resRecord->set('title',$strTitle);
			$resRecord->set('description',$strDescription);

			return $resRecord->commit();
		}

		/**
		 * Set the status of an asset between active/inactive by passing a boolean of either true or false in the second parameter.
		 *
		 * @param $intAssetID ID of the asset to be updated
		 * @param $blActive Status in which to set the enabled field
		 * @return bool Returns that status of teh update
		 */
		public function active($intAssetID,$blActive=true){

			$resRecord = \Twist::Database()->getRecord(sprintf('%sassets',TWIST_DATABASE_TABLE_PREFIX),$intAssetID);
			$resRecord->set('enabled',($blActive) ? '1' : '0');

			return $resRecord->commit();
		}

		/**
		 * Delete an asset from the system, this will remove both the database record and the file (if there is one)
		 *
		 * @param $intAssetID ID of the asset to be deleted
		 * @return bool Returns that status of the delete command
		 */
		public function delete($intAssetID){

			$blOut = false;
			$arrAsset = $this->get($intAssetID);

			if(count($arrAsset)){

				//Delete if the asset is a file
				if(file_exists($arrAsset['data'])){
					\Twist::File()->delete($arrAsset['data']);
				}

				//Delete the asset record
				$blOut = \Twist::Database()->delete(sprintf('%sassets',TWIST_DATABASE_TABLE_PREFIX),$intAssetID);
			}

			return $blOut;
		}

		/**
		 * Extends the default functionality of the Template package, adding the ability to output simple upload forms and asset information directly into a template
		 * {asset:upload}
		 * {asset:upload-html}
		 * {asset:upload-init}
		 * {asset:upload-js}
		 * {asset:uri}
		 * {asset:title}
		 * {asset:description}
		 * {asset:size_bytes}
		 * {asset:size}
		 * {asset:type}
		 * {asset:group}
		 * {asset:link}
		 * {asset:inline,type}
		 *
		 * @extends Template
		 * @param $strReference Template tag passed in from a template
		 * @return string Formatted HTML/Markup to be output by the template package
		 */
		public function viewExtension($strReference,$arrParameters = array()){

			$strOut = '';
			$arrAsset = $arrParams = array();

			if(array_key_exists('asset-id',$arrParameters)){
				$arrAsset = $this->get($arrParameters['asset-id']);
			}

			switch($strReference){

				case'upload':
					$strOut = \Twist::File()->viewExtension(sprintf('asset-%s',$strReference),$arrParameters);
					break;

				case'upload-html':
					$strOut = \Twist::File()->viewExtension(sprintf('asset-%s',$strReference),$arrParameters);
					break;

				case'upload-init':
					$strOut = \Twist::File()->viewExtension(sprintf('asset-%s',$strReference),$arrParameters);
					break;

				case'upload-js':
					$strOut = \Twist::File()->viewExtension(sprintf('asset-%s',$strReference),$arrParameters);
					break;

				case'uri':
					$strOut = $arrAsset['uri'];
					break;

				case'title':
					$strOut = $arrAsset['title'];
					break;

				case'description':
					$strOut = $arrAsset['description'];
					break;

				case'size_bytes':
					$strOut = $arrAsset['size'];
					break;

				case'size':
					$strOut = \Twist::File()->bytesToSize($arrAsset['size']);
					break;

				case'type':
					$strOut = $arrAsset['type']['name'];
					break;

				case'group':
					$strOut = $arrAsset['group']['name'];
					break;

				case'link':
					$strOut = $this->resTemplate->build('link.tpl',$arrAsset);
					break;

				case'inline':

					//Detect the file type
					switch($arrAsset['type']['slug']){
						case'image':
							$strOut = $this->resTemplate->build('image.tpl',$arrAsset);
							break;
						case'video':
							$strOut = $this->resTemplate->build('video.tpl',$arrAsset);
							break;
						case'gmap':
							$strOut = $this->resTemplate->build('gmap.tpl',$arrAsset);
							break;
						case'youtube':
							$strOut = $this->resTemplate->build('youtube.tpl',$arrAsset);
							break;
						case'vimeo':
							$strOut = $this->resTemplate->build('vimeo.tpl',$arrAsset);
							break;
						default:
							$strOut = $this->resTemplate->build('link.tpl',$arrAsset);
							break;
					}
					break;
			}

			return $strOut;
		}
	}