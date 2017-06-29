/*!
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

class twistdebug {
	constructor( noConflict, arrThingsToLog = [] ) {
		try {
			let args = [
				'%c %c %c TwistPHP Debug %c %c ',
				'font-size: 15px; background: #2a5200;',
				'font-size: 17px; background: #3f7a00;',
				'color: #FFF; font-size: 18px; background: #539F00;',
				'font-size: 17px; background: #3f7a00;',
				'font-size: 15px; background: #2a5200;'
			];

			console.log.apply( console, args );
		} catch( e ) {
			if( console.info ) {
				console.info( 'TwistPHP Debug' );
			} else {
				console.log( 'TwistPHP Debug' );
			}
		}

		const $ = ( noConflict === true ) ? window.jQuery.noConflict( true ) : window.jQuery;

		for( let objErrorLog of arrThingsToLog ) {
			this.error( objErrorLog.title, objErrorLog.message, objErrorLog.url, objErrorLog.line, objErrorLog.column, objErrorLog.error );
		}

		window.onerror = ( strErrorMessage, strURL, intLineNumber, intColumn, objError ) => {
			console.log( 'HAHAHAHA' );
			this.error( 'OH NOES!', strErrorMessage, strURL, intLineNumber, intColumn, objError );
			return true;
		};

		this.setupUI();

		console.info( 'TwistPHP Debug is now loaded with jQuery v.' + $.fn.jquery );
	}

	static getScript( url, integrity = null, onSuccess = () => {} ) {
		let domScript = document.createElement( 'script' ),
				domHead = document.getElementsByTagName( 'head' )[0],
				blDone = false;

		onSuccess = ( typeof integrity === 'function' ) ? integrity : ( ( typeof onSuccess === 'function' ) ? onSuccess : () => {} );

		domScript.src = url;
		domScript.crossorigin = 'anonymous';
		if( integrity !== null ) {
			// domScript.integrity = integrity;
		}

		domScript.onload = domScript.onreadystatechange = function() {
			if( !blDone &&
					( !this.readyState ||
					this.readyState === 'loaded' ||
					this.readyState === 'complete' ) ) {
				blDone = true;
				try {
					onSuccess();
				} catch( err ) {
					console.error( err );
				}
				domScript.onload = domScript.onreadystatechange = null;
				domHead.removeChild( domScript );
			}
		};

		domHead.appendChild( domScript );
	}

	objectLength( objIn ) {
		let intLength = 0;
		for( let mxdKey in objIn ) {
			if( objIn.hasOwnProperty( mxdKey ) ) {
				intLength++;
			}
		}
		return intLength;
	}

	logToTwist( jqsAppendTo, strColour, mxdValue, objDetails, strURL, intLineNumber, intColumn ) {
		if( mxdValue ) {
			let strLogHTML = mxdValue || '',
					strTitle = '',
					strDetailsHTML = '';

			if( typeof mxdValue === 'object' ) {
				strLogHTML = '<pre>' + JSON.stringify( mxdValue, undefined, 2 ) + '</pre>';
			}

			if( typeof objDetails === 'object' ) {
				for( let strDetail in objDetails ) {
					let strKey = strDetail.charAt( 0 ).toUpperCase() + strDetail.slice( 1 ).replace( '_', ' ' ),
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

			let jqoLogBox = $( '<div class="twist-debug-box-' + strColour + '" data-title="' + strTitle + '"/>' ).html( strLogHTML );

			if( strDetailsHTML !== '' ) {
				jqoLogBox.append( '<div class="twist-debug-more-details"><dl>' + strDetailsHTML + '</dl></div><a href="#twist-debug-more-details" class="twist-debug-more-details">&ctdot;</a>' );
			}

			$( jqsAppendTo ).append( jqoLogBox );

			return true;
		} else {
			return false;
		}
	}

	error( mxdValue, strURL, intLineNumber, intColumn ) {
		let objDetails = {
			type: typeof mxdValue,
			length: ( typeof mxdValue === 'object' ) ? this.objectLength( mxdValue ) : mxdValue.length
		};

		if( this.logToTwist( '#twist-debug-messages-list', 'red', '<p>' + mxdValue + '</p>', objDetails, strURL, intLineNumber, intColumn ) ) {
			let jqoErrorCount = $( '#twist-debug-errors' );

			jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
		}
	}

	warn( mxdValue, strURL, intLineNumber, intColumn ) {
		let objDetails = {
			type: typeof mxdValue,
			length: ( typeof mxdValue === 'object' ) ? this.objectLength( mxdValue ) : mxdValue.length
		};

		if( this.logToTwist( '#twist-debug-messages-list', 'yellow', '<p>' + mxdValue + '</p>', objDetails, strURL, intLineNumber, intColumn ) ) {
			let jqoErrorCount = $( '#twist-debug-warnings' );

			jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
		}
	}

	log( mxdValue, strURL, intLineNumber, intColumn ) {
		let objDetails = {
			type: typeof mxdValue,
			length: ( typeof mxdValue === 'object' ) ? this.objectLength( mxdValue ) : mxdValue.length
		};

		if( this.logToTwist( '#twist-debug-messages-list', 'blue', '<p>' + mxdValue + '</p>', objDetails, strURL, intLineNumber, intColumn ) ) {
			let jqoErrorCount = $( '#twist-debug-dumps' );

			jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
		}
	}

	logAJAX( blSuccess, objResponse, objRequest ) {
		let objRequestToLog = {
			type: objRequest.type,
			URL: objRequest.url,
			timeout: objRequest.timeout,
			cache: objRequest.cache,
			request_data: objRequest.data
		};

		if( this.logToTwist( '#twist-debug-ajax-list', blSuccess ? 'green' : 'red', objResponse, objRequestToLog, objRequest.type + ' ' + objRequest.url ) ) {
			let jqoErrorCount = $( '#twist-debug-ajax-count' );

			jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
		}
	}

	logFileUpload( resFile, objResponse ) {
		let strPreview = ( objResponse.support && objResponse.support['thumb-128'] ) ? objResponse.support['thumb-128'] : objResponse.uri_preview,
				strLogHTML = '<pre>' + JSON.stringify( objResponse, undefined, 2 ) + '</pre><div class="twist-debug-fileupload-preview"><img src="' + strPreview + '"></div>';

		if( this.logToTwist( '#twist-debug-fileupload-list', 'green', strLogHTML, resFile, resFile.name ) ) {
			let jqoErrorCount = $( '#twist-debug-fileupload-count' );

			jqoErrorCount.attr( 'data-count', parseInt( jqoErrorCount.attr( 'data-count' ) ) + 1 );
		}
	}

	setupUI() {
		let jqoTwistDebugBlocks = $( '#twist-debug-blocks' ),
				jqoTwistDebugDetails = $( '#twist-debug-details' );

		$( '.twist-debug-box, [class^="twist-debug-box-"], [class*=" twist-debug-box-"]' ).has( '.twist-debug-more-details' ).each(
				function() {
					let jqoMoreDetails = $( this ).find( '.twist-debug-more-details' );

					jqoMoreDetails.after( '<a href="#twist-debug-more-details" class="twist-debug-more-details">&ctdot;</a>' );
				}
		);
		jqoTwistDebugBlocks.on( 'click', 'a',
				function( e ) {
					e.preventDefault();
					let jqoThisBlock = $( this );
					if( jqoThisBlock.hasClass( 'current' ) ) {
						jqoTwistDebugDetails.removeClass( 'show' );
						jqoThisBlock.removeClass( 'current' );
					} else {
						let jqsTarget = jqoThisBlock.attr( 'href' );

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
		jqoTwistDebugDetails.find( 'table' ).wrap( '<div class="table-wrapper"/>' );

		$( '#twist-debug' ).addClass( 'ready' );
	}
}






(function( window, undefined ) {
			let blOtherJSLibrary = false,
					arrThingsToLog = [],
					addDebugToWindow = ( instance ) => {
						if( !window.twist ) {
							window.twist = {debug: instance};
						} else {
							window.twist.debug = instance;
						}
					};

			window.onerror = ( strErrorMessage, strURL, intLineNumber, intColumn, objError ) => {
				arrThingsToLog.push( {
					message: '<strong>JS Error:</strong> ' + strErrorMessage,
					url: strURL,
					line: intLineNumber,
					column: intColumn,
					error: objError
				} );

				return true;
			};

			if( typeof window.jQuery === 'undefined' ) {
				blOtherJSLibrary = ( typeof window.$ === 'function' );

				twistdebug.getScript( 'https://code.jquery.com/jquery-3.2.1.slim.min.js', 'sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=', () => {
							if( typeof window.jQuery === 'undefined' ) {
								console.error( 'This is embarrassing... jQuery couldn\'t be loaded' );
							} else {
								if( !blOtherJSLibrary ) {
									addDebugToWindow( new twistdebug( false, arrThingsToLog ) );
								} else {
									console.warn( 'Another JS library controls $' );
									addDebugToWindow( new twistdebug( true, arrThingsToLog ) );
								}
							}
						} );
			} else {
				console.info( 'jQuery v.' + $.fn.jquery + ' already exists' );
				addDebugToWindow( new twistdebug( false, arrThingsToLog ) );
			}
		})( window );