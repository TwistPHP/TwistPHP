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

namespace Twist\Core\Models\Image;

/**
 * Image object that allow the manipulation of images, adding text, inserting watermarks, applying effects, resizing and altering output quality.
 * The object can both import and export an image in Base64 encoding and Raw image source. Images can be served to screen our output as a file if required.
 * @package TwistPHP\Packages
 */
class Image{

	protected $mxdFile = null;
	protected $mxdBase64 = null;
	protected $resImage = null;
	protected $arrImageInfo = array();
	protected $intWidth = 0;
	protected $intHeight = 0;
	protected $intDefaultQuality = 80;

	public function __construct($mxdImage=null,$intWidth=null,$intHeight=null,$strFillColour=null){
		(is_null($mxdImage)) ? $this->create($intWidth,$intHeight,$strFillColour) : $this->load($mxdImage);
	}

	public function __destruct(){
		return (is_resource($this->resImage)) ? imagedestroy($this->resImage) : null;
	}

	protected function load($mxdImage,$blAlphaBlending = true){

		if(preg_match('#^data:image/[^;]+;base64,(.*)$#',$mxdImage,$arrMatches)){

			if(function_exists('getimagesizefromstring')){
				$this->mxdBase64 = $mxdImage;
				$strCleanCode = str_replace(' ','+',$arrMatches[0]);
				$strDecodedImage = base64_decode($strCleanCode);

				$arrImageInfo = getimagesizefromstring($strDecodedImage);
				$this->resImage = imagecreatefromstring($strDecodedImage);
			}else{
				throw new \Exception('PHP 5.4 is required to use method getimagesizefromstring');
			}

		}elseif(file_exists($mxdImage)){

			$this->mxdFile = $mxdImage;
			$arrImageInfo = getimagesize($this->mxdFile);

			switch($arrImageInfo['mime']){
				case 'image/gif':
					$this->resImage = imagecreatefromgif($this->mxdFile);
					break;
				case 'image/jpeg':
					$this->resImage = imagecreatefromjpeg($this->mxdFile);
					break;
				case 'image/png':
					$this->resImage = imagecreatefrompng($this->mxdFile);
					break;
				default:
					throw new \Exception('Invalid image: '.$this->mxdFile);
					break;
			}
		}else{
			throw new \Exception('Failed to load image, unknown input type');
		}

		$this->intWidth = $arrImageInfo[0];
		$this->intHeight = $arrImageInfo[1];

		$this->arrImageInfo = array(
			'width' => $arrImageInfo[0],
			'height' => $arrImageInfo[1],
			'orientation' => $this->detectOrientation(),
			'aspect' => $this->aspectRatio($arrImageInfo[0],$arrImageInfo[1]),
			'exif' => (function_exists('exif_read_data') && $arrImageInfo['mime'] === 'image/jpeg') ? @exif_read_data($this->mxdFile) : null,
			'format' => preg_replace('/^image\//', '', $arrImageInfo['mime']),
			'mime' => $arrImageInfo['mime']
		);

		if($blAlphaBlending){
			imagealphablending($this->resImage, $blAlphaBlending);
			imagesavealpha($this->resImage, true);
		}
	}

	protected function create($intWidth,$intHeight,$strFillColour = null){

		$this->resImage = imagecreatetruecolor($intWidth, $intHeight);

		$this->intWidth = $intWidth;
		$this->intHeight = $intHeight;

		$this->arrImageInfo = array(
			'width' => $intWidth,
			'height' => $intHeight,
			'orientation' => $this->detectOrientation(),
			'aspect_ratio' => $this->aspectRatio($intWidth,$intHeight),
			'exif' => null,
			'format' => 'png',
			'mime' => 'image/png'
		);

		//Original base colour of the image
		if(!is_null($strFillColour)){
			$this->fill($strFillColour);
		}
	}

	/**
	 * Information about the original image such as width, height, orientation
	 * @return array Image Information
	 */
	public function originalInfo(){
		return $this->arrImageInfo;
	}

	/**
	 * Information about the image in its current state such as width, height, orientation
	 * @return array Image Information
	 */
	public function currentInfo(){

		$arrCurrentInfo = $this->arrImageInfo;

		$arrCurrentInfo['width'] = $this->intWidth;
		$arrCurrentInfo['height'] = $this->intHeight;
		$arrCurrentInfo['orientation'] = $this->detectOrientation();
		$arrCurrentInfo['aspect_ratio'] = $this->aspectRatio($this->intWidth,$this->intHeight);

		return $arrCurrentInfo;
	}

