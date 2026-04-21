/**
 * Queue Google Maps callbacks and load the API on demand.
 */
( function( window, document ) {
	'use strict';

	var config = window.wpsightGoogleMapsApiLoader || {};

	window.wpsightGoogleMapsReadyCallbacks = window.wpsightGoogleMapsReadyCallbacks || [];
	window.wpsightGoogleMapsApiState = window.wpsightGoogleMapsApiState || 'idle';
	window.wpsightGoogleMapsApiRequested = window.wpsightGoogleMapsApiRequested || false;

	/**
	 * Flush all queued Google Maps callbacks.
	 */
	function flushCallbacks() {
		var callbacks = window.wpsightGoogleMapsReadyCallbacks.slice();
		var index;

		window.wpsightGoogleMapsReadyCallbacks = [];

		for ( index = 0; index < callbacks.length; index++ ) {
			if ( 'function' === typeof callbacks[ index ] ) {
				callbacks[ index ]();
			}
		}
	}

	/**
	 * Request the Google Maps API if it has not been requested yet.
	 */
	function requestGoogleMaps() {
		var script;

		if ( window.google && window.google.maps && window.google.maps.Map ) {
			window.wpsightGoogleMapsApiState = 'ready';
			flushCallbacks();
			return;
		}

		if ( window.wpsightGoogleMapsApiRequested || ! config.googleApiUrl ) {
			return;
		}

		window.wpsightGoogleMapsApiRequested = true;
		window.wpsightGoogleMapsApiState = 'loading';

		script = document.createElement( 'script' );
		script.src = config.googleApiUrl;
		script.async = true;

		document.head.appendChild( script );
	}

	/**
	 * Queue a callback until Google Maps is ready.
	 *
	 * @param {Function} callback Function to run after Google Maps is available.
	 */
	window.wpsightOnGoogleMapsReady = function( callback ) {
		if ( 'function' !== typeof callback ) {
			return;
		}

		if ( window.google && window.google.maps && window.google.maps.Map ) {
			callback();
			return;
		}

		window.wpsightGoogleMapsReadyCallbacks.push( callback );
		requestGoogleMaps();
	};

	window.wpsightGoogleMapsApiReady = function() {
		window.wpsightGoogleMapsApiState = 'ready';
		flushCallbacks();
	};
}( window, document ) );
