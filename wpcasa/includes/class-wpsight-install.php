<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPSight_Install class
 */
class WPSight_Install {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->cron();
		delete_transient( 'wpsight_addons_html' );
		delete_transient( 'wpsight_themes_html' );
		update_option( 'wpsight_version', WPSIGHT_VERSION );
	}

	/**
	 * cron()
	 *
	 * Setup custom cron jobs
	 *
	 * @uses wp_clear_scheduled_hook()
	 * @uses wp_schedule_event()
	 *
	 * @since 1.0.0
	 */
	public function cron() {
		
		// Handle delete previews cron		
		wp_clear_scheduled_hook( 'wpsight_delete_listing_previews' );
		wp_schedule_event( time(), 'daily', 'wpsight_delete_listing_previews' );

	}

}

new WPSight_Install();
