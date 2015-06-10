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
	function( window, document ) {
		var debug = false,
				log = function( mxdData, strType, blOverride ) {
					if( ( debug
								|| blOverride === true )
							&& window.console ) {
						if( window.console[strType] ) {
							window.console[strType]( mxdData );
						} else if( window.console.log ) {
							console.log( mxdData );
						}
					}
				},
				hasClass = function( domElement, strClass ) {
						return domElement.className.indexOf( strClass ) !== -1;
					},
				addClass = function( domElement, strClass ) {
						if( !hasClass( domElement, strClass ) ) {
							domElement.className += ' ' + strClass;
						}
					},
				removeClass = function( domElement, strClass ) {
						if( hasClass( domElement, strClass ) ) {
							domElement.className = domElement.className.replace( new RegExp( '^' + strClass + '$', 'g' ), '' ).replace( new RegExp( '^' + strClass + ' ', 'g' ), '' ).replace( new RegExp( ' ' + strClass + '$', 'g' ), '' ).replace( new RegExp( ' ' + strClass + ' ', 'g' ), ' ' );
						}
					},
				prettySize = function( intBytes ) {
					var arrLimits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
							intLimit = 0;

					while( arrLimits[intLimit]
					&& intBytes > Math.pow( 1024, intLimit + 1 ) ) {
						intLimit++;
					}

					return round( intBytes / Math.pow( 1024, intLimit ), ( intLimit > 1 ? 2 : 0 ) ) + arrLimits[intLimit];
				},
				round = function( intNumber, intDP ) {
					intDP = ( typeof intDP !== 'number' ) ? 0 : intDP;
					return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
				},
				uploader = function( strInputID, strUri, objSettings ) {

					var uploadSupported = ( typeof new XMLHttpRequest().responseType === 'string' && 'withCredentials' in new XMLHttpRequest() );

					if( uploadSupported ) {
						var requestTest = new XMLHttpRequest();
						requestTest.open( 'GET', '/' );
						try {
							requestTest.responseType = 'arraybuffer';
						} catch( e ) {
							uploadSupported = false;
						}
					}

					var thisUploader = this;

					thisUploader.acceptExtentions = [],
							thisUploader.acceptRaw = [],
							thisUploader.acceptTypes = [],
							thisUploader.created = ( new Date() ).getTime(),
							thisUploader.cancelUpload = function( e ) {
								e.preventDefault();
								thisUploader.request.abort();
							},
							thisUploader.clearInput = function( e ) {
								if( e ) {
									e.preventDefault();
								}
								thisUploader.domInput.value = '';

								if( thisUploader.domInput.value ) {
									thisUploader.domInput.type = 'text';
									thisUploader.domInput.type = 'file';
								}

								thisUploader.hideClear();

								thisUploader.domPseudo.value = '';

								thisUploader.settings.onclear();
							},
							thisUploader.domCancelUpload = document.getElementById( strInputID + '-cancel' ),
							thisUploader.domCancelUploadDisplay = null,
							thisUploader.domClearUpload = document.getElementById( strInputID + '-clear' ),
							thisUploader.domCount = document.getElementById( strInputID + '-count' ),
							thisUploader.domCountWrapper = document.getElementById( strInputID + '-count-wrapper' ),
							thisUploader.domCountWrapperDisplay = null,
							thisUploader.domCountTotal = document.getElementById( strInputID + '-total' ),
							thisUploader.domInput = document.getElementById( strInputID ),
							thisUploader.domInputDisplay = null,
							thisUploader.domProgress = document.getElementById( strInputID + '-progress' ),
							thisUploader.domProgressWrapper = document.getElementById( strInputID + '-progress-wrapper' ),
							thisUploader.domPseudo = document.getElementById( strInputID + '-pseudo' ),
							thisUploader.hideClear = function() {
								if( thisUploader.domClearUpload ) {
									thisUploader.domClearUpload.style.display = 'none';
									thisUploader.domClearUpload.removeEventListener( 'click', thisUploader.clearInput );
								}
							},
							thisUploader.hideProgress = function() {
								if( thisUploader.domInput ) {
									thisUploader.domInput.style.display = thisUploader.domInputDisplay;
								}

								if( thisUploader.domProgressWrapper ) {
									thisUploader.domProgressWrapper.style.display = 'none';
								}

								if( thisUploader.domCancelUpload ) {
									thisUploader.domCancelUpload.removeEventListener( 'click', thisUploader.cancelUpload );
								}
							},
							thisUploader.multiple = ( thisUploader.domInput && thisUploader.domInput.hasAttribute( 'multiple' ) ),
							thisUploader.queue = [],
							thisUploader.queueCount = 0,
							thisUploader.queueSize = 0,
							thisUploader.queueUploadedCount = 0,
							thisUploader.queueUploadedSize = 0,
							thisUploader.request = new XMLHttpRequest(),
							thisUploader.settings = {
								abortable: true,
								clearoncomplete: true,
								counter: true,
								debug: false,
								dragdrop: null,
								onabort: function() {},
								onclear: function() {},
								oncompletefile: function() {},
								oncompletequeue: function() {},
								onerror: function() {},
								onprogress: function() {},
								onstart: function() {}
							},
							thisUploader.showClear = function() {
								if( thisUploader.domClearUpload ) {
									thisUploader.domClearUpload.style.display = this.domInputDisplay;
									thisUploader.domClearUpload.addEventListener( 'click', thisUploader.clearInput );
								}
							},
							thisUploader.showProgress = function() {
								thisUploader.domInput.style.display = 'none';

								if( thisUploader.domProgressWrapper ) {
									thisUploader.domProgressWrapper.style.display = thisUploader.domInputDisplay;
								}

								if( thisUploader.domCancelUpload ) {
									thisUploader.domCancelUpload.addEventListener( 'click', thisUploader.cancelUpload );
								}
							},
							thisUploader.supported = false,
							thisUploader.uid = strInputID,
							thisUploader.upload = function( e, arrFiles ) {
								try {
									if( e ) {
										if( !arrFiles ) {
											var domCaller = e.target || e.srcElement,
													resFiles = domCaller.files;
										} else {
											resFiles = arrFiles;
										}

										thisUploader.queue.push.apply( thisUploader.queue, resFiles );
										thisUploader.queueCount += resFiles.length;

										for( var intFile = 0, intFiles = resFiles.length; intFile < intFiles; intFile++ ) {
											thisUploader.queueSize += parseInt( resFiles[intFile].size );
										}

										if( thisUploader.domCountTotal ) {
											thisUploader.domCountTotal.innerText = thisUploader.queueCount;
										}

										log( 'Added ' + resFiles.length + ' files to the queue', 'info' );
									}

									if( thisUploader.domCancelUploadDisplay === null ) {
										thisUploader.domCancelUploadDisplay = thisUploader.domCancelUpload.style.display || 'inline-block';
									}

									if( thisUploader.domCountWrapperDisplay === null ) {
										thisUploader.domCountWrapperDisplay = thisUploader.domCountWrapper.style.display || 'inline-block';
									}

									if( thisUploader.domInputDisplay === null ) {
										thisUploader.domInputDisplay = thisUploader.domInput.style.display || 'inline-block';
									}

									if( thisUploader.queue.length ) {
										var resFile = thisUploader.queue[0],
												strFileName = resFile.name,
												strFileType = resFile.type,
												strFileExtention = strFileName.substr( strFileName.lastIndexOf( '.' ) + 1 ),
												intFileSize = parseInt( resFile.size ),
												resFileReader = new FileReader( { blob: true } ),
												blAcceptedType = !thisUploader.acceptTypes.length && !thisUploader.acceptExtentions.length;

										if( !blAcceptedType ) {
											for( var intType in thisUploader.acceptTypes ) {
												if( new RegExp( '^' + thisUploader.acceptTypes[intType] + '$', 'gi' ).test( strFileType ) ) {
													blAcceptedType = true;
													break;
												}
											}
										}

										if( !blAcceptedType ) {
											for( var intExtention in thisUploader.acceptExtentions ) {
												if( strFileExtention === thisUploader.acceptExtentions[intExtention] ) {
													blAcceptedType = true;
													break;
												}
											}
										}

										if( blAcceptedType ) {
											thisUploader.settings.onstart( resFile );
											thisUploader.showProgress();

											if( thisUploader.domCount ) {
												thisUploader.domCount.innerText = thisUploader.queueUploadedCount + 1;
											}

											if( thisUploader.queueCount === 1 ) {
												if( thisUploader.domProgress ) {
													thisUploader.domProgress.removeAttribute( 'value' );
												}

												if( thisUploader.domCountWrapper ) {
													thisUploader.domCountWrapper.style.display = 'none';
												}
											} else if( thisUploader.domCountWrapper ) {
												thisUploader.domCountWrapper.style.display = thisUploader.domCountWrapperDisplay;
											}

											resFileReader.addEventListener( 'load',
													function( e ) {
														thisUploader.request.onreadystatechange = function() {
															switch( thisUploader.request.status ) {
																case 200:
																	if( thisUploader.request.readyState == 4 ) {
																		log( 'Uploaded ' + strFileName + ' (' + prettySize( intFileSize ) + ')' );

																		thisUploader.queue.shift();
																		thisUploader.queueUploadedCount++;
																		thisUploader.queueUploadedSize += intFileSize;

																		var jsonResponse = JSON.parse( thisUploader.request.responseText );

																		if( thisUploader.queue.length ) {
																			if( thisUploader.multiple ) {
																				thisUploader.uploaded.push( jsonResponse.form_value );
																			} else {
																				thisUploader.uploaded = [jsonResponse.form_value];
																			}

																			thisUploader.settings.oncompletefile( jsonResponse, resFile );

																			thisUploader.upload();
																		} else {
																			thisUploader.hideProgress();

																			log( 'Finsihed uploading ' + thisUploader.queueUploadedCount + ' files (' + prettySize( thisUploader.queueUploadedSize ) + ')', 'info' );
																			thisUploader.queueCount = 0;
																			thisUploader.queueSize = 0;
																			thisUploader.queueUploadedCount = 0;
																			thisUploader.queueUploadedSize = 0;

																			if( thisUploader.settings.clearoncomplete ) {
																				thisUploader.clearInput();
																				thisUploader.hideClear();
																			} else {
																				thisUploader.showClear();
																			}

																			if( thisUploader.multiple ) {
																				thisUploader.uploaded.push( jsonResponse.form_value );
																			} else {
																				thisUploader.uploaded = [jsonResponse.form_value];
																			}

																			thisUploader.settings.oncompletefile( jsonResponse, resFile );

																			thisUploader.domPseudo.value = thisUploader.uploaded.join( ',' );

																			thisUploader.settings.oncompletequeue();
																		}
																	}
																	break;

																case 403:
																	log( 'Permission denied', 'error' );

																	thisUploader.queue.shift();
																	thisUploader.queueCount--;
																	thisUploader.queueSize--;

																	thisUploader.settings.onerror( resFile );

																	if( thisUploader.queue.length ) {
																		thisUploader.upload();
																	} else {
																		thisUploader.hideProgress();
																	}
																	break;

																case 404:
																	log( 'Invalid function call', 'error' );

																	thisUploader.queue.shift();
																	thisUploader.queueCount--;
																	thisUploader.queueSize--;

																	thisUploader.settings.onerror( resFile );

																	if( thisUploader.queue.length ) {
																		thisUploader.upload();
																	} else {
																		thisUploader.hideProgress();
																	}
																	break;
															}
														},
														thisUploader.request.onprogress = function( e ) {
															if( e.lengthComputable ) {
																if( thisUploader.domProgress ) {
																	var intPercentage = Math.round( ( e.loaded / e.total ) * 100 );
																	thisUploader.domProgress.value = intPercentage;

																	log( prettySize( e.loaded ) + '/' + prettySize( e.total ) + ' (' + intPercentage + '%)' );
																}

																thisUploader.settings.onprogress( resFile, e.loaded, e.total );
															}
														},
														thisUploader.request.upload.onprogress = thisUploader.request.onprogress,
														thisUploader.request.addEventListener( 'load',
															function() {}, false
														),
														thisUploader.request.addEventListener( 'error',
															function() {
																if( thisUploader.queue.length ) {
																	thisUploader.hideProgress();

																	thisUploader.queue = [];
																	thisUploader.queueCount = 0;
																	thisUploader.queueSize = 0;
																	thisUploader.queueUploadedCount = 0;
																	thisUploader.queueUploadedSize = 0;

																	thisUploader.settings.onerror( resFile );

																	log( 'An error occurred', 'error' );
																}
															}, false
														),
														thisUploader.request.addEventListener( 'abort',
															function() {
																if( thisUploader.queue.length ) {
																	thisUploader.hideProgress();

																	thisUploader.queue = [];
																	thisUploader.queueCount = 0;
																	thisUploader.queueSize = 0;
																	thisUploader.queueUploadedCount = 0;
																	thisUploader.queueUploadedSize = 0;

																	thisUploader.settings.onabort( resFile );

																	log( 'Upload aborted', 'warning' );
																}
															}, false
														),
														thisUploader.request.open( 'PUT', thisUploader.uri, true ),
														thisUploader.request.setRequestHeader( 'Accept', '"text/plain; charset=iso-8859-1", "Content-Type": "text/plain; charset=iso-8859-1"' ),
														thisUploader.request.setRequestHeader( 'Twist-File', strFileName ),
														thisUploader.request.setRequestHeader( 'Twist-Length', intFileSize ),
														thisUploader.request.setRequestHeader( 'Twist-UID', thisUploader.uid ),
														thisUploader.request.send( resFileReader.result );
													}, false
											);

											resFileReader.readAsArrayBuffer( resFile );
										} else {
											thisUploader.queue.shift();
											thisUploader.domInput.value = '';

											log( strFileName + ' (' + strFileType + ') is not in the list of allowed types', 'warn' );

											if( thisUploader.acceptTypes.length ) {
												log( 'Allowed MIME types: ' + thisUploader.acceptTypes.join( ', ' ) );
											}

											if( thisUploader.acceptExtentions.length ) {
												log( 'Allowed file extenstions: ' + thisUploader.acceptExtentions.join( ', ' ) );
											}

											alert( 'This file type is not permitted' );

											thisUploader.clearInput();
										}
									}
								} catch( err ) {
									thisUploader.hideProgress();

									thisUploader.queue = [];
									thisUploader.queueCount = 0;
									thisUploader.queueSize = 0;
									thisUploader.queueUploadedCount = 0;
									thisUploader.queueUploadedSize = 0;

									thisUploader.settings.onerror( resFile );
									thisUploader.settings.onabort( resFile );

									log( err, 'error' );
								}
							},
							thisUploader.uploaded = [];
							thisUploader.uri = '/' + strUri.replace( /^\//, '' ).replace( /\/$/, '' );

					for( var strSetting in objSettings ) {
						thisUploader.settings[strSetting] = objSettings[strSetting];
					}

					if( thisUploader.multiple ) {
						thisUploader.settings.clearoncomplete = true;
					}

					if( thisUploader.domPseudo
							&& thisUploader.domPseudo.value
							&& thisUploader.domPseudo.value !== '' ) {
						thisUploader.uploaded = thisUploader.domPseudo.value.split( ',' ) || [];
					}

					debug = ( thisUploader.settings.debug === true );

					if( thisUploader.domCountWrapper
							&& !thisUploader.settings.counter ) {
						thisUploader.domCountWrapper.style.display = 'none';
					}

					if( thisUploader.domCancelUpload
							&& !thisUploader.settings.abortable ) {
						thisUploader.domCancelUpload.style.display = 'none';
					}

					if( thisUploader.domClearUpload ) {
						thisUploader.domClearUpload.style.display = 'none';
					}

					thisUploader.hideProgress();

					if( thisUploader.settings.dragdrop !== null ) {
						var domDrop = document.getElementById( thisUploader.settings.dragdrop );

						if( domDrop ) {
							domDrop.ondrop = function( e ) {
									e.preventDefault();
									thisUploader.upload( e, e.target.files || e.dataTransfer.files );

									removeClass( domDrop, 'hover' ),
									removeClass( domDrop, 'droppable' );
								},
							domDrop.ondragstart = function() {
									addClass( domDrop, 'droppable' );
									return false;
								},
							domDrop.ondragover = function() {
									addClass( domDrop, 'hover' );
									return false;
								},
							domDrop.ondragleave = function() {
									removeClass( domDrop, 'hover' );
									return false;
								},
							domDrop.ondragend = function() {
									removeClass( domDrop, 'hover' ),
									removeClass( domDrop, 'droppable' );
									return false;
								};
						}
					}

					var strAccept = thisUploader.domInput ? thisUploader.domInput.getAttribute( 'accept' ) : '';
					if( strAccept ) {
						var arrAcceptValues = strAccept.replace( / /g, '' ).split( ',' );

						if( arrAcceptValues.length ) {
							for( var intAccept in arrAcceptValues ) {
								if( arrAcceptValues[intAccept].substr( 0, 1 ) === '.' ) {
									thisUploader.acceptExtentions.push( arrAcceptValues[intAccept].substr( 1 ) );
								} else {
									thisUploader.acceptTypes.push( arrAcceptValues[intAccept].replace( /\//g, '\\/' ).replace( /\*/g, '.*' ) );
								}

								thisUploader.acceptRaw.push( arrAcceptValues[intAccept] );
							}
						}
					}

					if( thisUploader.domPseudo
							&& thisUploader.domInput ) {
						thisUploader.domPseudo.name = thisUploader.domInput.name.replace( '[]', '' );
						thisUploader.domInput.removeAttribute( 'name' );
					}

					if( uploadSupported ) {
						try {
							if( thisUploader.domInput ) {
								thisUploader.domInput.addEventListener( 'change', thisUploader.upload );
							} else {
								throw 'No element exists with id="' + strInputID + '"';
							}

							return thisUploader;
						} catch( err ) {
							log( err, 'error' );
						}
					} else {
						thisUploader.hideClear();
						thisUploader.hideProgress();
						log( 'Your browser does not support AJAX uploading', 'warn', true );

						return null;
					}
				};

		window.TwistUploader = function( strInputID, strUri, objSettings ) {
			return new uploader( strInputID, strUri, objSettings );
		};
	}
)( window, document );