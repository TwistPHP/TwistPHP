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
 * TwistPHP Debug
 * --------------
 * @version 0.9.0
 */

(
	function( window, document ) {
		var arrThingsToLog = { errors: [], warnings: [], logs: [] },
				logErrorToDebugConsole = function( strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
					var objToStack = {
						title: strTitle,
						message: strErrorMessage,
						url: strURL,
						line: intLineNumber,
						column: intColumn,
						error: objError
					};

					arrThingsToLog.errors.push( objToStack );
				},
				logWarningToDebugConsole = function( strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
					var objToStack = {
						title: strTitle,
						message: strErrorMessage,
						url: strURL,
						line: intLineNumber,
						column: intColumn,
						error: objError
					};

					arrThingsToLog.warnings.push( objToStack );
				},
				logToDebugConsole = function( strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
					var objToStack = {
						title: strTitle,
						message: strErrorMessage,
						url: strURL,
						line: intLineNumber,
						column: intColumn,
						error: objError
					};

					arrThingsToLog.logs.push( objToStack );
				},
				/*log = function() {
					if( window.console &&
							window.console.log &&
							arguments.length ) {
						for( var intArguement in arguments ) {
							window.console.log( arguments[intArguement] );
						}
					}
				},
				info = function() {
					if( window.console &&
							arguments.length ) {
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
					if( window.console &&
							arguments.length ) {
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
					if( window.console &&
							arguments.length ) {
						for( var intArguement in arguments ) {
							if( window.console.warn ) {
								window.console.warn( arguments[intArguement] );
							} else {
								log( 'WARNING: ', arguments[intArguement] );
							}
						}
					}
				},*/
				funOriginalWindowError = window.onerror || function() {},
				blUseOriginalError = false;

		window.onerror = function( strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
					logErrorToDebugConsole( 'JS Error', strErrorMessage, strURL, intLineNumber, intColumn, objError );

					return true;
				};

		var blOtherJSLibrary = false,
				getScript = function( strURL, funSuccess ) {
					var domScript = document.createElement( 'script' ),
							domHead = document.getElementsByTagName( 'head' )[0],
							blDone = false;

					funSuccess = ( typeof funSuccess === 'function' ) ? funSuccess : function() {};

					domScript.src = strURL,
					domScript.onload = domScript.onreadystatechange = function() {
						if( !blDone &&
								( !this.readyState ||
									this.readyState === 'loaded' ||
									this.readyState === 'complete' ) ) {
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
				TwistDebug = function( blNoConfilct ) {
					/* ===================================================== GO, GO, GO! ===================================================== */
					var $ = ( blNoConfilct === true ) ? window.jQuery.noConflict( true ) : window.jQuery,
							jqoTwistDebugBlocks = $( '#twist-debug-blocks' ),
							jqoTwistDebugDetails = $( '#twist-debug-details' ),
							logDebug = function( strColour, strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
								var jqoLog = $( '<p/>' ).html( '<strong>' + strTitle + ':</strong> ' + strErrorMessage );

								$( '#twist-debug-messages' ).find( '.twist-debug-column-wrapper' ).append( jqoLog );

								jqoLog.wrap( '<div class="twist-debug-column-100"/>' ).wrap( '<div class="twist-debug-box-' + strColour + ' twist-debug-message" data-title="' + strURL + ', line ' + intLineNumber + '"/>' );
							};

					info( 'jQuery v.' + $.fn.jquery + ' is ready' );

					logErrorToDebugConsole = function( strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
						logDebug( 'red', strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError );

						var jqoErrorCount = $( '#twist-debug-errors' );

						jqoErrorCount.html( jqoErrorCount.find( 'i' )[0].outerHTML + ( parseInt( jqoErrorCount.text() ) + 1 ) ).removeClass( 'twist-debug-hidden' );
					};

					logWarningToDebugConsole = function( strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
						logDebug( 'yellow', strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError );

						var jqoErrorCount = $( '#twist-debug-warnings' );

						jqoErrorCount.html( jqoErrorCount.find( 'i' )[0].outerHTML + ( parseInt( jqoErrorCount.text() ) + 1 ) ).removeClass( 'twist-debug-hidden' );
					};

					logToDebugConsole = function( strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
						logDebug( 'blue', strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError );

						var jqoErrorCount = $( '#twist-debug-dumps' );

						jqoErrorCount.html( jqoErrorCount.find( 'i' )[0].outerHTML + ( parseInt( jqoErrorCount.text() ) + 1 ) ).removeClass( 'twist-debug-hidden' );
					};

					for( var intStackedLog in arrThingsToLog.errors ) {
						var objLog = arrThingsToLog[intStackedLog];
						logErrorToDebugConsole( objLog.title, objLog.message, objLog.url, objLog.line, objLog.column, objLog.error );
					}

					for( var intStackedLog in arrThingsToLog.warnings ) {
						var objLog = arrThingsToLog[intStackedLog];
						logWarningToDebugConsole( objLog.title, objLog.message, objLog.url, objLog.line, objLog.column, objLog.error );
					}

					for( var intStackedLog in arrThingsToLog.logs ) {
						var objLog = arrThingsToLog[intStackedLog];
						logToDebugConsole( objLog.title, objLog.message, objLog.url, objLog.line, objLog.column, objLog.error );
					}

					$( '.twist-debug-box, [class^="twist-debug-box-"], [class*=" twist-debug-box-"]' ).has( '.twist-debug-more-details' ).each(
						function() {
							var jqoMoreDetails = $( this ).find( '.twist-debug-more-details' );

							jqoMoreDetails.after( '<a href="#twist-debug-more-details" class="twist-debug-more-details">&ctdot;</a>' );
						}
					),
					jqoTwistDebugBlocks.on( 'click', 'a',
						function( e ) {
							e.preventDefault();
							var jqoThisBlock = $( this );
							if( jqoThisBlock.hasClass( 'current' ) ) {
								jqoTwistDebugDetails.removeClass( 'show' ),
								jqoThisBlock.removeClass( 'current' );
							} else {
								var jqsTarget = jqoThisBlock.attr( 'href' );

								jqoTwistDebugDetails.addClass( 'show' ).children( 'div' ).hide().filter( jqsTarget ).show(),
								jqoTwistDebugBlocks.find( 'a.current' ).removeClass( 'current' ),
								jqoThisBlock.addClass( 'current' );
							}
						}
					),
					jqoTwistDebugDetails.on( 'click', 'a[href="#close-twist-debug-details"]',
						function( e ) {
							e.preventDefault();
							jqoTwistDebugBlocks.find( 'a.current' ).removeClass( 'current' ),
							jqoTwistDebugDetails.removeClass( 'show' );
						}
					).on( 'click', 'a[href="#twist-debug-more-details"]',
						function( e ) {
							e.preventDefault();

							$( this ).prev( '.twist-debug-more-details' ).slideToggle();
						}
					);

					this.log = logErrorToDebugConsole;
					
					return this;
				};

		if( typeof window.jQuery === 'undefined' ) {
			blOtherJSLibrary = ( typeof window.$ === 'function' );

			getScript( 'http' + ( location.protocol === 'https:' ? 's' : '' ) + '://code.jquery.com/jquery-1.12.0.min.js',
				function() {
					if( typeof window.jQuery === 'undefined' ) {
						error( 'This is embarrassing... jQuery couldn\'t be loaded' );
					} else {
						if( !blOtherJSLibrary ) {
							window.twistdebug = new TwistDebug( false );
						} else {
							warn( 'Another JS library controls $' );
							window.twistdebug = new TwistDebug( true );
						}
					}
				}
			);
		} else {
			info( 'jQuery v.' + $.fn.jquery + ' exists' );
			window.twistdebug = new TwistDebug( false );
		}
	}
)( window, document );