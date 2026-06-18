<?php
/**
 * Translation notice for WordPress.org hosted plugins.
 *
 * @package WPCasa
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSight_Admin_Translation_Notice class.
 */
class WPSight_Admin_Translation_Notice {

	/**
	 * Notice arguments.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Ajax action for dismissing the notice.
	 *
	 * @var string
	 */
	protected $ajax_action = '';

	/**
	 * Translation URL for the current notice.
	 *
	 * @var string
	 */
	protected $current_translation_url = '';

	/**
	 * Cache schema version.
	 *
	 * @var string
	 */
	protected $cache_version = '2';

	/**
	 * Constructor.
	 *
	 * @param array $args Notice configuration.
	 */
	public function __construct( $args ) {

		$this->args        = $this->parse_args( $args );
		$this->ajax_action = $this->args['notice_id'] . '_dismiss';

		add_action( 'admin_notices', array( $this, 'output' ) );
		add_action( 'wp_ajax_' . $this->ajax_action, array( $this, 'dismiss' ) );

	}

	/**
	 * Parse notice arguments.
	 *
	 * @param array $args Notice configuration.
	 * @return array Parsed arguments.
	 */
	protected function parse_args( $args ) {

		$defaults = array(
			'textdomain'        => '',
			'project_slug'      => '',
			'plugin_name'       => '',
			'notice_id'         => 'wpsight_translation_notice',
			'capability'        => 'manage_options',
			'screens'           => array(),
			'minimum_percent'   => 90,
			'cache_lifetime'    => WEEK_IN_SECONDS,
			'translation_url'   => '',
			'api_url'           => '',
			'logo_url'          => '',
			'translation_name'  => 'Translating WordPress',
		);

		$args = wp_parse_args( $args, $defaults );

		$args['textdomain']      = sanitize_key( $args['textdomain'] );
		$args['project_slug']    = sanitize_key( $args['project_slug'] );
		$args['notice_id']       = sanitize_key( $args['notice_id'] );
		$args['minimum_percent'] = absint( $args['minimum_percent'] );
		$args['cache_lifetime']  = absint( $args['cache_lifetime'] );
		$args['screens']         = array_map( 'sanitize_key', (array) $args['screens'] );

		if ( empty( $args['project_slug'] ) ) {
			$args['project_slug'] = $args['textdomain'];
		}

		if ( empty( $args['plugin_name'] ) ) {
			$args['plugin_name'] = $args['project_slug'];
		}

		if ( empty( $args['translation_url'] ) && ! empty( $args['project_slug'] ) ) {
			$args['translation_url'] = 'https://translate.wordpress.org/projects/wp-plugins/' . $args['project_slug'] . '/';
		}

		if ( empty( $args['api_url'] ) && ! empty( $args['project_slug'] ) ) {
			$args['api_url'] = 'https://translate.wordpress.org/api/projects/wp-plugins/' . $args['project_slug'] . '/stable/';
		}

		return $args;

	}