	/**
	 * Save the image as a file, the format will be determined by the file extension. Leaving the file name as null will use the original filename.
	 * @param string $strFilename If omitted the original file will be overwritten
	 * @param integer $intQuality Output image quality in percents 0-100
	 * @return $this
	 * @throws \Exception
	 */
	public function save($strFilename=null,$intQuality=null){

		$intQuality = (!is_null($intQuality)) ? $this->keepWithinRange($intQuality,0,100) : $this->intDefaultQuality;
		$strFilename = (!is_null($strFilename)) ? $strFilename : $this->mxdFile;
		$strExtension = (!is_null($strFilename)) ? substr($strFilename, strrpos($strFilename, '.') + 1) : $this->arrImageInfo['format'];

		switch(strtolower($strExtension)){
			case 'gif':
				$blResult = imagegif($this->resImage, $strFilename);
				break;
			case 'jpg':
			case 'jpeg':
				imageinterlace($this->resImage, true);
				$blResult = imagejpeg($this->resImage, $strFilename, round($intQuality));
				break;
			case 'png':
				$blResult = imagepng($this->resImage, $strFilename, round(9 * $intQuality / 100));
				break;
			default:
				throw new \Exception(sprintf("The file format '%s' is not a supported type",$strExtension));
		}

		if(!$blResult){
			throw new \Exception(sprintf('Unable to save the image as %s',$strFilename));
		}

		return $this;
	}

	/**
	 * Outputs the image to the screen without saving
	 * @param string $strFormat If omitted or null - format of original file will be used, may be gif|jpg|png
	 * @param integer $intQuality Output image quality in percents 0-100
	 * @param integer $intCache set the life of the image so that the browser can cache it, defaults to 3600 seconds, set to null for no caching
	 * @param bool $blGZip Output the image compressed with gzip, this will only happen if set to true and the browser accepts gzip encoding
	 * @throws \Exception
	 */
	public function output($strFormat=null,$intQuality=null,$intCache=3600,$blGZip=true){

		//Output to the screen
		$arrData = $this->outputRaw($strFormat,$intQuality);

		//Decide if to GZip the output
		if($blGZip && strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false){
			ob_start('ob_gzhandler');
		}

		header(sprintf('Content-Type: %s',$arrData['mimetype']));

		//Decide if to tell the browser to cache the output
		if(!is_null($intCache)){
			header('Cache-Control: must-revalidate');
			header(sprintf('Expires: %s GMT',gmdate('D, d M Y H:i:s',\Twist::DateTime()->time()+$intCache)));
		}

		echo $arrData['blob'];

		//Remove the image from memory as it is no longer required
		$this->__destruct();
	}

	/**
	 * Outputs the image as a Base64 encoded string
	 * @param string $strFormat If omitted or null - format of original file will be used, may be gif|jpg|png
	 * @param integer $intQuality Output image quality in percents 0-100
	 * @return string
	 */
	public function outputBase64($strFormat=null,$intQuality=null){
		$arrData = $this->outputRaw($strFormat,$intQuality);

		return sprintf('data:%s;base64,%s',$arrData['mimetype'],base64_encode($arrData['blob']));
	}

	/**
	 * Get the raw data of an image that can be output as a file, served to the screen or transformed into a base64 string
	 * @param string$strFormat
	 * @param integer $intQuality
	 * @return array
	 * @throws \Exception
	 */
	protected function outputRaw($strFormat=null,$intQuality=null){

		$arrOut = array();
		$strFormat = !is_null($strFormat) ? $strFormat : $this->arrImageInfo['format'];
		$intQuality = !is_null($intQuality) ? $this->keepWithinRange($intQuality,0,100) : $this->intDefaultQuality;

		ob_start();
		switch(strtolower($strFormat)){
			case 'gif':
				$arrOut['mimetype'] = 'image/gif';
				imagegif($this->resImage);
				break;
			case 'jpeg':
			case 'jpg':
				$arrOut['mimetype'] = 'image/jpeg';
				imageinterlace($this->resImage, true);
				imagejpeg($this->resImage, null, round($intQuality));
				break;
			case 'png':
				$arrOut['mimetype'] = 'image/png';
				imagepng($this->resImage, null, round(9 * $intQuality/100));
				break;
			default:
				ob_end_clean();//End the OB as exception handler to take over here
				throw new \Exception(sprintf("The file format '%s' is not a supported type",$strFormat));
				break;
		}

		$arrOut['blob'] = ob_get_contents();
		ob_end_clean();

		return $arrOut;
	}

	/**
	 * Get the PHP resource for the image in its current state
	 * @return resource an image resource identifier
	 */
	public function resource(){
		return $this->resImage;
	}

