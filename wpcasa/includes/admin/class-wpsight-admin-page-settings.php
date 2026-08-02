<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *	WPSight_Admin_Settings class
 */
class WPSight_Admin_Settings {

	// Variables
	public $settings;
	public $settings_group;
	public $settings_name;

	/**
	 *	Constructor
	 */
	public function __construct() {

		$this->settings_group = WPSIGHT_DOMAIN;
		$this->settings_name  = WPSIGHT_DOMAIN;

		add_action( 'admin_init',			array( $this, 'register_settings' ) );
		add_filter( 'admin_body_class',		array( $this, 'admin_body_class' ) );

        add_action( 'admin_post_reset_settings',  array( $this, 'reset_settings' ) );
        add_action( 'admin_post_migrate_data',  array( $this, 'migrate_data' ) );
		//add_action( 'admin_post_delete_all_transients',  array( $this, 'delete_all_transients' ) );
        add_action( 'admin_post_delete_all_data',  array( $this, 'delete_all_data' ) );
	}

	/**
	 *	admin_body_class()
	 *
	 *	@since 1.1.0
	 */
	public function admin_body_class( $classes ) {

		$screen		= get_current_screen();
		$post_type	= wpsight_post_type();

		if ( in_array( $screen->id, array( 'toplevel_page_wpsight-settings' ) ) ) {
			$classes .= ' wpsight-settings-page ';
		} elseif( in_array( $screen->id, array( 'wpcasa_page_wpsight-addons', 'wpcasa_page_wpsight-themes', 'wpcasa_page_wpsight-licenses', 'wpcasa_page_wpsight-recommendations' ) ) ) {
			$classes .= ' wpsight-extras-page ';
		} elseif( in_array( $screen->id, array( 'edit-' . $post_type, $post_type ) ) ) {
			$classes .= ' wpsight-post-type-page ';
		}

		return $classes;
	}

	/**
	 *	init_settings()
	 *
	 *	@access	protected
	 *	@uses	get_editable_roles()
	 *	@uses	apply_filters()
	 *	@uses	wpsight_options()
	 *
	 *	@since 1.0.0
	 */
	protected function init_settings() {

		// Prepare roles option
		$roles         = get_editable_roles();
		$account_roles = array();

		foreach ( $roles as $key => $role ) {
			if ( $key == 'administrator' )
				continue;
			$account_roles[ $key ] = $role['name'];
		}

		$this->settings = apply_filters( 'wpsight_settings', wpsight_options() );

	}

	/**
	 * Get the optional tabs registered for a settings section.
	 *
	 * Tabs are registered in the third section element under the `tabs` key.
	 * Existing sections without this metadata remain unchanged.
	 * Tab IDs are sanitized with `sanitize_key()` and retain their registered
	 * order. Labels remain untranslated here because add-ons are responsible
	 * for translating their labels when registering the section.
	 *
	 * @since 1.5.4
	 *
	 * @param array $section Settings section data.
	 * @return array Sanitized tab IDs mapped to their labels.
	 */
	public function get_section_tabs( $section ) {

		$tabs = array();

		if ( ! isset( $section[2]['tabs'] ) || ! is_array( $section[2]['tabs'] ) ) {
			return $tabs;
		}

		foreach ( $section[2]['tabs'] as $tab_id => $tab_label ) {
			$tab_id = sanitize_key( $tab_id );

			if ( '' === $tab_id || ! is_scalar( $tab_label ) ) {
				continue;
			}

			$tabs[ $tab_id ] = (string) $tab_label;
		}

		return $tabs;
	}

	/**
	 * Group settings fields by their registered section tab.
	 *
	 * Fields without a `tab` value remain visible above the tab navigation.
	 * Fields with an invalid tab value are also kept in the common group so
	 * that a configuration error never makes a field inaccessible.
	 *
	 * The returned `common` element contains unassigned fields. The `tabs`
	 * element contains one field collection for every registered tab,
	 * including empty tabs that are populated through rendering hooks.
	 *
	 * @since 1.5.4
	 *
	 * @param array $section Settings section data.
	 * @param array $tabs    Sanitized section tabs.
	 * @return array Common and tab-specific settings fields.
	 */
	public function get_section_tab_options( $section, $tabs ) {

		$groups = array(
			'common' => array(),
			'tabs'   => array(),
		);

		foreach ( array_keys( $tabs ) as $tab_id ) {
			$groups['tabs'][ $tab_id ] = array();
		}

		if ( ! isset( $section[1] ) || ! is_array( $section[1] ) ) {
			return $groups;
		}

		foreach ( $section[1] as $option_key => $option ) {
			if ( ! is_array( $option ) ) {
				continue;
			}

			$tab_id = isset( $option['tab'] ) && is_scalar( $option['tab'] )
				? sanitize_key( $option['tab'] )
				: '';

			if ( '' === $tab_id || ! isset( $groups['tabs'][ $tab_id ] ) ) {
				$groups['common'][ $option_key ] = $option;
				continue;
			}

			$groups['tabs'][ $tab_id ][ $option_key ] = $option;
		}

		return $groups;
	}

