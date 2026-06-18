<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 *	WPSight_Admin class
 */
class WPSight_Admin {

    // Variables
    public $cpt;
    public $agents;
    public $settings_page;
    public $license_page;
    public $color_scheme;
    public $helpers;
    public $translation_notice;


    /**
     *	Constructor
     */
    public function __construct() {

        

		// Include files
        include_once WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-cpt.php';
        include_once WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-agents.php';
        include_once WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-page-settings.php';
        include_once WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-page-licenses.php';
        include_once WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-color-scheme.php';
        include_once WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-helpers.php';
        include_once WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-translation-notice.php';

		// Load classes
        $this->cpt				= new WPSight_Admin_CPT();
        $this->agents			= new WPSight_Admin_Agents();
		$this->settings_page	= new WPSight_Admin_Settings();
        $this->license_page		= new WPSight_Admin_Licenses();
        $this->color_scheme		= new WPSight_Admin_Color_Scheme();
        $this->helpers			= new WPSight_Admin_Helpers();
        $this->translation_notice	= new WPSight_Admin_Translation_Notice(
            array(
                'textdomain'       => 'wpcasa',
                'project_slug'     => 'wpcasa',
                'plugin_name'      => WPSIGHT_NAME,
                'notice_id'        => 'wpsight_translation_notice',
                'logo_url'         => WPSIGHT_PLUGIN_URL . '/assets/img/icon.png',
                'minimum_percent'  => 90,
                'translation_url'  => 'https://translate.wordpress.org/projects/wp-plugins/wpcasa/',
                'screens'          => array(
                    'plugins',
                    'toplevel_page_wpsight-settings',
                    'wpcasa_page_wpsight-addons',
                    'wpcasa_page_wpsight-themes',
                    'wpcasa_page_wpsight-licenses',
                    'wpcasa_page_wpsight-recommendations',
                ),
            )
        );

		// Actions & Filers
		
        add_action( 'admin_menu',								[ $this, 'admin_menu' ], 12 );
        add_action( 'admin_enqueue_scripts',					[ $this, 'admin_enqueue_scripts' ] );
        add_action( 'upgrader_process_complete',				array( $this, 'maybe_set_update_page_redirect' ), 10, 2 );
        add_action( 'admin_init',								array( $this, 'maybe_redirect_to_update_page' ) );
        add_action( 'admin_init',								array( $this, 'maybe_set_review_notice_timestamp' ) );
        add_action( 'admin_footer-update.php',					array( $this, 'maybe_print_update_page_redirect_script' ) );

        add_action( 'admin_notices',							[ $this, 'notice_setup' ] );
	    add_action( 'admin_notices',							[ $this, 'notice_updater' ] );
        add_action( 'admin_notices',							array( $this, 'notice_review' ) );
        add_action( 'admin_notices',							array( $this, 'notice_theme_update_required' ) );
        add_action( 'wp_ajax_wpsight_dismiss_review_notice',	array( $this, 'dismiss_review_notice' ) );

        add_filter( 'views_upload',								[ $this, 'media_custom_views' ] );
        add_filter( 'views_edit-listing',						[ $this, 'listings_custom_views' ] );
        add_filter( 'views_edit-property',						[ $this, 'listings_custom_views' ] );
        add_filter( 'plugin_row_meta',							array( $this, 'plugin_row_meta' ), 10, 2 );
        add_filter( 'manage_users_columns',						[ $this, 'manage_users_columns' ] );
        add_action( 'manage_users_custom_column',				[ $this, 'manage_users_custom_column' ], 10, 3 );
		
		add_filter( 'install_plugins_tabs',						[ $this, 'add_addon_tab' ] );
		add_action( 'install_plugins_wpcasa_addons',			[ $this, 'addons_page' ] );
		add_action( 'install_themes_wpcasa_themes',				[ $this, 'themes_pag ' ] );
		add_action( 'install_plugins_wpcasa_recommendations',	[ $this, 'recommends_page' ] );

	}

    /**
     *	admin_enqueue_scripts()
     *
     *	Enqueue scripts and styles used
     *	on WordPress admin pages.
     *
     *	@access	public
     *	@uses	get_current_screen()
     *	@uses	wp_enqueue_style()
     *	@uses	wp_register_script()
     *	@uses	wp_enqueue_script()
     *
     *	@since 1.0.0
     */
    public function admin_enqueue_scripts() {

        global $wp_scripts;

        // Script debugging?
        $suffix = SCRIPT_DEBUG ? '' : '.min';

        $screen		= get_current_screen();
        $post_type	= wpsight_post_type();

		// TODO: Delete it till wpcasa 1.7
        if ( in_array( $screen->id, array( 'plugins' ), true ) ) {
            wp_enqueue_script( 'jquery-plugins-admin', WPSIGHT_PLUGIN_URL . '/assets/js/wpsight-plugins-admin' . $suffix . '.js', array( 'jquery' ), WPSIGHT_VERSION, true );

            wp_add_inline_script(
                'jquery-plugins-admin',
                'var wpsightPluginsAdmin = ' . wp_json_encode(
                    array(
                        'pluginFile'    => plugin_basename( WPSIGHT_PLUGIN_DIR . '/wpcasa.php' ),
                        'updatePageUrl' => admin_url( 'admin.php?page=wpsight-about' ),
                    )
                ) . ';',
                'before'
            );
        }

		wp_enqueue_style( 'wpsight-admin-swiper', WPSIGHT_PLUGIN_URL . '/vendor/swiper/swiper-bundle' . $suffix . '.css', array( 'cmb2-styles' ), '6.7.5' );
		wp_enqueue_script( 'wpsight-admin-swiper', WPSIGHT_PLUGIN_URL . '/vendor/swiper/swiper-bundle' . $suffix . '.js', array( 'jquery' ), '6.7.5', true );

	    wp_enqueue_style( 'wpsight-admin', WPSIGHT_PLUGIN_URL . '/assets/css/wpsight-admin' . $suffix . '.css', array(), WPSIGHT_VERSION );

	    if ( in_array( $screen->id, array( 'edit-' . $post_type, $post_type, 'toplevel_page_wpsight-settings', 'wpcasa_page_wpsight-addons', 'wpcasa_page_wpsight-themes', 'wpcasa_page_wpsight-licenses', 'wpcasa_page_wpsight-recommendations', 'wpcasa_page_wpsight-about' ) ) || $screen->id == 'plugin-install' && isset( $_GET['tab'] ) && $_GET['tab'] == 'wpcasa_addons' ) {

            wp_register_script( 'jquery-tiptip', WPSIGHT_PLUGIN_URL . '/assets/js/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), '1.3', true );
			
            wp_enqueue_style( 'wpsight-admin-ui-framework', WPSIGHT_PLUGIN_URL . '/assets/css/wpsight-admin-ui-framework' . $suffix . '.css', array( 'cmb2-styles' ), WPSIGHT_VERSION );
            wp_enqueue_style( 'wpsight-listing-admin', WPSIGHT_PLUGIN_URL . '/assets/css/wpsight-listing-admin' . $suffix . '.css', array( 'wpsight-admin-ui-framework', 'cmb2-styles' ), WPSIGHT_VERSION );
            if ( is_rtl() ) {
                wp_enqueue_style( 'wpsight-listing-admin-rtl', WPSIGHT_PLUGIN_URL . '/assets/css/wpsight-admin-rtl' . $suffix . '.css', array( 'wpsight-listing-admin' ), WPSIGHT_VERSION );
            }

	        wp_enqueue_style('wp-color-picker');

	        wp_enqueue_script( 'wpsight_admin_js', WPSIGHT_PLUGIN_URL . '/assets/js/wpsight-admin' . $suffix . '.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-datepicker', 'wp-color-picker' ), WPSIGHT_VERSION, true );

			wp_add_inline_script( 'wpsight_admin_js', 'const WPCASA_SETTINGS = ' . wp_json_encode( array(
				'name' => $this->settings_page->settings_name
			) ), 'before' );

        }
		
		if ( $screen->id == 'wpcasa_page_wpsight-about' )
            wp_enqueue_style( 'wpsight-admin-page-about', WPSIGHT_PLUGIN_URL . '/assets/css/wpsight-admin-page-about' . $suffix . '.css', null, WPSIGHT_VERSION );

        if ( in_array( $screen->id, array( 'profile', 'user-edit' ) ) )
            wp_enqueue_media();

    }

