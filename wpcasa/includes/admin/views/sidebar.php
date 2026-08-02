<?php
/**
 * Render the WPCasa settings navigation.
 *
 * The sidebar is permanently visible on desktop and becomes an accessible
 * overlay drawer at the WordPress administration mobile breakpoint.
 *
 * @package WPCasa
 * @updated 1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = isset( $settings ) && is_array( $settings ) ? $settings : array();
?>

<div class="wpsight-admin-sidebar-back" aria-hidden="true"></div>

<aside id="wpsight-admin-sidebar" class="wpsight-admin-sidebar">
	<button
		type="button"
		class="wpsight-admin-sidebar-close"
		aria-label="<?php echo esc_attr__( 'Close settings navigation', 'wpcasa' ); ?>"
	>
		<span aria-hidden="true">&times;</span>
	</button>

	<div class="wpsight-admin-intro-box">
		<div class="wpsight-admin-ui-image">
			<img
				src="<?php echo esc_url( WPSIGHT_PLUGIN_URL . '/assets/img/wpcasa-admin-logo.jpg' ); ?>"
				alt="<?php echo esc_attr__( 'WPCasa', 'wpcasa' ); ?>"
			/>
		</div>
	</div>

	<nav
		class="wpsight-admin-nav nav-tab-wrapper"
		aria-label="<?php echo esc_attr__( 'WPCasa settings', 'wpcasa' ); ?>"
	>
		<a href="#settings-overview" id="settings-overview-tab" class="nav-tab">
			<span class="dashicons dashicons-laptop" aria-hidden="true"></span>
			<?php echo esc_html__( 'Overview', 'wpcasa' ); ?>
		</a>

		<?php
		foreach ( $settings as $key => $section ) {
			if ( ! is_array( $section ) || ! isset( $section[0] ) || ! is_scalar( $section[0] ) ) {
				continue;
			}

			$section_id    = sanitize_title( $key );
			$section_label = (string) $section[0];
			?>

			<a
				href="#settings-<?php echo esc_attr( $section_id ); ?>"
				id="settings-<?php echo esc_attr( $section_id ); ?>-tab"
				class="nav-tab"
			>
				<?php
				echo wp_kses(
					$section_label,
					array(
						'span' => array(
							'class' => array(),
						),
					)
				);
				?>
			</a>

			<?php
		}
		?>

		<a href="#settings-tools" id="settings-tools-tab" class="nav-tab">
			<span class="dashicons dashicons-admin-tools" aria-hidden="true"></span>
			<?php echo esc_html__( 'Tools', 'wpcasa' ); ?>
		</a>
	</nav>
</aside>
