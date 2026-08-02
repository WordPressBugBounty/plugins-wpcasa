<?php
/**
 * Render the WPCasa settings page.
 *
 * @package WPCasa
 * @updated 1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display the confirmation returned by the WordPress Settings API.
if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	echo '<div class="fade notice notice-success"><p>' . esc_html__( 'Settings saved.', 'wpcasa' ) . '</p></div>';
}
?>

<div class="wrap wpsight-settings-wrap">
	<?php require WPSIGHT_PLUGIN_DIR . '/includes/admin/views/sidebar.php'; ?>

	<div class="wpsight-admin-main">
		<div class="wpsight-admin-ui-panel wpsight-admin-main-wrap-btn-toggle">
			<button
				type="button"
				class="wpsight-settings-sidebar-toggle"
				aria-controls="wpsight-admin-sidebar"
				aria-expanded="false"
				aria-label="<?php echo esc_attr__( 'Open settings navigation', 'wpcasa' ); ?>"
				data-wpsight-open-label="<?php echo esc_attr__( 'Open settings navigation', 'wpcasa' ); ?>"
				data-wpsight-close-label="<?php echo esc_attr__( 'Close settings navigation', 'wpcasa' ); ?>"
			>
				<span class="wpsight-settings-sidebar-toggle-line" aria-hidden="true"></span>
				<span class="wpsight-settings-sidebar-toggle-line" aria-hidden="true"></span>
				<span class="wpsight-settings-sidebar-toggle-line" aria-hidden="true"></span>
			</button>
		</div>

		<?php require WPSIGHT_PLUGIN_DIR . '/includes/admin/views/panels.php'; ?>
		<?php require WPSIGHT_PLUGIN_DIR . '/includes/admin/views/settings-form.php'; ?>
		<?php require WPSIGHT_PLUGIN_DIR . '/includes/admin/views/page-tools.php'; ?>
	</div>
</div>
