<?php
/**
 * Uninstall WPCasa.
 *
 * @package WPCasa
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete the review notice dismiss state for all users.
delete_metadata( 'user', 0, 'wpsight_review_notice_dismissed', '', true );
