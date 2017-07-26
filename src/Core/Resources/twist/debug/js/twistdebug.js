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
	constructor() {
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

		window.onerror = ( strErrorMessage, strURL, intLineNumber, intColumn, objError ) => {
			this.error( strErrorMessage, 'OH NOES!', strURL, intLineNumber, intColumn, objError );
			return false;
		};

		this.setupUI();
		this.outputExistingAJAX();
	}

	static objectLength( objIn ) {
		let intLength = 0;
		for( let mxdKey in objIn ) {
			if( objIn.hasOwnProperty( mxdKey ) ) {
				intLength++;
			}
		}
		return intLength;
	}

	outputExistingAJAX() {
		if( twist.ajax ) {
			for( let instance of twist.ajax.instances ) {
				for( let request of instance.requests ) {
					this.logAJAX( request );
				}
			}
		}
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
					if( objDetails.hasOwnProperty( strDetail ) ) {
						let strKey = strDetail.charAt( 0 ).toUpperCase() + strDetail.slice( 1 ).replace( '_', ' ' ),
								strValue = ( typeof objDetails[strDetail] === 'object' ) ? '<pre>' + JSON.stringify( objDetails[strDetail], undefined, 2 ) + '</pre>' : objDetails[strDetail];
						strDetailsHTML += '<dt>' + strKey + '</dt><dd>' + strValue + '</dd>';
					}
				}

				strDetailsHTML = '<dl class="details">' + strDetailsHTML + '</dl>';
			} else {
				strDetailsHTML = '<p class="details">' + objDetails + '</p>';
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

			let domLogBox = document.createElement( 'div' );
			domLogBox.innerHTML = strLogHTML;
			domLogBox.classList.add( 'twist-debug-box-' + strColour );
			domLogBox.setAttribute( 'data-title', strTitle );

			let domMoreDetails = document.createElement( 'div' );
			domMoreDetails.classList.add( 'twist-debug-more-details' );
			domMoreDetails.innerHTML = strDetailsHTML;

			domLogBox.appendChild( domMoreDetails );

			if( strDetailsHTML !== '' ) {
				let domMoreDetailsButton = document.createElement( 'a' );
				domMoreDetailsButton.classList.add( 'twist-debug-more-details' );
				domMoreDetailsButton.innerHTML = '&ctdot;';
				domMoreDetailsButton.setAttribute( 'href', '#twist-debug-more-details' );
				domMoreDetailsButton.addEventListener( 'click',
						function( e ) {
							e.preventDefault();

							if( domMoreDetails.offsetWidth > 0 && domMoreDetails.offsetHeight > 0 ) {
								domMoreDetails.style.display = 'none';
							} else {
								domMoreDetails.style.display = 'block';
							}
						}
				);

				domLogBox.appendChild( domMoreDetailsButton );
			}

			document.querySelector( jqsAppendTo ).appendChild( domLogBox );

			return domLogBox;
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
			let domErrorCount = document.getElementById( 'twist-debug-errors' );
			domErrorCount.setAttribute( 'data-count', ( parseInt( domErrorCount.getAttribute( 'data-count' ) ) + 1 ).toString() );
		}
	}

	warn( mxdValue, strURL, intLineNumber, intColumn ) {
		let objDetails = {
			type: typeof mxdValue,
			length: ( typeof mxdValue === 'object' ) ? this.objectLength( mxdValue ) : mxdValue.length
		};

		if( this.logToTwist( '#twist-debug-messages-list', 'yellow', '<p>' + mxdValue + '</p>', objDetails, strURL, intLineNumber, intColumn ) ) {
			let domErrorCount = document.getElementById( 'twist-debug-warnings' );
			domErrorCount.setAttribute( 'data-count', ( parseInt( domErrorCount.getAttribute( 'data-count' ) ) + 1 ).toString() );
		}
	}

	log( mxdValue, strURL, intLineNumber, intColumn ) {
		let objDetails = {
			type: typeof mxdValue,
			length: ( typeof mxdValue === 'object' ) ? this.objectLength( mxdValue ) : mxdValue.length
		};

		if( this.logToTwist( '#twist-debug-messages-list', 'blue', '<p>' + mxdValue + '</p>', objDetails, strURL, intLineNumber, intColumn ) ) {
			let domErrorCount = document.getElementById( 'twist-debug-dumps' );
			domErrorCount.setAttribute( 'data-count', ( parseInt( domErrorCount.getAttribute( 'data-count' ) ) + 1 ).toString() );

		}
	}

	logAJAX( objRequest ) {
		let objRequestToLog = {
			uri: objRequest.url,
			options: objRequest.options
		};

		if( objRequestToLog.options.body ) {
			objRequestToLog.options.body = JSON.parse( objRequestToLog.options.body );
		}

		let log = this.logToTwist( '#twist-debug-ajax-list', '', objRequestToLog, 'Waiting...', objRequest.options.method + ' ' + objRequest.url );

		if( log ) {
			let domErrorCount = document.getElementById( 'twist-debug-ajax-count' );
			domErrorCount.setAttribute( 'data-count', ( parseInt( domErrorCount.getAttribute( 'data-count' ) ) + 1 ).toString() );

			objRequest.$debug = log;
		}
	}

	logFileUpload( resFile, objResponse ) {
		let strPreview = ( objResponse.support && objResponse.support['thumb-128'] ) ? objResponse.support['thumb-128'] : objResponse.uri_preview,
				strLogHTML = '<pre>' + JSON.stringify( objResponse, undefined, 2 ) + '</pre><div class="twist-debug-fileupload-preview"><img src="' + strPreview + '"></div>';

		if( this.logToTwist( '#twist-debug-fileupload-list', 'green', strLogHTML, resFile, resFile.name ) ) {
			let domErrorCount = document.getElementById( 'twist-debug-fileupload-count' );
			domErrorCount.setAttribute( 'data-count', ( parseInt( domErrorCount.getAttribute( 'data-count' ) ) + 1 ).toString() );
		}
	}

	setupUI() {
		let domTwistDebugBlocks = document.getElementById( 'twist-debug-blocks' ),
				domTwistDebugDetails = document.getElementById( 'twist-debug-details' );

		for( let boxEl of document.getElementById( 'twist-debug-details' ).querySelectorAll( '.twist-debug-box, [class^="twist-debug-box-"], [class*=" twist-debug-box-"]' ) ) {
			if( boxEl.querySelector( '.twist-debug-more-details' ) ) {
				let domMoreDetails = boxEl.querySelector( '.twist-debug-more-details' ),
						moreDetailsButton = document.createElement( 'a' );

				moreDetailsButton.setAttribute( 'href', '#twist-debug-more-details' );
				moreDetailsButton.classList.add( 'twist-debug-more-details' );
				moreDetailsButton.innerHTML = '&ctdot;';

				domMoreDetails.parentNode.appendChild( moreDetailsButton );
			}
		}

		for( let el of domTwistDebugBlocks.querySelectorAll( 'button' ) ) {
			el.addEventListener( 'click',
					function( e ) {
						e.preventDefault();
						let domThisBlock = this;

						if( domThisBlock.classList.contains( 'current' ) ) {
							domTwistDebugDetails.classList.remove( 'show' );
							domThisBlock.classList.remove( 'current' );
						} else {
							let jqsTarget = domThisBlock.getAttribute( 'data-panel' );

							domTwistDebugDetails.classList.add( 'show' );
							for( let el of domTwistDebugDetails.children ) {
								if( el.tagName.toLowerCase() === 'div' ) {
									el.style.display = 'none';
								}
							}
							document.querySelector( jqsTarget ).style.display = 'block';

							for( let el of domTwistDebugBlocks.querySelectorAll( 'button.current' ) ) {
								el.classList.remove( 'current' );
							}

							domThisBlock.classList.add( 'current' );
						}
					} );
		}

		document.getElementById( 'close-twist-debug-details' ).addEventListener( 'click',
				function( e ) {
					e.preventDefault();
					domTwistDebugBlocks.querySelector( 'button.current' ).classList.remove( 'current' );
					domTwistDebugDetails.classList.remove( 'show' );
				}
		);

		//TODO
		//jqoTwistDebugDetails.find( 'table' ).wrap( '<div class="table-wrapper"/>' );

		document.getElementById( 'twist-debug' ).classList.add( 'ready' );
	}
}

if( !window.twist ) {
	window.twist = {debug: new twistdebug()};
} else {
	window.twist.debug = new twistdebug();
}