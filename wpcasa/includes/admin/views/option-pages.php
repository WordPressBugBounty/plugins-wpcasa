<?php
/**
 * Render a page-selection settings field.
 *
 * @package WPCasa
 * @updated 1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $option ) || ! is_array( $option ) || empty( $option['id'] ) ) {
	return;
}

$option_type = isset( $option['type'] ) && is_scalar( $option['type'] )
	? sanitize_html_class( (string) $option['type'] )
	: 'pages';
$option_key  = is_scalar( $option['id'] ) ? (string) $option['id'] : '';

if ( '' === $option_key ) {
	return;
}

$option_id  = $this->settings_name . '[' . $option_key . ']';
$option_css = sanitize_html_class( $this->settings_name . '_' . $option_key );
$attributes = array();

if ( isset( $option['attributes'] ) && is_array( $option['attributes'] ) ) {
	foreach ( $option['attributes'] as $attribute_name => $attribute_value ) {
		if ( ! is_string( $attribute_name ) || ! is_scalar( $attribute_value ) ) {
			continue;
		}

		// Only valid HTML attribute names may be rendered dynamically.
		if ( 1 !== preg_match( '/^[a-zA-Z_:][a-zA-Z0-9:._-]*$/', $attribute_name ) ) {
			continue;
		}

		$attributes[ $attribute_name ] = (string) $attribute_value;
	}
}

$value = wpsight_get_option( $option_key );

if ( null === $value && isset( $option['default'] ) ) {
	$value = $option['default'];
}

$pages = get_pages(
	array(
		'sort_order'   => 'asc',
		'sort_column'  => 'post_title',
		'hierarchical' => 0,
	)
);
?>

<div class="wpsight-settings-field wpsight-settings-field-<?php echo esc_attr( $option_type ); ?>">
	<select
		id="setting-<?php echo esc_attr( $option_css ); ?>"
		class="regular-text"
		name="<?php echo esc_attr( $option_id ); ?>"
		<?php foreach ( $attributes as $attribute_name => $attribute_value ) : ?>
			<?php echo esc_attr( $attribute_name ); ?>="<?php echo esc_attr( $attribute_value ); ?>"
		<?php endforeach; ?>
	>
		<option value=""><?php echo esc_html_x( 'Select page', 'plugin settings', 'wpcasa' ); ?>&hellip;</option>
		<?php foreach ( $pages as $page ) : ?>
			<?php
			$page_id = absint( $page->ID );

			if ( 0 === $page_id ) {
				continue;
			}

			$page_label = sprintf(
				/* translators: 1: page title, 2: page slug. */
				_x( '%1$s (%2$s)', 'page title and slug in plugin settings', 'wpcasa' ),
				$page->post_title,
				$page->post_name
			);
			?>
			<option
				value="<?php echo esc_attr( $page_id ); ?>"
				<?php selected( $value, $page_id ); ?>
			>
				<?php echo esc_html( $page_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
