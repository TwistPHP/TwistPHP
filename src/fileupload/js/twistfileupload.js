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

class Element {
	constructor( el ) {
		this.el = el;
		return this;
	}

	static create( tag, classes = [], attributes = {}, html = '' ) {
		const el = document.createElement( tag );

		if( classes ) {
			try {
				el.className = classes.join( ' ' );
			} catch( e ) {
				if( typeof classes === 'string' ) {
					el.className = classes;
				}
			}
		}

		if( attributes ) {
			for( let attribute in attributes ) {
				if( attributes.hasOwnProperty( attribute ) ) {
					el.setAttribute( attribute, attributes[attribute] );
				}
			}
		}

		if( html ) {
			el.innerHTML = html;
		}

		return el;
	}

	show() {
		this.toggle( true );
	}

	hide() {
		this.toggle();
	}

	toggle( show ) {
		let value = this.el.getAttribute( 'data-initialdisplay' ),
				display = this.el.style.display,
				computedDisplay = (window.getComputedStyle ? getComputedStyle( this.el, null ) : this.el.currentStyle).display;

		if( show ) {
			if( !value &&
					display === 'none' ) {
				this.el.style.display = '';
			}
			if( this.el.style.display === '' &&
					computedDisplay === 'none' ) {
				value = value || defaultDisplay( this.el.nodeName );
			}
		} else {
			if( display &&
					display !== 'none'
					|| !(computedDisplay === 'none') ) {
				this.el.setAttribute( 'data-initialdisplay', (computedDisplay === 'none') ? display : computedDisplay );
			}
		}

		if( !show ||
				this.el.style.display === 'none' ||
				this.el.style.display === '' ) {
			this.el.style.display = show ? value || '' : 'none';
		}
	}
}

