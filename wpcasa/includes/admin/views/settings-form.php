<?php
/**
 * Render the WPCasa settings form.
 *
 * A section keeps the existing two-element structure for its label and
 * fields. Add-ons can opt into a second navigation level by registering a
 * `tabs` map in the optional third element and assigning fields with `tab`.
 *
 * @package WPCasa
 * @updated 1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings       = isset( $settings ) && is_array( $settings ) ? $settings : array();
$settings_group = isset( $settings_group ) ? $settings_group : '';
?>

<form method="post" action="options.php">

	<?php settings_fields( $settings_group ); ?>

	<?php
	foreach ( $settings as $key => $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}

		// Normalize the optional tab metadata while preserving legacy sections.
		$section_id          = sanitize_title( $key );
		$section_tabs        = $this->get_section_tabs( $section );
		$section_tab_options = $this->get_section_tab_options( $section, $section_tabs );
		?>

		<div id="settings-<?php echo esc_attr( $section_id ); ?>" class="settings_panel">
			<div class="wpsight-admin-ui-container">
				<div class="wpsight-admin-ui-grid">
					<div class="wpsight-admin-ui-grid-col wpsight-admin-ui-grid-1-1">
						<div class="wpsight-admin-ui-panel wpsight-admin-ui-panel-large">

							<?php
							if ( empty( $section_tabs ) ) {
								// Render legacy sections through the shared field view.
								$settings_options = isset( $section[1] ) && is_array( $section[1] )
									? $section[1]
									: array();

								require WPSIGHT_PLUGIN_DIR . '/includes/admin/views/settings-fields.php';
							} else {
								if ( ! empty( $section_tab_options['common'] ) ) {
									$settings_options = $section_tab_options['common'];

									require WPSIGHT_PLUGIN_DIR . '/includes/admin/views/settings-fields.php';
								}
								?>

								<div
									class="wpsight-settings-subtabs"
									data-wpsight-settings-section="<?php echo esc_attr( $section_id ); ?>"
								>
									<?php $tab_select_id = 'settings-' . $section_id . '-subtab-select'; ?>

									<?php // The mobile select mirrors the desktop tab list and is synchronized in JavaScript. ?>
									<div class="wpsight-settings-subtab-select-wrap">
										<label
											class="wpsight-settings-subtab-select-label"
											for="<?php echo esc_attr( $tab_select_id ); ?>"
										>
											<?php echo esc_html__( 'Select section', 'wpcasa' ); ?>
										</label>

										<select
											id="<?php echo esc_attr( $tab_select_id ); ?>"
											class="wpsight-settings-subtab-select"
											data-wpsight-settings-subtab-select
										>
											<?php
											$is_first_option = true;

											foreach ( $section_tabs as $tab_id => $tab_label ) {
												?>

												<option
													value="<?php echo esc_attr( $tab_id ); ?>"
													<?php selected( $is_first_option ); ?>
												>
													<?php echo esc_html( $tab_label ); ?>
												</option>

												<?php
												$is_first_option = false;
											}
											?>
										</select>
									</div>

									<?php // The desktop control follows the ARIA tabs pattern. ?>
									<div
										class="wpsight-settings-subtab-nav"
										role="tablist"
										aria-label="<?php echo esc_attr__( 'Settings sections', 'wpcasa' ); ?>"
									>
										<?php
										$is_first_tab = true;

										foreach ( $section_tabs as $tab_id => $tab_label ) {
											$tab_button_id = 'settings-' . $section_id . '-subtab-' . $tab_id;
											$tab_panel_id  = 'settings-' . $section_id . '-subpanel-' . $tab_id;
											$tab_class     = $is_first_tab ? 'nav-tab nav-tab-active' : 'nav-tab';
											?>

											<button
												type="button"
												id="<?php echo esc_attr( $tab_button_id ); ?>"
												class="<?php echo esc_attr( $tab_class ); ?>"
												role="tab"
												aria-controls="<?php echo esc_attr( $tab_panel_id ); ?>"
												aria-selected="<?php echo esc_attr( $is_first_tab ? 'true' : 'false' ); ?>"
												tabindex="<?php echo esc_attr( $is_first_tab ? '0' : '-1' ); ?>"
												data-wpsight-settings-subtab="<?php echo esc_attr( $tab_id ); ?>"
											>
												<?php echo esc_html( $tab_label ); ?>
											</button>

											<?php
											$is_first_tab = false;
										}
										?>
									</div>

									<?php
									$is_first_tab = true;

									// Render every panel so hooks can populate tabs without registered fields.
									foreach ( array_keys( $section_tabs ) as $tab_id ) {
										$tab_button_id    = 'settings-' . $section_id . '-subtab-' . $tab_id;
										$tab_panel_id     = 'settings-' . $section_id . '-subpanel-' . $tab_id;
										$tab_panel_class  = $is_first_tab ? 'wpsight-settings-subpanel is-active' : 'wpsight-settings-subpanel';
										$settings_options = $section_tab_options['tabs'][ $tab_id ];
										?>

										<div
											id="<?php echo esc_attr( $tab_panel_id ); ?>"
											class="<?php echo esc_attr( $tab_panel_class ); ?>"
											role="tabpanel"
											aria-labelledby="<?php echo esc_attr( $tab_button_id ); ?>"
											data-wpsight-settings-subpanel="<?php echo esc_attr( $tab_id ); ?>"
										>
											<?php
											/**
											 * Fires before a WPCasa settings subtab is rendered.
											 *
											 * @since 1.5.4
											 *
											 * @param string $section_id Sanitized settings section ID.
											 * @param string $tab_id     Sanitized settings tab ID.
											 * @param array  $section    Complete settings section data.
											 */
											do_action( 'wpsight_settings_subtab_before', $section_id, $tab_id, $section );

											if ( ! empty( $settings_options ) ) {
												require WPSIGHT_PLUGIN_DIR . '/includes/admin/views/settings-fields.php';
											}

											/**
											 * Fires after a WPCasa settings subtab is rendered.
											 *
											 * @since 1.5.4
											 *
											 * @param string $section_id Sanitized settings section ID.
											 * @param string $tab_id     Sanitized settings tab ID.
											 * @param array  $section    Complete settings section data.
											 */
											do_action( 'wpsight_settings_subtab_after', $section_id, $tab_id, $section );
											?>
										</div>

										<?php
										$is_first_tab = false;
									}
									?>
								</div>

								<?php
							}
							?>

						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
	}
	?>

</form>