    /**
     *	admin_menu()
     *
     *	Add WPSight settings main and
     *	sub pages to the admin menu.
     *
     *	@access	public
     *	@uses	add_menu_page()
     *	@uses	add_submenu_page()
     *	@uses	apply_filters()
     *
     *	@since 1.0.0
     */
    public function admin_menu() {

		// WPCasa logo
	    $icon_base64 = "PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNi42NjciIGhlaWdodD0iMjYuNjY3IiB2aWV3Qm94PSIwIDAgMjAgMjAiPjxwYXRoIGQ9Ik0xNy42NCAwSC4zNkEuMzYuMzYgMCAwIDAgMCAuMzZ2MTcuMjhjMCAuMTk5LjE2MS4zNi4zNi4zNmgxNy4yOGEuMzYuMzYgMCAwIDAgLjM2LS4zNlYuMzZhLjM2LjM2IDAgMCAwLS4zNi0uMzZNMy4xMjMgMTQuMDEzdi02LjJjMC0uMjY5LjEyNS0uNTIyLjMzOC0uNjg2TDguNDc1IDMuMjlhLjg2NC44NjQgMCAwIDEgMS4wNSAwbDUuMDE0IDMuODM3YS44Ni44NiAwIDAgMSAuMzM4LjY4NnY2LjJhLjg2NC44NjQgMCAwIDEtLjg2NC44NjRIMy45ODdhLjg2NC44NjQgMCAwIDEtLjg2NC0uODY0IiBzdHlsZT0iZmlsbDojZjBmMGYxIi8+PHBhdGggZD0iTTkuNTIgOC41MjdjLTEuNjU4LS4wODYtMi4xNjIgMS45Ny0xLjYxOSAzLjIxNi40MzIgMS4xNDIgMS44NDMgMS4xMjYgMi44MzYuNzg4di41MTdjLTUuMTQgMS43MDgtNC43NjctNi43NzYuMTg4LTQuNzQ5bC0uMjM3LjUwNGMtLjM1OC0uMTYtLjc1LS4yODItMS4xNjctLjI3NiIgc3R5bGU9ImZpbGw6I2YwZjBmMSIvPjwvc3ZnPg==";

        add_menu_page( WPSIGHT_NAME, WPSIGHT_NAME, 'manage_options', 'wpsight-settings', [ $this->settings_page, 'output' ], 'data:image/svg+xml;base64,' . $icon_base64 );

        add_submenu_page(  'wpsight-settings', WPSIGHT_NAME . ' ' . __( 'Settings', 'wpcasa' ),  __( 'Settings', 'wpcasa' ) , 'manage_options', 'wpsight-settings', [ $this->settings_page, 'output' ] );

        if ( apply_filters( 'wpsight_show_addons_page', true ) )
            add_submenu_page(  'wpsight-settings', WPSIGHT_NAME . ' ' . __( 'Add-Ons', 'wpcasa' ),  __( 'Add-Ons', 'wpcasa' ) , 'manage_options', 'wpsight-addons', [ $this, 'addons_page' ] );

        if ( apply_filters( 'wpsight_show_themes_page', true ) )
            add_submenu_page(  'wpsight-settings', WPSIGHT_NAME . ' ' . __( 'Themes', 'wpcasa' ),  __( 'Themes', 'wpcasa' ) , 'manage_options', 'wpsight-themes', [ $this, 'themes_page' ] );

        if ( apply_filters( 'wpsight_show_licenses_page', true ) )
            add_submenu_page(  'wpsight-settings', WPSIGHT_NAME . ' ' . __( 'Licenses', 'wpcasa' ),  __( 'Licenses', 'wpcasa' ) , 'manage_options', 'wpsight-licenses', [ $this->license_page, 'output' ] );

        if ( apply_filters( 'wpsight_show_about_page', true ) )
            add_submenu_page(  'wpsight-settings', WPSIGHT_NAME . ' ' . __( 'About', 'wpcasa' ),  __( 'About', 'wpcasa' ) , 'manage_options', 'wpsight-about', [ $this, 'about_page' ] );
		
    }

