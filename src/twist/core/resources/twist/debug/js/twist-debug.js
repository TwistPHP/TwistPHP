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
 * TwistPHP Debug
 * --------------
 * @version 0.9.0
 */

(
	function( window, document ) {
		var log = function() {
					if( window.console
							&& window.console.log
							&& arguments.length ) {
						for( var intArguement in arguments ) {
							window.console.log( arguments[intArguement] );
						}
					}
				},
				info = function() {
					if( window.console
							&& arguments.length ) {
						for( var intArguement in arguments ) {
							if( window.console.info ) {
								window.console.info( arguments[intArguement] );
							} else {
								log( 'INFO: ', arguments[intArguement] );
							}
						}
					}
				},
				error = function() {
					if( window.console
							&& arguments.length ) {
						for( var intArguement in arguments ) {
							if( window.console.error ) {
								window.console.error( arguments[intArguement] );
							} else {
								log( 'ERROR: ', arguments[intArguement] );
							}
						}
					}
				},
				warn = function() {
					if( window.console
							&& arguments.length ) {
						for( var intArguement in arguments ) {
							if( window.console.warn ) {
								window.console.warn( arguments[intArguement] );
							} else {
								log( 'WARNING: ', arguments[intArguement] );
							}
						}
					}
				};

		for( var intLog in ['error', 'warn', 'info'] ) {

		}

		try {
			var blOtherJSLibrary = false,
					getScript = function( strURL, funSuccess ) {
						var domScript = document.createElement( 'script' ),
								domHead = document.getElementsByTagName( 'head' )[0],
								blDone = false;

						funSuccess = ( typeof funSuccess === 'function' ) ? funSuccess : function() {};

						domScript.src = strURL,
								domScript.onload = domScript.onreadystatechange = function() {
									if( !blDone
											&& ( !this.readyState
												|| this.readyState === 'loaded'
												|| this.readyState === 'complete' ) ) {
										blDone = true;
										try {
											funSuccess();
										} catch( err ) {
											error( err );
										}
										domScript.onload = domScript.onreadystatechange = null;
										domHead.removeChild( domScript );
									}
								};

						domHead.appendChild( domScript );
					},
					loadDebugger = function() {
						var $ = jQuery.noConflict( true );
						$( 'body' ).append( '<p>...I am ready</p>' );
						info( 'jQuery v.' + $.fn.jquery + ' ready' );

						alert( window.devicePixelRatio ? window.devicePixelRatio : 1 );

						if( window.devicePixelRatio ) {
							var testElem = document.createElement( 'div' );
							testElem.style.border = '.5px solid transparent';
							document.body.appendChild( testElem );
							if( testElem.offsetHeight == 1 ) {
								document.querySelector( 'html' ).classList.add( 'hairlines' );
								$( 'body' ).append( '<p>hairlines</p>' );
							} else {
								$( 'body' ).append( '<p>no hairlines</p>' );
							}
							document.body.removeChild( testElem );
						} else {
							$( 'body' ).append( '<p>Pixel ratio too small</p>' );
						}

						$( '#twist-debug-blocks' ).on( 'click', 'a',
							function( e ) {
								e.preventDefault();
							}
						);
					};

			if( typeof jQuery === 'undefined' ) {
				blOtherJSLibrary = ( typeof $ === 'function' );

				getScript( '../src/twist/core/resources/jquery/jquery-2.1.3.min.js',
					function() {
						if( typeof jQuery === 'undefined' ) {
							error( 'This is embarrassing... jQuery couldn\'t be loaded' );
						} else {
							if( !blOtherJSLibrary ) {
								loadDebugger();
							} else {
								warn( 'Another JS library controls $' );
								log( $ );
								loadDebugger();
							}
						}
					}
				);
			} else {
				info( 'jQuery v.' + $.fn.jquery + ' exists' );
				loadDebugger();
			}
		} catch( err ) {
			error( err );
		}
	}
)( window, document );