	/**
	 * Get the aspect ratio of an image from its width and height
	 * @param integer $intWidth
	 * @param integer $intHeight
	 * @return float
	 */
	protected function aspectRatio($intWidth,$intHeight){
		return $intHeight / $intWidth;
	}

	/**
	 * Determine the orientation of the image that has been loaded into the class
	 * @return string
	 */
	protected function detectOrientation(){

		$intImageX = imagesx($this->resImage);
		$intImageY = imagesy($this->resImage);

		if($intImageX == $intImageY){
			$strOrientation = 'square';
		}else{
			$strOrientation = ($intImageX > $intImageY) ? 'landscape' : 'portrait';
		}

		return $strOrientation;
	}

	/**
	 * Determine the correct orientation from the Exif data and rotate/mirror is necessary
	 * @return string
	 */
	public function fixOrientation(){

		if(count($this->arrImageInfo['exif']) && array_key_exists('Orientation',$this->arrImageInfo['exif'])){

			switch ($this->arrImageInfo['exif']['Orientation']){
				case 2:
					$this->flip('horizonal');
					break;
				case 3:
					$this->rotate(180);
					break;
				case 4:
					$this->rotate(180);
					$this->flip('horizonal');
					break;
				case 5:
					$this->rotate(90);
					$this->flip('horizonal');
					break;
				case 6:
					$this->rotate(90);
					break;
				case 7:
					$this->rotate(-90);
					$this->flip('horizonal');
					break;
				case 8:
					$this->rotate(-90);
					break;
			}
		}
	}

	/**
	 * Converts a hex color value to its RGB equivalent, you can pass in a Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
	 * Red,Green,Blue must be integers between 0-255 and Alpha must be an integer between 0-127
	 * @param mixed $mxdColour
	 * @return array|bool
	 */
	protected function normalizeColor($mxdColour){

		if(is_string($mxdColour)){

			$mxdColour = trim($mxdColour,'#');

			if(strlen($mxdColour) == 6){
				list($intRed, $intGreen, $intBlue) = array( $mxdColour[0].$mxdColour[1], $mxdColour[2].$mxdColour[3], $mxdColour[4].$mxdColour[5] );
			}elseif(strlen($mxdColour) == 3){
				list($intRed, $intGreen, $intBlue) = array( $mxdColour[0].$mxdColour[0], $mxdColour[1].$mxdColour[1], $mxdColour[2].$mxdColour[2] );
			}else{
				return false;
			}

			return array( 'r' => hexdec($intRed), 'g' => hexdec($intGreen), 'b' => hexdec($intBlue), 'a' => 0 );

		}elseif(is_array($mxdColour) && (count($mxdColour) == 3 || count($mxdColour) == 4)){

			if(isset($mxdColour['r'], $mxdColour['g'], $mxdColour['b'])){

				return array(
					'r' => $this->keepWithinRange($mxdColour['r'], 0, 255),
					'g' => $this->keepWithinRange($mxdColour['g'], 0, 255),
					'b' => $this->keepWithinRange($mxdColour['b'], 0, 255),
					'a' => $this->keepWithinRange(isset($mxdColour['a']) ? $mxdColour['a'] : 0, 0, 127)
				);

			}elseif(isset($mxdColour[0], $mxdColour[1], $mxdColour[2])){

				return array(
					'r' => $this->keepWithinRange($mxdColour[0], 0, 255),
					'g' => $this->keepWithinRange($mxdColour[1], 0, 255),
					'b' => $this->keepWithinRange($mxdColour[2], 0, 255),
					'a' => $this->keepWithinRange(isset($mxdColour[3]) ? $mxdColour[3] : 0, 0, 127)
				);
			}
		}

		return false;
	}

	/**
	 * Ensures $intInteger is always within $intMinValue and $intMaxValue range. If $intInteger is lower than $intMinValue, $intMinValue is returned. If $intInteger is higher than $intMaxValue, $intMaxValue is returned.
	 * @param integer $intInteger
	 * @param integer $intMinValue
	 * @param integer $intMaxValue
	 * @return mixed
	 */
	protected function keepWithinRange($intInteger,$intMinValue,$intMaxValue){
		return ($intInteger < $intMinValue) ? $intMinValue : ($intInteger > $intMaxValue) ? $intMaxValue : $intInteger;
	}

	/**
	 * Fill the image background background with a colour, pass in a hex colour or an array of (r,g,b) or (r,g,b,a)
	 * @param string $strFillColour
	 */
	public function fill($strFillColour='#000000'){

		$arrRGBA = $this->normalizeColor($strFillColour);
		$resFillColor = imagecolorallocatealpha($this->resImage, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $arrRGBA['a']);

		imagealphablending($this->resImage, false);
		imagesavealpha($this->resImage, true);

		imagefilledrectangle($this->resImage, 0, 0, $this->intWidth, $this->intHeight, $resFillColor);
	}

