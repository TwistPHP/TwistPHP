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

	namespace Twist\Core\Helpers;

	/**
	 * Cache data using a simple store and retrieve process, all cached data must be assigned a unique key. Each cache can be given a life time in seconds, when the cache expires it will no longer be returned.
	 * The default storage location used is the folder '/cache' in the document root of your site. A '.htaccess' file is placed in the cache folder to ensure all cached data is private.
	 * The cache system is instanceable, for each instance a new cache folder will be created. This makes management of cache files easier.
	 *
	 * @instanceable
	 */
	class Cache extends Base{

		protected $strExtension = 'cache';
		protected $dirLocation = '';
		protected $strInstanceKey = '';
		protected $blCacheEnabled = true;

		protected $arrRuntimeSessionCache = array();

		/**
		 * Load all the default options for the cache system, create folders and '.htaccess' if required.
		 * @param string $strInstanceKey Instance key to help group cache data
		 */
		public function __construct($strInstanceKey){

			//Get the status of the cache system, disabled cache wil return null for all requests (no data will be stored)
			$this->blCacheEnabled = (\Twist::framework()->setting('CACHE_ENABLED') && !\Twist::framework() -> settings() -> showInstallWizard());

			if($this->blCacheEnabled){

				$this->strInstanceKey = $strInstanceKey;
				$this->dirLocation = TWIST_APP_CACHE;

				//Create the default cache folder
				if(!file_exists($this->dirLocation)){
					mkdir($this->dirLocation,0777,true);
				}

				//Check that it has been protected
				if(!file_exists($this->dirLocation.'.htaccess')){
					file_put_contents($this->dirLocation.'.htaccess',"Deny from all");
				}

				//Create the instance cache folder
				$this->dirLocation = sprintf('%s/%s/',rtrim($this->dirLocation,'/'),ltrim($strInstanceKey,'/'));

				if(!file_exists($this->dirLocation)){
					\Twist::File()->recursiveCreate($this->dirLocation);
				}

				//Probability it set between 1-10 set to 0
				if(mt_rand(1, 10) <= \Twist::framework()->setting('CACHE_GB_PROBABILITY')){
					$this->clean();
				}
			}
		}

		/**
		 * Set/Get the storage location for cache files
		 * @param string $dirStorageLocation Full path to the new storage location
		 * @return null|string
		 */
		public function location($dirStorageLocation = null){

			if(!is_null($dirStorageLocation)){
				$this->dirLocation = $dirStorageLocation;
			}

			return $this->dirLocation;
		}

		/**
		 * Set/Get the cache file extension that will be used when storing cache files
		 * @param string $strFileExtension Custom cache file extension to use
		 * @return null|string
		 */
		public function extension($strFileExtension = null){

			if(!is_null($strFileExtension)){
				$this->strExtension = $strFileExtension;
			}

			return $this->strExtension;
		}

		/**
		 * Store data in the cache, default life time is 1 hour (3600 seconds). Setting the life time to '0' will mean that the cache will be stored as a PHP Runtime Session and will be no longer exists once the current runtime has ended.
		 * A Unique ID must be passed in so that you can reference the data again later.
		 *
		 * @param string|integer $mxdUniqueID Unique ID used to reference the cache
		 * @param string|array $mxdData Data to be stored in the cache
		 * @param integer $intLifeTime Life of the cache, time until expiry
		 */
		public function write($mxdUniqueID,$mxdData,$intLifeTime = 3600){

			if($this->blCacheEnabled){

				//Generate the expiry time - Fix for php session cache (allow 30 second runtime before re-cache)
				$intCreated = \Twist::DateTime()->time();
				$intExpiryTime = ($intLifeTime == 0) ? ($intCreated + 30) : ($intCreated + $intLifeTime);
				$strCacheName = sprintf("%s.%s", $mxdUniqueID, $this->strExtension);

				$arrData = array(
					'created' => $intCreated,
					'expiry' => $intExpiryTime,
					'data' => $mxdData
				);

				if($intLifeTime == 0){
					//If life of store is '0' the use the temp storage (Current PHP session only)
					$this->arrRuntimeSessionCache[$strCacheName] = $arrData;
				}else{
					$dirCacheFile = sprintf("%s/%s", rtrim($this->dirLocation, '/'), ltrim($strCacheName, '/'));
					\Twist::File()->write($dirCacheFile, json_encode($arrData),null,\Twist::framework()->setting('CACHE_DELAYED_WRITE'));
				}
			}
		}

		/**
		 * Retrieve the data form the cache at any point by passing in the Unique ID. Expired cache date will be purged and passed back as NULL in the result.
		 *
		 * @param mixed $mxdUniqueID string Unique ID used to reference the cache
		 * @return mixed Returns cache data or array of cache properties
		 */
		public function read($mxdUniqueID){

			//Build the cache files location
			$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strExtension);
			$dirCacheFile = sprintf("%s/%s",rtrim($this->dirLocation,'/'),ltrim($strCacheName,'/'));

			if($this->blCacheEnabled){

				if(array_key_exists($strCacheName,$this->arrRuntimeSessionCache)){
					return $this->arrRuntimeSessionCache[$strCacheName]['data'];
				}elseif(\Twist::File()->exists($dirCacheFile)){
					$arrData = json_decode(\Twist::File()->read($dirCacheFile),true);

					if(count($arrData) && array_key_exists('expiry',$arrData) && array_key_exists('data',$arrData) && $arrData['expiry'] >= \Twist::DateTime()->time()){
						return $arrData['data'];
					}

					//Only gets here if the cache has expired or is invalid
					\Twist::File()->remove($dirCacheFile);
				}
			}

			return null;
		}

		/**
		 * Remove a cache manually (before expiry) if you want to stop using it or no longer require its contents. Pass in the Unique ID to reference the cache you want to remove, alternatively passing in null will remove all cache files for this instance.
		 *
		 * @param mixed $mxdUniqueID Unique ID used to reference the cache
		 * @return bool Status of the cache removal
		 */
		public function remove($mxdUniqueID = null){

			$blOut = false;

			if(is_null($mxdUniqueID)){
				\Twist::File()->recursiveRemove($this->dirLocation);
				$this->arrRuntimeSessionCache = array();
			}else{
				//Build the cache files location
				$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strExtension);
				$dirCacheFile = sprintf("%s/%s",rtrim($this->dirLocation,'/'),ltrim($strCacheName,'/'));

				if(array_key_exists($strCacheName,$this->arrRuntimeSessionCache)){
					unset($this->arrRuntimeSessionCache[$strCacheName]);
					$blOut = true;
				}else{
					$blOut = \Twist::File()->remove($dirCacheFile);
				}
			}

			return $blOut;
		}

		/**
		 * Get the created time for the cache file
		 * @param mixed $mxdUniqueID
		 * @return int|null
		 */
		public function created($mxdUniqueID){

			//Build the cache files location
			$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strExtension);
			$dirCacheFile = sprintf("%s/%s",rtrim($this->dirLocation,'/'),ltrim($strCacheName,'/'));

			if($this->blCacheEnabled && \Twist::File()->exists($dirCacheFile)){

				$arrData = json_decode(\Twist::File()->read($dirCacheFile),true);

				if(count($arrData) && array_key_exists('created',$arrData) && array_key_exists('data',$arrData)){
					return $arrData['created'];
				}
			}

			return null;
		}

		/**
		 * Get the last modified time of the cache file
		 * @param mixed $mxdUniqueID
		 * @return int|null
		 */
		public function modified($mxdUniqueID){

			//Build the cache files location
			$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strExtension);
			$dirCacheFile = sprintf("%s/%s",rtrim($this->dirLocation,'/'),ltrim($strCacheName,'/'));

			if($this->blCacheEnabled && \Twist::File()->exists($dirCacheFile)){
				return filemtime($dirCacheFile);
			}

			return null;
		}

		/**
		 * Get the expiry timestamp of a cache file by its unique ID
		 * @param mixed $mxdUniqueID
		 * @return null
		 * @throws \Exception
		 */
		public function expiry($mxdUniqueID){

			//Build the cache files location
			$strCacheName = sprintf("%s.%s",$mxdUniqueID,$this->strExtension);
			$dirCacheFile = sprintf("%s/%s",rtrim($this->dirLocation,'/'),ltrim($strCacheName,'/'));

			if($this->blCacheEnabled && \Twist::File()->exists($dirCacheFile)){

				$arrData = json_decode(\Twist::File()->read($dirCacheFile),true);

				if(count($arrData) && array_key_exists('expiry',$arrData) && array_key_exists('data',$arrData)){
					return $arrData['expiry'];
				}
			}

			return null;
		}

		/**
		 * Clean up the system, this is garbage collection. Any expired caches will be removed form the system. This will not remove any session data in the PHP session storage
		 */
		public function clean(){
			$this->cleanDirectory($this->dirLocation,\Twist::DateTime()->time());
		}

		/**
		 * Cleans out a directory and all its sub directories
		 * @param string $strDirectory
		 * @param integer $intCurrentTime
		 */
		protected function cleanDirectory($strDirectory,$intCurrentTime){

			$arrCacheFiles = scandir($strDirectory);
			$intExtensionLength = strlen($this->strExtension)+1;

			//Go through all the cache items to keep them clean
			if(is_array($arrCacheFiles) && count($arrCacheFiles) > 0){
				foreach($arrCacheFiles as $strCacheFile){

					$strFilePath = sprintf('%s/%s',rtrim($strDirectory,'/'),$strCacheFile);

					if(!in_array($strCacheFile,array('..','.')) && strstr($strCacheFile,sprintf(".%s",$this->strExtension))){

						$strCacheKey = substr($strCacheFile,0,$intExtensionLength);

						if($this->expiry($strCacheKey) < $intCurrentTime){
							$this->remove($strCacheKey);
						}
					}elseif(is_dir($strFilePath)){
						$this->cleanDirectory($strFilePath,$intCurrentTime);
					}
				}
			}
		}
	}
