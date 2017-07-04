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

let hasClass = ( domElement, strClass ) => {
			return domElement.className.indexOf( strClass ) !== -1;
		},
		addClass = ( domElement, strClass ) => {
			if( !hasClass( domElement, strClass ) ) {
				domElement.className += ' ' + strClass;
			}
		},
		removeClass = ( domElement, strClass ) => {
			if( hasClass( domElement, strClass ) ) {
				domElement.className = domElement.className.replace( new RegExp( '^' + strClass + '$', 'g' ), '' ).replace( new RegExp( '^' + strClass + ' ', 'g' ), '' ).replace( new RegExp( ' ' + strClass + '$', 'g' ), '' ).replace( new RegExp( ' ' + strClass + ' ', 'g' ), ' ' );
			}
		},
		prettySize = ( intBytes ) => {
			let arrLimits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
					intLimit = 0;

			while( arrLimits[intLimit] &&
			intBytes > Math.pow( 1024, intLimit + 1 ) ) {
				intLimit++;
			}

			return round( intBytes / Math.pow( 1024, intLimit ), ( intLimit > 1 ? 2 : 0 ) ) + arrLimits[intLimit];
		},
		round = ( intNumber, intDP ) => {
			intDP = ( typeof intDP !== 'number' ) ? 0 : intDP;
			return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
		},
		uploadSupported = ( typeof new XMLHttpRequest().responseType === 'string' && 'withCredentials' in new XMLHttpRequest() );








