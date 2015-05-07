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
		try {
			var thisPageUsingOtherJSLibrary = false,
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
										funSuccess();
										domScript.onload = domScript.onreadystatechange = null;
										domHead.removeChild( domScript );
									}
								};

						domHead.appendChild( domScript );
					},
					loadDebugger = function() {
						var this$ = jQuery.noConflict( true );
						//console.log( $.fn.jquery );
						console.log( this$.fn.jquery );
						this$( 'body' ).append( '<p>123</p>' );

						if( window.devicePixelRatio
								&& devicePixelRatio >= 2 ) {
							var jqoTestElement = this$( '<div/>' ).style( 'border', '0.5px solid transparent' );
							this$( 'body' ).append( jqoTestElement );
							if( jqoTestElement.height() === 1 ) {
								this$( 'html' ).addClass( 'hairlines2' );
								this$( 'body' ).append( '<p>hairlines 2</p>' );
							}

							var testElem = document.createElement( 'div' );
							testElem.style.border = '.5px solid transparent';
							document.body.appendChild( testElem );
							if( testElem.offsetHeight == 1 ) {
								document.querySelector( 'html' ).classList.add( 'hairlines' );
								this$( 'body' ).append( '<p>hairlines</p>' );
							}
							document.body.removeChild( testElem );
						}
						
					};

			if( typeof jQuery === 'undefined' ) {
				if( typeof $ === 'function' ) {
					thisPageUsingOtherJSLibrary = true;
				}

				getScript( '../src/twist/core/resources/jquery/jquery-2.1.3.min.js',
					function() {
						if( typeof jQuery === 'undefined' ) {
							console.error( 'Uhhh...' );
						} else {
							if( !thisPageUsingOtherJSLibrary ) {
								loadDebugger();
							} else {
								loadDebugger();
							}
						}
					}
				);
			} else {
				loadDebugger();
			}
		} catch( err ) {
			if( window.console ) {
				if( window.console.error ) {
					console.error( err );
				} else if( window.console.log ) {
					console.log( err );
				}
			}
		}
	}
)( window, document );