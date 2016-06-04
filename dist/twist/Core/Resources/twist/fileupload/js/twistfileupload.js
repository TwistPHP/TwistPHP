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

(
	(
		function( root, factory ) {
			if( typeof define === 'function' &&
					define.amd ) {
				define( 'twistfileupload', [], factory );
			} else if( typeof module === 'object' &&
					module.exports ) {
				module.exports = factory();
			} else {
				root.twistfileupload = factory();
			}
		}
	)(
		this,
		function() {
			/**
			 * The file uploader for TwistPHP
			 * @param {string} strInputID The ID of the input element
			 * @param {string} strUri The URI to post files to
			 * @param {Object} objSettings Settings
			 * @param {boolean} [objSettings.abortable=true] If true, allow the uploads to be aborted
			 * @param {boolean} [objSettings.counter=true] If true, show a counter for the files
			 * @param {boolean} [objSettings.debug=false] If true, show console output
			 * @param {string} [objSettings.dragdrop=null] The ID of the element which can act as a drop area
			 * @param {string} [objSettings.dropableclass='twistupload-dropable'] The class to add to the drop area when items can be dropped on it
			 * @param {string} [objSettings.hoverclass='twistupload-hover'] The class to add to the drop area when items are dragged over it
			 * @param {string} [objSettings.invalidtypemessage='This file type is not permitted'] The error message to show when an invalid file type is selected
			 * @param {function} [objSettings.onabort=function() {}] A function to execute when the upload is aborted
			 * @param {function} [objSettings.onclear=function() {}] A function to execute when the queue is cleared
			 * @param {function} [objSettings.oncompletefile=function() {}] A function to execute when a file is finished uploading
			 * @param {function} [objSettings.oncompletequeue=function() {}] A function to execute when the queue is finished uploading
			 * @param {function} [objSettings.onerror=function() {}] A function to execute when an error occurs
			 * @param {function} [objSettings.oninvalidtype=function() {}] A function to execute when an invalid type is selected
			 * @param {function} [objSettings.onprogress=function() {}] A function to execute as the queue is uploading
			 * @param {function} [objSettings.onstart=function() {}] A function to execute when the upload starts
			 * @param {number} [objSettings.previewsize=128] The size of the thumbnail to display
			 * @param {boolean} [objSettings.previewsquare=true] If true, use square thumbnails
			 * @returns {boolean|null}
			 * @alias twistfileupload
			 * @constructor
			 * @author Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
			 * @version 1.0.0
			 * @license GPL-3.0
			 */
			var TwistUploader = function( strInputID, strUri, objSettings ) {
				var debug = true,
						log = function( mxdData, strType, blOverride ) {
							if( ( debug ||
									blOverride === true ) &&
									window.console ) {
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

							while( arrLimits[intLimit] &&
							intBytes > Math.pow( 1024, intLimit + 1 ) ) {
								intLimit++;
							}

							return round( intBytes / Math.pow( 1024, intLimit ), ( intLimit > 1 ? 2 : 0 ) ) + arrLimits[intLimit];
						},
						round = function( intNumber, intDP ) {
							intDP = ( typeof intDP !== 'number' ) ? 0 : intDP;
							return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
						},
						uploadSupported = ( typeof new XMLHttpRequest().responseType === 'string' && 'withCredentials' in new XMLHttpRequest() );

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

				/**
				 * An array of allowed file extensions
				 * @type {string[]}
				 */
				this.acceptExtentions = [];

				/**
				 * An array of all the raw allowed types and extensions
				 * @type {string[]}
				 */
				this.acceptRaw = [];

				/**
				 * An array of allowed file types
				 * @type {string[]}
				 */
				this.acceptTypes = [];

				/**
				 * Add a listener to the remove file button
				 */
				this.addRemoveFileListener = function() {
					var funRemoveFile = function( intUploadedFileIndex ) {
								return function() {
									console.log( 'Remove' );
									thisUploader.removeFileFromListFunction( intUploadedFileIndex );
								};
							};

					for( var intUploadedFile in thisUploader.uploaded ) {
						var domRemoveButton = document.getElementById( strInputID + '-remove-' + intUploadedFile );

						domRemoveButton.removeEventListener( 'click', ( funRemoveFile )( intUploadedFile ) );
						domRemoveButton.addEventListener( 'click', ( funRemoveFile )( intUploadedFile ) );
					}
				};

				/**
				 * The time that the class was initiated
				 * @type {number}
				 * @deprecated
				 */
				this.created = ( new Date() ).getTime();

				/**
				 * Cancel the current upload
				 */
				this.cancelUpload = function() {
					thisUploader.request.abort();
				};

				/**
				 * Clear the file input
				 */
				this.clearInput = function() {
					thisUploader.domInput.value = '';

					if( thisUploader.domInput.value ) {
						thisUploader.domInput.type = 'text';
						thisUploader.domInput.type = 'file';
					}

					thisUploader.domPseudo.value = '';
					thisUploader.settings.onclear();
				};

				/**
				 * The cancel upload button
				 * @type {Element}
				 */
				this.domCancelUpload = document.getElementById( strInputID + '-cancel' );

				/**
				 * The display property of the cancel upload button
				 * @type {string|null}
				 */
				this.domCancelUploadDisplay = null;

				/**
				 * The upload count element
				 * @type {Element}
				 */
				this.domCount = document.getElementById( strInputID + '-count' );

				/**
				 * The upload count wrapper element
				 * @type {Element}
				 */
				this.domCountWrapper = document.getElementById( strInputID + '-count-wrapper' );

				/**
				 * The display property of the upload count element
				 * @type {null}
				 */
				this.domCountWrapperDisplay = null;

				/**
				 * The total file count element
				 * @type {Element}
				 */
				this.domCountTotal = document.getElementById( strInputID + '-total' );

				/**
				 * The file input element
				 * @type {Element}
				 */
				this.domInput = document.getElementById( strInputID );

				/**
				 * The display property of the file input element
				 * @type {null}
				 */
				this.domInputDisplay = null;

				/**
				 * The list of uploaded files element
				 * @type {Element}
				 */
				this.domList = document.getElementById( strInputID + '-list' );

				/**
				 * The upload progress element
				 * @type {Element}
				 */
				this.domProgress = document.getElementById( strInputID + '-progress' );

				/**
				 * The upload progress wrapper element
				 * @type {Element}
				 */
				this.domProgressWrapper = document.getElementById( strInputID + '-progress-wrapper' );

				/**
				 * The pseudo element containing the CSV values that will be posted
				 * @type {Element}
				 */
				this.domPseudo = document.getElementById( strInputID + '-pseudo' );

				/**
				 * Hide the upload progress bar
				 */
				this.hideProgress = function() {
					if( thisUploader.domInput ) {
						thisUploader.domInput.style.display = thisUploader.domInputDisplay;
					}

					if( thisUploader.domProgressWrapper ) {
						thisUploader.domProgressWrapper.style.display = 'none';
					}

					if( thisUploader.domCancelUpload ) {
						thisUploader.domCancelUpload.removeEventListener( 'click', thisUploader.cancelUpload );
					}
				};

				/**
				 * True if the file input field has a 'multiple' attribute
				 * @type {boolean}
				 */
				this.multiple = ( thisUploader.domInput && thisUploader.domInput.hasAttribute( 'multiple' ) ) || false;

				/**
				 * The queue of files still to be uploaded
				 * @type {Array}
				 */
				this.queue = [];

				/**
				 * The number of files still in the queue
				 * @type {number}
				 */
				this.queueCount = 0;

				/**
				 * The size (in bytes) of the files still in the queue
				 * @type {number}
				 */
				this.queueSize = 0;

				/**
				 * The number of files uploaded
				 * @type {number}
				 */
				this.queueUploadedCount = 0;

				/**
				 * The size (in bytes) of the files uploaded
				 * @type {number}
				 */
				this.queueUploadedSize = 0;

				/**
				 * Remove an uploaded file from the list
				 * @param intFileIndex
				 */
				this.removeFileFromListFunction = function( intFileIndex ) {
					thisUploader.uploaded.splice( intFileIndex, 1 );
					thisUploader.updateUploadedList();
				};

				/**
				 * The XML HTTP request object
				 * @type {XMLHttpRequest}
				 */
				this.request = new XMLHttpRequest();

				/**
				 * The default settings object
				 * @type {{abortable: boolean, counter: boolean, debug: boolean, dragdrop: null, dropableclass: string, hoverclass: string, invalidtypemessage: string, onabort: twistfileupload.settings.onabort, onclear: twistfileupload.settings.onclear, oncompletefile: twistfileupload.settings.oncompletefile, oncompletequeue: twistfileupload.settings.oncompletequeue, onerror: twistfileupload.settings.onerror, oninvalidtype: twistfileupload.settings.oninvalidtype, onprogress: twistfileupload.settings.onprogress, onstart: twistfileupload.settings.onstart, previewsize: number, previewsquare: boolean}}
				 */
				this.settings = {
					abortable: true,
					counter: true,
					debug: false,
					dragdrop: null,
					dropableclass: 'twistupload-dropable',
					hoverclass: 'twistupload-hover',
					invalidtypemessage: 'This file type is not permitted',
					onabort: function() {},
					onclear: function() {},
					oncompletefile: function() {},
					oncompletequeue: function() {},
					onerror: function() {},
					oninvalidtype: function() {},
					onprogress: function() {},
					onstart: function() {},
					previewsize: 128,
					previewsquare: true
				};

				/**
				 * Show the progress upload bar
				 */
				this.showProgress = function() {
					thisUploader.domInput.style.display = 'none';

					if( thisUploader.domProgressWrapper ) {
						thisUploader.domProgressWrapper.style.display = thisUploader.domInputDisplay;
					}

					if( thisUploader.domCancelUpload ) {
						thisUploader.domCancelUpload.addEventListener( 'click', thisUploader.cancelUpload );
					}
				};

				/**
				 * @deprecated
				 * @type {boolean}
				 */
				this.supported = false;

				/**
				 * The UID of the uploader
				 * @type {string}
				 */
				this.uid = strInputID;

				/**
				 * Update the list of uploaded files
				 */
				this.updateUploadedList = function() {
					var strListHTML = '',
							arrUploadedFormValues = [];

					for( var intUploadedFile in thisUploader.uploaded ) {
						var objUploadedFile = thisUploader.uploaded[intUploadedFile],
								strFilePreview = objUploadedFile.uri_preview,
								strFileDetails = '',
								arrFileDetails = ['file/name', 'file/size', 'file_type'];

						arrUploadedFormValues.push( objUploadedFile.form_value );

						var strPreview = 'thumb-' + thisUploader.settings.previewsize;

						if( thisUploader.settings.previewsquare ) {
							strPreview = 'square-' + strPreview;
						}

						if( objUploadedFile.support &&
								objUploadedFile.support[strPreview] ) {
							strFilePreview = objUploadedFile.support[strPreview];
						}

						for( var intFileDetail in arrFileDetails ) {
							var strFileDetail = arrFileDetails[intFileDetail],
									strProperty;

							if( strFileDetail.indexOf( '/' ) !== -1 ) {
								var arrDelve = strFileDetail.split( '/' ),
										objToDelve = objUploadedFile[arrDelve[0]];

								arrDelve.shift();

								for( var intKeyPart in arrDelve ) {
									objToDelve = objToDelve[arrDelve[intKeyPart]];
								}

								strProperty = objToDelve;
							} else {
								strProperty = objUploadedFile[strFileDetail] || null;
							}

							strFileDetails += '<li data-key="' + strFileDetail + '"><span>' + strFileDetail.replace( /[\/_]/g, ' ' ) + ' :</span>' + strProperty + '</li>';
						}

						strListHTML += '<li class="twistupload-file-list-item"><img src="' + strFilePreview + '"><ul class="twistupload-file-info">' + strFileDetails + '</ul><button id="' + strInputID + '-remove-' + intUploadedFile + '" data-file="' + intUploadedFile + '">Remove</button></li>';
					}

					thisUploader.domPseudo.value = arrUploadedFormValues.join( ',' );
					thisUploader.domList.innerHTML = strListHTML;

					thisUploader.addRemoveFileListener();
				};

				/**
				 * Do the upload with the selected files
				 * @param e The upload event
				 * @param arrFiles The files selected
				 */
				this.upload = function( e, arrFiles ) {
					try {
						if( e ) {
							var resFiles = ( !arrFiles ? ( e.target || e.srcElement ).files : arrFiles );

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
									strFileExtention = strFileName.substr( strFileName.lastIndexOf( '.' ) + 1 ).toLowerCase(),
									intFileSize = parseInt( resFile.size ),
									resFileReader = new FileReader( {blob: true} ),
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
																	thisUploader.uploaded.push( jsonResponse );
																} else {
																	thisUploader.uploaded = [jsonResponse.form_value];
																}

																thisUploader.updateUploadedList();

																if( window.twistdebug ) {
																	window.twistdebug.logFileUpload( resFile,jsonResponse );
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

																thisUploader.clearInput();

																if( thisUploader.multiple ) {
																	thisUploader.uploaded.push( jsonResponse );
																} else {
																	thisUploader.uploaded = [jsonResponse];
																}

																thisUploader.updateUploadedList();

																if( window.twistdebug ) {
																	window.twistdebug.logFileUpload( resFile,jsonResponse );
																}

																thisUploader.settings.oncompletefile( jsonResponse, resFile );
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
											};
											thisUploader.request.onprogress = function( e ) {
												if( e.lengthComputable ) {
													if( thisUploader.domProgress ) {
														var intPercentage = Math.round( ( e.loaded / e.total ) * 100 );
														thisUploader.domProgress.value = intPercentage;

														log( prettySize( e.loaded ) + '/' + prettySize( e.total ) + ' (' + intPercentage + '%)' );
													}

													thisUploader.settings.onprogress( resFile, e.loaded, e.total );
												}
											};
											thisUploader.request.upload.onprogress = thisUploader.request.onprogress;
											thisUploader.request.addEventListener( 'load',
													function() {}, false
											);
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
											);
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
											);
											thisUploader.request.open( 'PUT', thisUploader.uri, true );
											thisUploader.request.setRequestHeader( 'Accept', '"text/plain; charset=iso-8859-1", "Content-Type": "text/plain; charset=iso-8859-1"' );
											thisUploader.request.setRequestHeader( 'Twist-File', strFileName );
											thisUploader.request.setRequestHeader( 'Twist-Length', intFileSize );
											thisUploader.request.setRequestHeader( 'Twist-UID', thisUploader.uid );
											thisUploader.request.send( resFileReader.result );
										}
								);

								resFileReader.readAsArrayBuffer( resFile );
							} else {
								var objInvalidFile = thisUploader.queue.shift();
								thisUploader.domInput.value = '';

								thisUploader.settings.oninvalidtype( objInvalidFile, thisUploader.acceptTypes, thisUploader.acceptExtentions );

								log( strFileName + ' (' + strFileType + ') is not in the list of allowed types', 'warn' );

								if( thisUploader.acceptTypes.length ) {
									log( 'Allowed MIME types: ' + thisUploader.acceptTypes.join( ', ' ) );
								}

								if( thisUploader.acceptExtentions.length ) {
									log( 'Allowed file extensions: ' + thisUploader.acceptExtentions.join( ', ' ) );
								}

								alert( thisUploader.settings.invalidtypemessage );

								thisUploader.clearInput();
							}
						}
					} catch( err ) {
						thisUploader.hideProgress();

						thisUploader.settings.onerror( thisUploader.queue[0] );
						thisUploader.settings.onabort( thisUploader.queue[0] );

						thisUploader.queue = [];
						thisUploader.queueCount = 0;
						thisUploader.queueSize = 0;
						thisUploader.queueUploadedCount = 0;
						thisUploader.queueUploadedSize = 0;

						log( err, 'error' );
					}
				};

				/**
				 * An array of uploaded files
				 * @type {Object[]}
				 */
				this.uploaded = [];

				/**
				 * The URI to upload files to
				 * @type {string}
				 */
				this.uri = '/' + strUri.replace( /^\//, '' ).replace( /\/$/, '' );

				for( var strSetting in objSettings ) {
					thisUploader.settings[strSetting] = objSettings[strSetting];
				}

				if( thisUploader.domPseudo &&
						thisUploader.domPseudo.value &&
						thisUploader.domPseudo.value !== '' ) {
					thisUploader.uploaded = thisUploader.domPseudo.value.split( ',' ) || [];
				}

				debug = ( thisUploader.settings.debug === true );

				if( thisUploader.domCountWrapper && !thisUploader.settings.counter ) {
					thisUploader.domCountWrapper.style.display = 'none';
				}

				if( thisUploader.domCancelUpload && !thisUploader.settings.abortable ) {
					thisUploader.domCancelUpload.style.display = 'none';
				}

				thisUploader.hideProgress();

				if( thisUploader.settings.dragdrop !== null ) {
					var domDrop = document.getElementById( thisUploader.settings.dragdrop );

					if( domDrop ) {
						domDrop.ondrop = function( e ) {
							e.preventDefault();
							thisUploader.upload( e, e.target.files || e.dataTransfer.files );

							removeClass( domDrop, thisUploader.settings.hoverclass );
							removeClass( domDrop, thisUploader.settings.dropableclass );
						};
						domDrop.ondragstart = function() {
							addClass( domDrop, thisUploader.settings.dropableclass );
							return false;
						};
						domDrop.ondragover = function() {
							addClass( domDrop, thisUploader.settings.hoverclass );
							return false;
						};
						domDrop.ondragleave = function() {
							removeClass( domDrop, thisUploader.settings.hoverclass );
							return false;
						};
						domDrop.ondragend = function() {
							removeClass( domDrop, thisUploader.settings.hoverclass );
							removeClass( domDrop, thisUploader.settings.dropableclass );
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
								thisUploader.acceptExtentions.push( arrAcceptValues[intAccept].substr( 1 ).toLowerCase() );
							} else {
								thisUploader.acceptTypes.push( arrAcceptValues[intAccept].replace( /\//g, '\\/' ).replace( /\*/g, '.*' ) );
							}

							thisUploader.acceptRaw.push( arrAcceptValues[intAccept] );
						}
					}
				}

				if( uploadSupported ) {
					if( thisUploader.domInput ) {
						if( thisUploader.domPseudo ) {
							thisUploader.domPseudo.name = thisUploader.domInput.name.replace( '[]', '' );
							thisUploader.domInput.removeAttribute( 'name' );
						}

						thisUploader.domInput.addEventListener( 'change', thisUploader.upload );
					} else {
						throw 'No element exists with id="' + strInputID + '"';
					}
				} else {
					thisUploader.hideProgress();

					log( 'Your browser does not support AJAX uploading', 'warn', true );

					return null;
				}

				return true;
			};

			return function( strInputID, strUri, objSettings ) {
				return new TwistUploader( strInputID, strUri, objSettings );
			};
		}
	)
);