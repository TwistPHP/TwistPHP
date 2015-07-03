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

(
	function( window ) {
		var funOriginalError = window.onerror;

		window.onerror = function( strErrorMessage, strURL, intLineNumber ) {
			//LOG ERROR TO TWIST

			if( funOriginalError ) {
				return funOriginalError( strErrorMessage, strURL, intLineNumber );
			}

			return false;
		};

		if( window.console ) {
			var objOriginalConsole = window.console;

			window.console.log = function( mxdData ) {
				//LOG ERROR TO TWIST

				if( funOriginalError ) {
					return objOriginalConsole.log( mxdData );
				}

				return false;
			},
			window.console.error = function( mxdData ) {
				//LOG ERROR TO TWIST

				if( funOriginalError ) {
					return objOriginalConsole.error( mxdData );
				}

				return false;
			},
			window.console.warn = function( mxdData ) {
				//LOG ERROR TO TWIST

				if( funOriginalError ) {
					return objOriginalConsole.warn( mxdData );
				}

				return false;
			},
			window.console.debug = function( mxdData ) {
				//LOG ERROR TO TWIST

				if( funOriginalError ) {
					return objOriginalConsole.debug( mxdData );
				}

				return false;
			},
			window.console.info = function( mxdData ) {
				//LOG ERROR TO TWIST

				if( funOriginalError ) {
					return objOriginalConsole.info( mxdData );
				}

				return false;
			}
		}
	}
)( window );