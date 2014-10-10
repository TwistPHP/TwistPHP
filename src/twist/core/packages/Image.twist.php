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

	class Image extends ModuleBase{

		public function __construct(){
			if(!extension_loaded('gd')){
				throw new \Exception('Required extension GD is not loaded.');
			}

            require_once sprintf('%s/libraries/Image/Object.lib.php',DIR_FRAMEWORK_PACKAGES);
		}

		/**
		 * Load an existing image into an object
		 * @param $mxdImage
		 */
		public function load($mxdImage){
			return new ImageObject($mxdImage);
		}

		/**
		 * Create a new image from scratch
		 * @param $intWidth
		 * @param $intHeight
		 * @param $strFillColour
		 */
		public function create($intWidth,$intHeight,$strFillColour){
			return new ImageObject(null,$intWidth,$intHeight,$strFillColour);
		}

		/**
		 * Get the URI for a placeholder image
		 * @param int $intWidth
		 * @param int $intHeight
		 * @param string $strBackgroundColour
		 * @param string $strLineColour
		 * @return string
		 */
		public function placeholder($intWidth = 400,$intHeight = 300,$strBackgroundColour = '#999999',$strLineColour = '#ffffff'){

			$strURL = sprintf('%s://%s%s?width=%s&height=%d&bg=%s&line=%s',
				\Twist::framework()->setting('HTTP_PROTOCOL'),
				\Twist::framework()->setting('HTTP_HOST'),
				FRAMEWORK_URI,
				$intWidth,
				$intHeight,
				str_replace('#','',$strBackgroundColour),
				str_replace('#','',$strLineColour)
			);

			return $strURL;
		}

		/**
		 * Template tags using the tag {image:X}
		 * - Output a placeholder URI {image:placholder,[int width],[int height],[hex bgcolour],[hex linecolour]}
		 *
		 * @param $strReference
		 * @return mixed|string
		 */
		public function templateExtension($strReference){

			$strOut = '';
			$arrParams = array();

			if(strstr($strReference,',')){
				$arrParams = explode(',', $strReference);
				$strReference = $arrParams[0];
			}

			switch($strReference){

				case 'placeholder':
					//Allow to call the placeholder function and pass params (width,height,bgcolour,linecolour)
					$strOut = call_user_func_array('placeholder',$arrParams);
					break;
			}

			return $strOut;
		}
	}