	/**
	 * Output the translation notice.
	 *
	 * @uses self::get_notice_message()
	 * @uses wp_create_nonce()
	 * @uses wp_kses_post()
	 * @return void
	 */
	public function output() {

		$message = $this->get_notice_message();

		if ( empty( $message ) ) {
			return;
		}

		echo '<div id="' . esc_attr( $this->args['notice_id'] ) . '" class="notice notice-info is-dismissible wpsight-review-notice wpsight-translation-notice" data-action="' . esc_attr( $this->ajax_action ) . '" data-nonce="' . esc_attr( wp_create_nonce( $this->ajax_action ) ) . '">';
		echo '<div class="wpsight-review-notice__inner">';

		if ( ! empty( $this->args['logo_url'] ) ) {
			echo '<div class="wpsight-review-notice__media">';
			echo '<img src="' . esc_url( $this->args['logo_url'] ) . '" alt="' . esc_attr( $this->args['plugin_name'] ) . '" class="wpsight-review-notice__image" width="62" height="62" />';
			echo '</div>';
		}

		echo '<div class="wpsight-review-notice__content">';
		echo '<p class="wpsight-review-notice__text">';
		echo '<strong>';
		printf(
			/* translators: %s: plugin name. */
			esc_html__( 'Help translate %s', 'wpcasa' ),
			esc_html( $this->args['plugin_name'] )
		);
		echo '</strong><br>';
		echo wp_kses_post( $message );
		echo '</p>';
		echo '<p class="wpsight-review-notice__actions">';
		echo '<a href="' . esc_url( $this->get_current_translation_url() ) . '" target="_blank" rel="noopener noreferrer" class="wpsight-review-notice__button"><span class="wpsight-review-notice__button-icon wpsight-translation-notice__button-icon-translate dashicons dashicons-translation"></span>' . esc_html__( 'Translate now', 'wpcasa' ) . '</a>';
		echo '<button type="button" class="wpsight-review-notice__button wpsight-translation-notice__button-dismiss"><span class="wpsight-review-notice__button-icon wpsight-translation-notice__button-icon-dismiss">&times;</span>' . esc_html__( 'Do not show again', 'wpcasa' ) . '</button>';
		echo '</p>';
		echo '</div>';
		echo '</div>';
		echo '</div>';

		$this->print_dismiss_script();

	}

	/**
	 * Get the translated notice message.
	 *
	 * @return string Notice message.
	 */
	protected function get_notice_message() {

		if ( ! $this->should_show() ) {
			return '';
		}

		$locale  = $this->get_admin_locale();
		$details = $this->get_translation_details( $locale );

		if ( empty( $details['available'] ) ) {
			return '';
		}

		if ( ! empty( $details['exists'] ) && absint( $details['percent_translated'] ) >= $this->args['minimum_percent'] ) {
			return '';
		}

		$this->current_translation_url = $this->get_translation_url( $details );

		$translation_link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( $this->get_current_translation_url() ),
			esc_html( $this->get_translation_name() )
		);

		if ( ! empty( $details['exists'] ) ) {
			return sprintf(
				wp_kses(
					/* translators: 1: plugin name, 2: locale name, 3: translation percentage, 4: translation platform link. */
					__( '%1$s is currently translated into %2$s for %3$d%%. Help complete the translation on %4$s.', 'wpcasa' ),
					array(
						'a' => array(
							'href'   => array(),
							'rel'    => array(),
							'target' => array(),
						),
					)
				),
				esc_html( $this->args['plugin_name'] ),
				esc_html( $details['locale_name'] ),
				absint( $details['percent_translated'] ),
				$translation_link
			);
		}

