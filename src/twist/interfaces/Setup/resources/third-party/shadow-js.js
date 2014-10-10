/**
 * ================================================================================
 * Shadow JS
 * --------------------------------------------------------------------------------
 * Author:      Andrew Hosgood
 * Version:     1.14.2
 * Date:        31/07/2014
 * ================================================================================
 */

(
	function( screen, window, document ) {
		var Shadow = function() {
				var base64String = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
				fltYearDays = 356.2425;
				this.afterLast = function( strLastInstance, strHaystack, blCaseSensitive ) {
						if( blCaseSensitive === true ) {
							strHaystack = strHaystack.toLowerCase();
						}
						var intIndex = strHaystack.lastIndexOf( strLastInstance );
						return ( intIndex > 0 ) ? strHaystack.slice( intIndex + strLastInstance.length ) : '';
					},
				this.arrayToObject = function( arrRaw ) {
						var objReturn = {};
						for( var mxdIndex in arrRaw ) {
							if( typeof arrRaw[mxdIndex] === 'array' ) {
								objReturn[mxdIndex] = this.arrayToObject( arrRaw[mxdIndex] );
							} else {
								objReturn[mxdIndex] = arrRaw[mxdIndex];
							}
						}
						return objReturn;
					},
				this.base64Decode = function( strIn ) {
						if( this.isSet( window.atob ) ) {
							return decodeURIComponent( window.atob( strIn ) );
						} else {
							// http://kevin.vanzonneveld.net
							// +   original by: Tyler Akins (http://rumkin.com)
							// +   improved by: Thunder.m
							// +      input by: Aman Gupta
							// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
							// +   bugfixed by: Onno Marsman
							// +   bugfixed by: Pellentesque Malesuada
							// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
							// +      input by: Brett Zamir (http://brett-zamir.me)
							// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
							var strOctet1, strOctet2, strOctet3, strHextet1, strHextet2, strHextet3, strHextet4, intBits,
								intChar = 0,
								intOutChar = 0,
								arrOut = [];
							if( !strIn ) {
								return strIn;
							}
							strIn += '';
							do {
								strHextet1 = base64String.indexOf( strIn.charAt( intChar++ ) );
								strHextet2 = base64String.indexOf( strIn.charAt( intChar++ ) );
								strHextet3 = base64String.indexOf( strIn.charAt( intChar++ ) );
								strHextet4 = base64String.indexOf( strIn.charAt( intChar++ ) );
								intBits = strHextet1 << 18 | strHextet2 << 12 | strHextet3 << 6 | strHextet4;
								strOctet1 = intBits >> 16 & 0xff;
								strOctet2 = intBits >> 8 & 0xff;
								strOctet3 = intBits & 0xff;
								if( strHextet3 == 64 ) {
									arrOut[intOutChar++] = String.fromCharCode( strOctet1 );
								} else if( strHextet4 == 64 ) {
									arrOut[intOutChar++] = String.fromCharCode( strOctet1, strOctet2 );
								} else {
									arrOut[intOutChar++] = String.fromCharCode( strOctet1, strOctet2, strOctet3 );
								}
							} while( intChar < strIn.length );
							return arrOut.join( '' );
						}
					},
				this.base64Encode = function( strIn ) {
						var strOut;
						if( this.isSet( window.btoa ) ) {
							strOut = window.btoa( encodeURIComponent( strIn ) );
						} else {
							// http://kevin.vanzonneveld.net
							// +   original by: Tyler Akins (http://rumkin.com)
							// +   improved by: Bayron Guevara
							// +   improved by: Thunder.m
							// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
							// +   bugfixed by: Pellentesque Malesuada
							// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
							// +   improved by: Rafał Kukawski (http://kukawski.pl)
							var strOctet1, strOctet2, strOctet3, strHextet1, strHextet2, strHextet3, strHextet4, intBits,
								intChar = 0,
								intOutChar = 0,
								arrOut = [];
							if( !strIn ) {
								return strIn;
							}
							do {
								strOctet1 = strIn.charCodeAt( intChar++ );
								strOctet2 = strIn.charCodeAt( intChar++ );
								strOctet3 = strIn.charCodeAt( intChar++ );
								intBits = strOctet1 << 16 | strOctet2 << 8 | strOctet3;
								strHextet1 = intBits >> 18 & 0x3f;
								strHextet2 = intBits >> 12 & 0x3f;
								strHextet3 = intBits >> 6 & 0x3f;
								strHextet4 = intBits & 0x3f;
								arrOut[intOutChar++] = base64String.charAt( strHextet1 ) + base64String.charAt( strHextet2 ) + base64String.charAt( strHextet3 ) + base64String.charAt( strHextet4 );
							} while( intChar < strIn.length );
							strOut = arrOut.join( '' );
						}
						var intRemainder = strIn.length % 3;
						return ( intRemainder ? strOut.slice( 0, intRemainder - 3 ) : strOut ) + '==='.slice( intRemainder || 3 );
					},
				this.beforeFirst = function( strFirstInstance, strHaystack, blCaseSensitive ) {
						if( blCaseSensitive === true ) {
							strHaystack = strHaystack.toLowerCase();
						}
						var intIndex = strHaystack.indexOf( strFirstInstance );
						return ( intIndex > 0 ) ? strHaystack.slice( 0, intIndex ) : '';
					},
				this.cookieDelete = function( strName ) {
						var datNow = new Date();
						datNow.setTime( datNow.getTime() - 1000 );
						document.cookie = strName + '=;expires=' + datNow.toUTCString() + ';path=/';
					},
				this.cookieGet = function( strName ) {
						strName = strName + "=",
						arrData = document.cookie.split( ';' );
						for( var intCharacter = 0, intDataLength = arrData.length; intCharacter < intDataLength; intCharacter++ ) {
							var strData = arrData[intCharacter];
							while( strData.charAt( 0 ) == ' ' ) {
								strData = strData.substring( 1, strData.length )
							}
							if( strData.indexOf( strName ) === 0 ) {
								return strData.substring( strName.length, strData.length )
							}
						}
						return null;
					},
				this.cookieSet = function( strName, mxdData, intSecondsLife, strPath ) {
						strPath = ( this.isSet( strPath ) && !this.isBlank( strPath ) ) ? strPath : '/';
						if( this.isInt( intSecondsLife ) ) {
							var datNow = new Date();
							datNow.setTime( datNow.getTime() + ( 1000 * intSecondsLife ) );
							document.cookie = strName + '=' + mxdData +';expires=' + datNow.toUTCString() + ';path=' + strPath;
						} else {
							document.cookie = strName + '=' + mxdData +';path=' + strPath;
						}
					},
				this.console = function() {
						if( arguments.length > 1 ) {
							var strType = arguments[0],
							arrArguements = arguments[1];
							if( this.isSet( window.console ) ) {
								if( this.isSet( window.console[strType] ) ) {
									var funConsole = window.console[strType];
									if( this.inArray( typeof arrArguements, ['array', 'object'] )
											&& arrArguements.length > 0 ) {
										for( var intArguement in arrArguements ) {
											funConsole( arrArguements[intArguement] );
										}
									} else {
										funConsole( arrArguements );
									}
								} else {
									this.log( arrArguements );
								}
							}
						}
					},
				this.contains = function( strNeedle, strHaystack, blCaseSensitive ) {
						if( blCaseSensitive === true ) {
							return strHaystack.indexOf( strNeedle ) !== -1;
						} else {
							return strHaystack.toLowerCase().indexOf( strNeedle.toLowerCase() ) !== -1;
						}
					},
				this.cssAnimations = function() {
						var blAnimation = false,
						strAnimation = 'animation',
						strKeyframePrefix = '',
						arrDOMPrefixes = ['Webkit', 'Moz', 'O', 'ms', 'Khtml'],
						strPrefix  = '';
						if( elm.style.AnimationName ) {
							blAnimation = true;
						} else {
							for( var intPrefix = 0, intDomPrefixes = arrDOMPrefixes.length; intPrefix < intDomPrefixes; intPrefix++ ) {
								if( this.isSet( elm.style[arrDOMPrefixes[intPrefix] + 'AnimationName'] ) ) {
									strPrefix = arrDOMPrefixes[intPrefix];
									strAnimation = strPrefix + 'Animation';
									strKeyframePrefix = '-' + strPrefix.toLowerCase() + '-';
									blAnimation = true;
									break;
								}
							}
						}
						this.cssAnimations = blAnimation ? function() { return true; } : function() { return false; };
						return blAnimation;
					},
				this.cssTransforms = function() {
						var blTransforms = false,
						arrPrefixes = 'transform WebkitTransform MozTransform OTransform msTransform'.split( ' ' );
						for( var intPrefix = 0; intPrefix < arrPrefixes.length; intPrefix++ ) {
							if( document.createElement( 'div' ).style[arrPrefixes[intPrefix]] !== undefined ) {
								blTransforms = arrPrefixes[intPrefix];
							}
						}
						this.cssTransforms = function() { return blTransforms; };
						return blTransforms;
					},
				this.dataSet = function( strName, mxdData, blPersistant ) {
						if( !this.isSet( blPersistant ) ) {
							blPersistant = true;
						}
						if( ( blPersistant
									&& localStorage )
								|| ( !blPersistant
									&& sessionStorage ) ) {
							try {
								if( blPersistant ) {
									localStorage.setItem( strName, mxdData );
								} else {
									sessionStorage.setItem( strName, mxdData );
								}
							} catch( e ) {
								switch( e ) {
									case QUOTA_EXCEEDED_ERR:
										this.error( 'Local storage quota exceeded' );
										break;
								}
							}
						} else {
							this.cookieSet( strName, mxdData );
						}
					},
				this.dataGet = function( strName ) {
						if( localStorage
								|| sessionStorage ) {
							var mxdLocal = localStorage.getItem( strName );
							var mxdSession = sessionStorage.getItem( strName );

							return ( mxdLocal === null ) ? mxdSession : mxdLocal;
						} else {
							return this.cookieGet( strName );
						}
					},
				this.dataDelete = function( strName ) {
						if( localStorage
								|| sessionStorage ) {
							localStorage.removeItem( strName );
							sessionStorage.removeItem( strName );
						} else {
							this.cookieDelete( strName );
						}
					},
				this.dataClear = function() {
						if( localStorage
								|| sessionStorage ) {
							localStorage.clear();
							sessionStorage.clear();
						} else {
							document.cookie.split( ';' );
						}
					},
				this.date = function( strFormat, intUnixSeconds ) {
						strFormat = ( typeof strFormat === 'string' ) ? strFormat : '';
						var datThisDate = this.isNumber( intUnixSeconds ) ? new Date( intUnixSeconds * 1000 ) : new Date(),
						objNames = {
								days: {
										0: {
												long: 'Sunday',
												short: 'Sun'
											},
										1: {
												long: 'Monday',
												short: 'Mon'
											},
										2: {
												long: 'Tuesday',
												short: 'Tue'
											},
										3: {
												long: 'Wednesday',
												short: 'Wed'
											},
										4: {
												long: 'Thursday',
												short: 'Thu'
											},
										5: {
												long: 'Friday',
												short: 'Fri'
											},
										6: {
												long: 'Saturday',
												short: 'Sat'
											}
									},
								months: {
										1: {
												long: 'January',
												short: 'Jan'
											},
										2: {
												long: 'February',
												short: 'Feb'
											},
										3: {
												long: 'March',
												short: 'Mar'
											},
										4: {
												long: 'April',
												short: 'Apr'
											},
										5: {
												long: 'May',
												short: 'May'
											},
										6: {
												long: 'June',
												short: 'Jun'
											},
										7: {
												long: 'July',
												short: 'Jul'
											},
										8: {
												long: 'August',
												short: 'Aug'
											},
										9: {
												long: 'September',
												short: 'Sep'
											},
										10: {
												long: 'October',
												short: 'Oct'
											},
										11: {
												long: 'November',
												short: 'Nov'
											},
										12: {
												long: 'December',
												short: 'Dec'
											}
									}
								},
						objTime = {
								N: datThisDate.getDay() % 7,
								j: datThisDate.getDate(),
								n: datThisDate.getMonth() + 1,
								Y: datThisDate.getFullYear(),
								G: datThisDate.getHours(),
								i: this.padLeft( datThisDate.getMinutes(), 2, '0' ),
								s: this.padLeft( datThisDate.getSeconds(), 2, '0' ),
								u: datThisDate.getMilliseconds(),
								U: Math.floor( datThisDate.getTime() / 1000 ),
								Z: datThisDate.getTimezoneOffset() * 3600
							};
						objTime.D = objNames.days[objTime.N]['short'],
						objTime.l = objNames.days[objTime.N]['long'],
						objTime.d = this.padLeft( objTime.j, 2, '0' ),
						objTime.S = this.numberSuffix( objTime.j ),
						objTime.m = this.padLeft( objTime.n, 2, '0' ),
						objTime.F = objNames.months[objTime.n]['long'],
						objTime.M = objNames.months[objTime.n]['short'],
						objTime.y = this.padLeft( objTime.Y, 2 ),
						objTime.H = this.padLeft( objTime.G, 2, '0' ),
						objTime.g = objTime.G == 0 ? 12 : ( objTime.G > 12 ? objTime.G - 12 : objTime.G ),
						objTime.h = this.padLeft( objTime.g, 2, '0' ),
						objTime.a = ( objTime.G < 12 ) ? 'am' : 'pm',
						objTime.A = objTime.a.toUpperCase(),
						objTime.L = ( objTime.Y % 4 === 0 && ( ( objTime.Y % 100 === 0 && objTime.Y % 400 === 0 ) || objTime.Y % 100 !== 0 ) ) ? 1 : 0;
						if( typeof strFormat !== '' ) {
							var strOut = '';
							var intChar = 0;
							while( intChar < strFormat.length ) {
								var strChar = strFormat.charAt( intChar );
								if( strChar === '\\' ) {
									intChar++;
									strOut += strFormat.substr( intChar, 1 );
								} else if( strChar === 'r' ) {
									strOut += objTime.D + ', ' + objTime.j + ' ' + objTime.M + ' ' + objTime.Y + ' ' + objTime.H + ':' + objTime.i + ':' + objTime.s;
								} else if( strChar === 'x' ) {
									strOut += objTime.Y + '-' + objTime.m + '-' + objTime.d + '\\T' + objTime.H + ':' + objTime.i + ':' + objTime.s + '\\Z';
								} else {
									if( strChar !== ''
											&& objTime.hasOwnProperty( strChar ) ) {
										strOut += objTime[strChar];
									} else {
										strOut += strChar;
									}
								}
								intChar++;
							}
							return strOut;
						} else {
							return objTime;
						}
					},
				this.debug = function() {
						if( arguments.length > 0 ) {
							for( var intArguement in arguments ) {
								this.console( 'debug', arguments[intArguement] );
							}
						}
					},
				this.decimalise = function( fltIn ) {
						var strRounded = this.round( fltIn, 2 ).toString();
						if( this.contains( '.', strRounded ) ) {
							var arrSplit = strRounded.split( '.' );
							var strDigitsAfterPoint = arrSplit.pop();
							arrSplit.push( this.padRight( strDigitsAfterPoint, 2, '0' ) );
							return arrSplit.join( '.' );
						} else {
							return strRounded + '.00';
						}
					},
				this.degToRad = function( intDegrees ) {
						return intDegrees * ( Math.PI / 180 );
					},
				this.endsWith = function( strNeedle, strHaystack, blCaseSensitive ) {
						if( blCaseSensitive === true ) {
							return new RegExp( strNeedle + '$' ).test( strHaystack );
						} else {
							return new RegExp( strNeedle + '$', 'i' ).test( strHaystack );
						}
					},
				this.error = function() {
						if( this.isSet( window.console )
								&& this.isSet( window.console.error )
								&& arguments.length > 0 ) {
							for( var intArguement in arguments ) {
								window.console.error( arguments[intArguement] );
							}
						}
					},
				this.fullScreen = function( jqoObject ) {
						if( jqoObject[0].requestFullScreen ) {
							jqoObject[0].requestFullScreen();
						} else if( jqoObject[0].mozRequestFullScreen ) {
							jqoObject[0].mozRequestFullScreen();
						} else if( jqoObject[0].webkitRequestFullScreen ) {
							jqoObject[0].webkitRequestFullScreen();
						}
					},
				this.getFileHeaders = function( strResource, funCallback ) {
						var resXHR;
						if( window.XMLHttpRequest ) {
							resXHR = new XMLHttpRequest();
						} else if( window.ActiveXObject ) {
							resXHR = new ActiveXObject( 'Microsoft.XMLHTTP' );
						}
						resXHR.onreadystatechange = function() {
							if( resXHR.readyState === 4
									&& resXHR.status === 200 ) {
								var arrResourceResponseHeaders = new Object();
								arrResourceResponseHeaders.Type = resXHR.getResponseHeader( 'Content-Type' ).toString();
								arrResourceResponseHeaders.Size = resXHR.getResponseHeader( 'Content-Length' ).toString();
								arrResourceResponseHeaders.Modified = resXHR.getResponseHeader( 'Last-Modified' ).toString();
								funCallback.call( arrResourceResponseHeaders );
							}
						}
						try {
							resXHR.open( 'HEAD', strResource, true );
							resXHR.send();
						} catch( e ) {
							error( e );
						}
					},
				this.getHash = function( blIncludeHash ) {
						var strHash = window.location.hash;
						return ( blIncludeHash === true ) ? strHash : strHash.substring( 1 );
					},
				this.ie = function( mxdVersion ) {
						var strUserAgent = navigator.userAgent,
						intMSIEOffset = strUserAgent.indexOf( 'MSIE ' ),
						intRVOffset = strUserAgent.indexOf( 'rv:' ),
						intVersion = -1;
						if( intMSIEOffset !== -1 ) {
							intVersion = parseInt( this.beforeFirst( ';', strUserAgent.substr( intMSIEOffset + 5 ) ) );
						} else if( intRVOffset !== -1
								&& this.contains( 'Trident', strUserAgent ) ) {
							intVersion = parseInt( this.beforeFirst( ')', strUserAgent.substr( intRVOffset + 3 ) ) );
						}
						if( intVersion !== -1
								&& ( ( this.isSet( mxdVersion )
										&& this.isNumber( mxdVersion )
										&& mxdVersion == intVersion )
									|| !this.isSet( mxdVersion ) ) ) {
							return intVersion;
						} else {
							return false;
						}
					},
				this.inArray = function( mxdNeedle, arrHaystack ) {
						var blMatch = false;
						for( var mxdValue in arrHaystack ) {
							if( arrHaystack[mxdValue] === mxdNeedle ) {
								blMatch = true;
							}
						}
						return blMatch;
					},
				this.info = function() {
						if( arguments.length > 0 ) {
							for( var intArguement in arguments ) {
								this.console( 'info', arguments[intArguement] );
							}
						}
					},
				this.iOS = function () {
						var blResult = this.iPad() || this.iPod() || this.iPhone();
						this.iOS = blResult ? function() { return true; } : function() { return false; };
						return blResult;
					},
				this.iPad = function () {
						var blResult = this.contains( 'iPad', navigator.userAgent );
						this.iPad = blResult ? function() { return true; } : function() { return false; };
						return blResult;
					},
				this.iPod = function () {
						var blResult = this.contains( 'iPod', navigator.userAgent );
						this.iPod = blResult ? function() { return true; } : function() { return false; };
						return blResult;
					},
				this.iPhone = function () {
						var blResult = this.contains( 'iPhone', navigator.userAgent );
						this.iPhone = blResult ? function() { return true; } : function() { return false; };
						return blResult;
					},
				this.isAlpha = function( mxdValue ) {
						return ( /^[a-z]+$/i ).test( mxdValue );
					},
				this.isAlphanumeric = function( mxdValue ) {
						return ( /^[a-z0-9]+$/i ).test( mxdValue );
					},
				this.isArray = function( mxdValue ) {
						return typeof mxdValue === 'array';
					},
				this.isBlank = function( mxdValue ) {
						return mxdValue.replace( /[\s\t\r\n]*/g, '' ) == '';
					},
				this.isCapsLock = function( e ) {
						e = e ? e : window.event;
						var intCharCode = e.which ? e.which : e.keyCode,
						blShiftOn = false;
						if( e.shiftKey ) {
							blShiftOn = e.shiftKey;
						} else if( e.modifiers ) {
							blShiftOn = !!( e.modifiers & 4 );
						}
						if( intCharCode >= 97
								&& intCharCode <= 122
								&& blShiftOn ) {
							return true;
						}
						return ( intCharCode >= 65
								&& intCharCode <= 90
								&& !blShiftOn );
					},
				this.isDomain = function( mxdValue ) {
						return ( /^([a-z0-9][a-z0-9\-]*)(.[a-z0-9]{2,}[a-z0-9\-]*)+$/i ).test(  mxdValue );
					},
				this.isEmail = function( mxdValue ) {
						/**
						 * JavaScript function to check an email address conforms to RFC822 (http://www.ietf.org/rfc/rfc0822.txt)
						 *
						 * Version: 0.2
						 * Author: Ross Kendall
						 * Created: 2006-12-16
						 * Updated: 2007-03-22
						 *
						 * Based on the PHP code by Cal Henderson
						 * http://iamcal.com/publish/articles/php/parsing_email/
						 */
						var sQtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]',
						sDtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]',
						sAtom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+',
						sQuotedPair = '\\x5c[\\x00-\\x7f]',
						sDomainLiteral = '\\x5b(' + sDtext + '|' + sQuotedPair + ')*\\x5d',
						sQuotedString = '\\x22(' + sQtext + '|' + sQuotedPair + ')*\\x22',
						sSubDomain = '(' + sAtom + '|' + sDomainLiteral + ')',
						sWord = '(' + sAtom + '|' + sQuotedString + ')',
						sDomain = sSubDomain + '(\\x2e' + sSubDomain + ')*',
						sLocalPart = sWord + '(\\x2e' + sWord + ')*',
						sAddrSpec = sLocalPart + '\\x40' + sDomain,
						sValidEmail = '^' + sAddrSpec + '$';
						return new RegExp( sValidEmail ).test( mxdValue );
					},
				this.isHexadecimal = function( mxdValue ) {
						return ( /^[0-9a-f]+$/i ).test( mxdValue );
					},
				this.isHexColor = function( mxdValue ) {
						return ( /^#?[0-9a-f]{3,6}$/i ).test( mxdValue );
					},
				this.isInt = function( mxdValue ) {
						return ( parseFloat( mxdValue ) == parseInt( mxdValue ) ) && !isNaN( mxdValue );
					},
				this.isIP = function( mxdValue ) {
						return this.isIPV4( mxdValue ) || this.isIPV6( mxdValue );
					},
				this.isIPV4 = function( mxdValue ) {
						return ( /^(1?[0-9]{1,2}|2([0-4][0-9]|5[0-5]))(\.(1?[0-9]{1,2}|2([0-4][0-9]|5[0-5]))){3}$/ ).test( mxdValue );
					},
				this.isLocalIPV4 = function( mxdValue ) {
						return ( /^10(\.((1[0-9]{2})|(2([0-4][0-9]|5[0-5]))|[0-9][0-9]?)){3}|172\.(1[6-9]|2[0-9]|3[0-1])(\.((1[0-9]{2})|(2([0-4][0-9]|5[0-5]))|[0-9][0-9]?)){2}|192\.168(\.((1[0-9]{2})|(2([0-4][0-9]|5[0-5]))|[0-9][0-9]?)){2}$/ ).test( mxdValue );
					},
				this.isIPV6 = function( mxdValue ) {
						return ( /^((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?$/i ).test( mxdValue );
					},
				this.isNull = function( mxdValue ) {
						return typeof( mxdValue ) === 'null' || mxdValue === null;
					},
				this.isNumber = function( mxdValue ) {
						return typeof( mxdValue ) === 'number' || ( typeof( mxdValue ) === 'string' && mxdValue !== '' && !isNaN( mxdValue ) );
					},
				this.isObject = function( mxdValue ) {
						return typeof mxdValue === 'object';
					},
				this.isSet = function( mxdValue ) {
						return mxdValue !== null && mxdValue !== undefined && typeof mxdValue !== 'null' && typeof mxdValue !== 'undefined' && mxdValue !== 'undefined';
					},
				this.isUrl = function( mxdValue ) {
						return ( /^(https?|s?ftp):\/\/([a-z0-9][a-z0-9\-]*)(.[a-z0-9]{2,}[a-z0-9\-]*)+(:\d+)?\/?([a-z0-9_\-\.\?\=\&\%\+\/\|\'\"\[\]]*)$/i ).test(  mxdValue );
					},
				this.isWebkit = function() {
						var blResult = this.contains( 'AppleWebKit', navigator.userAgent );
						this.isWebkit = blResult ? function() { return true; } : function() { return false; };
						return blResult;
					},
				this.isWindowFocused = function() {
						return this.windowFocused;
					},
				this.keyCodeName = function( e ) {
						e = e ? e : window.event;
						var strReturn = '[UNKNOWN]';
						var intCharCode = e.which ? e.which : e.keyCode;
						if( ( this.ie()
									&& Object.prototype.hasOwnProperty.call( objUnlistedKeyNames, intCharCode ) )
								|| objUnlistedKeyNames.hasOwnProperty( intCharCode ) ) {
							strReturn = objUnlistedKeyNames[intCharCode];
						} else {
							strReturn = String.fromCharCode( intCharCode );
						}
						return strReturn;
					},
				this.log = function() {
						var arrArguements = arguments;
						if( this.isSet( window.console )
								&& this.isSet( window.console.log )
								&& arrArguements.length > 0 ) {
							for( var intArguement in arrArguements ) {
								window.console.log( arrArguements[intArguement] );
							}
						}
					},
				this.ltrim = function( strIn, strTrimChars ) {
						return strIn.replace( new RegExp( '^[' + ( ( typeof strTrimChars !== 'string' || strTrimChars === '' ) ? ' ' : strTrimChars ) + ']+' ), '' );
					},
				this.microtime = function() {
						var datNow = new Date();
						return parseInt( datNow.getTime() );
					},
				this.moderniseInputs = function( blPlaceholders, funCallback ) {
						funCallback = ( typeof funCallback === 'function' ) ? funCallback : ( ( typeof blPlaceholders === 'function' ) ? blPlaceholders : function() {} );
						var blYeOldieBrowser = this.oldie();
						var arrInputs = ['text', 'hidden', 'password', 'button', 'reset', 'submit', 'checkbox', 'radio', 'email', 'number', 'tel', 'url', 'range', 'search', 'file', 'color', 'date', 'month', 'week', 'time', 'datetime', 'datetime-local'];
						for( var intInput in arrInputs ) {
							var strInput = arrInputs[intInput];
							$( 'input[type="' + strInput + '"]:not(.input-' + strInput + ')' ).addClass( 'input-' + strInput );
						}
						if( blYeOldieBrowser
								&& blPlaceholders === true ) {
							$( 'input[placeholder]:not(.placeholder)' ).each(
								function() {
									var jqoInput = $( this );
									var strPlaceholder = jqoInput.attr( 'placeholder' );
									if( jqoInput.val() == '' ) {
										jqoInput.val( strPlaceholder );
										jqoInput.addClass( 'placeholder' );
									}
									jqoInput.focus(
										function() {
											$( this ).removeClass( 'placeholder' );
											if( jqoInput.val() == strPlaceholder ) {
												jqoInput.val( '' );
											}
										}
									).blur(
										function() {
											if( blYeOldieBrowser ) {
												jqoInput.val( strPlaceholder );
												$( this ).addClass( 'placeholder' );
											}
										}
									);
								}
							).closest( 'form' ).submit(
								function() {
									$( this ).find( 'input.placeholder' ).val( '' );
								}
							);
						}
						funCallback.call();
					},
				this.numberSuffix = function( intNumber, blIncludeNumber ) {
						if( !this.isInt( intNumber ) ) {
							intNumber = parseInt( intNumber );
						}
						var strSuffix = '';
						if( this.isInt( intNumber )
								&& intNumber > 0 ) {
							var intNumberTensUnits = parseInt( intNumber.toString().substr( -2 ) );
							var intNumberUnits = parseInt( intNumber.toString().substr( -1 ) );

							if( intNumberUnits === 1
									|| ( intNumberTensUnits > 10
										&& intNumberTensUnits !== 11 ) ) {
								strSuffix = 'st';
							} else if( intNumberUnits === 2
									|| ( intNumberTensUnits > 10
										&& intNumberTensUnits !== 12 ) ) {
								strSuffix = 'nd';
							} else if( intNumberUnits === 3
									|| ( intNumberTensUnits > 10
										&& intNumberTensUnits !== 13 ) ) {
								strSuffix = 'rd';
							} else {
								strSuffix = 'th';
							}
						}
						return ( blIncludeNumber === true ) ? intNumber + strSuffix : strSuffix;
					},
				this.objectLength = function( objIn ) {
						var intLength = 0;
						if( typeof objIn === 'object' ) {
							for( var mxdKey in objIn ) {
								if( ( this.ie()
											&& Object.prototype.hasOwnProperty.call( objIn, mxdKey ) )
										|| objIn.hasOwnProperty( mxdKey ) ) {
									intLength++;
								}
							}
						}
						return intLength;
					},
				this.objUnlistedKeyNames = {
						8: 'Backspace',
						13: 'Return',
						16: 'Shift',
						17: 'Ctrl',
						18: 'Alt',
						19: 'F15',
						27: 'Escape',
						32: 'Space',
						44: 'F13',
						112: 'F1',
						113: 'F2',
						114: 'F3',
						115: 'F4',
						116: 'F5',
						117: 'F6',
						118: 'F7',
						119: 'F8',
						120: 'F9',
						121: 'F10',
						122: 'F11',
						123: 'F12',
						145: 'F14',
						224: 'Cmd'
					},
				this.oldie = function() {
						var blResult = this.ie( 6 ) || this.ie( 7 ) || this.ie( 8 );
						this.oldie = blResult ? function() { return true; } : function() { return false; };
						return blResult;
					},
				this.padLeft = function( strRaw, intFinalLength, strPadChar ) {
						strRaw = strRaw.toString();
						strPadChar = ( typeof strPadChar === 'string' ) ? strPadChar : ' ';
						if( strRaw.length < intFinalLength ) {
							for( var intChar = 0; intChar < intFinalLength; intChar++ ) {
								strRaw = strPadChar + strRaw;
							}
						}
						return strRaw.substr( -intFinalLength );
					},
				this.padRight = function( strRaw, intFinalLength, strPadChar ) {
						strRaw = strRaw.toString();
						strPadChar = ( typeof strPadChar === 'string' ) ? strPadChar : ' ';
						if( strRaw.length < intFinalLength ) {
							for( var intChar = 0; intChar < intFinalLength; intChar++ ) {
								strRaw += strPadChar;
							}
						}
						return strRaw.substr( 0, intFinalLength );
					},
				this.pixelDensity = function() {
						return this.isNumber( window.devicePixelRatio ) ? window.devicePixelRatio : 1;
					},
				this.ppi = function( fltDisplaySize ) {
					var intPixelDensity = this.pixelDensity(),
					intScreenWidth = screen.width * intPixelDensity,
					intScreenHeight = screen.height * intPixelDensity,
					fltDiagPixels = Math.sqrt( Math.pow( intScreenWidth, 2 ) + Math.pow( intScreenHeight, 2 ) );
					return intScreenHeight / fltDisplaySize * Math.sin( Math.acos( ( Math.pow( intScreenWidth, 2 ) + Math.pow( fltDiagPixels, 2 ) - Math.pow( intScreenHeight, 2 ) ) / ( 2 * fltDiagPixels * intScreenWidth ) ) );
				},
				this.prettyAge = function( intSeconds ) {
						var intSecondsDifference = parseInt( this.date( 'U' ) ) - intSeconds,
						blFuture = ( intSecondsDifference < 0 ),
						intMonthSeconds = ( fltYearDays / 12 ) * 86400;
						intSecondsDifference = Math.abs( intSecondsDifference );
						var strOut = '';
						if( intSecondsDifference < 60 ) {
							return blFuture ? 'In a moment' : 'A moment ago';
						} else if( intSecondsDifference < 120 ) {
							return ( blFuture ) ? 'In an minute' : 'A minute ago';
						} else if( intSecondsDifference < 3600 ) {
							strOut = Math.floor( intSecondsDifference / 60 ) + ' minutes';
						} else if( intSecondsDifference < 7200 ) {
							return ( blFuture ) ? 'In an hour' : 'An hour ago';
						} else if( intSecondsDifference < 86400 ) {
							strOut = Math.floor( intSecondsDifference / 3600 ) + ' hours';
						} else if( intSecondsDifference < 172800 ) {
							return ( blFuture ) ? 'Tomorrow' : 'Yesterday';
						} else if( intSecondsDifference < intMonthSeconds ) {
							strOut = Math.floor( intSecondsDifference / 86400 ) + ' days';
						} else if( intSecondsDifference < intMonthSeconds * 2 ) {
							return ( blFuture ) ? 'In a month' : 'A month ago';
						} else if( intSecondsDifference < fltYearDays * 86400 ) {
							strOut = Math.floor( intSecondsDifference / intMonthSeconds ) + ' months';
						} else if( intSecondsDifference < fltYearDays * 86400 * 2 ) {
							return ( blFuture ) ? 'In a year' : 'A year ago';
						} else {
							strOut = Math.floor( intSecondsDifference / ( fltYearDays * 86400 ) ) + ' years';
						}
						return ( blFuture ) ? 'In ' + strOut : strOut + ' ago';
					},
				this.prettySize = function( intBytes, blUseSpace ) {
						var arrLimits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
						var intLimit = 0;
						while( arrLimits[intLimit]
								&& intBytes > Math.pow( 1024, intLimit + 1 ) ) {
							intLimit++;
						}
						return this.round( intBytes / Math.pow( 1024, intLimit ), 2 ) + ( blUseSpace === true ? ' ' : '' ) + arrLimits[intLimit];
					},
				this.prettyTime = function( intSeconds, blShortLabels ) {
						blShortLabels = ( typeof blShortLabels === 'boolean' ) ? blShortLabels : false;
						var strUptime = '',
						arrLimits = [fltYearDays * 86400, ( fltYearDays / 84 ) * 604800, 604800, 86400, 3600, 60],
						arrLimitLabels = blShortLabels ? ['y', 'mo', 'w', 'd', 'h', 'm'] : ['year','month','week','day','hour','minute'];
						for( var intLimitIndex in arrLimits ) {
							if( intSeconds >= arrLimits[intLimitIndex] ) {
								if( blShortLabels ) {
									strUptime += Math.floor( intSeconds / arrLimits[intLimitIndex] ) + arrLimitLabels[intLimitIndex];
								} else {
									strUptime += Math.floor( intSeconds / arrLimits[intLimitIndex] ) + ' ' + arrLimitLabels[intLimitIndex] + ( ( Math.floor( intSeconds / arrLimits[intLimitIndex] ) === 1 ) ? '' : 's' );
								}
								intSeconds -= Math.floor( intSeconds / arrLimits[intLimitIndex] ) * arrLimits[intLimitIndex];
								if( intSeconds === 0 ) {
									return strUptime;
								} else {
									strUptime += ' ';
								}
							} else if( strUptime !== '' ) {
								if( blShortLabels ) {
									strUptime += '0' + arrLimitLabels[intLimitIndex];
								} else {
									strUptime += '0 ' + arrLimitLabels[intLimitIndex] + 's ';
								}
								if( intSeconds !== 0 ) {
									strUptime += ' ';
								}
							}
						}
						if( blShortLabels ) {
							return strUptime + intSeconds + 's';
						} else {
							return strUptime + intSeconds + ' seconds';
						}
					},
				this.radToDeg = function( intRadians ) {
						return intRadians * ( 180 / Math.PI );
					},
				this.randomString = function( intStringLength, mxdExtendedChars ) {
						intStringLength = this.isSet( intStringLength ) && this.isInt( intStringLength ) ? intStringLength : 16;
						var strChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz',
						strRandomString = '';
						if( typeof mxdExtendedChars === 'string' ) {
							strChars = mxdExtendedChars;
						} else if( mxdExtendedChars === true ) {
							strChars += '!@£$%^&*()_-=+[]{};:|<>?/';
						}
						for( var intChar = 0; intChar < intStringLength; intChar++ ) {
							var intRand = Math.floor( Math.random() * strChars.length );
							strRandomString += strChars.substring( intRand, intRand + 1 );
						}
						return( strRandomString );
					},
				this.round = function( intNumber, intDP ) {
						intDP = ( typeof intDP !== 'number' ) ? 0 : intDP;
						return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
					},
				this.rtrim = function( strIn, strTrimChars ) {
						return strIn.replace( new RegExp( '[' + ( ( typeof strTrimChars !== 'string' || strTrimChars === '' ) ? ' ' : strTrimChars ) + ']+$' ), '' );
					},
				this.slug = function( strOriginal, blAllowSlashes ) {
						return this.trim( strOriginal, ' -' ).replace( ( blAllowSlashes === true ? /[^a-z0-9\s\-_/]/gi : /[^a-z0-9\s\-_]/gi ), '' ).replace( /\s{2,}/g, ' ' ).replace( /\s/g, '-' ).replace( /\-{2,}/g, '-' ).toLowerCase();
					},
				this.startsWith = function( strNeedle, strHaystack, blCaseSensitive ) {
						if( blCaseSensitive === true ) {
							return new RegExp( '^' + strNeedle ).test( strHaystack );
						} else {
							return new RegExp( '^' + strNeedle, 'i' ).test( strHaystack );
						}
					},
				this.trim = function( strIn, strTrimChars ) {
						if( typeof strTrimChars !== 'string'
								|| strTrimChars === '' ) {
							strTrimChars = ' ';
						}
						return this.ltrim( this.rtrim( strIn, strTrimChars ), strTrimChars );
					},
				this.warn = function() {
						if( arguments.length > 0 ) {
							for( var intArguement in arguments ) {
								this.console( 'warn', arguments[intArguement] );
							}
						}
					},
				this.windowFocused = false;

				return this;
			};

		window.Shadow = new Shadow;
		window.onfocus = function() {
				window.Shadow.windowFocused = true;
			},
		window.onblur = function() {
				window.Shadow.windowFocused = false;
			};

		if( !window.shdw ) {
			window.shdw = window.Shadow;
		}
	}
)( screen, window, document );