export default class twistfileupload {
	constructor( id, uri, name, settings = {} ) {
		let uploadSupported = ( typeof new XMLHttpRequest().responseType === 'string' && 'withCredentials' in new XMLHttpRequest() );

		if( uploadSupported ) {
			let requestTest = new XMLHttpRequest();
			requestTest.open( 'GET', '/' );
			try {
				requestTest.responseType = 'arraybuffer';
			} catch( e ) {
				uploadSupported = false;
			}
		}

		this.settings = Object.assign( {
			abortable: true,
			acceptTypes: [],
			acceptExtensions: [],
			counter: true,
			debug: false,
			dragdrop: null,
			dragdrophoverclass: 'twistupload-drop-hover',
			hideInput: false,
			invalidtypemessage: 'This file type is not permitted',
			multiple: false,
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
		}, settings );

		this.id = id;
		this.elements = {
			// The cancel upload button
			CancelUpload: Element.create( 'button', '', {}, 'Cancel' ),

			// The upload count element
			Count: Element.create( 'span' ),

			// The total file count element
			CountTotal: Element.create( 'span' ),

			// The upload count wrapper element
			CountWrapper: Element.create( 'span' ),

			// The file input element
			Input: Element.create( 'input', '', (() => {
				let attributes = {
					type: 'file',
					//name: multiple ? name + '[]' : name,
					accept: this.settings.acceptTypes.concat( this.settings.acceptExtensions ).join( ',' )
				};
				if( this.settings.multiple ) {
					attributes.multiple = 'multiple';
				}
				return attributes;
			})() ),

			// The list of uploaded files element
			List: Element.create( 'ul' ),

			// The upload progress element
			Progress: Element.create( 'progress', '', {
				value: '0',
				max: '100'
			} ),

			// The upload progress wrapper element
			ProgressWrapper: Element.create( 'span' ),

			// The pseudo element containing the CSV values that will be posted
			Pseudo: Element.create( 'input', '', {
				type: 'hidden',
				name: name,
				value: ''
			} ),

			Wrapper: document.getElementById( id )
		};
		this.events = {};
		this.queue = [];
		this.queueCount = 0;
		this.queueSize = 0;
		this.queueUploadedCount = 0;
		this.queueUploadedSize = 0;
		this.request = new XMLHttpRequest();
		this.uploaded = [];
		this.uri = '/' + uri.replace( /^\//, '' ).replace( /\/$/, '' );

		this.addMarkup();
		this.addDragAndDropListeners();

		if( uploadSupported ) {
			this.elements.Input.addEventListener( 'change', ( e, files ) => {
				this.upload( e, files );
			} );
		} else {
			this.hideProgress();

			console.warn( 'Browser does not support AJAX uploading' );
		}
	}

	static prettySize( intBytes ) {
		let arrLimits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
				intLimit = 0;

		while( arrLimits[intLimit] && intBytes > Math.pow( 1024, intLimit + 1 ) ) {
			intLimit++;
		}

		return this.round( intBytes / Math.pow( 1024, intLimit ), ( intLimit > 1 ? 2 : 0 ) ) + arrLimits[intLimit];
	}

	static round( intNumber, intDP = 0 ) {
		return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
	}

	addMarkup() {
		this.elements.ProgressWrapper.appendChild( this.elements.Progress );
		this.elements.ProgressWrapper.appendChild( this.elements.CancelUpload );
		if( this.settings.multiple ) {
			this.elements.CountWrapper.appendChild( this.elements.Count );
			this.elements.CountWrapper.insertAdjacentHTML( 'beforeend', '/' );
			this.elements.CountWrapper.appendChild( this.elements.CountTotal );
			this.elements.ProgressWrapper.appendChild( this.elements.CountWrapper );
		}
		this.elements.Wrapper.appendChild( this.elements.Input );
		this.elements.Wrapper.appendChild( this.elements.Pseudo );
		this.elements.Wrapper.appendChild( this.elements.ProgressWrapper );
		this.elements.Wrapper.appendChild( this.elements.List );
		if( this.settings.hideInput ) {
			new Element( this.elements.Input ).hide();
		}

		this.hideProgress();
	}

	addDragAndDropListeners() {
		let dropArea = this.elements.Wrapper;
		let possibleDropArea = this.settings.dragdrop ? document.querySelectorAll( this.settings.dragdrop ) : [];

		if( possibleDropArea.length ) {
			dropArea = possibleDropArea[0];
		}

		dropArea.addEventListener( 'dragover', e => {
			e.stopPropagation();
			e.preventDefault();
			console.log( 'DRAG OVER' )
			dropArea.classList.add( this.settings.dragdrophoverclass );
		}, false );
		dropArea.addEventListener( 'dragleave', e => {
			e.stopPropagation();
			e.preventDefault();
			console.log( 'DRAG LEAVE' )
			dropArea.classList.remove( this.settings.dragdrophoverclass );
		}, false );
		dropArea.addEventListener( 'drop', e => {
			e.stopPropagation();
			e.preventDefault();
			this.upload( e, e.target.files || e.dataTransfer.files );
			dropArea.classList.remove( this.settings.dragdrophoverclass );
		}, false );
	}

	upload( e, arrFiles ) {
		try {
			if( e ) {
				let resFiles = ( !arrFiles ? ( e.target || e.srcElement ).files : arrFiles );

				this.queue.push.apply( this.queue, resFiles );
				this.queueCount += resFiles.length;

				for( let intFile = 0, intFiles = resFiles.length; intFile < intFiles; intFile++ ) {
					this.queueSize += parseInt( resFiles[intFile].size );
				}

				if( this.elements.CountTotal ) {
					this.elements.CountTotal.innerText = this.queueCount;
				}

				//console.log( 'Added ' + resFiles.length + ' files to the queue', 'info' );
			}

			if( this.queue.length ) {
				let resFile = this.queue[0],
						strFileName = resFile.name,
						strFileType = resFile.type,
						strFileExtention = strFileName.substr( strFileName.lastIndexOf( '.' ) + 1 ).toLowerCase(),
						intFileSize = parseInt( resFile.size ),
						resFileReader = new FileReader( {blob: true} ),
						blAcceptedType = !this.settings.acceptTypes.length && !this.settings.acceptExtensions.length;

				if( !blAcceptedType ) {
					for( let type of this.settings.acceptTypes ) {
						if( new RegExp( '^' + type.replace( '*', '.*' ) + '$', 'gi' ).test( strFileType ) ) {
							blAcceptedType = true;
							break;
						}
					}
				}

				if( !blAcceptedType ) {
					if( this.settings.acceptExtensions.indexOf( '.' + strFileExtention ) !== -1 ) {
						blAcceptedType = true;
					}
					
					for( let extention of this.settings.acceptExtensions ) {

						if( strFileExtention === extention ) {
							blAcceptedType = true;
							break;
						}
					}
				}

				if( blAcceptedType ) {
					this.settings.onstart( resFile );
					this.showProgress();

					if( this.elements.Count ) {
						this.elements.Count.innerText = this.queueUploadedCount + 1;
					}

					if( this.queueCount === 1 ) {
						if( this.elements.Progress ) {
							this.elements.Progress.removeAttribute( 'value' );
						}

						new Element( this.elements.CountWrapper ).hide();
					} else if( this.elements.CountWrapper ) {
						new Element( this.elements.CountWrapper ).show();
					}

					resFileReader.addEventListener( 'load',
							e => {
								this.request.onreadystatechange = () => {
									switch( this.request.status ) {
										case 200:
											if( this.request.readyState === 4 ) {
												//console.info( 'Uploaded ' + strFileName + ' (' + twistfileupload.prettySize( intFileSize ) + ')' );

												this.queue.shift();
												this.queueUploadedCount++;
												this.queueUploadedSize += intFileSize;

												let jsonResponse = JSON.parse( this.request.responseText );

												if( this.queue.length ) {
													if( this.settings.multiple ) {
														this.uploaded.push( jsonResponse );
													} else {
														this.uploaded = [jsonResponse.form_value];
													}

													this.updateUploadedList();

													if( window.twist.debug ) {
														window.twist.debug.logFileUpload( resFile, jsonResponse );
													}

													this.settings.oncompletefile( jsonResponse, resFile );
													this.upload();
												} else {
													this.hideProgress();

													//console.info( 'Finished uploading ' + this.queueUploadedCount + ' files (' + twistfileupload.prettySize( this.queueUploadedSize ) + ')', 'info' );

													this.queueCount = 0;
													this.queueSize = 0;
													this.queueUploadedCount = 0;
													this.queueUploadedSize = 0;

													this.clearInput();

													if( this.settings.multiple ) {
														this.uploaded.push( jsonResponse );
													} else {
														this.uploaded = [jsonResponse];
													}

													this.updateUploadedList();

													if( window.twist.debug ) {
														window.twist.debug.logFileUpload( resFile, jsonResponse );
													}

													this.settings.oncompletefile( jsonResponse, resFile );
													this.settings.oncompletequeue();
												}
											}
											break;

										case 403:
											console.error( 'Permission denied' );

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
											console.error( 'Invalid function call' );

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
										if( this.elements.Progress ) {
											let intPercentage = Math.round( ( e.loaded / e.total ) * 100 );
											this.elements.Progress.value = intPercentage;

											//console.log( twistfileupload.prettySize( e.loaded ) + '/' + twistfileupload.prettySize( e.total ) + ' (' + intPercentage + '%)' );
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

										console.error( 'An error occurred' );
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

										console.warn( 'Upload aborted' );
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
					this.elements.Input.value = '';

					this.settings.oninvalidtype( objInvalidFile, this.settings.acceptTypes, this.settings.acceptExtensions );

					console.error( strFileName + ' (' + strFileType + ') is not in the list of allowed types or extensions' );

					if( this.settings.acceptTypes.length ) {
						console.info( 'Allowed MIME types: ' + this.settings.acceptTypes.join( ', ' ) );
					}

					if( this.settings.acceptExtensions.length ) {
						console.info( 'Allowed file extensions: ' + this.settings.acceptExtensions.join( ', ' ) );
					}

					//alert( this.settings.invalidtypemessage ); //TODO: Handle this without using alert()

					this.clearInput();
				}
			}
		} catch( err ) {
			this.hideProgress();

			this.settings.onerror( this.queue[0] );
			this.settings.onabort( this.queue[0] );

			this.queue = [];
			this.queueCount = 0;
			this.queueSize = 0;
			this.queueUploadedCount = 0;
			this.queueUploadedSize = 0;

			console.error( err );
		}
	}

	showProgress() {
		new Element( this.elements.Input ).hide();
		new Element( this.elements.ProgressWrapper ).show();

		if( this.elements.CancelUpload ) {
			this.elements.CancelUpload.addEventListener( 'click', () => { this.cancelUpload() } );
		}
	}

	hideProgress() {
		if( !this.settings.hideInput ) {
			new Element( this.elements.Input ).show();
		}
		new Element( this.elements.ProgressWrapper ).hide();

		if( this.elements.CancelUpload ) {
			this.elements.CancelUpload.removeEventListener( 'click', () => { this.cancelUpload() } );
		}
	}

	clearInput() {
		this.elements.Input.value = '';

		if( this.elements.Input.value ) {
			this.elements.Input.type = 'text';
			this.elements.Input.type = 'file';
		}

		this.elements.Pseudo.value = '';
		this.settings.onclear();
	}

	cancelUpload() {
		this.request.abort();
		this.clearInput();
	}

	updateUploadedList() {
		let arrUploadedFormValues = [];

		this.elements.List.innerHTML = '';

		//console.log( this.uploaded );

		for( let objUploadedFile of this.uploaded ) {
			let strFilePreview = objUploadedFile.uri_preview,
					strFileDetails = '',
					arrFileDetails = ['file/name', 'file/size', 'file_type'],
					strPreview = 'thumb-' + this.settings.previewsize;

			arrUploadedFormValues.push( objUploadedFile.form_value );

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

			let listItem = Element.create( 'li', 'twistupload-file-list-item' ),
					listItemPreview = Element.create( 'img', '', {src: strFilePreview} ),
					listItemInfo = Element.create( 'ul', 'twistupload-file-list-item-info', {}, strFileDetails ),
					listItemRemoveButton = Element.create( 'button', '', {}, 'Remove' );

			listItemRemoveButton.addEventListener( 'click', (fileToRemove => {
				return () => {
					this.uploaded.splice( this.uploaded.indexOf( fileToRemove ), 1 );
					this.updateUploadedList();
				}
			})( objUploadedFile ) );

			listItem.appendChild( listItemPreview );
			listItem.appendChild( listItemInfo );
			listItem.appendChild( listItemRemoveButton );

			this.elements.List.appendChild( listItem );
		}

		this.elements.Pseudo.value = JSON.stringify( arrUploadedFormValues );
	}

	on( event, action, context = null ) {
		//TODO
	}

	off( event, action ) {
		//TODO
	}

	trigger( event ) {
		//TODO
	}
}