	/**
	 * Draw a line on the image, you have the option of setting the width of the line. The line can be drawn at any angle
	 * @param integer $intStartX
	 * @param integer $intStartY
	 * @param integer $intEndX
	 * @param integer $intEndY
	 * @param string $strFillColour
	 * @param int $intWidth
	 * @return bool
	 */
	public function line($intStartX,$intStartY,$intEndX,$intEndY,$strFillColour='#000000',$intWidth=1){

		$arrRGBA = $this->normalizeColor($strFillColour);
		$resFillColor = imagecolorallocatealpha($this->resImage, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $arrRGBA['a']);

		if($intWidth == 1){

			imageline( $this->resImage, $intStartX, $intStartY, $intEndX, $intEndY, $resFillColor );
		}else{

			$t = $intWidth / 2 - 0.5;
			if($intStartX == $intEndX || $intStartY == $intEndY){

				$this->rectangle( round( min( $intStartX, $intEndX ) - $t ), round( min( $intStartY, $intEndY ) - $t ), round( max( $intStartX, $intEndX ) + $t ), round( max( $intStartY, $intEndY ) + $t ), $strFillColour );
			}else{

				$k = ($intEndY-$intStartY) / ($intEndX-$intStartX);
				$a = $t / sqrt( 1 + pow( $k, 2 ) );

				$arrPoints = array(
					round( $intStartX - ( 1 + $k ) * $a ), round( $intStartY + ( 1 - $k ) * $a ),
					round( $intStartX - ( 1 - $k ) * $a ), round( $intStartY - ( 1 + $k ) * $a ),
					round( $intEndX + ( 1 + $k ) * $a ), round( $intEndY - ( 1 - $k ) * $a ),
					round( $intEndX + ( 1 - $k ) * $a ), round( $intEndY + ( 1 + $k ) * $a )
				);

				$this->polygon($arrPoints,4,$strFillColour);
			}
		}
	}

	/**
	 * Draw a rectangle on the image
	 * @param integer $intStartX
	 * @param integer $intStartY
	 * @param integer $intEndX
	 * @param integer $intEndY
	 * @param string $strFillColour
	 * @return bool
	 */
	public function rectangle($intStartX,$intStartY,$intEndX,$intEndY,$strFillColour='#000000'){

		$arrRGBA = $this->normalizeColor($strFillColour);
		$resFillColor = imagecolorallocatealpha($this->resImage, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $arrRGBA['a']);

		return imagefilledrectangle($this->resImage,$intStartX,$intStartY,$intEndX,$intEndY,$resFillColor);
	}

	/**
	 * Draw a polygon on the image
	 * @param array $arrPoints
	 * @param string $strFillColour
	 * @param null $intPointsCount
	 */
	public function polygon($arrPoints,$intPointsCount,$strFillColour='#000000'){

		$arrRGBA = $this->normalizeColor($strFillColour);
		$resFillColor = imagecolorallocatealpha($this->resImage, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $arrRGBA['a']);

		imagefilledpolygon( $this->resImage, $arrPoints, $intPointsCount, $resFillColor );
		imagepolygon( $this->resImage, $arrPoints, $intPointsCount, $resFillColor );
	}


	/**
	 * Add a string to the image as a watermark or caption
	 * @param integer $intStartX
	 * @param integer $intStartY
	 * @param string $strString
	 * @param string $strFillColour
	 */
	public function string($intStartX,$intStartY,$strString,$strFillColour='#000000'){

		$arrRGBA = $this->normalizeColor($strFillColour);
		$resFillColor = imagecolorallocatealpha($this->resImage, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $arrRGBA['a']);

		imagestring($this->resImage,2,$intStartX,$intStartY,$strString,$resFillColor);
	}

	/**
	 * Flip the image horizontally or vertically. This will create the same effect as looking at the image from behind or in a mirror.
	 * @param string $strDirection Direction of the flip x|y
	 * @return $this
	 */
	public function flip($strDirection){

		$resTempImage = imagecreatetruecolor($this->intWidth, $this->intHeight);

		imagealphablending($resTempImage, false);
		imagesavealpha($resTempImage, true);

		switch(strtolower($strDirection)){
			case 'y':
			case 'vertical':
			case 'vertically':
				for ($intY = 0; $intY < $this->intHeight; $intY++) {
					imagecopy($resTempImage, $this->resImage, 0, $intY, 0, $this->intHeight-$intY-1, $this->intWidth, 1);
				}
				break;
			default:
				for($intX = 0; $intX < $this->intWidth; $intX++){
					imagecopy($resTempImage, $this->resImage, $intX, 0, $this->intWidth-$intX-1, 0, 1, $this->intHeight);
				}
				break;
		}

		$this->resImage = $resTempImage;
		return $this;
	}

