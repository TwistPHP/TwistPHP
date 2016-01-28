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
	function( window, document, undefined ) {
		var arrThingsToLog = [],
				log = function() {
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
				},
				funOriginalWindowError = window.onerror || function() {},
				blUseOriginalError = false;

		window.onerror = function( strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
			var objToStack = {
				message: '<strong>JS Error:</strong> ' + strErrorMessage,
				url: strURL,
				line: intLineNumber,
				column: intColumn,
				error: objError
			};

			arrThingsToLog.push( objToStack );

			if( blUseOriginalError ) {
				//funOriginalWindowError.call( this );
			}
	
			return true;
		};

		var blOtherJSLibrary = false,
				getScript = function( strURL, funSuccess ) {
					var domScript = document.createElement( 'script' ),
							domHead = document.getElementsByTagName( 'head' )[0],
							blDone = false;

					funSuccess = ( typeof funSuccess === 'function' ) ? funSuccess : function() {};

					domScript.src = strURL;
					
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
							objectLength = function( objIn ) {
								var intLength = 0;
								for( var mxdKey in objIn ) {
									if( objIn.hasOwnProperty( mxdKey ) ) {
										intLength++;
									}
								}
								return intLength;
							},
							logToTwist = function( jqsAppendTo, strColour, mxdValue, objDetails, strURL, intLineNumber, intColumn ) {
								if( mxdValue ) {
									var strLogHTML = '<p>' + ( mxdValue || '' ) + '</p>',
											strTitle = '',
											strDetailsHTML = '';

									if( typeof mxdValue === 'object' ) {
										strLogHTML = '<pre>' + JSON.stringify( mxdValue, undefined, 2 ) + '</pre>';
									}

									if( typeof objDetails === 'object' ) {
										for( var strDetail in objDetails ) {
											var strKey = strDetail.charAt( 0 ).toUpperCase() + strDetail.slice( 1 ).replace( '_', ' ' ),
													strValue = ( typeof objDetails[strDetail] === 'object' ) ? '<pre>' + JSON.stringify( objDetails[strDetail], undefined, 2 ) + '</pre>' : objDetails[strDetail];
											strDetailsHTML += '<dt>' + strKey + '</dt><dd>' + strValue + '</dd>';
										}
									}

									if( strURL !== undefined ) {
										if( strURL !== '' ) {
											if( intLineNumber !== undefined &&
													intLineNumber !== '' ) {
												if( intColumn !== undefined &&
														intColumn !== '' ) {
													strTitle = strURL + ', line ' + intLineNumber + ', column ' + intColumn;
												} else {
													strTitle = strURL + ', line ' + intLineNumber;
												}
											} else {
												strTitle = strURL;
											}
										}
									} else {
										strTitle = 'JavaScript [' + ( new Date() ).getTime() + ']';
									}

									var jqoLogBox = $( '<div class="twist-debug-box-' + strColour + ' twist-debug-message" data-title="' + strTitle + '"/>' ).html( strLogHTML );

									if( strDetailsHTML !== '' ) {
										jqoLogBox.append( '<div class="twist-debug-more-details"><dl>' + strDetailsHTML + '</dl></div><a href="#twist-debug-more-details" class="twist-debug-more-details">&ctdot;</a>' );
									}

									$( jqsAppendTo ).append( jqoLogBox );

									return true;
								} else {
									return false;
								}
							};

					this.error = function( mxdValue, strURL, intLineNumber, intColumn ) {
						var objDetails = {
							type: typeof mxdValue,
							length: ( typeof mxdValue === 'object' ) ? objectLength( mxdValue ) : mxdValue.length
						};

						if( logToTwist( '#twist-debug-messages-list', 'red', mxdValue, objDetails, strURL, intLineNumber, intColumn ) ) {
							var jqoErrorCount = $( '#twist-debug-errors' );

							jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
						}
					};

					this.warn = function( mxdValue, strURL, intLineNumber, intColumn ) {
						var objDetails = {
							type: typeof mxdValue,
							length: ( typeof mxdValue === 'object' ) ? objectLength( mxdValue ) : mxdValue.length
						};

						if( logToTwist( '#twist-debug-messages-list', 'yellow', mxdValue, objDetails, strURL, intLineNumber, intColumn ) ) {
							var jqoErrorCount = $( '#twist-debug-warnings' );

							jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
						}
					};

					this.log = function( mxdValue, strURL, intLineNumber, intColumn ) {
						var objDetails = {
							type: typeof mxdValue,
							length: ( typeof mxdValue === 'object' ) ? objectLength( mxdValue ) : mxdValue.length
						};

						if( logToTwist( '#twist-debug-messages-list', 'blue', mxdValue, objDetails, strURL, intLineNumber, intColumn ) ) {
							var jqoErrorCount = $( '#twist-debug-dumps' );

							jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
						}
					};

					this.logAJAX = function( blSuccess, objResponse, objRequest ) {
						var objRequestToLog = {
							type: objRequest.type,
							URL: objRequest.url,
							timeout: objRequest.timeout,
							cache: objRequest.cache,
							request_data: objRequest.data
						};

						if( logToTwist( '#twist-debug-ajax-list', blSuccess ? 'green' : 'red', objResponse, objRequestToLog, objRequest.type + ' ' + objRequest.url ) ) {
							var jqoErrorCount = $( '#twist-debug-ajax-count' );

							jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
						}
					};

					this.logFileUpload = function( strPreviewURI, objDetails, strOriginalFilename ) {
						if( logToTwist( '#twist-debug-fileupload-list', 'green', '[IMAGE]', {width:'200px'}, 'uploaded-image.png' ) ) {
							var jqoErrorCount = $( '#twist-debug-fileupload-count' );

							jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
						}
					};

					for( var intStackedLogError in arrThingsToLog ) {
						var objErrorLog = arrThingsToLog[intStackedLogError];
						logError( objErrorLog.title, objErrorLog.message, objErrorLog.url, objErrorLog.line, objErrorLog.column, objErrorLog.error );
					}

					$( '.twist-debug-box, [class^="twist-debug-box-"], [class*=" twist-debug-box-"]' ).has( '.twist-debug-more-details' ).each(
						function() {
							var jqoMoreDetails = $( this ).find( '.twist-debug-more-details' );

							jqoMoreDetails.after( '<a href="#twist-debug-more-details" class="twist-debug-more-details">&ctdot;</a>' );
						}
					);
					jqoTwistDebugBlocks.on( 'click', 'a',
						function( e ) {
							e.preventDefault();
							var jqoThisBlock = $( this );
							if( jqoThisBlock.hasClass( 'current' ) ) {
								jqoTwistDebugDetails.removeClass( 'show' );
								jqoThisBlock.removeClass( 'current' );
							} else {
								var jqsTarget = jqoThisBlock.attr( 'href' );

								jqoTwistDebugDetails.addClass( 'show' ).children( 'div' ).hide().filter( jqsTarget ).show();
								jqoTwistDebugBlocks.find( 'a.current' ).removeClass( 'current' );
								jqoThisBlock.addClass( 'current' );
							}
						}
					);
					$( '#close-twist-debug-details' ).on( 'click',
						function( e ) {
							e.preventDefault();
							jqoTwistDebugBlocks.find( 'a.current' ).removeClass( 'current' );
							jqoTwistDebugDetails.removeClass( 'show' );
						}
					);
					jqoTwistDebugDetails.on( 'click', 'a[href="#twist-debug-more-details"]',
						function( e ) {
							e.preventDefault();

							$( this ).prev( '.twist-debug-more-details' ).slideToggle();
						}
					);
					$( '#twist-debug-details' ).find( 'table' ).wrap( '<div class="table-wrapper"/>' );

					$( '#twist-debug' ).addClass( 'ready' );

					info( 'TwistPHP Debug is now loaded with jQuery v.' + $.fn.jquery );

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
			//info( 'jQuery v.' + $.fn.jquery + ' already exists' );
			window.twistdebug = new TwistDebug( false );
		}
	}
)( window, document );