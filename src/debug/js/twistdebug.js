/*!
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

class twistdebug {
	constructor() {
		try {
			let args = [
				'%c %c %c TwistPHP %c %c ',
				'font-size: 15px; background: #2A5200;',
				'font-size: 17px; background: #3F7A00;',
				'color: #FFF; font-size: 18px; background: #539F00;',
				'font-size: 17px; background: #3F7A00;',
				'font-size: 15px; background: #2A5200;'
			];

			console.log.apply( console, args );
		} catch( e ) {
			if( console.info ) {
				console.info( 'TwistPHP' );
			} else {
				console.log( 'TwistPHP' );
			}
		}

		if( window.twist &&
				window.twist.catches ) {
			for( let caught of window.twist.catches ) {
				switch( caught.type ) {
					case 'error':
						this.error.apply( this, caught.details );
						break;
					case 'warning':
						this.warn.apply( this, caught.details );
						break;
				}
			}
		}

		window.onerror = ( strErrorMessage, strURL, intLineNumber, intColumn ) => {
			this.error( strErrorMessage, strURL, intLineNumber, intColumn );
		};

		window.onwarn = ( strErrorMessage, strURL, intLineNumber, intColumn ) => {
			this.warn( strErrorMessage, strURL, intLineNumber, intColumn );
		};

		this.setupUI();
		this.outputExistingAJAX();

		window.console.error = message => {
			this.error( message );
		};

		window.console.warn = message => {
			this.warn( message );
		};

		window.console.info = message => {
			this.info( message );
		};

		window.console.log = message => {
			this.log( message );
		};
	}

	static wrap( query, tag, classes = '' ) {
		document.querySelectorAll( query )
				.forEach( elem => {
					const wrapper = document.createElement( tag );
					if( classes ) {
						wrapper.className = classes;
					}
					elem.parentElement.insertBefore( wrapper, elem );
					wrapper.appendChild( elem );
				} );
	}

	outputExistingAJAX() {
		if( window.twist && window.twist.ajax ) {
			for( let instance of window.twist.ajax.instances ) {
				for( let request of instance.requests ) {
					this.logAJAX( request );
				}
			}
		}
	}

	stringifyJSON( objectIn ) {
		let seen = [];
		let replacer = ( key, value ) => {
			if( value !== null && typeof value === 'object' ) {
				if( seen.indexOf( value ) >= 0 ) {
					return;
				}
				seen.push( value );
			}
			return value;
		};

		return JSON.stringify( objectIn, replacer, 2 );
	}

	logToTwist( jqsAppendTo, strColour, mxdValue, objDetails = null, strURL = undefined, intLineNumber = undefined, intColumn = undefined ) {
		if( mxdValue ) {
			let strLogHTML = mxdValue || '',
					strTitle = '',
					strDetailsHTML = '';

			if( typeof mxdValue === 'object' ) {
				strLogHTML = '<pre>' + this.stringifyJSON( mxdValue ) + '</pre>';
			}

			if( typeof objDetails === 'string' ) {
				strDetailsHTML = '<pre class="details">' + objDetails + '</pre>';
			} else if( !!objDetails && Object.keys( objDetails ).length ) {
				for( let strDetail in objDetails ) {
					if( objDetails.hasOwnProperty( strDetail ) ) {
						let objDetail = objDetails[strDetail];
						let strKey = strDetail.charAt( 0 ).toUpperCase() + strDetail.slice( 1 ).replace( '_', ' ' ),
								strValue = ( typeof objDetail === 'object' ) ? '<pre>' + this.stringifyJSON( objDetail ) + '</pre>' : objDetail;
						strDetailsHTML += '<dt>' + strKey + '</dt><dd>' + strValue + '</dd>';
					}
				}

				strDetailsHTML = '<dl class="details">' + strDetailsHTML + '</dl>';
			}

			if( strURL ) {
				if( intLineNumber ) {
					if( intColumn ) {
						strTitle = strURL + ', line ' + intLineNumber + ', column ' + intColumn;
					} else {
						strTitle = strURL + ', line ' + intLineNumber;
					}
				} else {
					strTitle = strURL;
				}
			} else {
				strTitle = 'JavaScript [' + (new Date()).getTime() + ']';
			}

			let domLogBox = document.createElement( 'div' );
			domLogBox.innerHTML = strLogHTML;
			domLogBox.classList.add( 'twist-debug-box-' + strColour );
			domLogBox.setAttribute( 'data-title', strTitle );

			if( strDetailsHTML ) {
				let domMoreDetails = document.createElement( 'div' );
				domMoreDetails.classList.add( 'twist-debug-more-details' );
				domMoreDetails.innerHTML = strDetailsHTML;

				domLogBox.appendChild( domMoreDetails );

				let domMoreDetailsButton = document.createElement( 'a' );
				domMoreDetailsButton.classList.add( 'twist-debug-more-details-button' );
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

	recordLog( countElement, strColour, mxdValue, strURL = undefined, intLineNumber = undefined, intColumn = undefined ) {
		let objDetails = {
			type: typeof mxdValue,
			length: !!mxdValue ? ( typeof mxdValue === 'object' ? Object.keys( mxdValue ).length : mxdValue.length ) : 0
		};
		let valueToLog = typeof mxdValue === 'object' ? mxdValue : '<p>' + mxdValue + '</p>';

		if( this.logToTwist( '#twist-debug-messages-list', strColour, valueToLog, objDetails, strURL, intLineNumber, intColumn ) ) {
			let domCount = document.getElementById( countElement );
			domCount.setAttribute( 'data-count', (parseInt( domCount.getAttribute( 'data-count' ) ) + 1).toString() );
		}
	}

	error( mxdValue, strURL, intLineNumber, intColumn ) {
		this.recordLog( 'twist-debug-errors', 'red', mxdValue, strURL, intLineNumber, intColumn );
	}

	warn( mxdValue, strURL, intLineNumber, intColumn ) {
		this.recordLog( 'twist-debug-warnings', 'yellow', mxdValue, strURL, intLineNumber, intColumn );
	}

	info( mxdValue, strURL, intLineNumber, intColumn ) {
		this.recordLog( 'twist-debug-info', 'blue', mxdValue, strURL, intLineNumber, intColumn );
	}

	log( mxdValue, strURL, intLineNumber, intColumn ) {
		this.recordLog( 'twist-debug-logs', 'grey', mxdValue, strURL, intLineNumber, intColumn );
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






		//console.debug( log )






		if( log ) {
			let domAJAXCount = document.getElementById( 'twist-debug-ajax-count' );
			domAJAXCount.setAttribute( 'data-count', (parseInt( domAJAXCount.getAttribute( 'data-count' ) ) + 1).toString() );

			objRequest.$debug = log;
		}
	}

	logFileUpload( resFile, objResponse ) {
		let strPreview = (objResponse.support && objResponse.support['thumb-128']) ? objResponse.support['thumb-128'] : objResponse.uri_preview,
				strLogHTML = '<pre>' + this.stringifyJSON( objResponse ) + '</pre><div class="twist-debug-fileupload-preview"><img src="' + strPreview + '"></div>';

		if( this.logToTwist( '#twist-debug-fileupload-list', 'green', strLogHTML, null, resFile.name ) ) {
			let domFileUploadCount = document.getElementById( 'twist-debug-fileupload-count' );
			domFileUploadCount.setAttribute( 'data-count', (parseInt( domFileUploadCount.getAttribute( 'data-count' ) ) + 1).toString() );
		}
	}

	setupUI() {
		let domTwistDebugBlocks = document.getElementById( 'twist-debug-blocks' ),
				domTwistDebugDetails = document.getElementById( 'twist-debug-details' );

		for( let boxEl of document.getElementById( 'twist-debug-details' ).querySelectorAll( '.twist-debug-box, [class^="twist-debug-box-"], [class*=" twist-debug-box-"]' ) ) {
			if( boxEl.querySelector( '.twist-debug-more-details' ) && !boxEl.querySelector( '.twist-debug-more-details-button' ) ) {
				let domMoreDetails = boxEl.querySelector( '.twist-debug-more-details' ),
						domMoreDetailsButton = document.createElement( 'a' );

				domMoreDetailsButton.classList.add( 'twist-debug-more-details-button' );
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

				boxEl.appendChild( domMoreDetailsButton );
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
							domTwistDebugDetails.querySelector( jqsTarget ).style.display = 'block';

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

		twistdebug.wrap( '#twist-debug-details table', 'div', 'table-wrapper' );

		document.getElementById( 'twist-debug' ).classList.add( 'ready' );
	}
}

if( !window.twist ) {
	window.twist = {debug: new twistdebug()};
} else {
	window.twist.debug = new twistdebug();
}

// TODO: Remove dependency
let fontAwesomeLink = document.createElement( 'link' );
fontAwesomeLink.setAttribute( 'rel', 'stylesheet' );
fontAwesomeLink.setAttribute( 'type', 'text/css' );
fontAwesomeLink.setAttribute( 'href', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
document.getElementsByTagName( 'head' )[0].appendChild( fontAwesomeLink );