	/**
	 * Changes the opacity level of the image, between 0.0 and 1.0
	 * @param float|int $intOpacity
	 * @return $this
	 */
	public function opacity($intOpacity){

		// Make a copy of the image
		$resTempImage = imagecreatetruecolor($this->intWidth, $this->intHeight);

		imagealphablending($resTempImage, false);
		imagesavealpha($resTempImage, true);

		imagecopy($resTempImage, $this->resImage, 0, 0, 0, 0, $this->intWidth, $this->intHeight);

		//Create new image with transparent layer (same size as original)
		$this->resImage = imagecreatetruecolor($this->intWidth, $this->intHeight);
		$this->fill(array(0, 0, 0, 127));

		// Merge with specified opacity
		imagecopymerge($this->resImage, $resTempImage, 0, 0, 0, 0, $this->intWidth, $this->intHeight, $this->keepWithinRange($intOpacity, 0, 1) * 100);
		imagedestroy($resTempImage);

		return $this;
	}

	/**
	 * Rotate the image by adding in either left/right which will rotate by 90 degrees, or passing in a number form 0-360 (also negative 0-360 can be specified) in degrees. Blank space created during rotation will be filled with the colour passed in the second parameter.
	 * @param int|string $mxdAngle left|right|0-360
	 * @param string $mxdBackgroundColour
	 * @return $this
	 */
	public function rotate($mxdAngle, $mxdBackgroundColour = '#000000'){

		$mxdAngle = ($mxdAngle == 'left') ? 270 : ($mxdAngle == 'right') ? 90 : $mxdAngle;

		// Perform the rotation
		$arrRGBA = $this->normalizeColor($mxdBackgroundColour);
		$resColour = imagecolorallocatealpha($this->resImage, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $arrRGBA['a']);

		$resTempImage = imagerotate($this->resImage,-($this->keepWithinRange($mxdAngle, -360, 360)), $resColour);

		imagealphablending($resTempImage, false);
		imagesavealpha($resTempImage, true);

		// Update meta data
		$this->intWidth = imagesx($resTempImage);
		$this->intHeight = imagesy($resTempImage);
		$this->resImage = $resTempImage;

		return $this;
	}

	/**
	 * Embed a new image into the main image, a TwistPHP image library object or a local image file path can be passed in with X, Y coordinates
	 * @param resource|string Twist Image Object or File Path
	 * @param integer $intX X position of the overlay image
	 * @param integer $intY Y position of the overlay image
	 * @param integer $intAlphaTransparency Alpha transparency of the merge
	 * @return $this
	 * @throws \Exception
	 */
	public function copy($mxdImage, $intX, $intY, $intAlphaTransparency = 0){

		$blRemoveImage = false;

		if(is_object($mxdImage) && get_class($mxdImage) == 'Twist\Core\Models\Image\Image'){

			//The object is a tewist image object and is OK to use
			$resResource = $mxdImage;

		}elseif(!is_object($mxdImage) && is_file($mxdImage)){

			//Generate an invoice object
			$resResource = new self();
			$resResource->load($mxdImage);

			//As the resource has been generated in this function kill it after use
			$blRemoveImage = true;
		}else{
			throw new \Exception("The image to be copied must either be a TwistPHP image library object or a local image file path");
		}

		$arrInfo = $resResource->currentInfo();

		//Copy the watermark image onto the main image

		if(is_null($intAlphaTransparency) || $intAlphaTransparency == 0){
			imagecopy($this->resImage, $resResource->resource(), $intX, $intY, 0, 0, $arrInfo['width'], $arrInfo['height']);
		}else{
			imagecopymerge($this->resImage, $resResource->resource(), $intX, $intY, 0, 0, $arrInfo['width'], $arrInfo['height'],$intAlphaTransparency);
		}

		if($blRemoveImage){
			$resResource->__destruct();
		}

		return $this;
	}


	/** IMAGE SIZING OPTIONS */

