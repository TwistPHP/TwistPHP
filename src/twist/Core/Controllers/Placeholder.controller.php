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

/**
 * The route controller for the Placeholder image generator and serves as an image in the browser.
 * This route can be easily added to your site by calling the Twist::Route()->placeholder() alias function.
 * @package Twist\Core\Controllers
 */
class Placeholder extends Base{

	/**
	 * Output a placeholder image, this route uses the following GET parameters if set:
	 * width - Width of the image e.g 400 (do not suffix with px)
	 * height - Height of the image e.g 400 (do not suffix with px)
	 * bg - Background color as a Hex code
	 * line - Line color as a Hex code
	 *
	 * To register this functionality look at Twist::Route()->placeholder()
	 * @related \Twist\Core\Packages\Route
	 */
	public function _index(){

		//ob_start( 'ob_gzhandler' );
		//header( 'Content-type: image/png; charset: UTF-8' );
		//header( 'Cache-Control: must-revalidate' );
		//header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 3600 ) . ' GMT' );

		$intWidth = ( array_key_exists( 'width', $_GET ) && $_GET['width'] != '' ) ? $_GET['width'] : 400;
		$intHeight = ( array_key_exists( 'height', $_GET ) && $_GET['height'] != '' ) ? $_GET['height'] : 300;

		$strBGColour = ( array_key_exists( 'bg', $_GET ) && $_GET['bg'] != '' ) ? $_GET['bg'] : '#999999';
		$strLineColour = ( array_key_exists( 'line', $_GET ) && $_GET['line'] != '' ) ? $_GET['line'] : '#ffffff';

		$intPadding = 10;
		$intThickness = 5;

		$intStartX = $intPadding;
		$intStartY = $intPadding;
		$intEndX = $intWidth - $intPadding;
		$intEndY = $intHeight - $intPadding;

		$objImage = \Twist::Image()->create($intWidth,$intHeight,$strBGColour);
		$objImage->antialias();

		//Draw the border
		$objImage->line($intPadding, $intPadding, $intEndX, $intPadding, $strLineColour, $intThickness);
		$objImage->line($intPadding, $intEndY, $intEndX, $intEndY, $strLineColour, $intThickness);
		$objImage->line($intPadding, $intPadding, $intPadding, $intEndY, $strLineColour, $intThickness);
		$objImage->line($intEndX, $intPadding, $intEndX, $intEndY, $strLineColour, $intThickness);

		//Draw the diagonal lines
		$objImage->line($intPadding + floor( $intThickness / 2 ), $intPadding + floor( $intThickness / 2 ), $intEndX - floor( $intThickness / 2 ), $intEndY - floor( $intThickness / 2 ), $strLineColour, $intThickness );
		$objImage->line($intPadding + floor( $intThickness / 2 ), $intEndY - floor( $intThickness / 2 ), $intEndX - floor( $intThickness / 2 ), $intPadding + floor( $intThickness / 2 ), $strLineColour, $intThickness );

		//Draw the information
		$objImage->rectangle($intPadding + $intThickness, $intPadding + $intThickness, 165, 29, $strLineColour);
		$objImage->string($intPadding + $intThickness + 4, $intPadding + $intThickness, sprintf( '%dx%d PLACEHOLDER', $intWidth, $intHeight ), $strBGColour);

		$objImage->rectangle($intPadding + $intThickness, $intPadding + $intThickness + 17, 165, 46, $strLineColour);
		$objImage->string($intPadding + $intThickness + 4, $intPadding + $intThickness + 17, 'https://twistphp.com/', $strBGColour);

		$objImage->rectangle($intPadding + $intThickness, $intPadding + $intThickness + 34, 207, 63, $strLineColour);
		$objImage->string($intPadding + $intThickness + 4, $intPadding + $intThickness + 34, sprintf( 'Copyright %s TwistPHP.', date( 'Y' ) ), $strBGColour);

		//Output the image to the screen
		$objImage->output();
	}
}