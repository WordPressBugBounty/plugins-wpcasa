<?php
/**
 * Render a table of WPCasa settings fields.
 *
 * This partial is required from the WPSight_Admin_Settings rendering context.
 * It expects `$settings_options` to contain one normalized field collection
 * and delegates each supported field type to its existing `option-*.php`
 * partial. Invalid field definitions are skipped instead of breaking the
 * complete settings page.
 *
 * @package WPCasa
 * @since 1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings_options = isset( $settings_options ) && is_array( $settings_options )
	? $settings_options
	: array();
?>

<table class="form-table">
	<?php
	foreach ( $settings_options as $option ) {
		// A stable field ID is required for selectors, labels and option views.
		$has_valid_id = is_array( $option )
			&& isset( $option['id'] )
			&& is_scalar( $option['id'] )
			&& '' !== (string) $option['id'];

		if ( ! $has_valid_id ) {
			continue;
		}

		$option_css     = sanitize_html_class( $this->settings_name . '_' . $option['id'] );
		$option_name    = isset( $option['name'] ) && is_scalar( $option['name'] )
			? stripslashes( (string) $option['name'] )
			: '';
		$option_desc    = isset( $option['desc'] ) && is_scalar( $option['desc'] )
			? stripslashes( (string) $option['desc'] )
			: '';
		$option_type    = isset( $option['type'] ) && is_scalar( $option['type'] )
			? sanitize_key( $option['type'] )
			: '';
		$option_classes = array();
		$class          = '';
		$compare_class  = '';

		// Sanitize every custom class separately before using it in the row.
		if ( isset( $option['class'] ) && is_scalar( $option['class'] ) ) {
			foreach ( preg_split( '/\s+/', (string) $option['class'] ) as $option_class ) {
				$option_class = sanitize_html_class( $option_class );

				if ( '' !== $option_class ) {
					$option_classes[] = $option_class;
				}
			}

			if ( ! empty( $option_classes ) ) {
				$class = ' ' . implode( ' ', $option_classes );
			}
		}

		// Preserve the existing show_if contract for checkbox dependencies.
		if ( isset( $option['show_if'] ) && is_array( $option['show_if'] ) ) {
			$dependable_option_id = isset( $option['show_if']['id'] )
				? sanitize_key( $option['show_if']['id'] )
				: '';
			$comparable_value     = isset( $option['show_if']['value'] )
				? $option['show_if']['value']
				: null;
			$comparison_operator  = isset( $option['show_if']['compare'] )
				? $option['show_if']['compare']
				: '==';

			if ( '' !== $dependable_option_id && '==' === $comparison_operator ) {
				$dependable_option_value = wpsight_get_option( $dependable_option_id );

				// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Stored settings can be strings or integers.
				$compare_result = $comparable_value == $dependable_option_value;

				$compare_class = $compare_result ? '' : ' hidden';

				$dependable_selector = '#setting-' . sanitize_html_class( $this->settings_name . '_' . $dependable_option_id );

				$option_selector = '#setting-' . $option_css;
				?>

				<script>
					jQuery( document ).ready( function( $ ) {
						var dependableOptionId = <?php echo wp_json_encode( $dependable_selector ); ?>;
						var optionId = <?php echo wp_json_encode( $option_selector ); ?>;

						$( document ).on( 'click', dependableOptionId, function() {
							$( optionId ).closest( 'tr' ).toggleClass( 'hidden', ! $( dependableOptionId ).is( ':checked' ) );
						} );
					} );
				</script>

				<?php
			}
		}

		$option_view = WPSIGHT_PLUGIN_DIR . '/includes/admin/views/option-' . $option_type . '.php';

		// Unknown field types must not be able to include arbitrary files.
		if ( ! file_exists( $option_view ) ) {
			continue;
		}
		?>

		<tr valign="top" class="setting-<?php echo esc_attr( $option_css ); ?>-tr<?php echo esc_attr( $class . $compare_class ); ?>">
			<?php
			if ( in_array( $option_type, array( 'pageheading', 'heading' ), true ) ) {
				require $option_view;
			} else {
				?>

				<th scope="row">
					<label for="setting-<?php echo esc_attr( $option_css ); ?>"><?php echo esc_html( $option_name ); ?></label>
					<p class="description"><?php echo wp_kses( $option_desc, wp_kses_allowed_html( 'post' ) ); ?></p>
				</th>
				<td>
					<div class="wpsight-settings-field-wrap wpsight-settings-field-<?php echo esc_attr( $option_type ); ?>-wrap">
						<?php require $option_view; ?>
					</div>
				</td>

				<?php
			}
			?>
		</tr>

		<?php
	}
	?>
</table>