	/**
	 * Crop the image with the following left, top, right, bottom coordinates
	 * @param integer $intX1 Left
	 * @param integer $intY1 Top
	 * @param integer $intX2 Right
	 * @param integer $intY2 Bottom
	 * @return $this
	 */
	public function crop($intX1, $intY1, $intX2, $intY2){

		//Sanitise the X and Y parameters to that they cannot be entered backwards causing an error
		if($intX2 < $intX1) list($intX1, $intX2) = array($intX2, $intX1);
		if($intY2 < $intY1) list($intY1, $intY2) = array($intY2, $intY1);

		$intCropWidth = $intX2 - $intX1;
		$intCropHeight = $intY2 - $intY1;

		//Preform the crop
		$resTempImage = imagecreatetruecolor($intCropWidth, $intCropHeight);

		imagealphablending($resTempImage, false);
		imagesavealpha($resTempImage, true);

		imagecopyresampled($resTempImage, $this->resImage, 0, 0, $intX1, $intY1, $intCropWidth, $intCropHeight, $intCropWidth, $intCropHeight);

		//Update the object image details
		$this->intWidth = $intCropWidth;
		$this->intHeight = $intCropHeight;
		$this->resImage = $resTempImage;

		return $this;
	}

	/**
	 * Proportionally resize to a specified width (scales up and down the image as required)
	 * @param int $intWidth
	 * @return Image
	 */
	public function resizeWidth($intWidth){
		$intHeight = $intWidth * $this->aspectRatio($this->intWidth,$this->intHeight);
		return $this->resize($intWidth,$intHeight);
	}

	/**
	 * Proportionally resize the image to a specified height (scales up and down the image as required)
	 * @param int $intHeight
	 * @return Image
	 */
	public function resizeHeight($intHeight){
		$intWidth = $intHeight / $this->aspectRatio($this->intWidth,$this->intHeight);
		return $this->resize($intWidth,$intHeight);
	}

	/**
	 * Proportionally resize to a specified width (scales up and down the image ONLY, if scaling up is required look at @alias resizeToWidth)
	 * @param int $intMaxWidth
	 */
	public function resizeMaxWidth($intMaxWidth){
		if($this->intWidth > $intMaxWidth){
			$this->resizeWidth($intMaxWidth);
		}
	}

	/**
	 * Proportionally resize the image to a specified height (scales down the image ONLY, if scaling up is required look at @alias resizeToHeight)
	 * @param int $intMaxHeight
	 */
	public function resizeMaxHeight($intMaxHeight){
		if($this->intHeight > $intMaxHeight){
			$this->resizeHeight($intMaxHeight);
		}
	}

	public function resizeMaxDimension($intMaxDimension){
		($this->intWidth > $this->intHeight) ? $this->resizeMaxWidth($intMaxDimension) : $this->resizeMaxHeight($intMaxDimension);
	}

	/**
	 * Resize the image to specified dimensions provided in the width and height parameters
	 * @param int $intWidth
	 * @param int $intHeight
	 * @return $this
	 */
	public function resize($intWidth, $intHeight){

		//Generate new GD image
		$resTempImage = imagecreatetruecolor($intWidth, $intHeight);

		if($this->arrImageInfo['format'] === 'gif'){
			//Preserve transparency in GIFs
			$intTransparentIndex = imagecolortransparent($this->resImage);

			if($intTransparentIndex >= 0){
				$arrTransparentColour = imagecolorsforindex($this->resImage,$intTransparentIndex);
				$intTransparentIndex = imagecolorallocate($resTempImage,$arrTransparentColour['red'],$arrTransparentColour['green'],$arrTransparentColour['blue']);
				imagefill($resTempImage,0,0,$intTransparentIndex);
				imagecolortransparent($resTempImage,$intTransparentIndex);
			}
		}else{
			//Preserve transparency in PNGs (benign for JPEGs)
			imagealphablending($resTempImage,false);
			imagesavealpha($resTempImage,true);
		}

		//Resize
		imagecopyresampled($resTempImage,$this->resImage,0,0,0,0,$intWidth,$intHeight,$this->intWidth,$this->intHeight);

		//Update the object image details
		$this->intWidth = $intWidth;
		$this->intHeight = $intHeight;
		$this->resImage = $resTempImage;

		return $this;
	}

	/**
	 * Get the image to as close to the provided dimensions as possible, and then crops the remaining overflow (from the center) to get the image to be the size specified. Useful for generating thumbnails.
	 * @param integer $intWidth
	 * @param null $intHeight (If omitted, assumed to be equal to $intWidth)
	 * @return mixed
	 */
	public function resizeCover($intWidth, $intHeight = null){

		//Determine the new image height
		$intHeight = (is_null($intHeight)) ? $intWidth : $intHeight;

		//If the new Aspect Ration os bigger then the current aspect ratio then fit to height otherwise fit to width
		if($this->aspectRatio($intWidth,$intHeight) > $this->aspectRatio($this->intWidth,$this->intHeight)){
			$this->resizeHeight($intHeight);

			$intLeft = floor(($this->intWidth/2) - ($intWidth/2));
			$intTop = 0;
		}else{
			$this->resizeWidth($intWidth);

			$intLeft = 0;
			$intTop = floor(($this->intHeight/2) - ($intHeight/2));
		}

		// Return trimmed image
		return $this->crop($intLeft, $intTop, $intWidth+$intLeft, $intHeight+$intTop);
	}

