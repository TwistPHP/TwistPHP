<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
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

	namespace Twist\Core\Models\Route;

	/**
	 * Route Meta Tag manager and template extension
	 * @reference http://ogp.me/
	 * @package TwistPHP\Packages
	 */
	class Meta{

		protected $arrTags = array();

		protected function add($strType,$strName,$strValue){

			$strType = strtolower($strType);
			$strName = strtolower($strName);

			$this->arrTags[sprintf('%s-%s',$strType,$strName)] = array(
				'type' => $strType,
				'name' => $strName,
				'value' => $strValue
			);
		}

		public function get($strType,$strName){
			return (array_key_exists(sprintf('%s-%s',$strType,$strName),$this->arrTags)) ? [sprintf('%s-%s',$strType,$strName)] : null;
		}

		public function getTags(){

			$arrOut = array();

			//Output all the items as plain text in a usable format
			foreach($this->arrTags as $arrEachItem){

				if($arrEachItem['type'] == $arrEachItem['name']){
					$arrOut[$arrEachItem['type']] = $arrEachItem['value'];
				}else{

					if(!array_key_exists($arrEachItem['type'],$arrOut)){
						$arrOut[$arrEachItem['type']] = array();
					}

					$arrOut[$arrEachItem['type']][$arrEachItem['name']] = $arrEachItem['value'];
				}
			}

			//Generate the HTML tags to be output
			$arrOut['tags'] = $this->generate();

			return $arrOut;
		}

		public function title($strValue){
			$this->add('title','title',$strValue);
		}

		public function description($strValue){
			$this->add('meta','description',$strValue);
		}

		public function keywords($strValue){
			$this->add('meta','keywords',$strValue);
		}

		public function author($strValue){
			$this->add('meta','author',$strValue);
		}

		public function charset($strValue){
			$this->add('meta','charset',$strValue);
		}

		public function language($strValue){
			$this->add('meta','language',$strValue);
		}

		public function robots($strValue){
			$this->add('meta','robots',$strValue);
		}

		public function custom($strKey,$strValue){
			$this->add('meta',$strKey,$strValue);
		}

		public function css($strFile,$strRel = 'stylesheet'){
			$this->add('link',$strFile,array('href' => $strFile,'rel' => $strRel));
		}

		public function js($strFile){
			$this->add('script',$strFile,array('src' => $strFile));
		}

		public function ogTitle($strContent){
			$this->add('og','title',$strContent);
		}

		public function ogDescription($strContent){
			$this->add('og','description',$strContent);
		}

		public function ogSiteName($strContent){
			$this->add('og','site_name',$strContent);
		}

		public function ogType($strContent){

			$arrTypes = array(
				'music.song','music.album','music.playlist','music.radio_station',
				'video.movie','video.episode','video.tv_show','video.other',
				'article','book','profile','website'
			);

			if(in_array($strContent,$arrTypes)){
				$this->add('og','type',$strContent);
			}else{
				throw new \Exception('Invalid OG type passed in, please use on of '.implode(', ',$arrTypes));
			}
		}

		public function ogTypeArticle($mxdPublishedDate = null,$mxdModifiedDate = null,$mxdExpirationDate = null,$arrAuthor = array(),$strSection = null,$mxdTags = null){

			$this->ogType('article');

			if(!is_null($mxdPublishedDate)){
				$this->add('og','article:published_time',date('Y-m-d\TH:i:s\Z',strtotime($mxdPublishedDate)));
			}

			if(!is_null($mxdModifiedDate)){
				$this->add('og','article:modified_time',date('Y-m-d\TH:i:s\Z',strtotime($mxdModifiedDate)));
			}

			if(!is_null($mxdExpirationDate)){
				$this->add('og','article:expiration_time',date('Y-m-d\TH:i:s\Z',strtotime($mxdExpirationDate)));
			}

			if(is_array($arrAuthor)){
				foreach($arrAuthor as $strKey => $strValue){
					if(in_array($strKey,array('first_name','last_name','username','gender'))){
						$this->add('og',sprintf('article:author:%s',$strKey),$strValue);
					}
				}
			}

			if(!is_null($strSection)){
				$this->add('og','article:section',$strSection);
			}

			if(!is_null($mxdTags)){
				foreach(((!is_array($mxdTags)) ? array($mxdTags) : $mxdTags) as $strEachTag){
					$this->add('og','article:tag',$strEachTag);
				}
			}
		}

		public function ogTypeBook($arrAuthor = array(),$strISBN = null,$mxdReleaseDate = null,$mxdTags = null){

			$this->ogType('book');

			if(is_array($arrAuthor)){
				foreach($arrAuthor as $strKey => $strValue){
					if(in_array($strKey,array('first_name','last_name','username','gender'))){
						$this->add('og',sprintf('book:author:%s',$strKey),$strValue);
					}
				}
			}

			if(!is_null($strISBN)){
				$this->add('og','book:isbn',$strISBN);
			}

			if(!is_null($mxdReleaseDate)){
				$this->add('og','book:release_date',date('Y-m-d\TH:i:s\Z',strtotime($mxdReleaseDate)));
			}

			if(!is_null($mxdTags)){
				foreach(((!is_array($mxdTags)) ? array($mxdTags) : $mxdTags) as $strEachTag){
					$this->add('og','book:tag',$strEachTag);
				}
			}
		}

		public function ogTypeProfile($strFirstName = null,$strLastName = null,$strUsername = null,$strGender = null){

			$this->ogType('profile');

			if(!is_null($strFirstName)){
				$this->add('og','profile:first_name',$strFirstName);
			}

			if(!is_null($strLastName)){
				$this->add('og','profile:last_name',$strLastName);
			}

			if(!is_null($strUsername)){
				$this->add('og','profile:username',$strUsername);
			}

			if(in_array($strGender,array('male','female'))){
				$this->add('og','profile:gender',$strGender);
			}
		}

		public function ogTypeWebsite(){
			$this->ogType('website');
		}

		public function ogImage($strURL,$intWidth=null,$intHeight=null,$strType=null,$strSecureURL = null){
			$this->add('og','image',$strURL);

			if(!is_null($strSecureURL)){
				$this->add('og','image:secure_url',$strSecureURL);
			}

			if(!is_null($intWidth)){
				$this->add('og','image:width',$intWidth);
			}

			if(!is_null($intHeight)){
				$this->add('og','image:height',$intHeight);
			}

			if(!is_null($strType)){
				$this->add('og','image:type',$strType);
			}
		}

		public function ogVideo($strURL,$intWidth=null,$intHeight=null,$strType=null,$strSecureURL = null){
			$this->add('og','video',$strURL);

			if(!is_null($strSecureURL)){
				$this->add('og','video:secure_url',$strSecureURL);
			}

			if(!is_null($intWidth)){
				$this->add('og','video:width',$intWidth);
			}

			if(!is_null($intHeight)){
				$this->add('og','video:height',$intHeight);
			}

			if(!is_null($strType)){
				$this->add('og','video:type',$strType);
			}
		}

		public function ogAudio($strURL,$strType=null,$strSecureURL = null){
			$this->add('og','audio',$strURL);

			if(!is_null($strSecureURL)){
				$this->add('og','audio:secure_url',$strSecureURL);
			}

			if(!is_null($strType)){
				$this->add('og','audio:type',$strType);
			}
		}

		public function ogURL($strContent){
			$this->add('og','url',$strContent);
		}

		public function ogCustom($strKey,$strContent){
			$this->add('og',$strKey,$strContent);
		}

		public function generate(){

			$strOut = '';

			foreach($this->arrTags as $arrEachTag) {
				//Do not output blank SEO tags, ignore them
				if($arrEachTag['value'] != ''){
					$strOut .= $this->createTag($arrEachTag) . "\n";
				}
			}

			return $strOut;
		}

		protected function createTag($arrData){

			$strOut = '';

			switch($arrData['type']){
				case'title':
					$strOut = sprintf("<title>%s</title>", $arrData['value']);
					break;

				case'meta':

					if($arrData['name'] == 'charset'){
						$strOut = sprintf('<meta charset="%s">', $arrData['value']);
					}else{
						$strOut = sprintf('<meta name="%s" content="%s">', $arrData['name'], $arrData['value']);
					}

					break;

				case'og':
					$strOut = sprintf('<meta property="og:%s" content="%s">', $arrData['name'], $arrData['value']);
					break;

				case'script':
					$strOut = sprintf('<script src="%s"></script>', $arrData['value']['src']);
					break;

				case'link':
					$strOut = sprintf('<link href="%s" type="text/css" rel="%s"/>', $arrData['value']['href'], $arrData['value']['rel']);
					break;
			}

			return $strOut;
		}
	}