	/**
	 *	register_settings()
	 *
	 *	@access	public
	 *	@uses	$this->init_settings()
	 *	@uses	get_option()
	 *	@uses	add_option()
	 *	@uses	wpsight_options_defaults()
	 *	@uses	register_setting()
	 *
	 *	@since 1.0.0
	 */
	public function register_settings() {

		$this->init_settings();

	    // If no settings available, set defaults

	    if( get_option( $this->settings_name ) === false )
	    	add_option( $this->settings_name, wpsight_options_defaults() );

		register_setting( $this->settings_group, $this->settings_name );

	}

	/**
	 *	output()
	 *
	 *	@access	public
	 *	@uses	$this->init_settings()
	 *	@uses	settings_fields()
	 *	@uses	flush_rewrite_rules()
	 *	@uses	wpsight_options_defaults()
	 *	@uses	update_option()
	 *	@uses	wpsight_get_option()
	 *	@uses	do_action()
	 *	@uses	submit_button()
	 *
	 *	@since 1.0.0
	 */
	public function output() {
		$this->init_settings();
        $settings = $this->settings;
        $settings_group = $this->settings_group;

        include WPSIGHT_PLUGIN_DIR . '/includes/admin/views/settings.php';

		do_action( 'wpsight_settings_scripts', $this->settings_name );
    }

    public function reset_settings() {
        check_admin_referer( 'reset', 'reset_settings' );
        flush_rewrite_rules();
        update_option( $this->settings_name, wpsight_options_defaults() );

        $redirect = add_query_arg( 'reset_settings', 'success', admin_url("/admin.php?page=wpsight-settings") );
        wp_redirect($redirect, 301);
        exit;

    }
//    TODO: delete till wpcasa 1.5
    public function migrate_data() {
		
        check_admin_referer( 'migrate', 'migrate_data' );

//        $args = array(
//            'post_type' => 'listing',
//            'posts_per_page' => -1,
//            'meta_query' => array(
//                array(
//                    'key' => '_map_geolocation',
//                    'compare' => 'NOT EXISTS'
//                ),
//            )
//        );
//        $map_query = new WP_Query( $args );
//
//        while ( $map_query->have_posts() ) : $map_query->the_post();
//            $geo_lat = esc_js( get_post_meta( get_the_id(), '_geolocation_lat', true ) );
//            $geo_lng = esc_js( get_post_meta( get_the_id(), '_geolocation_long', true ) );
//
//            update_post_meta(get_the_id(), '_map_geolocation', array('lat' => $geo_lat, 'long' => $geo_lng));
//        endwhile;
//        wp_reset_query();

        $redirect = add_query_arg( 'migrate_data', 'success', admin_url("/admin.php?page=wpsight-settings") );
        wp_redirect($redirect, 301);
        exit;

    }

//    public function delete_all_transients() {
//        check_admin_referer( 'delete_transients', 'delete_all_transients' );
//
//        delete_transient( 'wpsight_addons_html' );
//        delete_transient( 'wpsight_themes_html' );
//
//        $redirect = add_query_arg( 'delete_all_transients', 'success', admin_url("/admin.php?page=wpsight-settings") );
//        wp_redirect($redirect, 301);
//
//        exit;
//
//    }

    public function delete_all_data() {
        check_admin_referer( 'delete_data', 'delete_all_data' );
        global $wpdb;

        //delete listing posts
        $result = $wpdb->query(
          $wpdb->prepare("
            DELETE posts,pt,pm
            FROM wp_posts posts
            LEFT JOIN wp_term_relationships pt ON pt.object_id = posts.ID
            LEFT JOIN wp_postmeta pm ON pm.post_id = posts.ID
            WHERE posts.post_type = %s
            ",
            'listing'
          )
        );

        //delete listings taxonomy terms
        $taxes = ['feature', 'feature' , 'listing-type', 'location', 'listing-category'];

        foreach( $taxes as $tax ) {
          $terms = get_terms([
            'taxonomy' => $tax,
            'hide_empty' => false,
          ]);
          foreach ($terms as $term) {
            wp_delete_term( $term->term_id, $tax );
          }
        }

        $redirect = add_query_arg( 'delete_all_data', 'success', admin_url("/admin.php?page=wpsight-settings") );
        wp_redirect($redirect, 301);
        exit;
    }
}
