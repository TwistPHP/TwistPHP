( window => {
	if( !window.twist ) {
		window.twist = {
			catches: []
		};
	} else {
		window.twist.catches = [];
	}

	let storeMessage = ( type, message, strURL, intLineNumber, intColumn ) => {
		window.twist.catches.push( {
			type: type,
			details: [message, strURL, intLineNumber, intColumn]
		} );
		return false;
	};

	window.onerror = ( strErrorMessage, strURL, intLineNumber, intColumn ) => {
		return storeMessage( 'error', strErrorMessage, strURL, intLineNumber, intColumn );
	};

	window.onwarn = ( strErrorMessage, strURL, intLineNumber, intColumn ) => {
		return storeMessage( 'warning', strErrorMessage, strURL, intLineNumber, intColumn );
	};
} )( window );
