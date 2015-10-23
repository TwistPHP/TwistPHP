/*!
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
			};
		}

		var funOriginalError = window.onerror,
				log = function() {
					var arrArguements = arguments;
					if( this.isSet( window.console ) &&
							this.isSet( window.console.log ) &&
							arrArguements.length > 0 ) {
						for( var intArguement in arrArguements ) {
							window.console.log( arrArguements[intArguement] );
						}
					}
				},
				error = function() {
					if( arguments.length > 0 &&
							window.console ) {
						for( var intArguement in arguments ) {
							if( window.console.error ) {
								window.console.error( arguments[intArguement] );
							} else {
								log( arguments[intArguement] );
							}
						}
					}
				};

		window.onerror = function( strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
			//LOG ERROR TO TWIST

			if( funOriginalError ) {
				if( !strErrorMessage.indexOf( 'Script error.' ) ) {
					if( intColumn ) {
						if( objError ) {
							console.log("This is a stack trace! Wow! --> %s", objError.stack);
							return funOriginalError( strErrorMessage, strURL, intLineNumber, intColumn, objError );
						} else {
							return funOriginalError( strErrorMessage, strURL, intLineNumber, intColumn );
						}
					} else {
						return funOriginalError( strErrorMessage, strURL, intLineNumber );
					}
				} else {
					error( 'For security reasons, your browser does not report exceptions from scripts that are of a different origin' );
				}
			}

			return true;
		};
	}
)( window );