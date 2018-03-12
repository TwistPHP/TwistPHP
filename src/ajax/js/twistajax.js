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

import serialize from '../../../node_modules/form-serialize/index';

export default class twistajax {
	constructor( uri = '' ) {
		this.uri = uri.replace( /\/$/, '' );
		this.cache = false;
		this.requests = [];
		this.debug = false;
		this.events = {};

		if( !window.twist ) {
			window.twist = {ajax: {instances: []}};
		} else if( !window.twist.ajax ) {
			window.twist.ajax = {instances: []};
		}

		this.on( 'request', request => {
			if( window.twist.debug ) {
				window.twist.debug.logAJAX( request );
			} else if( this.debug ) {
				console.info( 'New AJAX Request', request );
			}
		} )
				.on( 'response', request => {
					if( window.twist.debug &&
							request.$debug ) {
						request.$debug
								.querySelector( '.details' )
								.outerHTML = '<pre>' + JSON.stringify( {response: request.response}, undefined, 2 ) + '</pre>';
					} else if( this.debug ) {
						//TODO: DEBUG OLD SKOOL
					}
				} )
				.on( 'success', request => {
					if( window.twist.debug &&
							request.$debug ) {
						if( request.$debug.getAttribute( 'class' ) === 'twist-debug-box-' ) {
							request.$debug.classList.remove( 'twist-debug-box-' );
							request.$debug.classList.add( 'twist-debug-box-green' );
						}
					} else if( this.debug ) {
						//TODO: DEBUG OLD SKOOL
					}
				} )
				.on( 'fail', request => {
					if( window.twist.debug &&
							request.$debug ) {
						if( request.$debug.getAttribute( 'class' ) === 'twist-debug-box-' ) {
							request.$debug.classList.remove( 'twist-debug-box-' );
							request.$debug.classList.add( 'twist-debug-box-yellow' );
						}
					} else if( this.debug ) {
						//TODO: DEBUG OLD SKOOL
					}
				} )
				.on( 'error', request => {
					if( window.twist.debug &&
							request.$debug ) {
						if( request.$debug.getAttribute( 'class' ) === 'twist-debug-box-' ) {
							request.$debug.classList.remove( 'twist-debug-box-' );
							request.$debug.classList.add( 'twist-debug-box-red' );
							let domError = document.createElement( 'pre' );
							domError.innerHTML = 'Error: ' + request.error;
							let domDetails = request.$debug.querySelector( '.details' );
							domDetails.parentNode.replaceChild( domError, domDetails );
						}
					} else if( this.debug ) {
						//TODO: DEBUG OLD SKOOL
					}
				} );

		window.twist.ajax.instances.push( this );
	}

	delete( location, data = {} ) {
		return this.send( location, data, 'DELETE' );
	}

	get( location ) {
		return this.send( location, {}, 'GET' );
	}

	head( location ) {
		return this.send( location, {}, 'HEAD' );
	}

	patch( location, data = {} ) {
		return this.send( location, data, 'PATCH' );
	}

	post( location, data = {} ) {
		return this.send( location, data, 'POST' );
	}

	put( location, data = {} ) {
		return this.send( location, data, 'PUT' );
	}

	postForm( location, formSelector ) {
		let data = serialize( document.querySelector( formSelector ), {empty: true} );
		return this.post( location, data );
	}

	on( event, callback ) {
		if( !this.events[event] ) {
			this.events[event] = [];
		}

		this.events[event].push( callback );

		return this;
	}

	trigger( event, context ) {
		if( this.events[event] ) {
			for( let callbackEvent of this.events[event] ) {
				callbackEvent.call( this, context );
			}
		}

		return this;
	}

	send( location, bodydata = {}, method = 'GET' ) {
		let fetchOptions = {
			method: method,
			credentials: 'same-origin',
			headers: {
				Accept: 'application/json, text/plain, */*',
				'Content-Type': 'application/json; charset=utf-8',
				'X-Requested-With': 'XMLHttpRequest'
			},
			cache: this.cache ? 'default' : 'no-store'
		};

		if( method !== 'GET' ) {
			fetchOptions.body = JSON.stringify( bodydata );
		}

		let request = {
			url: this.uri + '/' + location,
			options: fetchOptions
		};

		request.instance = new Promise( ( resolve, reject ) => {
			fetch( this.uri + '/' + location, fetchOptions )
					.then( response => {
						if( response.ok ) {
							return response.text()
									.then( response => {
										try {
											return JSON.parse( response );
										} catch( e ) {
											let expectedFields = '("status" ?: ?(true|false)?|"message" ?: ?".*"|"data" ?: ?(\\{.*\\}|\\[.*\\]))',
													regex = new RegExp( '\{(' + expectedFields + ' ?, ?){2}' + expectedFields + '\}', 'g' ),
													matches = regex.exec( response );

											if( matches !== null ) {
												console.warn( 'Broken AJAX response parsed' );
												return JSON.parse( matches[0] );
											} else {
												throw response;
											}
										}
									} )
									.catch( e => {
										throw(e);
									} );
						} else {
							throw(response.status + ' ' + response.statusText);
						}
					} )
					.then( response => {
						request.response = response;

						this.trigger( 'response', request );

						if( response.status !== true ) {
							this.trigger( 'fail', request );
							throw(response.message || 'AJAX status returned FALSE');
						} else {
							this.trigger( 'success', request );
						}

						resolve( response.data );

						return response;
					} )
					.catch( e => {
						request.error = e;

						this.trigger( 'error', request );

						reject( e );
					} );
		} );

		this.requests.push( request );

		this.trigger( 'request', request );

		return request.instance;
	}
}