		return sprintf(
			wp_kses(
				/* translators: 1: plugin name, 2: locale, 3: translation platform link. */
				__( '%1$s is not translated into your language yet. You can start the %2$s translation on %3$s.', 'wpcasa' ),
				array(
					'a' => array(
						'href'   => array(),
						'rel'    => array(),
						'target' => array(),
					),
				)
			),
			esc_html( $this->args['plugin_name'] ),
			esc_html( $locale ),
			$translation_link
		);

	}

	/**
	 * Check if the notice should be shown.
	 *
	 * @return bool True when the notice should be shown.
	 */
	protected function should_show() {

		if ( empty( $this->args['textdomain'] ) || empty( $this->args['project_slug'] ) ) {
			return false;
		}

		if ( ! current_user_can( $this->args['capability'] ) ) {
			return false;
		}

		if ( $this->is_default_locale( $this->get_admin_locale() ) ) {
			return false;
		}

		if ( get_user_meta( get_current_user_id(), $this->get_dismiss_meta_key(), true ) ) {
			return false;
		}

		return $this->is_allowed_screen();

	}

	/**
	 * Check if the current screen should show the notice.
	 *
	 * @return bool True when the screen is allowed.
	 */
	protected function is_allowed_screen() {

		if ( empty( $this->args['screens'] ) ) {
			return true;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		return in_array( $screen->id, $this->args['screens'], true );

	}

	/**
	 * Get the locale used in the admin.
	 *
	 * @return string Current admin locale.
	 */
	protected function get_admin_locale() {

		return get_user_locale();

	}

	/**
	 * Get the translation platform name.
	 *
	 * @return string Translation platform name.
	 */
	protected function get_translation_name() {

		if ( 'Translating WordPress' === $this->args['translation_name'] ) {
			return __( 'Translating WordPress', 'wpcasa' );
		}

		return $this->args['translation_name'];

	}

	/**
	 * Get the current translation URL.
	 *
	 * @return string Translation URL.
	 */
	protected function get_current_translation_url() {

		if ( empty( $this->current_translation_url ) ) {
			return $this->args['translation_url'];
		}

		return $this->current_translation_url;

	}

	/**
	 * Get the best translation URL for the current locale.
	 *
	 * @param array $details Translation details.
	 * @return string Translation URL.
	 */
	protected function get_translation_url( $details ) {

		if ( empty( $details['exists'] ) || empty( $details['locale_slug'] ) ) {
			return $this->args['translation_url'];
		}

		$set_slug = empty( $details['set_slug'] ) ? 'default' : $details['set_slug'];

		return trailingslashit( $this->args['translation_url'] ) . 'stable/' . rawurlencode( $details['locale_slug'] ) . '/' . rawurlencode( $set_slug ) . '/';

	}

	/**
	 * Check if the locale is the default plugin language.
	 *
	 * @param string $locale Locale to check.
	 * @return bool True for the default language.
	 */
	protected function is_default_locale( $locale ) {

		return 'en_US' === $locale;

	}

	/**
	 * Get cached or remote translation details for the locale.
	 *
	 * @param string $locale Locale to check.
	 * @return array Translation details.
	 */
	protected function get_translation_details( $locale ) {

		$cache_key = $this->get_cache_key( $locale );
		$details   = get_transient( $cache_key );

		if ( false !== $details && is_array( $details ) ) {
			if ( $this->has_valid_translation_details_cache( $details ) ) {
				return $details;
			}

			delete_transient( $cache_key );
		}

		$details = $this->retrieve_translation_details( $locale );

		$cache_lifetime = empty( $details['available'] ) ? HOUR_IN_SECONDS : $this->args['cache_lifetime'];

		set_transient( $cache_key, $details, $cache_lifetime );

		return $details;

	}

	/**
	 * Check if cached translation details use the current schema.
	 *
	 * @param array $details Translation details.
	 * @return bool True when cached details can be reused.
	 */
	protected function has_valid_translation_details_cache( $details ) {

		if ( empty( $details['exists'] ) ) {
			return true;
		}

		return isset( $details['locale_slug'], $details['set_slug'] );

	}

	/**
	 * Retrieve translation details from translate.wordpress.org.
	 *
	 * @param string $locale Locale to check.
	 * @return array Translation details.
	 */
	protected function retrieve_translation_details( $locale ) {

		$response = wp_remote_get(
			$this->args['api_url'],
			array(
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'available'          => false,
				'exists'             => false,
				'locale_name'        => '',
				'locale_slug'        => '',
				'set_slug'           => '',
				'percent_translated' => 0,
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $body->translation_sets ) || ! is_array( $body->translation_sets ) ) {
			return array(
				'available'          => true,
				'exists'             => false,
				'locale_name'        => '',
				'locale_slug'        => '',
				'set_slug'           => '',
				'percent_translated' => 0,
			);
		}

		foreach ( $body->translation_sets as $set ) {
			if ( ! $this->is_matching_translation_set( $set, $locale ) ) {
				continue;
			}

			return array(
				'available'          => true,
				'exists'             => true,
				'locale_name'        => isset( $set->name ) ? sanitize_text_field( $set->name ) : $locale,
				'locale_slug'        => $this->get_translation_set_locale_slug( $set, $locale ),
				'set_slug'           => $this->get_translation_set_slug( $set ),
				'percent_translated' => isset( $set->percent_translated ) ? absint( $set->percent_translated ) : 0,
			);
		}

		return array(
			'available'          => true,
			'exists'             => false,
			'locale_name'        => '',
			'locale_slug'        => '',
			'set_slug'           => '',
			'percent_translated' => 0,
		);

	}

	/**
	 * Get the URL locale slug for a translation set.
	 *
	 * @param object $set             Translation set.
	 * @param string $fallback_locale Fallback locale.
	 * @return string Locale slug.
	 */
	protected function get_translation_set_locale_slug( $set, $fallback_locale ) {

		if ( ! empty( $set->locale ) ) {
			$locale = $set->locale;
		} elseif ( ! empty( $set->wp_locale ) ) {
			$locale = $set->wp_locale;
		} else {
			$locale = $fallback_locale;
		}

		return str_replace( '_', '-', sanitize_key( $locale ) );

	}

	/**
	 * Get the URL set slug for a translation set.
	 *
	 * @param object $set Translation set.
	 * @return string Translation set slug.
	 */
	protected function get_translation_set_slug( $set ) {

		if ( empty( $set->slug ) ) {
			return 'default';
		}

		return sanitize_key( $set->slug );

	}

	/**
	 * Check if a GlotPress translation set matches the locale.
	 *
	 * @param object $set    Translation set.
	 * @param string $locale Locale to check.
	 * @return bool True when the set matches the locale.
	 */
	protected function is_matching_translation_set( $set, $locale ) {

		if ( ! is_object( $set ) || empty( $set->wp_locale ) ) {
			return false;
		}

		if ( $locale === $set->wp_locale ) {
			return true;
		}

		if ( empty( $set->slug ) || 'default' === $set->slug ) {
			return false;
		}

		return strtolower( $locale ) === strtolower( $set->wp_locale . '_' . $set->slug );

	}

	/**
	 * Dismiss the notice for the current user.
	 *
	 * @uses check_ajax_referer()
	 * @uses update_user_meta()
	 * @uses wp_send_json_success()
	 * @uses wp_send_json_error()
	 * @return void
	 */
	public function dismiss() {

		if ( ! current_user_can( $this->args['capability'] ) ) {
			wp_send_json_error();
		}

		check_ajax_referer( $this->ajax_action, 'nonce' );

		update_user_meta( get_current_user_id(), $this->get_dismiss_meta_key(), current_time( 'timestamp' ) );

		wp_send_json_success();

	}

	/**
	 * Print the dismiss script for the notice.
	 *
	 * @return void
	 */
	protected function print_dismiss_script() {

		?>
		<script type="text/javascript">
			jQuery( function( $ ) {
				var $notice = $( '#<?php echo esc_js( $this->args['notice_id'] ); ?>' );

				if ( ! $notice.length ) {
					return;
				}

				var dismissNotice = function() {
					$.post(
						ajaxurl,
						{
							action: $notice.data( 'action' ),
							nonce: $notice.data( 'nonce' )
						}
					);
				};

				$notice.on( 'click', '.notice-dismiss', function() {
					dismissNotice();
				} );

				$notice.on( 'click', '.wpsight-translation-notice__button-dismiss', function() {
					dismissNotice();
					$notice.fadeOut( 180, function() {
						$notice.remove();
					} );
				} );
			} );
		</script>
		<?php

	}

	/**
	 * Get the dismiss meta key.
	 *
	 * @return string Meta key.
	 */
	protected function get_dismiss_meta_key() {

		return '_' . $this->args['notice_id'] . '_dismissed';

	}

	/**
	 * Get the translation details cache key.
	 *
	 * @param string $locale Locale to check.
	 * @return string Cache key.
	 */
	protected function get_cache_key( $locale ) {

		return $this->args['notice_id'] . '_v' . $this->cache_version . '_' . md5( $this->args['project_slug'] . '_' . $locale );

	}

}