	/**
	 * Keeping the same aspect ratio as the original, contain the image within the width and height provided, any white space will be filled with the fill colour
	 * @param integer $intContainerWidth
	 * @param integer $intContainerHeight
	 * @param string $strFillColour
	 * @return $this
	 */
	public function resizeContain($intContainerWidth, $intContainerHeight,$strFillColour = '#000000'){

		//If it already fits, there's nothing to do
		if($this->intWidth <= $intContainerWidth && $this->intHeight <= $intContainerHeight){
			return $this;
		}

		// Determine aspect ratio
		$intAspectRatio = $this->aspectRatio($this->intWidth,$this->intHeight);

		// Make width fit into new dimensions
		if($this->intWidth > $intContainerWidth){
			$intWidth = $intContainerWidth;
			$intHeight = $intWidth * $intAspectRatio;
		}else{
			$intWidth = $this->intWidth;
			$intHeight = $this->intHeight;
		}

		// Make height fit into new dimensions
		if($intHeight > $intContainerHeight){
			$intHeight = $intContainerHeight;
			$intWidth = $intHeight / $intAspectRatio;
		}

		//Generate new GD image
		$resTempImage = imagecreatetruecolor($intContainerWidth, $intContainerHeight);

		if($this->arrImageInfo['format'] === 'gif'){
			//Preserve transparency in GIFs
			$intTransparentIndex = imagecolortransparent($this->resImage);

			if($intTransparentIndex >= 0){
				$arrTransparentColour = imagecolorsforindex($this->resImage,$intTransparentIndex);
				$intTransparentIndex = imagecolorallocate($resTempImage,$arrTransparentColour['red'],$arrTransparentColour['green'],$arrTransparentColour['blue']);
				imagefill($resTempImage,0,0,$intTransparentIndex);
				imagecolortransparent($resTempImage,$intTransparentIndex);
			}
		}else{
			//Preserve transparency in PNGs (benign for JPEGs)
			imagealphablending($resTempImage,false);
			imagesavealpha($resTempImage,true);

			$arrRGBA = $this->normalizeColor($strFillColour);
			$resFillColor = imagecolorallocatealpha($resTempImage, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $arrRGBA['a']);

			imagefill($resTempImage,0,0,$resFillColor);
		}

		//Center the placement of the new image within the canvas
		$intPositionX = ($intContainerWidth - $intWidth)/2;
		$intPositionY = ($intContainerHeight - $intHeight)/2;

		//Resample
		imagecopyresampled($resTempImage,$this->resImage,$intPositionX,$intPositionY,0,0,$intWidth,$intHeight,$this->intWidth,$this->intHeight);

		//Update the object image details
		$this->intWidth = $intContainerWidth;
		$this->intHeight = $intContainerHeight;
		$this->resImage = $resTempImage;

		return $this;
	}

	/** IMAGE EFFECTS */

	/**
	 * Apply Antialias to the image
	 * @return $this
	 */
	public function antialias(){
		imageantialias( $this->resImage, true );
		return $this;
	}

	/**
	 * Apply a Blur effect to the image, select the type of blur that is required and set the number of passes to make, defaults to 1
	 * @param string $strType selective|gaussian
	 * @param int $intTotalPasses
	 * @return $this
	 */
	public function filterBlur($strType = 'selective', $intTotalPasses = 1) {
		for($intPass = 0; $intPass < $intTotalPasses; $intPass++){
			imagefilter($this->resImage, ($strType == 'selective') ? IMG_FILTER_SELECTIVE_BLUR : IMG_FILTER_GAUSSIAN_BLUR);
		}
		return $this;
	}

	/**
	 * Apply a Brightness effect to the image, a level can be passed in for the required level of brightness between -255 and 255. Defaults to 0
	 * @param int $intLevel
	 * @return $this
	 */
	public function filterBrightness($intLevel = 0){
		imagefilter($this->resImage, IMG_FILTER_BRIGHTNESS, $this->keepWithinRange($intLevel, -255, 255));
		return $this;
	}