export default class twistfileupload {
	constructor( id, strUri, objSettings ) {
		if( uploadSupported ) {
			let requestTest = new XMLHttpRequest();
			requestTest.open( 'GET', '/' );
			try {
				requestTest.responseType = 'arraybuffer';
			} catch( e ) {
				uploadSupported = false;
			}
		}

		this.id = id;

		this.created = ( new Date() ).getTime();
		this.debug = false;
		this.elements = {
			// The cancel upload button
			CancelUpload: document.getElementById( this.id + '-cancel' ),

			// The upload count element
			Count: document.getElementById( this.id + '-count' ),

			// The upload count wrapper element
			CountWrapper: document.getElementById( this.id + '-count-wrapper' ),

			// The total file count element
			CountTotal: document.getElementById( this.id + '-total' ),

			// The file input element
			Input: document.getElementById( this.id ),

			// The list of uploaded files element
			List: document.getElementById( this.id + '-list' ),

			// The upload progress element
			Progress: document.getElementById( this.id + '-progress' ),

			// The upload progress wrapper element
			ProgressWrapper: document.getElementById( this.id + '-progress-wrapper' ),

			// The pseudo element containing the CSV values that will be posted
			Pseudo: document.getElementById( this.id + '-pseudo' ),
		};






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
		 * The display property of the upload count element
		 * @type {null}
		 */
		this.domCountWrapperDisplay = null;
		/**
		 * The display property of the file input element
		 * @type {null}
		 */
		this.domInputDisplay = null;
		/**
		 * The display property of the cancel upload button
		 * @type {string|null}
		 */
		this.domCancelUploadDisplay = null;

		/**
		 * True if the file input field has a 'multiple' attribute
		 * @type {boolean}
		 */
		this.multiple = ( this.element( 'Input' ) && this.element( 'Input' ).hasAttribute( 'multiple' ) ) || false;

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
		 * The XML HTTP request object
		 * @type {XMLHttpRequest}
		 */
		this.request = new XMLHttpRequest();

		/**
		 * @deprecated
		 * @type {boolean}
		 */
		this.supported = false;

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

		this.settings = {
			abortable: true,
			counter: true,
			debug: false,
			dragdrop: null,
			dropableclass: 'twistupload-dropable',
			hoverclass: 'twistupload-hover',
			invalidtypemessage: 'This file type is not permitted',
			onabort: () => {},
			onclear: () => {},
			oncompletefile: () => {},
			oncompletequeue: () => {},
			onerror: () => {},
			oninvalidtype: () => {},
			onprogress: () => {},
			onstart: () => {},
			previewsize: 128,
			previewsquare: true
		};




		this.boundCancelUpload = e => { this.cancelUpload(); };


		if( this.element( 'Pseudo' ) &&
				this.element( 'Pseudo' ).value &&
				this.element( 'Pseudo' ).value !== '' ) {
			this.uploaded = this.element( 'Pseudo' ).value.split( ',' ) || [];
		}

		if( this.element( 'CountWrapper' ) && !this.settings.counter ) {
			this.element( 'CountWrapper' ).style.display = 'none';
		}

		if( this.element( 'CancelUpload' ) && !this.settings.abortable ) {
			this.element( 'CancelUpload' ).style.display = 'none';
		}

		this.hideProgress();

		if( this.settings.dragdrop !== null ) {
			let domDrop = document.getElementById( this.settings.dragdrop );

			if( domDrop ) {
				domDrop.ondrop = e => {
					e.preventDefault();
					this.upload( e, e.target.files || e.dataTransfer.files );

					removeClass( domDrop, this.settings.hoverclass );
					removeClass( domDrop, this.settings.dropableclass );
				};
				domDrop.ondragstart = () => {
					addClass( domDrop, this.settings.dropableclass );
					return false;
				};
				domDrop.ondragover = () => {
					addClass( domDrop, this.settings.hoverclass );
					return false;
				};
				domDrop.ondragleave = () => {
					removeClass( domDrop, this.settings.hoverclass );
					return false;
				};
				domDrop.ondragend = () => {
					removeClass( domDrop, this.settings.hoverclass );
					removeClass( domDrop, this.settings.dropableclass );
					return false;
				};
			}
		}

		let strAccept = this.element( 'Input' ) ? this.element( 'Input' ).getAttribute( 'accept' ) : '';
		if( strAccept ) {
			let arrAcceptValues = strAccept.replace( / /g, '' ).split( ',' );

			if( arrAcceptValues.length ) {
				for( let intAccept in arrAcceptValues ) {
					if( arrAcceptValues[intAccept].substr( 0, 1 ) === '.' ) {
						this.acceptExtentions.push( arrAcceptValues[intAccept].substr( 1 ).toLowerCase() );
					} else {
						this.acceptTypes.push( arrAcceptValues[intAccept].replace( /\//g, '\\/' ).replace( /\*/g, '.*' ) );
					}

					this.acceptRaw.push( arrAcceptValues[intAccept] );
				}
			}
		}

		if( uploadSupported ) {
			if( this.element( 'Input' ) ) {
				if( this.element( 'Pseudo' ) ) {
					this.element( 'Pseudo' ).name = this.element( 'Input' ).name.replace( '[]', '' );
					this.element( 'Input' ).removeAttribute( 'name' );
				}

				this.element( 'Input' ).addEventListener( 'change', (e, files) => {this.upload( e, files );} );
			} else {
				throw 'No element exists with id="' + this.id + '"';
			}
		} else {
			this.hideProgress();

			console.warn( 'Your browser does not support AJAX uploading', 'warn', true );
		}













	}

	element( element ) {
		return this.elements[element] || null;
	}



	// Do the upload with the selected files
	upload( e, arrFiles ) {
		try {
			if( e ) {
				let resFiles = ( !arrFiles ? ( e.target || e.srcElement ).files : arrFiles );

				this.queue.push.apply( this.queue, resFiles );
				this.queueCount += resFiles.length;

				for( let intFile = 0, intFiles = resFiles.length; intFile < intFiles; intFile++ ) {
					this.queueSize += parseInt( resFiles[intFile].size );
				}

				if( this.element( 'CountTotal' ) ) {
					this.element( 'CountTotal' ).innerText = this.queueCount;
				}

				console.log( 'Added ' + resFiles.length + ' files to the queue', 'info' );
			}

			if( this.domCancelUploadDisplay === null ) {
				this.domCancelUploadDisplay = this.element( 'CancelUpload' ).style.display || 'inline-block';
			}

			if( this.domCountWrapperDisplay === null ) {
				this.domCountWrapperDisplay = this.element( 'CountWrapper' ).style.display || 'inline-block';
			}

			if( this.domInputDisplay === null ) {
				this.domInputDisplay = this.element( 'Input' ).style.display || 'inline-block';
			}

			if( this.queue.length ) {
				let resFile = this.queue[0],
						strFileName = resFile.name,
						strFileType = resFile.type,
						strFileExtention = strFileName.substr( strFileName.lastIndexOf( '.' ) + 1 ).toLowerCase(),
						intFileSize = parseInt( resFile.size ),
						resFileReader = new FileReader( {blob: true} ),
						blAcceptedType = !this.acceptTypes.length && !this.acceptExtentions.length;

				if( !blAcceptedType ) {
					for( let intType in this.acceptTypes ) {
						if( new RegExp( '^' + this.acceptTypes[intType] + '$', 'gi' ).test( strFileType ) ) {
							blAcceptedType = true;
							break;
						}
					}
				}

				if( !blAcceptedType ) {
					for( let intExtention in this.acceptExtentions ) {
						if( strFileExtention === this.acceptExtentions[intExtention] ) {
							blAcceptedType = true;
							break;
						}
					}
				}

				if( blAcceptedType ) {
					this.settings.onstart( resFile );
					this.showProgress();

					if( this.element( 'Count' ) ) {
						this.element( 'Count' ).innerText = this.queueUploadedCount + 1;
					}

					if( this.queueCount === 1 ) {
						if( this.element( 'Progress' ) ) {
							this.element( 'Progress' ).removeAttribute( 'value' );
						}

						if( this.element( 'CountWrapper' ) ) {
							this.element( 'CountWrapper' ).style.display = 'none';
						}
					} else if( this.element( 'CountWrapper' ) ) {
						this.element( 'CountWrapper' ).style.display = this.element( 'CountWrapperDisplay' );
					}

					resFileReader.addEventListener( 'load',
							e => {
								this.request.onreadystatechange = () => {
									switch( this.request.status ) {
										case 200:
											if( this.request.readyState === 4 ) {
												console.info( 'Uploaded ' + strFileName + ' (' + prettySize( intFileSize ) + ')' );

												this.queue.shift();
												this.queueUploadedCount++;
												this.queueUploadedSize += intFileSize;

												let jsonResponse = JSON.parse( this.request.responseText );

												if( this.queue.length ) {
													if( this.multiple ) {
														this.uploaded.push( jsonResponse );
													} else {
														this.uploaded = [jsonResponse.form_value];
													}

													this.updateUploadedList();

													if( window.twist.debug ) {
														window.twist.debug.logFileUpload( resFile,jsonResponse );
													}

													this.settings.oncompletefile( jsonResponse, resFile );
													this.upload();
												} else {
													this.hideProgress();

													console.info( 'Finished uploading ' + this.queueUploadedCount + ' files (' + prettySize( this.queueUploadedSize ) + ')', 'info' );

													this.queueCount = 0;
													this.queueSize = 0;
													this.queueUploadedCount = 0;
													this.queueUploadedSize = 0;

													this.clearInput();

													if( this.multiple ) {
														this.uploaded.push( jsonResponse );
													} else {
														this.uploaded = [jsonResponse];
													}

													this.updateUploadedList();

													if( window.twist.debug ) {
														window.twist.debug.logFileUpload( resFile,jsonResponse );
													}

													this.settings.oncompletefile( jsonResponse, resFile );
													this.settings.oncompletequeue();
												}
											}
											break;

										case 403:
											console.error( 'Permission denied', 'error' );

											this.queue.shift();
											this.queueCount--;
											this.queueSize--;

											this.settings.onerror( resFile );

											if( this.queue.length ) {
												this.upload();
											} else {
												this.hideProgress();
											}
											break;

										case 404:
											console.error( 'Invalid function call', 'error' );

											this.queue.shift();
											this.queueCount--;
											this.queueSize--;

											this.settings.onerror( resFile );

											if( this.queue.length ) {
												this.upload();
											} else {
												this.hideProgress();
											}
											break;
									}
								};
								this.request.onprogress = e => {
									if( e.lengthComputable ) {
										if( this.element( 'Progress' ) ) {
											let intPercentage = Math.round( ( e.loaded / e.total ) * 100 );
											this.element( 'Progress' ).value = intPercentage;

											console.log( prettySize( e.loaded ) + '/' + prettySize( e.total ) + ' (' + intPercentage + '%)' );
										}

										this.settings.onprogress( resFile, e.loaded, e.total );
									}
								};
								this.request.upload.onprogress = this.request.onprogress;
								this.request.addEventListener( 'load', () => {}, false );
								this.request.addEventListener( 'error', () => {
											if( this.queue.length ) {
												this.hideProgress();

												this.queue = [];
												this.queueCount = 0;
												this.queueSize = 0;
												this.queueUploadedCount = 0;
												this.queueUploadedSize = 0;

												this.settings.onerror( resFile );

												console.error( 'An error occurred', 'error' );
											}
										}, false );
								this.request.addEventListener( 'abort', () => {
											if( this.queue.length ) {
												this.hideProgress();

												this.queue = [];
												this.queueCount = 0;
												this.queueSize = 0;
												this.queueUploadedCount = 0;
												this.queueUploadedSize = 0;

												this.settings.onabort( resFile );

												console.error( 'Upload aborted', 'warning' );
											}
										}, false );
								this.request.open( 'PUT', this.uri, true );
								this.request.setRequestHeader( 'Accept', '"text/plain; charset=iso-8859-1", "Content-Type": "text/plain; charset=iso-8859-1"' );
								this.request.setRequestHeader( 'Twist-File', strFileName );
								this.request.setRequestHeader( 'Twist-Length', intFileSize );
								this.request.setRequestHeader( 'Twist-UID', this.id );
								this.request.send( resFileReader.result );
							}
					);

					resFileReader.readAsArrayBuffer( resFile );
				} else {
					let objInvalidFile = this.queue.shift();
					this.element( 'Input' ).value = '';

					this.settings.oninvalidtype( objInvalidFile, this.acceptTypes, this.acceptExtentions );

					console.error( strFileName + ' (' + strFileType + ') is not in the list of allowed types', 'warn' );

					if( this.acceptTypes.length ) {
						console.info( 'Allowed MIME types: ' + this.acceptTypes.join( ', ' ) );
					}

					if( this.acceptExtentions.length ) {
						console.info( 'Allowed file extensions: ' + this.acceptExtentions.join( ', ' ) );
					}

					//TODO: Handle this without using alert()
					//alert( this.settings.invalidtypemessage );

					this.clearInput();
				}
			}
		} catch( err ) {
			console.log( this );
			this.hideProgress();

			this.settings.onerror( this.queue[0] );
			this.settings.onabort( this.queue[0] );

			this.queue = [];
			this.queueCount = 0;
			this.queueSize = 0;
			this.queueUploadedCount = 0;
			this.queueUploadedSize = 0;

			console.error( err, 'error' );
		}
	}

	//Add a listener to the remove file button
	addRemoveFileListener() {
		let funRemoveFile = ( intUploadedFileIndex ) => {
			return () => {
				console.log( 'Remove' );
				this.removeFileFromListFunction( intUploadedFileIndex );
			};
		};

		for( let intUploadedFile in this.uploaded ) {
			let domRemoveButton = document.getElementById( this.id + '-remove-' + intUploadedFile );

			domRemoveButton.removeEventListener( 'click', ( funRemoveFile )( intUploadedFile ) );
			domRemoveButton.addEventListener( 'click', ( funRemoveFile )( intUploadedFile ) );
		}
	}

	// Hide the upload progress bar
	hideProgress() {
		if( this.element( 'Input' ) ) {
			this.element( 'Input' ).style.display = this.element( 'InputDisplay' );
		}

		if( this.element( 'ProgressWrapper' ) ) {
			this.element( 'ProgressWrapper' ).style.display = 'none';
		}

		if( this.element( 'CancelUpload' ) ) {
			this.element( 'CancelUpload' ).removeEventListener( 'click', this.boundCancelUpload );
		}
	}

	// Cancel the current upload
	cancelUpload() {
		this.request.abort();
	}

	// Clear the file input
	clearInput() {
		this.element( 'Input' ).value = '';

		if( this.element( 'Input' ).value ) {
			this.element( 'Input' ).type = 'text';
			this.element( 'Input' ).type = 'file';
		}

		this.element( 'Pseudo' ).value = '';
		this.settings.onclear();
	}

	// Remove an uploaded file from the list
	removeFileFromListFunction( intFileIndex ) {
		this.uploaded.splice( intFileIndex, 1 );
		this.updateUploadedList();
	}

	// Show the progress upload bar
	showProgress() {
		this.element( 'Input' ).style.display = 'none';

		if( this.element( 'ProgressWrapper' ) ) {
			this.element( 'ProgressWrapper' ).style.display = this.element( 'InputDisplay' );
		}

		if( this.element( 'CancelUpload' ) ) {
			this.element( 'CancelUpload' ).addEventListener( 'click', this.boundCancelUpload );
		}
	}

	// Update the list of uploaded files
	updateUploadedList() {
		let strListHTML = '',
				arrUploadedFormValues = [];

		for( let intUploadedFile in this.uploaded ) {
			let objUploadedFile = this.uploaded[intUploadedFile],
					strFilePreview = objUploadedFile.uri_preview,
					strFileDetails = '',
					arrFileDetails = ['file/name', 'file/size', 'file_type'];

			arrUploadedFormValues.push( objUploadedFile.form_value );

			let strPreview = 'thumb-' + this.settings.previewsize;

			if( this.settings.previewsquare ) {
				strPreview = 'square-' + strPreview;
			}

			if( objUploadedFile.support &&
					objUploadedFile.support[strPreview] ) {
				strFilePreview = objUploadedFile.support[strPreview];
			}

			for( let intFileDetail in arrFileDetails ) {
				let strFileDetail = arrFileDetails[intFileDetail],
						strProperty;

				if( strFileDetail.indexOf( '/' ) !== -1 ) {
					let arrDelve = strFileDetail.split( '/' ),
							objToDelve = objUploadedFile[arrDelve[0]] || null;

					arrDelve.shift();

					if( objToDelve ) {
						for( let intKeyPart in arrDelve ) {
							objToDelve = objToDelve[arrDelve[intKeyPart]] || null;
						}

						strProperty = objToDelve || null;
					}
				} else {
					strProperty = objUploadedFile[strFileDetail] || null;
				}

				strFileDetails += '<li data-key="' + strFileDetail + '"><span>' + strFileDetail.replace( /[\/_]/g, ' ' ) + ' :</span>' + strProperty + '</li>';
			}

			strListHTML += '<li class="twistupload-file-list-item"><img src="' + strFilePreview + '"><ul class="twistupload-file-info">' + strFileDetails + '</ul><button id="' + this.id + '-remove-' + intUploadedFile + '" data-file="' + intUploadedFile + '">Remove</button></li>';
		}

		this.element( 'Pseudo' ).value = arrUploadedFormValues.join( ',' );
		this.element( 'List' ).innerHTML = strListHTML;

		this.addRemoveFileListener();
	}
}
