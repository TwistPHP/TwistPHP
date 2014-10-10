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

	class Asset extends ModuleBase{

		protected $resTemplate = null;
		protected $strAssetDirectory = null;

		public $arrTypes = array();
		public $arrTypeSlugs = array();
		public $arrGroups = array();
		public $arrGroupSlugs = array();
		public $arrGroupTree = array();

		public function __construct(){

			$this->resTemplate = \Twist::Template('pkgAsset');
			$this->resTemplate->setTemplatesDirectory( sprintf('%s/templates/Asset/',DIR_FRAMEWORK_PACKAGES));

			$this->strAssetDirectory = sprintf('%s/assets',BASE_LOCATION);

			//Create the asset directory if it not exist
			if(!file_exists($this->strAssetDirectory)){
				mkdir($this->strAssetDirectory);
			}

			//Pre-cache the types and groups ready to be used
			$this->arrTypes = $this->framework()->tools()->arrayReindex(\Twist::Database()->getAll(sprintf('%sasset_types',DATABASE_TABLE_PREFIX)),'id');
			$this->arrTypeSlugs = $this->framework()->tools()->arrayReindex($this->arrTypes,'slug');

			$this->arrGroups = $this->framework()->tools()->arrayReindex(\Twist::Database()->getAll(sprintf('%sasset_groups',DATABASE_TABLE_PREFIX)),'id');
			$this->arrGroupSlugs = $this->framework()->tools()->arrayReindex($this->arrGroups,'slug');
			$this->arrGroupTree = $this->framework()->tools()->arrayRelationalTree($this->arrGroups,'id','parent');
		}

		/**
		 * Get an asset by Asset ID, this will also expand the asset to include a sub array of its type and group information
		 * @param $intAssetID
		 * @return array|null|DatabaseRecord
		 */
		public function get($intAssetID){

			$arrAsset = \Twist::Database()->get(sprintf('%sassets',DATABASE_TABLE_PREFIX),$intAssetID);
			$arrAsset = (count($arrAsset)) ? $this->expand($arrAsset) : array();

			return $arrAsset;
		}

		/**
		 * Get all assets by Asset ID, this will also expand the asset to include a sub array of its type and group information
		 * @param $intAssetID
		 * @return array|null|DatabaseRecord
		 */
		public function getAll(){

			$arrOut = array();
			$arrAssets = \Twist::Database()->getAll(sprintf('%sassets',DATABASE_TABLE_PREFIX));

			if(count($arrAssets)){
				foreach($arrAssets as $arrEachAsset){
					$arrOut[$arrEachAsset['id']] = $this->expand($arrEachAsset);
				}
			}

			return $arrOut;
		}

		/**
		 * Get all assets that are in any particular group by Asset Group ID, this will also expand the asset to include a sub array of its type and group information
		 * @param $intGroupID
		 * @return array
		 */
		public function getByGroup($intGroupID){

			$arrOut = array();
			$arrAssets = \Twist::Database()->find(sprintf('%sassets',DATABASE_TABLE_PREFIX),$intGroupID,'group_id','added');

			if(count($arrAssets)){
				foreach($arrAssets as $arrEachAsset){
					$arrOut[$arrEachAsset['id']] = $this->expand($arrEachAsset);
				}
			}

			return $arrOut;
		}

		/**
		 * Get all assets that are any particular type by Asset Type ID, this will also expand the asset to include a sub array of its type and group information
		 * @param $intTypeID
		 * @return array
		 */
		public function getByType($intTypeID){

			$arrOut = array();
			$arrAssets = \Twist::Database()->find(sprintf('%sassets',DATABASE_TABLE_PREFIX),$intTypeID,'type_id');

			if(count($arrAssets)){
				foreach($arrAssets as $arrEachAsset){
					$arrOut[$arrEachAsset['id']] = $this->expand($arrEachAsset);
				}
			}

			return $arrOut;
		}

		/**
		 * Expand the asset to include extra data such as type/group information
		 * @param $arrAsset
		 * @return mixed
		 */
		private function expand($arrAsset){

			$strFileName = \Twist::File()->name($arrAsset['data']);
			$arrNameData = explode('-',$strFileName);
			unset($arrNameData[0]);

			$arrAsset['filename'] = $strFileName;
			$arrAsset['original_filename'] = implode('-',$arrNameData);

			$arrAsset['type'] = $this->getType($arrAsset['type_id']);
			$arrAsset['group'] = $this->getGroup($arrAsset['group_id']);
			$arrAsset['support'] = $this->getSupportingContent($arrAsset);

			$arrAsset['icon'] = str_replace(BASE_LOCATION,'',sprintf('%s/images/icons/%s',DIR_FRAMEWORK_RESOURCES,$arrAsset['type']['icon']));

			return $arrAsset;
		}

		/**
		 * Get asset type information by its asset type ID
		 * @param $intTypeID
		 * @return array
		 */
		public function getType($intTypeID){
			return (array_key_exists($intTypeID,$this->arrTypes)) ? $this->arrTypes[$intTypeID] : array();
		}

		/**
		 * Get asset type information by its asset type Slug
		 * @param $strTypeSlug
		 * @return array
		 */
		public function getTypeBySlug($strTypeSlug){
			return (array_key_exists($strTypeSlug,$this->arrTypeSlugs)) ? $this->arrTypeSlugs[$strTypeSlug] : array();
		}

		/**
		 * Get asset group information by its asset group ID
		 * @param $intGroupID
		 * @return array
		 */
		public function getGroup($intGroupID){
			return (array_key_exists($intGroupID,$this->arrGroups)) ? $this->arrGroups[$intGroupID] : array();
		}

		public function addGroup($strDescription,$srtSlug){

			//Create the asset group record in the database
			$resRecord = \Twist::Database()->createRecord(sprintf('%sasset_groups',DATABASE_TABLE_PREFIX));
			$resRecord->set('description',$strDescription);
			$resRecord->set('slug',$srtSlug);
			$resRecord->set('created',\Twist::DateTime()->date('Y-m-d H:i:s'));

			return $resRecord->commit();
		}

		public function editGroup($intGroupID,$strDescription,$srtSlug){

			//Create the asset group record in the database
			$resRecord = \Twist::Database()->getRecord(sprintf('%sasset_groups',DATABASE_TABLE_PREFIX),$intGroupID);
			$resRecord->set('description',$strDescription);
			$resRecord->set('slug',$srtSlug);
			$resRecord->set('created',\Twist::DateTime()->date('Y-m-d H:i:s'));

			return $resRecord->commit();
		}

		public function getGroups(){
			return $this->arrGroups;
		}

		public function getGroupTree(){
			return $this->arrGroupTree;
		}


		/**
		 * Get asset group information by its asset group Slug
		 * @param $mxdGroupSlug
		 * @return array
		 */
		public function getGroupBySlug($mxdGroupSlug){
			return (array_key_exists($mxdGroupSlug,$this->arrGroupSlugs)) ? $this->arrGroupSlugs[$mxdGroupSlug] : array();
		}

		public function getSupportingContent($arrAsset){

			$arrOut = array();
			$arrSupport = \Twist::Database()->find(sprintf('%sasset_support',DATABASE_TABLE_PREFIX),$arrAsset['id'],'asset_id');

			if(count($arrSupport)){
				foreach($arrSupport as $arrEachItem){
					$arrOut[$arrEachItem['type']] = $arrEachItem['data'];
				}
			}else{
				$arrOut = $this->getDefaultSupportingContent($arrAsset);
			}

			return $arrOut;
		}

		public function getDefaultSupportingContent($arrAsset){

			$strIconURI = str_replace(BASE_LOCATION,'',sprintf('%s/images/icons/%s',DIR_FRAMEWORK_RESOURCES,$arrAsset['type']['icon']));

		   $arrOut = array(
			   'square-thumb-256' => $strIconURI,
			   'square-thumb-128' => $strIconURI,
			   'square-thumb-64' => $strIconURI,
			   'square-thumb-32' => $strIconURI,
			   'thumb-512' => $strIconURI,
			   'thumb-256' => $strIconURI,
			   'thumb-128' => $strIconURI,
			   'thumb-64' => $strIconURI
		   );

			return $arrOut;
		}

		/**
		 * Add an asset to the system, the asset type will be detected automatically. Asset group is not a requirement but will default to the default group
		 * In the first parameter you can either pass in a string i.e URL, Youtube Link, Co-ordinates or a full path to a file i.e /my/file/to/add/file.ext
		 * @param $mxdData
		 * @param $intGroupID
		 * @param string $strTitle
		 * @param string $strDescription
		 * @param bool $blActive
		 * @return bool|int
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

					//Generate the mandatory thumbnail
					$strSquareThumbPath256 = sprintf('%s/square-thumb-256',$strAssetGroupDir);
					$strSquareThumbPath128 = sprintf('%s/square-thumb-128',$strAssetGroupDir);
					$strSquareThumbPath64 = sprintf('%s/square-thumb-64',$strAssetGroupDir);
					$strSquareThumbPath32 = sprintf('%s/square-thumb-32',$strAssetGroupDir);

					//Create the asset group directory if it not exist
					if(!file_exists($strSquareThumbPath256)){ mkdir($strSquareThumbPath256); }
					if(!file_exists($strSquareThumbPath128)){ mkdir($strSquareThumbPath128); }
					if(!file_exists($strSquareThumbPath64)){ mkdir($strSquareThumbPath64); }
					if(!file_exists($strSquareThumbPath32)){ mkdir($strSquareThumbPath32); }

					$objImage = \Twist::Image()->load($strAssetPath);

					$objImage->resizeCover(256);
					$objImage->save(sprintf('%s/%s',$strSquareThumbPath256,$strFileName));

					$objImage->resizeCover(128);
					$objImage->save(sprintf('%s/%s',$strSquareThumbPath128,$strFileName));

					$objImage->resizeCover(64);
					$objImage->save(sprintf('%s/%s',$strSquareThumbPath64,$strFileName));

					$objImage->resizeCover(32);
					$objImage->save(sprintf('%s/%s',$strSquareThumbPath32,$strFileName));

					//Generate the mandatory thumbnail
					$strThumbPath512 = sprintf('%s/thumb-512',$strAssetGroupDir);
					$strThumbPath256 = sprintf('%s/thumb-256',$strAssetGroupDir);
					$strThumbPath128 = sprintf('%s/thumb-128',$strAssetGroupDir);
					$strThumbPath64 = sprintf('%s/thumb-64',$strAssetGroupDir);

					//Create the asset group directory if it not exist
					if(!file_exists($strThumbPath512)){ mkdir($strThumbPath512); }
					if(!file_exists($strThumbPath256)){ mkdir($strThumbPath256); }
					if(!file_exists($strThumbPath128)){ mkdir($strThumbPath128); }
					if(!file_exists($strThumbPath64)){ mkdir($strThumbPath64); }

					$objImage = \Twist::Image()->load($strAssetPath);

					$objImage->resizeMaxDimension(512);
					$objImage->save(sprintf('%s/%s',$strThumbPath512,$strFileName));

					$objImage->resizeMaxDimension(256);
					$objImage->save(sprintf('%s/%s',$strThumbPath256,$strFileName));

					$objImage->resizeMaxDimension(128);
					$objImage->save(sprintf('%s/%s',$strThumbPath128,$strFileName));

					$objImage->resizeMaxDimension(64);
					$objImage->save(sprintf('%s/%s',$strThumbPath64,$strFileName));

					$arrSupportingAssets = array(
						'square-thumb-256' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strSquareThumbPath256,$strFileName)),
						'square-thumb-128' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strSquareThumbPath128,$strFileName)),
						'square-thumb-64' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strSquareThumbPath64,$strFileName)),
						'square-thumb-32' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strSquareThumbPath32,$strFileName)),
						'thumb-512' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strThumbPath512,$strFileName)),
						'thumb-256' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strThumbPath256,$strFileName)),
						'thumb-128' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strThumbPath128,$strFileName)),
						'thumb-64' => str_replace(BASE_LOCATION,'',sprintf('%s/%s',$strThumbPath64,$strFileName)),
					);
				}

				$strAssetPath = str_replace(BASE_LOCATION,'',$strAssetPath);

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
			$resRecord = \Twist::Database()->createRecord(sprintf('%sassets',DATABASE_TABLE_PREFIX));
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
					$resSupportingRecord = \Twist::Database()->createRecord(sprintf('%sasset_support',DATABASE_TABLE_PREFIX));
					$resSupportingRecord->set('asset_id',$intOut);
					$resSupportingRecord->set('type',$strType);
					$resSupportingRecord->set('data',$strURI);
					$resSupportingRecord->commit();
				}
			}

			return $intOut;
		}

		/**
		 * Upload an asset to the system (utilises 'add' to store the asset once uploaded), the asset type will be detected automatically. Asset group is not a requirement but will default to the default group
		 * In the first parameter you can either pass in a string i.e URL, Youtube Link, Co-ordinates or a full path to a file i.e /my/file/to/add/file.ext
		 * @param $strFileKey
		 * @param $intGroupID
		 * @param string $strTitle
		 * @param string $strDescription
		 * @param bool $blActive
		 * @return bool|int
		 */
		public function upload($strFileKey,$intGroupID,$strTitle='',$strDescription='',$blActive=true){

			//Upload the file to the system
			$intOut = 0;
			$arrFile = \Twist::File()->upload($strFileKey,\Twist::DateTime()->time());

			//Store the file as an asset
			$intOut = $this->add($arrFile['file'],$intGroupID,$strTitle,$strDescription,$blActive);

			return $intOut;
		}

		/**
		 * Edit the title and description of an asset by its asset ID
		 * @param $intAssetID
		 * @param $strTitle
		 * @param string $strDescription
		 * @return bool|int
		 */
		public function edit($intAssetID,$strTitle,$strDescription=''){

			$resRecord = \Twist::Database()->getRecord(sprintf('%sasset',DATABASE_TABLE_PREFIX),$intAssetID);
			$resRecord->set('title',$strTitle);
			$resRecord->set('description',$strDescription);

			return $resRecord->commit();
		}

		/**
		 * Set the status of an asset between active/inactive by passing a boolean of either true or false in the second parameter.
		 * @param $intAssetID
		 * @param bool $blActive
		 * @return bool|int
		 */
		public function active($intAssetID,$blActive=true){

			$resRecord = \Twist::Database()->getRecord(sprintf('%sassets',DATABASE_TABLE_PREFIX),$intAssetID);
			$resRecord->set('enabled',($blActive) ? '1' : '0');

			return $resRecord->commit();
		}

		/**
		 * Delete an asset from the system, this will remove both the database record and the file (if there is one)
		 * @param $intAssetID
		 * @return bool
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
				$blOut = \Twist::Database()->delete(sprintf('%sassets',DATABASE_TABLE_PREFIX),$intAssetID);
			}

			return $blOut;
		}

		public function templateExtension($strReference){

			$strOut = '';
			$arrAsset = $arrParams = array();

			if(strstr($strReference,',')){
				$arrParams = explode(',',$strReference);
				if($arrParams[0] != 'upload'){
					$arrAsset = $this->get($arrParams[1]);
				}
			}else{
				$arrParams[] = $strReference;
				$arrAsset = $this->get($arrParams[1]);
			}

			switch($arrParams[0]){

				case'upload':
					$strOut = \Twist::File()->templateExtension($strReference.',asset');
					break;

				case'upload-html':
					$strOut = \Twist::File()->templateExtension($strReference.',asset');
					break;

				case'upload-init':
					$strOut = \Twist::File()->templateExtension($strReference.',asset');
					break;

				case'upload-js':
					$strOut = \Twist::File()->templateExtension($strReference.',asset');
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