    /**
     * plugin_row_meta()
     *
     * Add support and review links on the plugins overview page.
     *
     * @param array  $links Existing plugin meta links.
     * @param string $file Plugin file path.
     * @uses plugin_basename()
     * @uses esc_url()
     * @uses esc_attr__()
     * @uses esc_html__()
     * @return array Updated plugin meta links.
     *
     * @since 1.5.0
     */
    public function plugin_row_meta( $links, $file ) {

        if ( plugin_basename( WPSIGHT_PLUGIN_DIR . '/wpcasa.php' ) !== $file ) {
            return $links;
        }

        $custom_links = array(
            'support' => sprintf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s">%3$s</a>',
                esc_url( 'https://wordpress.org/support/plugin/wpcasa/' ),
                esc_attr__( 'Get support for WPCasa', 'wpcasa' ),
                esc_html__( 'Support', 'wpcasa' )
            ),
            'docs' => sprintf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s">%3$s</a>',
                esc_url( 'https://docs.wpcasa.com/' ),
                esc_attr__( 'Documentation for WPCasa', 'wpcasa' ),
                esc_html__( 'Docs', 'wpcasa' )
            ),
            'review'  => sprintf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s">%3$s <span class="wpsight-plugin-row-meta-stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span></a>',
                esc_url( 'https://wordpress.org/support/plugin/wpcasa/reviews/#new-post' ),
                esc_attr__( 'Rate WPCasa on WordPress.org', 'wpcasa' ),
                esc_html__( 'Rate the plugin', 'wpcasa' )
            ),
        );

        return array_merge( $links, $custom_links );

    }

    /**
     * maybe_set_update_page_redirect()
     *
     * Mark the current user for a one-time redirect after an individual WPCasa update.
     *
     * @param WP_Upgrader $upgrader   Upgrader instance.
     * @param array       $hook_extra Update context data.
     * @uses current_user_can()
     * @uses plugin_basename()
     * @uses update_user_meta()
     * @uses get_current_user_id()
     * @return void
     *
     * @since 1.5.3
     */
    public function maybe_set_update_page_redirect( $upgrader, $hook_extra ) {

        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        if ( wp_doing_ajax() ) {
            return;
        }

        if ( empty( $hook_extra['action'] ) || 'update' !== $hook_extra['action'] ) {
            return;
        }

        if ( empty( $hook_extra['type'] ) || 'plugin' !== $hook_extra['type'] ) {
            return;
        }

        if ( isset( $upgrader->skin->result ) && ( false === $upgrader->skin->result || is_wp_error( $upgrader->skin->result ) ) ) {
            return;
        }

        $plugin_file = plugin_basename( WPSIGHT_PLUGIN_DIR . '/wpcasa.php' );

        if ( ! $this->is_individual_wpcasa_update( $hook_extra, $plugin_file ) ) {
            return;
        }

        update_user_meta( get_current_user_id(), '_wpsight_update_page_redirect', WPSIGHT_VERSION );

    }

    /**
     * is_individual_wpcasa_update()
     *
     * Check if the update context contains WPCasa as the only updated plugin.
     *
     * @param array  $hook_extra Update context data.
     * @param string $plugin_file WPCasa plugin basename.
     * @return bool True when WPCasa was updated individually.
     *
     * @since 1.5.3
     */
    protected function is_individual_wpcasa_update( $hook_extra, $plugin_file ) {

        if ( ! empty( $hook_extra['plugin'] ) ) {
            return $plugin_file === $hook_extra['plugin'];
        }

        if ( empty( $hook_extra['plugins'] ) || ! is_array( $hook_extra['plugins'] ) ) {
            return false;
        }

        $plugins = array_values( array_filter( $hook_extra['plugins'] ) );

        return 1 === count( $plugins ) && $plugin_file === $plugins[0];

    }

    /**
     * maybe_redirect_to_update_page()
     *
     * Redirect the current user once to the WPCasa update information page.
     *
     * @uses current_user_can()
     * @uses get_current_user_id()
     * @uses get_user_meta()
     * @uses delete_user_meta()
     * @uses admin_url()
     * @uses wp_safe_redirect()
     * @return void
     *
     * @since 1.5.3
     */
    public function maybe_redirect_to_update_page() {

        if ( wp_doing_ajax() || wp_doing_cron() || is_network_admin() ) {
            return;
        }

        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        $user_id = get_current_user_id();

        if ( ! get_user_meta( $user_id, '_wpsight_update_page_redirect', true ) ) {
            return;
        }

        delete_user_meta( $user_id, '_wpsight_update_page_redirect' );

        if ( isset( $_GET['page'] ) && 'wpsight-about' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wpsight-about' ) );
        exit;

    }

    /**
     * maybe_print_update_page_redirect_script()
     *
     * Redirect from the classic plugin updater screen after a successful WPCasa update.
     *
     * @uses current_user_can()
     * @uses get_current_user_id()
     * @uses get_user_meta()
     * @uses delete_user_meta()
     * @uses admin_url()
     * @uses wp_json_encode()
     * @return void
     *
     * @since 1.5.3
     */
    public function maybe_print_update_page_redirect_script() {

        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        $user_id = get_current_user_id();

        if ( ! get_user_meta( $user_id, '_wpsight_update_page_redirect', true ) ) {
            return;
        }

        delete_user_meta( $user_id, '_wpsight_update_page_redirect' );

        ?>
        <script type="text/javascript">
            ( function() {
                var updatePageUrl = <?php echo wp_json_encode( admin_url( 'admin.php?page=wpsight-about' ) ); ?>;

                if ( window.parent && window.parent !== window ) {
                    window.parent.location.href = updatePageUrl;
                    return;
                }

                window.location.href = updatePageUrl;
            }() );
        </script>
        <?php

    }

    /**
     * Adds a new tab to the install-plugins-page.
     *
     * @return void
     */
    public function add_addon_tab( $tabs ) {
        $tabs['wpcasa_addons'] = WPSIGHT_NAME . ' <span class="wpcasa-addons">' . __( 'Addons', 'wpcasa' ) . '</span>' ;
        return $tabs;
    }

    /**
     *	addons_page()
     *
     *	Add WPSight addons page to sub menu.
     *
     *	@access	public
     *
     *	@since 1.0.0
     */
    public function addons_page() {
        $addons = include WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-page-addons.php';
        $addons->output();
    }

    /**
     *	themes_page()
     *
     *	Add WPSight themes page to sub menu.
     *
     *	@access	public
     *
     *	@since 1.0.0
     */
    public function themes_page() {
        $themes = include WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-page-themes.php';
        $themes->output();
    }

    /**
     *	about_page()
     *
     *	Add WPSight about page.
     *
     *	@access	public
     *
     *	@since 1.0.0
     */
    public function about_page() {
        $about = include WPSIGHT_PLUGIN_DIR . '/includes/admin/class-wpsight-admin-page-about.php';
        $about->output();
    }

    /**
     * Get the outdated bundled WPCasa theme data if an update is required.
     *
     * @return array|false
     */
    protected function get_outdated_wpcasa_theme_data() {

        $theme          = wp_get_theme();
        $minimum_versions = array(
            'wpcasa-oslo'   => '1.4.1',
            'wpcasa-madrid' => '1.5.1',
            'wpcasa-london' => '1.5.1',
        );
        $themes_to_check = array(
            array(
                'slug'  => $theme->get_stylesheet(),
                'theme' => $theme,
            ),
        );
        $parent_theme    = $theme->parent();

        if ( $parent_theme instanceof WP_Theme ) {
            $themes_to_check[] = array(
                'slug'  => $theme->get_template(),
                'theme' => $parent_theme,
            );
        }

        foreach ( $themes_to_check as $theme_data ) {
            if ( empty( $minimum_versions[ $theme_data['slug'] ] ) ) {
                continue;
            }

            if ( version_compare( $theme_data['theme']->get( 'Version' ), $minimum_versions[ $theme_data['slug'] ], '<' ) ) {
                return array(
                    'name'            => $theme_data['theme']->get( 'Name' ),
                    'current_version' => $theme_data['theme']->get( 'Version' ),
                    'required_version'=> $minimum_versions[ $theme_data['slug'] ],
                );
            }
        }

        return false;

    }

    /**
     * Display a notice when an outdated bundled WPCasa theme is active.
     *
     * @return void
     */
    public function notice_theme_update_required() {
        $theme_data = false;

        if ( ! current_user_can( 'update_themes' ) ) {
            return;
        }

        $theme_data = $this->get_outdated_wpcasa_theme_data();

        if ( false === $theme_data ) {
            return;
        }

        echo '<div class="notice notice-warning">';
        echo '<p>';
        echo wp_kses_post(
            sprintf(
                __(
                    '<strong>Theme update required:</strong> Please update %1$s from version %2$s to at least version %3$s so that the location output on the single listing page continues to work correctly.',
                    'wpcasa'
                ),
                esc_html( $theme_data['name'] ),
                esc_html( $theme_data['current_version'] ),
                esc_html( $theme_data['required_version'] )
            )
        );
        echo '</p>';
        echo '</div>';

    }

    /**
     *	options()
     *
     *	Merge option tabs and
     *	return wpsight_options_listings()
     *
     *	@uses	wpsight_options_listings()
     *	@return	array	$options
     *
     *	@since 1.0.0
     */
    public static function options() {
        $options = array(
            'listings' => array(
                '<span class="dashicons dashicons-admin-multisite"></span>' . __( 'Listings', 'wpcasa' ),
                (array) self::options_listings()
            ),
//            'search' => array(
//                '<span class="dashicons dashicons-search"></span>' . __( 'Search', 'wpcasa' ),
//                (array) self::options_search()
//            ),
            'maps' => array(
                '<span class="dashicons dashicons-location-alt"></span>' . __( 'Maps', 'wpcasa' ),
                (array) self::options_maps()
            )
        );

        $options = apply_filters( 'wpsight_options', $options );

        return $options;
    }

    /**
     *	licenses()
     *
     *	Create license array
     *
     *	@return	array $licenses
     *
     *	@since 1.0.0
     */
    public static function licenses() {

        // initialize empty array
        $licenses = array();

        // add default license
        $licenses['support_package'] = array(
            'name'		=> __( 'Support Package', 'wpcasa' ),
            'desc'		=> __( 'To receive support for a free product please enter your support package license key.', 'wpcasa' ),
            'id'		=> 'support_package',
            'section'	=> 'services',
            'priority'	=> 1000
        );

        // filter licenses
        $licenses = apply_filters( 'wpsight_licenses', $licenses );

        // sort by priority
        $licenses = wpsight_sort_array_by_priority( $licenses );

        // return
        return $licenses;

    }

    /**
     *	options_listings()
     *
     *	Create theme options array
     *	Listings options
     *
     *	@uses	wpsight_get_option()
     *	@uses	wpsight_measurements()
     *	@uses	wpsight_currencies()
     *	@uses	wpsight_details()
     *	@uses	wpsight_rental_periods()
     *	@uses	wpsight_date_formats()
     *	@return	array	$options_listings
     *
     *	@since 1.0.0
     */
    public static function options_listings() {

        $options_listings = array();

        $options_listings['pageheading_listings'] = array(
            'name'		=> __( 'Listings', 'wpcasa' ),
            'desc'		=> __( 'Here you can define some basic settings', 'wpcasa' ),
            'icon'		=> 'dashicons dashicons-admin-multisite',
            'link'		=> 'https://docs.wpcasa.com',
            'id'		=> 'pageheading_listings',
            'position'	=> 10,
            'type'		=> 'pageheading'
        );

        $options_listings['heading_listings'] = array(
            'name'		=> __( 'General Listing Settings', 'wpcasa' ),
            'desc'		=> __( 'Here you can define some basic settings', 'wpcasa' ),
            'id'		=> 'heading_listings',
            'position'	=> 20,
            'type'		=> 'heading'
        );

        $options_listings['listings_page'] = array(
            'name'		=> __( 'Listings Page', 'wpcasa' ),
            'desc'		=> __( 'Please select the main search results page with the <code>[wpsight_listings]</code> shortcode.', 'wpcasa' ),
            'id'		=> 'listings_page',
            'position'	=> 30,
            'type'		=> 'pages'
        );

        $options_listings['date_format'] = array(
            'name'		=> __( 'Date Format', 'wpcasa' ),
            'desc'		=> __( 'Please select the date format for the listings table in the admin.', 'wpcasa' ),
            'id'		=> 'date_format',
            'position'	=> 40,
            'type'		=> 'select',
            'options'	=> array_filter( wpsight_date_formats( true ) ),
            'default'	=> get_option( 'date_format' )
        );

        $options_listings['listings_css'] = array(
            'name'		=> __( 'Output CSS', 'wpcasa' ),
            'desc'		=> __( 'Please uncheck the box to disable the plugin from outputting CSS.', 'wpcasa' ),
            'id'		=> 'listings_css',
            'position'	=> 50,
            'type'		=> 'checkbox',
            'default'	=> '1'
        );

        // Check of old 'property_id' options was active
        $listing_id_default = wpsight_get_option( 'property_id' ) ? wpsight_get_option( 'property_id' ) : __( 'ID-', 'wpcasa' );

        $options_listings['listing_id'] = array(
            'name'		=> __( 'Listing ID Prefix', 'wpcasa' ),
            'desc'		=> __( 'The listing ID will be this prefix plus post ID. You can optionally set individual IDs on the listing edit screen.', 'wpcasa' ),
            'id'		=> 'listing_id',
            'position'	=> 60,
            'type'		=> 'text',
            'default'	=> $listing_id_default
        );

        $options_listings['measurement_unit'] = array(
            'name'		=> __( 'Measurement Unit', 'wpcasa' ),
            'desc'		=> __( 'Please select the general measurement unit. The unit for the listing details can be defined separately.', 'wpcasa' ),
            'id'		=> 'measurement_unit',
            'position'	=> 70,
            'type'		=> 'radio',
            'class'		=> 'mini',
            'options'	=> array_filter( wpsight_measurements() ),
            'default'	=> 'm2'
        );

	    $options_listings['heading_rest_api'] = array(
		    'name'		=> __( 'REST API', 'wpcasa' ),
		    'id'		=> 'heading_rest_api',
		    'position'	=> 75,
		    'type'		=> 'heading'
	    );

	    $options_listings['listings_rest_api'] = array(
		    'name'		=> __( 'Show in REST API (and block editor)', 'wpcasa' ),
		    'desc'		=> __( 'Please check the box to show listings and all property taxonomies in the REST API. This is also required to show listings in the block editor.', 'wpcasa' ),
		    'id'		=> 'listings_rest_api',
		    'position'	=> 76,
		    'type'		=> 'checkbox',
		    'default'	=> '0'
	    );

	    $options_listings['heading_media'] = array(
		    'name'		=> __( 'Media Files', 'wpcasa' ),
		    'id'		=> 'heading_media',
		    'position'	=> 78,
		    'type'		=> 'heading'
	    );

	    $options_listings['listings_delete_media'] = array(
		    'name'		=> __( 'Delete image when the listing is deleted', 'wpcasa' ),
		    'desc'		=> __( 'Please check the box to delete the listings gallery images and featured image from the WordPress media library when a listing is deleted. <b><u>Please note that file deletion cannot be undone.</u></b>', 'wpcasa' ),
		    'id'		=> 'listings_delete_media',
		    'position'	=> 79,
		    'type'		=> 'checkbox',
		    'default'	=> '0'
	    );

	    $options_listings['heading_currency'] = array(
		    'name'		=> __( 'Currency', 'wpcasa' ),
		    'id'		=> 'heading_currency',
		    'position'	=> 80,
		    'type'		=> 'heading'
	    );

	    $options_listings['currency'] = array(
            'name'		=> __( 'Currency', 'wpcasa' ),
            'desc'		=> __( 'Please select the currency for the listing prices. If your currency is not listed, please select <code>Other</code>.', 'wpcasa' ),
            'id'		=> 'currency',
            'position'	=> 90,
            'type'		=> 'select',
            'class'		=> 'mini',
            'options'	=> array_merge( array_filter( wpsight_currencies() ), array( 'other' => __( 'Other', 'wpcasa'  ) ) ),
            'default'	=> 'usd'
        );

        $options_listings['currency_other'] = array(
            'name'		=> __( 'Other Currency', 'wpcasa' ) . ' (' . __( 'Abbreviation', 'wpcasa' ) . ')',
            'desc'		=> __( 'Please insert the abbreviation of your currency (e.g. <code>EUR</code>).', 'wpcasa' ),
            'id'		=> 'currency_other',
            'position'	=> 100,
            'type'		=> 'text',
            'class'		=> 'hidden'
        );

        $options_listings['currency_other_ent'] = array(
            'name'		=> __( 'Other Currency', 'wpcasa' ) . ' (' . __( 'Symbol', 'wpcasa' ) . ')',
            'desc'		=> __( 'Please insert the currency symbol or HTML entity (e.g. <code>&amp;euro;</code>).', 'wpcasa' ),
            'id'		=> 'currency_other_ent',
            'position'	=> 110,
            'type'		=> 'text',
            'class'		=> 'hidden'
        );

        $options_listings['currency_symbol'] = array(
            'name'		=> __( 'Currency Symbol', 'wpcasa' ),
            'desc'		=> __( 'Please select the position of the currency symbol.', 'wpcasa' ),
            'id'		=> 'currency_symbol',
            'position'	=> 120,
            'type'		=> 'radio',
            'options'	=> array(
                'before'		=> __( 'Before the value', 'wpcasa' ),
                'after'			=> __( 'After the value', 'wpcasa' ),
                'before_space'	=> __( 'Before the value (with Space)', 'wpcasa' ),
                'after_space'	=> __( 'After the value (with Space)', 'wpcasa' )
            ),
            'default'	=> 'before'
        );

        $options_listings['currency_separator'] = array(
            'name'		=> __( 'Thousands Separator', 'wpcasa' ),
            'desc'		=> __( 'Please select the thousands separator for your listing prices.', 'wpcasa' ),
            'id'		=> 'currency_separator',
            'position'	=> 130,
            'type'		=> 'text',
            'default'	=> '.'
        );

        $options_listings['decimal_separator'] = array(
            'name'		=> __( 'Decimal Separator', 'wpcasa' ),
            'desc'		=> __( 'Please select the decimal separator for your listing prices.', 'wpcasa' ),
            'id'		=> 'decimal_separator',
            'position'	=> 131,
            'type'		=> 'text',
            'default'	=> ','
        );

        $options_listings['heading_details'] = array(
            'name'		=> __( 'Listing Details', 'wpcasa' ),
            'id'		=> 'heading_details',
            'position'	=> 140,
            'type'		=> 'heading'
        );

        /** Loop through standard details */

        $i=1;
        $position=150;

        foreach ( wpsight_details() as $detail_id => $value ) {

            $options_listings[ $detail_id ] = array(
                'name'		=> __( 'Listing Detail', 'wpcasa' ) . ' #' . $i++,
                'desc'		=> $value['description'] ?? '',
                'id'		=> $detail_id,
                'position'	=> $position++,
                'type'		=> 'measurement',
                'class'		=> '',
                'default'	=> array(
                    'label'		=> $value['label'],
                    'unit'		=> $value['unit']
                )
            );

        }

        $options_listings['heading_rental_periods'] = array(
            'name'		=> __( 'Rental Periods', 'wpcasa' ),
            'id'		=> 'heading_rental_periods',
            'position'	=> 300,
            'type'		=> 'heading'
        );

        /** Loop through rental periods */

        $i=1;
        $position=310;

        foreach ( wpsight_rental_periods() as $period => $value ) {

            $options_listings[ $period ] = array(
                'name'		=> __( 'Rental Period', 'wpcasa' ) . ' #' . $i++,
                'id'		=> $period,
                'position'	=> $position++,
                'type'		=> 'text',
                'class'		=> '',
                'default'	=> $value
            );

        }

        // filter options
        $options_listings = apply_filters( 'wpsight_options_listings', $options_listings );

        // sort options by position
        $options_listings = wpsight_sort_array_by_position( $options_listings );

        return $options_listings;

    }

    /**
     *	options_search()
     *
     *	Create theme options array
     *	Search options
     *
     *	@uses	wpsight_get_search_fields()
     *	@return	array	$options_search
     *
     *	@since 1.1.0
     */
    public static function options_search() {

        $options_search = array();

        /** Loop through search fields */
        $fields = wpsight_get_search_fields();

        $options = array();

        foreach( $fields as $field => $v ) {

            $label = isset($v['label']) ? $v['label'] : '';

            if( $v['type'] == 'taxonomy_select' && $v['data']['show_option_none'] )
                $label = $v['data']['show_option_none'];

            $options[$field] = $label;
        }

        $options_search['pageheading_search'] = array(
            'name'		=> __( 'Search', 'wpcasa' ),
            'desc'		=> __( 'Here you can define some basic settings', 'wpcasa' ),
            'icon'		=> 'dashicons dashicons-search',
            'link'		=> '',
            'id'		=> 'pageheading_search',
            'position'	=> 10,
            'type'		=> 'pageheading'
        );

        $options_search['search_elements'] = array(
            'name'		=> __( 'Search Form Elements', 'wpcasa' ),
            'desc'		=> __( 'Choose what to display in the search form', 'wpcasa' ),
            'id'		=> 'search_elements',
            'position'	=> 20,
            'type'		=> 'multicheck',
            'options'	=> $options
        );

        // filter options
        $options_search = apply_filters( 'wpsight_options_search', $options_search );

        // sort options by position
        $options_search = wpsight_sort_array_by_position( $options_search );

        return $options_search;

    }

    /**
     *	options_maps()
     *
     *	Create theme options array
     *	Maps options
     *
     *	@return	array	$options_maps
     *
     *	@since 1.1.0
     */
    public static function options_maps() {

        $options_maps = array();

        $options_maps['pageheading_maps'] = array(
            'name'	=> __( 'Maps', 'wpcasa' ),
            'desc'	=> __( 'Here you can define some basic settings', 'wpcasa' ),
            'icon'	=> 'dashicons dashicons-location-alt',
            'link'	=> 'https://docs.wpcasa.com/article/wpcasa-listings-map/',
            'id'	=> 'pageheading_maps',
            'type'	=> 'pageheading'
        );

        $options_maps['heading_listings_map_api'] = array(
            'name'	=> __( 'Map API', 'wpcasa' ),
            'desc'	=> __( 'Here you can enter Map API', 'wpcasa' ),
            'id'	=> 'heading_listings_map_api',
            'type'	=> 'heading',
            'position'	=> '490'
        );

        $options_maps['google_maps_api_key'] = array(
            'name'		=> __( 'Google Maps API', 'wpcasa' ),
            /* translators: %s: is the link to google maps */
            'desc'		=> sprintf( __( 'If necessary, please enter your Google Maps API key (<a href="%s" target="_blank">register here</a>).', 'wpcasa' ), 'https://developers.google.com/maps/documentation/javascript/get-api-key' ),
            'id'		=> 'google_maps_api_key',
            'type'		=> 'text',
            'position'	=> '500'
        );

        // filter options
        $options_maps = apply_filters( 'wpsight_options_maps', $options_maps );

        // sort options by position
        $options_maps = wpsight_sort_array_by_position( $options_maps );

        return $options_maps;

    }

    /**
     *	options_debug()
     *
     *	Create theme options array
     *	Listings options
     *
     *	@uses	wpsight_get_option()
     *	@uses	wpsight_measurements()
     *	@uses	wpsight_currencies()
     *	@uses	wpsight_details()
     *	@uses	wpsight_rental_periods()
     *	@uses	wpsight_date_formats()
     *	@return	array	$options_listings
     *
     *	@since 1.0.0
     */
    public static function options_debug() {

        $options_debug = array();

        if( version_compare( '1.1.0', WPSIGHT_VERSION, '>=' ) ) {

            $options_debug['example_slider'] = array(
                'name'		=> __( 'Example Slider', 'wpcasa' ),
                'desc'		=> __( 'Example Slider Option', 'wpcasa' ),
                'id'		=> 'example_slider',
                'type'		=> 'range',
                'min'		=> 0,
                'max'		=> 1000,
                'step'		=> 50,
                'default'	=> ''
            );

            $options_debug['example_number'] = array(
                'name'		=> __( 'Example Number', 'wpcasa' ),
                'desc'		=> __( 'Example Number Option', 'wpcasa' ),
                'id'		=> 'example_number',
                'type'		=> 'number',
                'default'	=> ''
            );

        }


        return apply_filters( 'wpsight_options_debug', $options_debug );

    }

    /**
     *	media_custom_views()
     *
     *	Media library views
     *
     *	@param	array	$views	Incoming views
     *	@uses	$wpdb->prepare()
     *	@uses	$wpdb->get_col()
     *	@uses	wp_count_attachments()
     *	@return	array	$views	Updated views
     *
     *	@since 1.0.0
     */
    public static function media_custom_views( $views ) {

        global $wpdb, $wp_query, $pagenow;

        if ( 'upload.php' != $pagenow )
            return;

        if ( ! isset( $wp_query->query_vars['s'] ) )
            return $views;

        // Search custom fields for listing ID

        $post_ids_meta = $wpdb->get_col( $wpdb->prepare( "
	    SELECT DISTINCT post_id FROM {$wpdb->postmeta}
	    WHERE meta_value LIKE %s
	    ", $wp_query->query_vars['s'] ) );

        if ( ! empty( $post_ids_meta ) && get_search_query() !== null ) {
			
            unset( $views );
			
			$s = get_search_query();
            $_num_posts = (array) wp_count_attachments();
            $_total_posts = array_sum( $_num_posts ) - $_num_posts['trash'];
			
            $views['all'] = '<a href="' . $pagenow . '">' . __( 'All', 'wpcasa' ) . ' <span class="count">(' . $_total_posts . ')</span></a>';
            $views['found'] = '<a href="' . $pagenow . '?s=' . $s . '" class="current">' . $s . ' <span class="count">(' . $wp_query->found_posts . ')</span></a>';
		}

        return $views;
    }

    /**
     *	listings_custom_views()
     *
     *	Listing views
     *
     *	@param	array	$views	Incoming views
     *	@uses	$wpdb->prepare()
     *	@uses	$wpdb->get_col()
     *	@return	array	$views	Updated views
     *
     *	@since 1.0.0
     */
    public static function listings_custom_views( $views ) {
        global $wpdb, $wp_query, $pagenow;

        if ( 'edit.php' != $pagenow )
            return;

        // Replace 'Published' with 'Active'

        if( isset( $views['publish'] ) )
            $views['publish'] = str_replace( __( 'Published', 'wpcasa' ), __( 'Active', 'wpcasa' ), $views['publish'] );

        if ( empty( $wp_query->query_vars['s'] ) )
            return $views;

        // Search custom fields for listing ID

        $post_ids_meta = $wpdb->get_col( $wpdb->prepare( "
	    SELECT DISTINCT post_id FROM $wpdb->postmeta
	    WHERE meta_value LIKE %s
	    ", $wp_query->query_vars['s'] ) );

        if ( empty( $post_ids_meta ) )
            return $views;

    }

    /**
     *	 manage_users_columns()
     *
     *	 Add column for number of listings of a user.
     *
     *	 @param		array	$columns	Incoming columns
     *	 @return	array	$columns	Updated columns
     *
     *	 @since 1.0.0
     */
    public static function manage_users_columns( $columns ) {
        $columns['listings_count'] = __( 'Listings', 'wpcasa' );
        return $columns;
    }

    /**
     *	manage_users_custom_column()
     *
     *	Show number of listings the user has
     *
     *	@param	string	$value
     *	@param	string	$column_name
     *	@param	int		$user_id
     *	@uses	count_user_posts()
     *	@uses	wpsight_post_type()
     *	@return	string	new value
     *
     *	@since 1.0.0
     */
    public static function manage_users_custom_column( $value, $column_name, $user_id ) {

        if ( 'listings_count' != $column_name  )
            return $value;

        $listings_count = count_user_posts( $user_id, wpsight_post_type() );
        $user_listings_links = '<a href="edit.php?author=' . $user_id . '&post_type=' . wpsight_post_type() . '">' . $listings_count . '</a>';

        return $user_listings_links;

    }



    /**
     *	check_license()
     *
     *	Check a specific license.
     *
     *	@uses	get_option()
     *	@uses	urlencode()
     *	@uses	home_url()
     *	@uses	wp_remote_post()
     *	@uses	is_wp_error()
     *	@uses	wp_remote_retrieve_body()
     *	@uses	json_decode()
     *	@uses	delete_option()
     *	@return	string	valid|invalid
     *
     *	@since 1.0.0
     */
//    public static function check_license( $id = '', $item = '' ) {
//
//        $licenses = get_option( 'wpsight_licenses' );
//
//        // retrieve the license from the database
//        $license = isset( $licenses[ $id ] ) ? trim( $licenses[ $id ] ) : false;
//
//        $api_params = array(
//            'edd_action'=> 'check_license',
//            'license'	=> $license,
//            'item_name' => urlencode( $item ),
//            'url'       => home_url()
//        );
//
//        // Call the custom API.
//        $response = wp_remote_post( WPSIGHT_SHOP_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
//
//        if ( is_wp_error( $response ) )
//            return false;
//
//        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
////        var_dump($license_data);
//        update_option( 'wpsight_' . $id . '_status', $license_data->license );
//
//
//
//        return $license_data->license;
//
////		if( $license_data->license == 'valid' ) {
////			return 'valid';
////		} else {
////			//delete_option( 'wpsight_' . $id . '_status' );
////			return 'invalid';
////		}
//
//    }

    /**
     *	is_premium()
     *
     *	Check if premium is active.
     *	Premium is at least one active and valid license
     *	which grants access to specific features and support
     *
     *	@uses	get_option()
     *	@uses	wpsight_licenses()
     *	@uses	in_array()
     *	@return	bool	true|false
     *
     *	@since 1.2.0
     */
//    public static function is_premium() {
//        var_dump(wpsight_licenses());
//        foreach( wpsight_licenses() as $id => $license )
//            $keys[$id] = get_transient( 'wpsight_' . $license['id'] )->license;
//
//        if( in_array( 'valid', $keys ) )
//            return true;
//
//        return false;
//
//    }

    /**
     * maybe_set_review_notice_timestamp()
     *
     * Make sure the review notice timestamp exists.
     * This keeps older installations compatible.
     *
     * @uses get_option()
     * @uses current_time()
     * @uses update_option()
     * @return void
     *
     * @since 1.5.0
     */
    public function maybe_set_review_notice_timestamp() {

        $options = get_option( WPSIGHT_DOMAIN, array() );

        if ( ! is_array( $options ) ) {
            $options = array();
        }

        if ( ! empty( $options['review_notice_timestamp'] ) ) {
            return;
        }

        $options['review_notice_timestamp'] = current_time( 'timestamp' );

        update_option( WPSIGHT_DOMAIN, $options );

    }

    /**
     * should_show_review_notice()
     *
     * Check if the review notice should be displayed.
     *
     * @uses current_user_can()
     * @uses get_current_user_id()
     * @uses get_user_meta()
     * @uses wpsight_get_option()
     * @uses current_time()
     * @return bool True if the notice should be shown.
     *
     * @since 1.5.0
     */
    protected function should_show_review_notice() {

        $timestamp = absint( wpsight_get_option( 'review_notice_timestamp', false ) );

        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        if ( get_user_meta( get_current_user_id(), 'wpsight_review_notice_dismissed', true ) ) {
            return false;
        }

        if ( empty( $timestamp ) ) {
            return false;
        }

        return current_time( 'timestamp' ) >= ( $timestamp + ( 7 * DAY_IN_SECONDS ) );

    }

    /**
     * get_review_notice_recipient_name()
     *
     * Get the current user's name for the review notice.
     * Use first name and last name when available.
     * Fall back to display name when first name is empty.
     *
     * @uses wp_get_current_user()
     * @return string Recipient name.
     *
     * @since 1.5.0
     */
    protected function get_review_notice_recipient_name() : string {

        $current_user = wp_get_current_user();
        $first_name   = isset( $current_user->first_name ) ? trim( $current_user->first_name ) : '';
        $display_name = isset( $current_user->display_name ) ? trim( $current_user->display_name ) : '';

        if ( empty( $first_name ) ) {
            return $display_name;
        }

        return $first_name;

    }

    /**
     * notice_review()
     *
     * Show the review notice after the waiting period has passed.
     *
     * @uses self::should_show_review_notice()
     * @uses esc_url()
     * @uses esc_attr__()
     * @uses esc_html__()
     * @uses wp_create_nonce()
     * @uses wp_kses()
     * @return void
     *
     * @since 1.5.0
     */
    public function notice_review() {

        if ( ! $this->should_show_review_notice() ) {
            return;
        }

        $review_url = 'https://wordpress.org/plugins/wpcasa/#reviews';
        $image_url  = WPSIGHT_PLUGIN_URL . '/assets/img/icon.png';
        $user_name  = '<b>' . esc_html__( $this->get_review_notice_recipient_name() ) . '</b>' ;
        $stars_link = sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer" class="wpsight-review-notice__text-link wpsight-review-notice__stars" aria-label="%2$s">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
            esc_url( $review_url ),
            esc_attr__( 'Rate WPCasa on WordPress.org', 'wpcasa' )
        );
        $wporg_link = sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer" class="wpsight-review-notice__text-link">%2$s</a>',
            esc_url( $review_url ),
            esc_html__( 'WordPress.org', 'wpcasa' )
        );
        $message    = sprintf(
            wp_kses(
                __( 'Hi %1$s, you have used this free plugin for some time now, and we hope you like it!<br>The contributors of WPCasa have spent countless hours developing it, and it would mean a lot to us if you could rate WPCasa %2$s on %3$s to help us spread the word.<br>It costs you nothing but helps us a lot. We really appreciate your time!', 'wpcasa' ),
                array(
                    'a'      => array(
                        'aria-label' => array(),
                        'class'      => array(),
                        'href'       => array(),
                        'rel'        => array(),
                        'target'     => array(),
                    ),
                    'b' => array(),
                    'br' => array(),
                )
            ),
            $user_name,
            $stars_link,
            $wporg_link
        );

        echo '<div class="notice notice-info is-dismissible wpsight-review-notice" data-nonce="' . esc_attr( wp_create_nonce( 'wpsight_dismiss_review_notice' ) ) . '">';
        echo '<div class="wpsight-review-notice__inner">';
        echo '<div class="wpsight-review-notice__media">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr__( 'WPCasa', 'wpcasa' ) . '" class="wpsight-review-notice__image" />';
        echo '</div>';
        echo '<div class="wpsight-review-notice__content">';
        echo '<p class="wpsight-review-notice__text">' . $message . '</p>';
        echo '<p class="wpsight-review-notice__actions">';
        echo '<a href="' . esc_url( $review_url ) . '" target="_blank" rel="noopener noreferrer" class="wpsight-review-notice__button"><span class="wpsight-review-notice__button-icon wpsight-review-notice__button-icon-star">&#9733;</span>' . esc_html__( 'Review WPCasa', 'wpcasa' ) . '</a>';
        echo '<button type="button" class="wpsight-review-notice__button wpsight-review-notice__button-dismiss"><span class="wpsight-review-notice__button-icon wpsight-review-notice__button-icon-close">&#x2705;</span>' . esc_html__( "I've already done it", 'wpcasa' ) . '</button>';
        echo '</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        ?>
        <script>
            jQuery( function( $ ) {
                var $notice = $( '.wpsight-review-notice' );
                var dismissNotice = function() {
                    $.post(
                        ajaxurl,
                        {
                            action: 'wpsight_dismiss_review_notice',
                            nonce: $notice.data( 'nonce' )
                        }
                    );
                };

                if ( ! $notice.length ) {
                    return;
                }

                $notice.on( 'click', '.notice-dismiss', function() {
                    dismissNotice();
                } );

                $notice.on( 'click', '.wpsight-review-notice__button-dismiss', function() {
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
     * dismiss_review_notice()
     *
     * Save the dismiss state for the current user.
     *
     * @uses current_user_can()
     * @uses check_ajax_referer()
     * @uses get_current_user_id()
     * @uses current_time()
     * @uses update_user_meta()
     * @uses wp_send_json_error()
     * @uses wp_send_json_success()
     * @return void
     *
     * @since 1.5.0
     */
    public function dismiss_review_notice() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }

        check_ajax_referer( 'wpsight_dismiss_review_notice', 'nonce' );

        update_user_meta( get_current_user_id(), 'wpsight_review_notice_dismissed', current_time( 'timestamp' ) );

        wp_send_json_success();

    }

    /**
     *	notice_setup()
     *
     *	Check if premium is active.
     *	Premium is at least one active and valid license
     *	which grants access to specific features and support
     *
     *	@uses	get_option()
     *	@uses	wpsight_licenses()
     *	@uses	in_array()
     *	@return	bool	true|false
     *
     *	@since 1.2.0
     */
    public static function notice_setup() {

        if( empty( wpsight_get_option( 'listings_page' ) ) ) {

            $link = admin_url() . 'admin.php?page=wpsight-settings#settings-listings';

            echo '<div id="" class="notice notice-warning">';
            echo '<p>' .
            sprintf( 
                wp_kses( 
                            /* translators: %s: is the link */
                            __( '<strong>Welcome to WPCasa</strong> &#8211; You&lsquo;re almost ready. Now go ahead and <a href="%s">setup your main listings page</a> as this is required in order to properly list your properties.', 'wpcasa' ),
                            array( 'strong' => array(), 'a' => array( 'href' => array() ) ) )
                            , esc_url( $link ) ) .
                '</p>';
            echo '</div>';

        }

    }


	/**
	 *	notice_updater()
	 *
	 *	Show a notice if updater is not available
	 *
	 *	@uses	get_option()
	 *	@uses	wpsight_licenses()
	 *	@uses	in_array()
	 *
	 *	@since 1.2.0
	 */
	public static function notice_updater() : void {

		// List of WPCasa paid plugin paths relative to the plugins directory
		$plugins = array(
			'wpcasa-currency-converter/wpcasa-currency-converter.php' => 'WPCasa Currency Converter',
			'wpcasa-dashboard/wpcasa-dashboard.php' => 'WPCasa Dashboard',
			'wpcasa-energy-efficiency/wpcasa-energy-efficiency.php' => 'WPCasa Energy Efficiency',
			'wpcasa-expire-listings/wpcasa-expire-listings.php' => 'WPCasa Expire Listings',
			'wpcasa-favorites/wpcasa-favorites.php' => 'WPCasa Favorites',
			'wpcasa-featured-listings/wpcasa-featured-listings.php' => 'WPCasa Featured Listings',
			'wpcasa-listing-labels/wpcasa-listing-labels.php' => 'WPCasa Listing Labels',
			'wpcasa-listing-pdf/wpcasa-listing-pdf.php' => 'WPCasa Listing PDF',
		);

		// Array to hold active plugins
		$active_plugins = array();

		foreach ( $plugins as $plugin => $name  ) {
			// Check if each plugin is active
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins[ $plugin ] = $name;
			}
		}

		// Output result
		if ( 0 < count( $active_plugins ) && ! class_exists( 'WPSight_EDD_SL_Plugin_Updater' ) ) {
			// At least one of the WPCasa paid plugins is active

			$plugin_names = implode(", ", $active_plugins );

			echo '<div id="" class="notice notice-warning">';
			echo '<p>' .
			     esc_html__( 'To comply with the WordPress guidelines for plugins, we have removed the update functionality for our premium plugins from WPCasa.', 'wpcasa' ) . '<br />' .
			     sprintf(
				       wp_kses(
				       /* translators: %s: is the link */
					       _n( 'As a result, your WPCasa plugin <strong>%1$s will no longer update automatically</strong> when a new version is available. Therefore, you will need to <strong>manually download the latest version of your plugin %1$s</strong> from <a href="https://wpcasa.com/login/" target="_blank">your account on wpcasa.com</a> and upload it to your WordPress website.',
					           'As a result, your WPCasa plugins <strong>%1$s will no longer update automatically</strong> when a new version is available. Therefore, you will need to <strong>manually download the latest version of your plugins %1$s</strong> from <a href="https://wpcasa.com/login/" target="_blank">your account on wpcasa.com</a> and upload it to your WordPress website.',
					           count( $active_plugins ), 'wpcasa' ),
					       array( 'strong' => array(), 'a' => array( 'href' => array() ) ) )
				     , esc_html( $plugin_names ) ) . '<br />' .
			     esc_html__( 'After this, automatic updates will resume as usual. Unfortunately, this action was necessary to ensure that WPCasa remains available on wordpress.org. We appreciate your understanding and continued support.', 'wpcasa' )
			     . '</p>';
			echo '</div>';

		}

	}

    /**
     *
     *	Return array of recommendation data
     *
     * @uses	apply_filters()
     * @return	array
     *
     *	@since 1.2.0
     */
    public static function recommendations() {

        $recommendations = [
            'shortpixel' => [
                'title' =>  __( 'ShortPixel Image Compression', 'wpcasa' ),
                'description' => __( 'Here you can compress your images for free or create your personal ShortPixel account', 'wpcasa' ),
                'image_url' => WPSIGHT_PLUGIN_URL . '/assets/img/wpcasa-recommendation-shortpixel.jpg',
                'button_text' => __( 'ShortPixel', 'wpcasa' ),
                'button_link' => 'https://shortpixel.com/otp/af/95WCWLA889753',
            ]

        ];

        if( ! wpsight_is_premium()) {
			
			$recommendations['premium'] = [
					'title' =>  __( 'Premium', 'wpcasa' ),
					'description' => __( 'Here you can upgrade to premium', 'wpcasa' ),
					'image_url' => WPSIGHT_PLUGIN_URL . '/assets/img/wpcasa-recommendation-premium.jpg',
					'button_text' => __( 'Premium', 'wpcasa' ),
					'button_link' => 'https://wpcasa.com?ref=wpcasa-admin-dashboard',
			];
			
		}
		
        $recommendations = apply_filters( 'wpsight_recommendations', $recommendations );

        return $recommendations;

    }


}
