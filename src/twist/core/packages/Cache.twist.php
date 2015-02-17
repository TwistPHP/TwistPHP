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

	namespace TwistPHP\Packages;
	use TwistPHP\ModuleBase;

	/**
	 * Cache data using a simple store and retrieve process, all cached data must be assigned a unique key. Each cache can be given a life time in seconds, when the cache expires it will no longer be returned.
	 * The default storage location used is the folder '/cache' in the document root of your site. A '.htaccess' file is placed in the cache folder to ensure all cached data is private.
	 * The cache system is instanceable, for each instance a new cache folder will be created. This makes management of cache files easier.
	 *
	 * @instanceable
	 */
	class Cache extends ModuleBase{

		protected $strFileExtension = 'twi';
		protected $strStorageLocation = '';
		protected $strInstanceKey = '';
		protected $blCacheEnabled = true;

		protected $arrRuntimeSessionCache = array();

		/**
		 * Load all the default options for the cache system, create folders and '.htaccess' if required.
		 * @param $strInstanceKey Instance key to help group cache data
		 */
		public function __construct($strInstanceKey){

			$this->strInstanceKey = $strInstanceKey;
			$this->strStorageLocation = DIR_CACHE;

			//Create the default cache folder
			if(!file_exists($this->strStorageLocation)){
				mkdir($this->strStorageLocation);
			}

			//Check that it has been protected
			if(!file_exists($this->strStorageLocation.'.htaccess')){
				file_put_contents($this->strStorageLocation.'.htaccess',"Deny from all");
			}

			//Create the instance cache folder
			$this->strStorageLocation = sprintf('%s%s/',$this->strStorageLocation,$strInstanceKey);

			if(!file_exists($this->strStorageLocation)){
				mkdir($this->strStorageLocation);
			}

			//Get the status of the cache system, disabled cache wil return null for all requests (no data will be stored)
			$this->blCacheEnabled = $this->framework()->setting('CACHE_ENABLED');

			//Probability it set between 1-10 set to 0
			if(mt_rand(1, 10) <= $this->framework()->setting('CACHE_GB_PROBABILITY')){
				$this->clean();
			}
		}

		/**
		 * Set a custom storage location for your cached data,this must be set every time you use the cache system
		 *
		 * @param $strStorageLocation Full path to the new storage location
		 */
		public function setStorageLocation($strStorageLocation){
			$this->strStorageLocation = $strStorageLocation;
		}

		/**
		 * Set the cache file extension that will be used when storing cache files,this must be set every time you use the cache system
		 *
		 * @param $strFileExtension Custom cache file extension to use
		 */
		public function setFileExtension($strFileExtension = 'twi'){
			$this->strFileExtension = $strFileExtension;
		}

		/**
		 * Store data in the cache, default life time is 1 hour (3600 seconds). Setting the life time to '0' will mean that the cache will be stored as a PHP Runtime Session and will be no longer exists once the current runtime has ended.
		 * A Unique ID must be passed in so that you can reference the data again later.
		 *
		 * @param $strUniqueID Unique ID used to reference the cache
		 * @param $mxdData Data to be stored in the cache
		 * @param $intLifeTime Life of the cache, time until expiry
		 */
		public function store($mxdUniqueID,$mxdData,$intLifeTime = 3600){

			if($this->blCacheEnabled){

				//Generate the expiry time - Fix for php session cache (allow 30 second runtime before re-cache)
				$intExpiryTime = ($intLifeTime == 0) ? (\Twist::DateTime()->time() + 30) : (\Twist::DateTime()->time() + $intLifeTime);
				$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strFileExtension);
				$strDataString = json_encode($mxdData);

				//Set all the cache info
				$arrCacheInfo = array(
					'unique_id' => $mxdUniqueID,
					'create_date' => \Twist::DateTime()->date('Y-m-d H:i:s'),
					'expiry_date' => \Twist::DateTime()->date('Y-m-d H:i:s',$intExpiryTime),
					'life_time' => $intLifeTime,
					'data_bytes' => strlen($strDataString),
					'data_hash' => sha1($strDataString)
				);

				$strCacheData = sprintf("%s[@--SSC--@]%s",json_encode($arrCacheInfo),$strDataString);

				if($intLifeTime == 0){
					//If life of store is '0' the use the temp storage (Current PHP session only)
					$this->arrRuntimeSessionCache[$strCacheName] = $strCacheData;
				}else{
					$dirCacheFile = sprintf("%s%s",$this->strStorageLocation,$strCacheName);
					\Twist::File()->recursiveCreate(dirname($dirCacheFile));
					file_put_contents($dirCacheFile,$strCacheData);
				}
			}
		}

		/**
		 * Retrieve the data form the cache at any point by passing in the Unique ID. Expired cache date will be purged and passed back as NULL in the result.
		 *
		 * @param $mxdUniqueID Unique ID used to reference the cache
		 * @param $blFullData Set true to retrieve cache properties as array
		 * @return mixed Returns cache data or array of cache properties
		 */
		public function retrieve($mxdUniqueID,$blFullData=false){

			$mxdOut = null;

			if($this->blCacheEnabled){
				$arrData = $this->getCacheData($mxdUniqueID);

				if(is_array($arrData) && count($arrData) == 2){
					$arrVerificationData = $this->verifyData($arrData['data'],$arrData['info']['data_hash'],$arrData['info']['data_bytes']);

					if($this->getRemainingLife($mxdUniqueID) > 0){
						if($arrVerificationData['status'] == true){

							$arrData['data'] = json_decode($arrData['data'],true);
							$mxdOut = ($blFullData) ? $arrData : $arrData['data'];
						}else{
							$this->remove($mxdUniqueID);
						}
					}else{
						$this->remove($mxdUniqueID);
					}
				}
			}

			return $mxdOut;
		}

		/**
		 * Remove a cache manually (before expiry) if you want to stop using it or no longer require its contents. Pass in the Unique ID to reference the cache you want to remove, alternatively passing in null will remove all cache files for this instance.
		 *
		 * @param $mxdUniqueID Unique ID used to reference the cache
		 * @return bool Status of the cache removal
		 */
		public function remove($mxdUniqueID = null){

			$blOut = false;

			//Build the cache files location
			$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strFileExtension);
			$strCacheFile = sprintf("%s%s",$this->strStorageLocation,$strCacheName);

			if(is_null($mxdUniqueID)){
				\Twist::File()->recursiveRemove($this->strStorageLocation);
				$this->arrRuntimeSessionCache = array();
			}else{
				if(array_key_exists($strCacheName,$this->arrRuntimeSessionCache)){
					unset($this->arrRuntimeSessionCache[$strCacheName]);
					$blOut = true;
				}elseif(file_exists($strCacheFile)){
					$blOut = unlink($strCacheFile);
				}
			}

			return $blOut;
		}

		/**
		 * Get the remaining life span of a cache, you will need to pass in the Unique ID and you will get back its remaining time in seconds.
		 *
		 * @param $mxdUniqueID Unique ID used to reference the cache
		 * @return int Returns the remaining life of the cache in seconds
		 */
		public function getRemainingLife($mxdUniqueID){

			$intOut = 0;
			$arrData = $this->getCacheData($mxdUniqueID);

			if(is_array($arrData) && count($arrData) == 2){
				//Return the life remaining in seconds
				$intOut = strtotime($arrData['info']['expiry_date']) - \Twist::DateTime()->time();
			}

			return $intOut;
		}

		/**
		 * Clean up the system, this is garbage collection. Any expired caches will be removed form the system. This will not remove any session data in the PHP session storage
		 */
		public function clean(){

			$arrCacheFiles = scandir($this->strStorageLocation);

			//Go through all the cache items to keep them clean
			if(is_array($arrCacheFiles) && count($arrCacheFiles) > 0){
				foreach($arrCacheFiles as $strCacheFile){

					//Now check the file is not a directory and that it is the correct extension
					if(!in_array($strCacheFile,array('..','.')) && strstr($strCacheFile,sprintf(".%s",$this->strFileExtension))){

						//Get the remaining life of the file
						$intRemainingLife = $this->getRemainingLife(substr($strCacheFile,0,-4));

						//If the file has a life of less than 1 second or a negative value then remove
						if($intRemainingLife < 1){
							$this->remove(substr($strCacheFile,0,-4));
						}
					}
				}
			}
		}

		/**
		 * Get the cache properties and extract its parts into a usable array
		 *
		 * @param $mxdUniqueID Unique ID used to reference the cache
		 * @return array Returns array of cache properties
		 */
		protected function getCacheData($mxdUniqueID){

			$arrOut = $strCacheData = null;

			//Build the cache files location
			$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strFileExtension);
			$strCacheFile = sprintf("%s%s",$this->strStorageLocation,$strCacheName);

			if(array_key_exists($strCacheName,$this->arrRuntimeSessionCache)){
				$strCacheData = $this->arrRuntimeSessionCache[$strCacheName];
			}elseif(file_exists($strCacheFile)){
				$strCacheData = file_get_contents($strCacheFile);
			}

			if(!is_null($strCacheData) && $strCacheData != ''){

				$arrParts = explode('[@--SSC--@]',$strCacheData);

				if(is_array($arrParts) && count($arrParts) == 2){

					//Return all the data
					$arrOut = array(
						'info' => json_decode($arrParts[0],true),
						'data' => $arrParts[1]
					);
				}
			}

			return $arrOut;
		}

		/**
		 * Verify the retrieved cache data and make sure it has not been tampered with
		 *
		 * @param $strDataJSON JSON Cache data
		 * @param $strHashKey Unique Hash of the data that can be verified
		 * @param $intDataLength Length of the cache date
		 * @return array Returns a status and message
		 */
		protected function verifyData($strDataJSON,$strHashKey,$intDataLength){

			$arrOut = array('status' => false, 'message' => '');

			//Check that the data length is correct
			if(strlen($strDataJSON) == $intDataLength){

				//Check that the data matches the data hash
				if(sha1($strDataJSON) == $strHashKey){
					$arrOut['status'] = true;
				}else{
					$arrOut['message'] = 'Error: Hash verification failed';
				}
			}else{
				$arrOut['message'] = 'Error: The data length is incorrect';
			}

			return $arrOut;
		}

	}
