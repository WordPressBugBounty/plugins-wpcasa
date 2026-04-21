/**
 * Load Google Maps and dependent map scripts in sequence.
 */
( function( window, document ) {
	'use strict';

	var config = window.wpsightMapLoader || {};

	window.wpsightGoogleMapsLoadedScripts = window.wpsightGoogleMapsLoadedScripts || {};
	window.wpsightGoogleMapsLoaderState = window.wpsightGoogleMapsLoaderState || 'idle';

	/**
	 * Load a script and invoke the callback when ready.
	 *
	 * @param {string}   url      Script URL.
	 * @param {Function} callback Callback after the script has loaded.
	 * @param {boolean}  async    Whether to set the async attribute.
	 */
	function loadScript( url, callback, async ) {
		var script;

		if ( ! url ) {
			if ( callback ) {
				callback();
			}

			return;
		}

		if ( window.wpsightGoogleMapsLoadedScripts[ url ] ) {
			if ( callback ) {
				callback();
			}

			return;
		}

		script = document.createElement( 'script' );
		script.src = url;
		script.async = !! async;

		script.onload = function() {
			window.wpsightGoogleMapsLoadedScripts[ url ] = true;

			if ( callback ) {
				callback();
			}
		};

		document.head.appendChild( script );
	}

	/**
	 * Load dependent scripts one after another.
	 *
	 * @param {number} index Current script index.
	 */
	function loadDependencies( index ) {
		var scripts = config.scripts || [];

		if ( index >= scripts.length ) {
			window.wpsightGoogleMapsLoaderState = 'loaded';
			return;
		}

		loadScript(
			scripts[ index ],
			function() {
				loadDependencies( index + 1 );
			},
			false
		);
	}

	/**
	 * Start loading the map dependencies once Google Maps is available.
	 */
	function startDependencies() {
		if ( 'dependencies-loading' === window.wpsightGoogleMapsLoaderState || 'loaded' === window.wpsightGoogleMapsLoaderState ) {
			return;
		}

		window.wpsightGoogleMapsLoaderState = 'dependencies-loading';
		loadDependencies( 0 );
	}

	if ( 'function' === typeof window.wpsightOnGoogleMapsReady ) {
		window.wpsightOnGoogleMapsReady( startDependencies );
	}
}( window, document ) );
