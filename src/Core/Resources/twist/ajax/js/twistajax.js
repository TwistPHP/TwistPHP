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

import serialize from '../../../../../../node_modules/form-serialize/index';

/*let prettySize = function( intBytes, blUseSpace ) {
			let arrLimits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
					intLimit = 0;

			while( arrLimits[intLimit] && intBytes > Math.pow( 1024, intLimit + 1 ) ) {
				intLimit++;
			}

			return round( intBytes / Math.pow( 1024, intLimit ), 2 ) + ( typeof blUseSpace === 'boolean' && blUseSpace ? ' ' : '' ) + arrLimits[intLimit];
		},
		round = function( intNumber, intDP ) {
			intDP = ( typeof intDP !== 'number' ) ? 0 : intDP;
			return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
		};*/

export default class twistajax {
	constructor( uri = '' ) {
		this.uri = uri.replace( /\/$/, '' );
		this.cache = false;
		this.requests = 0;
		this.debug = false;
		this.events = {};
	}

	set debug( debug ) {
		if( debug ) {
			try {
				let args = [
					'%c %c %c TwistPHP AJAX %c %c ',
					'font-size: 15px; background: #2a5200;',
					'font-size: 17px; background: #3f7a00;',
					'color: #FFF; font-size: 18px; background: #539F00;',
					'font-size: 17px; background: #3f7a00;',
					'font-size: 15px; background: #2a5200;'
				];

				console.log.apply( console, args );
			} catch( e ) {
				if( console.info ) {
					console.info( 'TwistPHP AJAX' );
				} else {
					console.log( 'TwistPHP AJAX' );
				}
			}

			let instanceData = {
				uri: this.uri,
				timeout: this.timeout,
				cache: this.cache,
				requests: this.requests
			};

			console.log( instanceData );
		}
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
	}

	trigger( event ) {
		for( let callbackEvent of this.events[event] ) {
			callbackEvent.call( this );
		}
	}

	send( location, bodydata = {}, method = 'GET' ) {

		return new Promise( ( resolve, reject ) => {
			this.requests++;
			this.trigger( 'request' );

			let fetchOptions = {
				method: method,
				headers: {
					Accept: 'application/json, text/plain, */*',
					'Content-Type': 'application/json; charset=utf-8'
				},
				cache: this.cache ? 'default' : 'no-store'
			};

			if( method !== 'GET' ) {
				fetchOptions.body = JSON.stringify( bodydata );
			}

			fetch( this.uri + '/' + location, fetchOptions )
					.then( response => response.json() )
					.then( response => {
						this.requests--;
						this.trigger( 'response' );

						if( response.status !== true ) {
							throw( response.message || 'Status returned FALSE' );
						}

						return response.data;
					} )
					.then( response => resolve( response ) )
					.catch( e => {
						reject( e );
					} );
		} );
	}
}