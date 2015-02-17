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
		log = function( mxdData, strType ) {
				if( debug
						&& window.console ) {
					if( window.console[strType] ) {
						window.console[strType]( mxdData );
					} else if( window.console.log ) {
						console.log( mxdData );
					}
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
		uploader = function( strInputID, strProcess, objSettings ) {
				try {
					var thisUploader = this;

					thisUploader.created = ( new Date() ).getTime(),
					thisUploader.cancelUpload = function( e ) {
							e.preventDefault();
							thisUploader.request.abort();
						},
					thisUploader.domCancelUpload = document.getElementById( strInputID + '-cancel' ),
					thisUploader.domCount = document.getElementById( strInputID + '-count' ),
					thisUploader.domCountWrapper = document.getElementById( strInputID + '-count-wrapper' ),
					thisUploader.domCountWrapperDisplay = thisUploader.domCountWrapper ? thisUploader.domCountWrapper.style.display : 'inline-block',
					thisUploader.domCountTotal = document.getElementById( strInputID + '-total' ),
					thisUploader.domInput = document.getElementById( strInputID ),
					thisUploader.domInputDisplay = thisUploader.domInput ? thisUploader.domInput.style.display : 'inline-block',
					thisUploader.domProgress = document.getElementById( strInputID + '-progress' ),
					thisUploader.domProgressWrapper = document.getElementById( strInputID + '-progress-wrapper' ),
					thisUploader.hideProgress = function() {
							thisUploader.domInput.style.display = this.domInputDisplay;

							if( thisUploader.domProgressWrapper ) {
								thisUploader.domProgressWrapper.style.display = 'none';
							}

							if( thisUploader.domCancelUpload ) {
								thisUploader.domCancelUpload.removeEventListener( 'click', thisUploader.cancelUpload );
							}
						},
					thisUploader.process = strProcess,
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
							onabort: function() {},
							oncompletefile: function() {},
							oncompletequeue: function() {},
							onerror: function() {},
							onprogress: function() {},
							verbose: false
						},
					thisUploader.showProgress = function() {
							thisUploader.domInput.style.display = 'none';

							if( thisUploader.domProgressWrapper ) {
								thisUploader.domProgressWrapper.style.display = this.domInputDisplay;
							}

							if( thisUploader.domCancelUpload ) {
								thisUploader.domCancelUpload.addEventListener( 'click', thisUploader.cancelUpload );
							}
						},
					thisUploader.uid = strInputID,
					thisUploader.upload = function( e ) {
							if( e ) {
								var domCaller = e.target || e.srcElement,
								resFiles = domCaller.files;

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

							if( thisUploader.queue.length ) {
								var resFile = thisUploader.queue[0],
								strFileName = resFile.name,
								intFileSize = parseInt( resFile.size ),
								resFileReader = new FileReader( { blob: true } );

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

															if( thisUploader.queue.length ) {
																thisUploader.settings.oncompletefile( resFile, JSON.parse( thisUploader.request.responseText ) );

																thisUploader.upload();
															} else {
																thisUploader.hideProgress();

																if( thisUploader.settings.clearoncomplete ) {
																	thisUploader.domInput.value = '';

																	if( thisUploader.domInput.value ) {
																		thisUploader.domInput.type = 'text';
																		thisUploader.domInput.type = 'file';
																	}
																}

																log( 'Finsihed uploading ' + thisUploader.queueUploadedCount + ' files (' + prettySize( thisUploader.queueUploadedSize ) + ')', 'info' );
																thisUploader.queueCount = 0;
																thisUploader.queueSize = 0;
																thisUploader.queueUploadedCount = 0;
																thisUploader.queueUploadedSize = 0;

																thisUploader.settings.oncompletefile( resFile, JSON.parse( thisUploader.request.responseText ) );
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

														if( thisUploader.settings.verbose ) {
															log( prettySize( e.loaded ) + '/' + prettySize( e.total ) + ' (' + intPercentage + '%)' );
														}
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
										thisUploader.request.open( 'PUT', '/twist/core/scripts/upload.php', true ),
										thisUploader.request.setRequestHeader( 'Accept', '"text/plain; charset=iso-8859-1", "Content-Type": "text/plain; charset=iso-8859-1"' ),
										thisUploader.request.setRequestHeader( 'Twist-File', strFileName ),
										thisUploader.request.setRequestHeader( 'Twist-Length', intFileSize ),
										thisUploader.request.setRequestHeader( 'Twist-UID', thisUploader.uid ),
										thisUploader.request.setRequestHeader( 'Twist-Process', thisUploader.process ),
										thisUploader.request.send( resFileReader.result );
									}, false
								);

								resFileReader.readAsArrayBuffer( resFile );
							}
						};

					for( var strSetting in objSettings ) {
						thisUploader.settings[strSetting] = objSettings[strSetting];
					}

					debug = ( thisUploader.settings.debug == true );

					if( thisUploader.domCountWrapper
							&& !thisUploader.settings.counter ) {
						thisUploader.domCountWrapper.style.display = 'none';
					}

					if( thisUploader.domCancelUpload
							&& !thisUploader.settings.abortable ) {
						thisUploader.domCancelUpload.style.display = 'none';
					}

					if( thisUploader.domInput ) {
						thisUploader.domInput.addEventListener( 'change', this.upload );
						thisUploader.hideProgress();
					} else {
						throw 'No element exists with the id="' + strInputID + '"';
					}

					return thisUploader;
				} catch( err ) {
					log( err, 'error' );
				}
			};

		window.twistUploader = function( strInputID, strProcess, objSettings ) {
			return new uploader( strInputID, strProcess, objSettings );
		}
	}
)( window, document );