	/**
	 * Apply a Colorize effect to the image, the image will be colorized according the the HEX colour value and opacity 0.0 - 1.0 value
	 * @param string $strColour
	 * @param int $fltOpacity
	 * @return $this
	 */
	public function filterColorize($strColour, $fltOpacity = 0) {
		$arrRGBA = $this->normalizeColor($strColour);
		$intAlpha = $this->keepWithinRange(127 - (127 * $fltOpacity), 0, 127);
		imagefilter($this->resImage, IMG_FILTER_COLORIZE, $arrRGBA['r'], $arrRGBA['g'], $arrRGBA['b'], $intAlpha);
		return $this;
	}

	/**
	 * Apply a Contrast effect to the image, a level can be passed in for the required level of contrast between -100 and 100. Defaults to 0
	 * @param int $intLevel
	 * @return $this
	 */
	public function filterContrast($intLevel = 0){
		imagefilter($this->resImage, IMG_FILTER_CONTRAST, $this->keepWithinRange($intLevel, -100, 100));
		return $this;
	}

	/**
	 * Apply a Edges effect to the image, the image will be converted to greyscale
	 * @return $this
	 */
	public function filterDesaturate(){
		imagefilter($this->resImage, IMG_FILTER_GRAYSCALE);
		return $this;
	}

	/**
	 * Apply a Edges effect to the image
	 * @return $this
	 */
	public function filterEdges(){
		imagefilter($this->resImage, IMG_FILTER_EDGEDETECT);
		return $this;
	}

	/**
	 * Apply a Emboss effect to the image
	 * @return $this
	 */
	public function filterEmboss(){
		imagefilter($this->resImage, IMG_FILTER_EMBOSS);
		return $this;
	}

	/**
	 * Apply a Invert effect to the image, the image will be inverted to a negative of the original
	 * @return $this
	 */
	public function filterInvert(){
		imagefilter($this->resImage, IMG_FILTER_NEGATE);
		return $this;
	}

	/**
	 * Apply a Sketch effect to the image
	 * @return $this
	 */
	public function filterMeanRemoval(){
		imagefilter($this->resImage, IMG_FILTER_MEAN_REMOVAL);
		return $this;
	}

	/**
	 * Apply a Pixalate effect to the image, pass in the pixel block size. The image will be made of of pixel blocks of the given size.
	 * @param int $intBlockPixelSize
	 * @return $this
	 */
	public function filterPixelate($intBlockPixelSize = 10){
		imagefilter($this->resImage, IMG_FILTER_PIXELATE, $intBlockPixelSize, true);
		return $this;
	}

	/**
	 * Apply a Sepia effect to the image
	 * @return $this
	 */
	public function filterSepia(){
		imagefilter($this->resImage, IMG_FILTER_GRAYSCALE);
		imagefilter($this->resImage, IMG_FILTER_COLORIZE, 100, 50, 0);
		return $this;
	}

	/**
	 * @alias filterMeanRemoval
	 * @return $this
	 */
	public function filterSketch(){
		return $this->filterMeanRemoval();
	}

	/**
	 * Apply a Smooth effect to the image, a level can be passed in for the required level of smoothing between -10 and 10. Defaults to 0
	 * @param int $intLevel
	 * @return $this
	 */
	public function filterSmooth($intLevel = 0){
		imagefilter($this->resImage, IMG_FILTER_SMOOTH, $this->keepWithinRange($intLevel, -10, 10));
		return $this;
	}

	/**
	 * Load an image to be overlaid onto the main image as a watermark
	 * @param string $strFilename Local path to image file
	 * @param int $intMarginRight Pixel margin between the right of the main image and the right of the watermark
	 * @param int $intMarginBottom Pixel margin between the right of the main image and the right of the watermark
	 * @param null|integer $intWidth Output width of the watermark image
	 * @param null|integer $intHeight Output height of the watermark image
	 * @param null|integer $intAlphaTransparency Set the alpha transparency of the watermark
	 * @return Image
	 * @throws \Exception
	 */
	public function watermark($strFilename,$intMarginRight = 30,$intMarginBottom = 30,$intWidth = null,$intHeight = null,$intAlphaTransparency = 0){

		$resWatermark = new self();
		$resWatermark->load($strFilename,true);

		//Resize the image if required
		if(!is_null($intWidth) && !is_null($intHeight)){
			$resWatermark->resize($intWidth,$intHeight);
		}elseif(!is_null($intWidth)){
			$resWatermark->resizeWidth($intWidth);
		}elseif(!is_null($intHeight)){
			$resWatermark->resizeHeight($intHeight);
		}

		$arrInfo = $resWatermark->currentInfo();

		$intPositionX = ($this->intWidth - $arrInfo['width']) - $intMarginRight;
		$intPositionY = ($this->intHeight - $arrInfo['height']) - $intMarginBottom;

		return $this->copy($resWatermark,$intPositionX,$intPositionY,$intAlphaTransparency);
	}
}