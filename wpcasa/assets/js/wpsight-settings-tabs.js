/**
 * Handle the optional second-level navigation on the WPCasa settings page.
 *
 * Every subtab container is initialized independently. Desktop tab buttons
 * and the mobile select share the same activation path, while the selected
 * tab is stored per settings section.
 *
 * @package WPCasa
 * @since 1.5.4
 */

jQuery( function( $ ) {
	'use strict';

	$( '.wpsight-settings-subtabs' ).each( function() {
		var $subtabs = $( this );
		var $tabs = $subtabs.find( '[data-wpsight-settings-subtab]' );
		var $panels = $subtabs.find( '[data-wpsight-settings-subpanel]' );
		var $mobileSelect = $subtabs.find( '[data-wpsight-settings-subtab-select]' );
		var sectionId = $subtabs.data( 'wpsight-settings-section' );
		var storageKey = 'wpsight_settings_subtab_' + sectionId;
		var activeTab = '';

		/**
		 * Activate a settings tab and its associated panel.
		 *
		 * @since 1.5.4
		 *
		 * @param {jQuery} $tab        Tab button to activate.
		 * @param {boolean} shouldStore Whether the active tab should be stored.
		 * @return {void}
		 */
		var activateTab = function( $tab, shouldStore ) {
			var tabId = $tab.attr( 'data-wpsight-settings-subtab' );
			var $panel = $panels.filter( function() {
				return $( this ).attr( 'data-wpsight-settings-subpanel' ) === tabId;
			} );

			if ( ! $tab.length || ! $panel.length ) {
				return;
			}

			$tabs
				.removeClass( 'nav-tab-active' )
				.attr( {
					'aria-selected': 'false',
					tabindex: '-1'
				} );

			$panels
				.removeClass( 'is-active' )
				.attr( 'hidden', 'hidden' );

			$tab
				.addClass( 'nav-tab-active' )
				.attr( {
					'aria-selected': 'true',
					tabindex: '0'
				} );

			$panel
				.addClass( 'is-active' )
				.removeAttr( 'hidden' );

			$mobileSelect.val( tabId );

			if ( shouldStore && typeof localStorage !== 'undefined' ) {
				try {
					localStorage.setItem( storageKey, tabId );
				} catch ( error ) {
					// Keep the tabs usable when browser storage is unavailable.
				}
			}
		};

		// Restore only values that still match a currently registered tab.
		if ( typeof localStorage !== 'undefined' ) {
			try {
				activeTab = localStorage.getItem( storageKey );
			} catch ( error ) {
				activeTab = '';
			}
		}

		var $activeTab = $tabs.filter( function() {
			return $( this ).attr( 'data-wpsight-settings-subtab' ) === activeTab;
		} ).first();

		if ( ! $activeTab.length ) {
			$activeTab = $tabs.first();
		}

		activateTab( $activeTab, false );

		// Keep desktop buttons and the mobile select synchronized.
		$tabs.on( 'click', function() {
			activateTab( $( this ), true );
		} );

		$mobileSelect.on( 'change', function() {
			var selectedTabId = $( this ).val();
			var $selectedTab = $tabs.filter( function() {
				return $( this ).attr( 'data-wpsight-settings-subtab' ) === selectedTabId;
			} ).first();

			activateTab( $selectedTab, true );
		} );

		// Implement the keyboard behavior required by the ARIA tabs pattern.
		$tabs.on( 'keydown', function( event ) {
			var currentIndex = $tabs.index( this );
			var targetIndex = currentIndex;

			if ( 'ArrowLeft' === event.key || 'ArrowUp' === event.key ) {
				targetIndex = 0 === currentIndex ? $tabs.length - 1 : currentIndex - 1;
			} else if ( 'ArrowRight' === event.key || 'ArrowDown' === event.key ) {
				targetIndex = currentIndex === $tabs.length - 1 ? 0 : currentIndex + 1;
			} else if ( 'Home' === event.key ) {
				targetIndex = 0;
			} else if ( 'End' === event.key ) {
				targetIndex = $tabs.length - 1;
			} else {
				return;
			}

			event.preventDefault();

			var $targetTab = $tabs.eq( targetIndex );

			activateTab( $targetTab, true );
			$targetTab.trigger( 'focus' );
		} );
	} );
} );
