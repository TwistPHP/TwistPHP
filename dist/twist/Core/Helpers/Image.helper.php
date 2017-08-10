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

	use \Twist\Core\Models\Image\Image as ImageObject;

	/**
	 * Image editing suite that allows for new images to be created and existing images to be loaded in for modification
	 * @package TwistPHP\utilities
	 */
	class Image extends Base{

		/**
		 * Checked that the PHP GD library is installed and loads in the image object library.
		 *
		 * @throws \Exception
		 */
		public function __construct(){

			if(!extension_loaded('gd')){
				throw new \Exception('Required extension GD is not loaded.');
			}
		}

		/**
		 * Load an existing image, and Image object will be produced allowing complete control over the image.
		 * Once all changes have been made you can then export the image as a file or serve to the screen.
		 *
		 * @param mixed $mxdImage
		 * @return \Twist\Core\Models\Image\Image Returns an object of the loaded Image
		 */
		public function load($mxdImage){
			return new ImageObject($mxdImage);
		}

		/**
		 * Create a new image from scratch, and Image object will be produced allowing complete control over the image.
		 * Once all changes have been made you can then export the image as a file or serve to the screen.
		 *
		 * @param integer $intWidth
		 * @param integer $intHeight
		 * @param string $strFillColour
		 * @return \Twist\Core\Models\Image\Image Returns an object of the new Image
		 */
		public function create($intWidth,$intHeight,$strFillColour){
			return new ImageObject(null,$intWidth,$intHeight,$strFillColour);
		}
	}