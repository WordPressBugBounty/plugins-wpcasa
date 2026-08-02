<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPSight_Post_Type_Listing class
 */
class WPSight_Post_Type_Listing {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Register listing post type
		add_action( 'init', array( $this, 'register_post_type_listing' ), 0 );

		// Register listing meta fields for the REST API when enabled.
		add_action( 'init', array( $this, 'register_listing_rest_meta' ), 1 );

		// Hide private listing meta from public REST responses.
		add_filter( 'rest_prepare_' . wpsight_post_type(), array( $this, 'filter_listing_rest_response_meta' ), 10, 3 );
		
		// Register custom post statuses
		add_action( 'init', array( $this, 'register_post_statuses' ), 0 );
        
        // Manage template for listing archive
        
        add_action( 'loop_start', array( $this, 'template_listing_archive' ) );
        add_action( 'loop_end', array( $this, 'template_listing_archive' ) );
        
        // Manage template for single listing
        
        add_action( 'loop_start', array( $this, 'template_listing_single' ) );
        add_action( 'loop_end', array( $this, 'template_listing_single' ) );
        
        // Optionally create location data from front end
//        TODO: delete till wpcasa 1.5
//        add_filter( 'wp', array( $this, 'listing_geolocation' ) );
        
        // Optionally add default meta data to listing
        add_action( 'wp_insert_post', array( $this, 'maybe_add_default_meta' ), 10, 2 );
        
        // Delete old listing previews to clean up
		add_action( 'wpsight_delete_listing_previews', array( $this, 'delete_listing_previews' ) );

		// Optionally delete listing media files on permanent removal.
		add_action( 'before_delete_post', array( 'WPSight_Listings', 'maybe_delete_listing_media' ), 10, 2 );

		// Handle custom statuses in publish box

		foreach ( array( 'post', 'post-new' ) as $hook )
			add_action( "admin_footer-{$hook}.php", array( $this,'custom_statuses_submitdiv' ) );
		
		// Handle single listing print view
		
		add_filter( 'query_vars', array( $this, 'wpsight_print_query_vars' ) );		
		add_action( 'template_redirect', array( $this, 'wpsight_print_redirect' ) );
		
		add_action( 'wpsight_head_print', array( $this, 'wpsight_head_print_css' ) );
		add_filter( 'wpsight_head_print', array( $this, 'wpsight_head_print_robots' ) );

        // Sanitize CPT

		add_filter( 'wp_insert_post_data', array( $this, 'wpsight_sanitize_post_title' ), 10, 2 );

	}

	/**
	 * register_post_type_listing()
	 *
	 * This functions adds the custom post type listing
	 * and all the corresponding taxonomies.
 	 *
 	 * @access public
 	 * @uses apply_filters()
 	 * @uses register_taxonomy()
 	 * @uses wpsight_post_type()
 	 * @uses register_post_type()
 	 *
 	 * @since 1.0.0
	 */
	public function register_post_type_listing() {
	
		// Custom admin capability
		$admin_capability = 'edit_listings';

		// Option value show in REST API true/false
		$show_in_rest = $this->is_rest_api_enabled();

		// Set labels and localize them
	
		$locations_name		= apply_filters( 'wpsight_taxonomy_locations_name', __( 'Locations', 'wpcasa' ) );
		$locations_singular	= apply_filters( 'wpsight_taxonomy_locations_singular', __( 'Location', 'wpcasa' ) );
	
		$locations_labels = array(
			'name' 				 		 => $locations_name,
			'singular_name' 	 		 => $locations_singular,
	        'menu_name' 		 		 => _x( 'Locations', 'taxonomy locations', 'wpcasa' ),
	        'all_items' 		 		 => _x( 'All Locations', 'taxonomy locations', 'wpcasa' ),
	        'edit_item' 		 		 => _x( 'Edit Location', 'taxonomy locations', 'wpcasa' ),
	        'view_item' 		 		 => _x( 'View Location', 'taxonomy locations', 'wpcasa' ),
	        'update_item' 		 		 => _x( 'Update Location', 'taxonomy locations', 'wpcasa' ),
	        'add_new_item' 		 		 => _x( 'Add New Location', 'taxonomy locations', 'wpcasa' ),
	        'new_item_name' 	 		 => _x( 'New Location Name', 'taxonomy locations', 'wpcasa' ),
	        'parent_item'  		 		 => _x( 'Parent Location', 'taxonomy locations', 'wpcasa' ),
	        'parent_item_colon'  		 => _x( 'Parent Location:', 'taxonomy locations', 'wpcasa' ),
	        'search_items' 		 		 => _x( 'Search locations', 'taxonomy locations', 'wpcasa' ),
	        'popular_items' 	 		 => _x( 'Popular Locations', 'taxonomy locations', 'wpcasa' ),
	        'separate_items_with_commas' => _x( 'Separate locations with commas', 'taxonomy locations', 'wpcasa' ),
	        'add_or_remove_items' 		 => _x( 'Add or remove locations', 'taxonomy locations', 'wpcasa' ),
	        'choose_from_most_used' 	 => _x( 'Choose from the most used locations', 'taxonomy locations', 'wpcasa' ),
	        'not_found' 		 		 => _x( 'No location found', 'taxonomy locations', 'wpcasa' )
		);
		
		// Set args and rewrite rules
	
		$locations_args = array(
			'labels' 		=> $locations_labels,
			'hierarchical' 	=> true,
			'capabilities'  => array(
				'manage_terms' => 'manage_listing_terms',
	            'edit_terms'   => 'edit_listing_terms',
	            'delete_terms' => 'delete_listing_terms',
				'assign_terms' => 'assign_listing_terms'
			),
			'rewrite' 		=> array(
				'slug' 		   => apply_filters( 'wpsight_rewrite_loctions_slug', 'location' ),
				'with_front'   => false,
				'hierarchical' => true
			),
			'show_in_rest'          => $show_in_rest,
			'rest_base'             => 'wpsight-' . apply_filters( 'wpsight_rewrite_loctions_slug', 'location' ),
			'rest_controller_class' => 'WP_REST_Terms_Controller'
		);
		
		$locations_args = apply_filters( 'wpsight_taxonomy_locations_args', $locations_args );
		
		// Register taxonomy		
		register_taxonomy( 'location', array( wpsight_post_type() ), $locations_args );
	
		// Set labels and localize them
		
		$types_name		= apply_filters( 'wpsight_taxonomy_types_name', __( 'Listing Types', 'wpcasa' ) );
		$types_singular	= apply_filters( 'wpsight_taxonomy_types_singular', __( 'Listing Type', 'wpcasa' ) );
		
		$types_labels = array(
			'name' 						 => $types_name,
			'singular_name' 			 => $types_singular,
			'menu_name' 		 		 => _x( 'Listing Types', 'taxonomy types', 'wpcasa' ),
	        'all_items' 		 		 => _x( 'All Listing Types', 'taxonomy types', 'wpcasa' ),
	        'edit_item' 		 		 => _x( 'Edit Listing Type', 'taxonomy types', 'wpcasa' ),
	        'view_item' 		 		 => _x( 'View Listing Type', 'taxonomy types', 'wpcasa' ),
	        'update_item' 		 		 => _x( 'Update Listing Type', 'taxonomy types', 'wpcasa' ),
	        'add_new_item' 		 		 => _x( 'Add New Listing Type', 'taxonomy types', 'wpcasa' ),
	        'new_item_name' 	 		 => _x( 'New Listing Type Name', 'taxonomy types', 'wpcasa' ),
	        'parent_item'  		 		 => _x( 'Parent Listing Type', 'taxonomy types', 'wpcasa' ),
	        'parent_item_colon'  		 => _x( 'Parent Listing Type', 'taxonomy types', 'wpcasa' ).':',
	        'search_items' 		 		 => _x( 'Search listing types', 'taxonomy types', 'wpcasa' ),
	        'popular_items' 	 		 => _x( 'Popular Listing Types', 'taxonomy types', 'wpcasa' ),
	        'separate_items_with_commas' => _x( 'Separate listing types with commas', 'taxonomy types', 'wpcasa' ),
	        'add_or_remove_items' 		 => _x( 'Add or remove listing types', 'taxonomy types', 'wpcasa' ),
	        'choose_from_most_used' 	 => _x( 'Choose from the most used listing types', 'taxonomy types', 'wpcasa' ),
	        'not_found' 		 		 => _x( 'No listing type found', 'taxonomy types', 'wpcasa' )
		);
		
		// Set args and rewrite rules
	
		$types_args = array(
			'labels' 	   => $types_labels,
			'hierarchical' => false,
			'capabilities'  => array(
				'manage_terms' => 'manage_listing_terms',
	            'edit_terms'   => 'edit_listing_terms',
	            'delete_terms' => 'delete_listing_terms',
				'assign_terms' => 'assign_listing_terms'
			),
			'rewrite' 	   => array( 
				'slug' 		 => apply_filters( 'wpsight_rewrite_types_slug', 'type' ),
				'with_front' => false
			),
			'show_in_rest'          => $show_in_rest,
			'rest_base'             => 'wpsight-' . apply_filters( 'wpsight_rewrite_types_slug', 'type' ),
			'rest_controller_class' => 'WP_REST_Terms_Controller'
		);
		
		$types_args = apply_filters( 'wpsight_taxonomy_types_args', $types_args );
		
		// Register taxonomy		
		register_taxonomy( 'listing-type', array( wpsight_post_type() ), $types_args );
	
		// Set labels and localize them
		
		$features_name	   = apply_filters( 'wpsight_taxonomy_features_name', __( 'Features', 'wpcasa' ) );
		$features_singular = apply_filters( 'wpsight_taxonomy_features_singular', __( 'Feature', 'wpcasa' ) );
		
		$features_labels = array(
			'name' 						 => $features_name,
			'singular_name' 			 => $features_singular,
			'menu_name' 		 		 => _x( 'Features', 'taxonomy features', 'wpcasa' ),
	        'all_items' 		 		 => _x( 'All Features', 'taxonomy features', 'wpcasa' ),
	        'edit_item' 		 		 => _x( 'Edit Feature', 'taxonomy features', 'wpcasa' ),
	        'view_item' 		 		 => _x( 'View Feature', 'taxonomy features', 'wpcasa' ),
	        'update_item' 		 		 => _x( 'Update Feature', 'taxonomy features', 'wpcasa' ),
	        'add_new_item' 		 		 => _x( 'Add New Feature', 'taxonomy features', 'wpcasa' ),
	        'new_item_name' 	 		 => _x( 'New Feature Name', 'taxonomy features', 'wpcasa' ),
	        'parent_item'  		 		 => _x( 'Parent Feature', 'taxonomy features', 'wpcasa' ),
	        'parent_item_colon'  		 => _x( 'Parent Feature:', 'taxonomy features', 'wpcasa' ),
	        'search_items' 		 		 => _x( 'Search features', 'taxonomy features', 'wpcasa' ),
	        'popular_items' 	 		 => _x( 'Popular Features', 'taxonomy features', 'wpcasa' ),
	        'separate_items_with_commas' => _x( 'Separate features with commas', 'taxonomy features', 'wpcasa' ),
	        'add_or_remove_items' 		 => _x( 'Add or remove features', 'taxonomy features', 'wpcasa' ),
	        'choose_from_most_used' 	 => _x( 'Choose from the most used features', 'taxonomy features', 'wpcasa' ),
	        'not_found' 		 		 => _x( 'No feature found', 'taxonomy features', 'wpcasa' )
		);
		
		// Set args and rewrite rules
	
		$features_args = array(
			'labels' 	   => $features_labels,
			'hierarchical' => false,
			'capabilities'  => array(
				'manage_terms' => 'manage_listing_terms',
	            'edit_terms'   => 'edit_listing_terms',
	            'delete_terms' => 'delete_listing_terms',
				'assign_terms' => 'assign_listing_terms'
			),
			'rewrite' 	   => array(
				'slug' 		 => apply_filters( 'wpsight_rewrite_features_slug', 'feature' ),
				'with_front' => false
			),
			'show_in_rest'          => $show_in_rest,
			'rest_base'             => 'wpsight-' . apply_filters( 'wpsight_rewrite_features_slug', 'feature' ),
			'rest_controller_class' => 'WP_REST_Terms_Controller'
		);
		
		$features_args = apply_filters( 'wpsight_taxonomy_features_args', $features_args );
		
		// Register taxonomy		
		register_taxonomy( 'feature', array( wpsight_post_type() ), $features_args );
	
		// Set labels and localize them
		
		$categories_name	 = apply_filters( 'wpsight_taxonomy_categories_name', __( 'Categories', 'wpcasa' ) );
		$categories_singular = apply_filters( 'wpsight_taxonomy_categories_singular', __( 'Category', 'wpcasa' ) );	
		
		$categories_labels = array(
			'name' 			=> $categories_name,
			'singular_name' => $categories_singular
		);
		
		// Set args and rewrite rules
	
		$categories_args = array(
			'labels' 	   => $categories_labels,
			'hierarchical' => true,
			'capabilities'  => array(
				'manage_terms' => 'manage_listing_terms',
	            'edit_terms'   => 'edit_listing_terms',
	            'delete_terms' => 'delete_listing_terms',
				'assign_terms' => 'assign_listing_terms'
			),
			'rewrite' 	   => array( 
				'slug' 		   => apply_filters( 'wpsight_rewrite_categories_slug', 'listing-category' ), 
				'with_front'   => false,
				'hierarchical' => true
			),
			'show_in_rest'          => $show_in_rest,
			'rest_base'             => 'wpsight-' . apply_filters( 'wpsight_rewrite_categories_slug', 'listing-category' ),
			'rest_controller_class' => 'WP_REST_Terms_Controller'
		);
		
		$categories_args = apply_filters( 'wpsight_taxonomy_categories_args', $categories_args );
		
		// Register taxonomy		
		register_taxonomy( 'listing-category', array( wpsight_post_type() ), $categories_args );
		
		// Set post type labels

		$labels = array( 
		    'name' 				 => _x( 'Listings', 'listing', 'wpcasa' ),
		    'singular_name' 	 => _x( 'Listing', 'listing', 'wpcasa' ),
		    'add_new' 			 => _x( 'Add New', 'listing', 'wpcasa' ),
		    'add_new_item' 		 => _x( 'Add New Listing', 'listing', 'wpcasa' ),
		    'edit_item' 		 => _x( 'Edit Listing', 'listing', 'wpcasa' ),
		    'new_item' 			 => _x( 'New Listing', 'listing', 'wpcasa' ),
		    'view_item' 		 => _x( 'View Listing', 'listing', 'wpcasa' ),
		    'search_items' 		 => _x( 'Search Listings', 'listing', 'wpcasa' ),
		    'not_found' 		 => _x( 'No listings found', 'listing', 'wpcasa' ),
		    'not_found_in_trash' => _x( 'No listings found in Trash', 'listing', 'wpcasa' ),
		    'menu_name' 		 => _x( 'Listings', 'listing', 'wpcasa' ),
		);
		
		$labels = apply_filters( 'wpsight_post_type_labels_listing', $labels );
		
		// Set post type arguments

		$args = array(
			'label'                 => _x( 'Listings', 'listing', 'wpcasa' ),
			'description'           => _x( 'Searchable listings with detailed information about the corresponding item.', 'listing', 'wpcasa' ),
		    'labels' 			    => $labels,
		    'hierarchical' 		    => false,
		    'supports' 			    => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'revisions', 'excerpt' ),
		    'public' 			    => true,
		    'show_ui' 			    => true,
		    'show_in_menu' 		    => true,
		    'show_in_nav_menus'     => true,
			'menu_position' 	    => 50,
		    'menu_icon'			    => 'dashicons-location',
		    'publicly_queryable'    => true,
		    'exclude_from_search'   => false,
		    'has_archive' 		    => true,
		    'query_var' 		    => true,
		    'can_export' 		    => true,
		    'rewrite' 			    => array( 'slug' => apply_filters( 'wpsight_rewrite_listings_slug', 'listing' ), 'with_front' => false ),
		    'capability_type' 	    => array( 'listing', 'listings' ),
		    'map_meta_cap'		    => true,
			'show_in_rest'          => $show_in_rest,
			'rest_base'             => 'wpsight-' . apply_filters( 'wpsight_rewrite_listings_slug', 'listing' ) ,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		$args = apply_filters( 'wpsight_post_type_args_listing', $args );
		
		// Register post type		
		register_post_type( 'listing', $args );
    	
	}

	/**
	 * is_rest_api_enabled()
	 *
	 * Check if listings should be exposed in the REST API.
	 *
	 * @access protected
	 * @uses wpsight_get_option()
	 * @uses apply_filters()
	 * @return bool True when listing REST support is enabled.
	 *
	 * @since 1.5.4
	 */
	protected function is_rest_api_enabled() : bool {
		return (bool) apply_filters( 'wpsight_show_in_rest', wpsight_get_option( 'listings_rest_api' ) );
	}

	/**
	 * register_listing_rest_meta()
	 *
	 * Register WPCasa listing meta fields for the REST API.
	 *
	 * @access public
	 * @uses register_post_meta()
	 * @uses wpsight_post_type()
	 * @uses apply_filters()
	 *
	 * @since 1.5.4
	 */
	public function register_listing_rest_meta() {
		if ( ! $this->is_rest_api_enabled() ) {
			return;
		}

		foreach ( $this->get_listing_rest_meta_fields() as $meta_key => $args ) {
			register_post_meta(
				wpsight_post_type(),
				$meta_key,
				wp_parse_args(
					$args,
					array(
						'single'            => true,
						'auth_callback'     => array( $this, 'listing_rest_meta_auth_callback' ),
						'show_in_rest'      => array(
							'schema' => array(
								'type' => 'string',
							),
						),
						'sanitize_callback' => 'sanitize_text_field',
					)
				)
			);
		}
	}

	/**
	 * get_listing_rest_meta_fields()
	 *
	 * Return WPCasa listing meta fields exposed through the REST API.
	 *
	 * @access protected
	 * @uses wpsight_details()
	 * @uses apply_filters()
	 * @return array REST meta field definitions.
	 *
	 * @since 1.5.4
	 */
	protected function get_listing_rest_meta_fields() : array {
		$string_field = array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
			'sanitize_callback' => 'sanitize_text_field',
		);

		$textarea_field = array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
			'sanitize_callback' => 'sanitize_textarea_field',
		);

		$boolean_field = array(
			'type'              => 'boolean',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'boolean',
				),
			),
			'sanitize_callback' => 'rest_sanitize_boolean',
		);

		$integer_field = array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'integer',
				),
			),
			'sanitize_callback' => 'absint',
		);

		$url_field = array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'   => 'string',
					'format' => 'uri',
				),
			),
			'sanitize_callback' => 'esc_url_raw',
		);

		$map_field = array(
			'single'            => true,
			'type'              => 'object',
			'show_in_rest'      => array(
				'schema' => array(
					'type'                 => 'object',
					'properties'           => array(
						'lat'  => array(
							'type' => 'string',
						),
						'long' => array(
							'type' => 'string',
						),
					),
					'additionalProperties' => array(
						'type' => 'string',
					),
				),
			),
			'sanitize_callback' => array( $this, 'sanitize_listing_rest_meta_map' ),
		);

		$gallery_field = array(
			'single'            => true,
			'type'              => 'object',
			'show_in_rest'      => array(
				'schema' => array(
					'type'                 => 'object',
					'additionalProperties' => array(
						'type' => 'string',
					),
				),
			),
			'sanitize_callback' => array( $this, 'sanitize_listing_rest_meta_gallery' ),
		);

		$fields = array(
			'_listing_id'            => $string_field,
			'_listing_not_available' => $boolean_field,
			'_listing_sticky'        => $boolean_field,
			'_listing_featured'      => $boolean_field,
			'_gallery'               => $gallery_field,
			'_price'                 => array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => array(
					'schema' => array(
						'type' => 'string',
					),
				),
				'sanitize_callback' => array( 'WPSight_Meta_Boxes', 'sanitize_meta_box_listing_price' ),
			),
			'_price_offer'           => $string_field,
			'_price_period'          => $string_field,
			'_map_address'           => $string_field,
			'_map_geolocation'       => $map_field,
			'_map_note'              => $string_field,
			'_map_secret'            => $textarea_field,
			'_map_hide'              => $boolean_field,
			'_map_type'              => $string_field,
			'_map_zoom'              => $integer_field,
			'_map_no_streetview'     => $boolean_field,
			'_geolocation_lat'       => $string_field,
			'_geolocation_long'      => $string_field,
			'_geolocation_elevation' => $string_field,
			'_geolocation_city'      => $string_field,
			'_geolocation_country_long'  => $string_field,
			'_geolocation_country_short' => $string_field,
			'_geolocation_formatted_address' => $string_field,
			'_geolocation_state_long'    => $string_field,
			'_geolocation_state_short'   => $string_field,
			'_geolocation_street'    => $string_field,
			'_geolocation_zipcode'   => $string_field,
			'_geolocation_postcode'  => $string_field,
			'_agent_name'            => $string_field,
			'_agent_company'         => $string_field,
			'_agent_description'     => $textarea_field,
			'_agent_phone'           => $string_field,
			'_agent_website'         => $url_field,
			'_agent_twitter'         => $string_field,
			'_agent_facebook'        => $string_field,
			'_agent_logo'            => $url_field,
			'_agent_logo_id'         => $integer_field,
		);

		foreach ( wpsight_details() as $detail ) {
			if ( ! empty( $detail['id'] ) ) {
				$fields[ '_' . $detail['id'] ] = $string_field;
			}
		}

		return apply_filters( 'wpsight_listing_rest_meta_fields', $fields );
	}

	/**
	 * listing_rest_meta_auth_callback()
	 *
	 * Limit protected listing meta access to users who can edit the listing.
	 *
	 * @access public
	 *
	 * @param bool   $allowed   Whether access is allowed.
	 * @param string $meta_key  Meta key being checked.
	 * @param int    $object_id Object ID being checked.
	 * @param int    $user_id   User ID being checked.
	 *
	 * @return bool True when the user may access listing meta.
	 *
	 * @since 1.5.4
	 */
	public function listing_rest_meta_auth_callback( bool $allowed, string $meta_key, int $object_id, int $user_id ) : bool {
		if ( ! $this->is_rest_api_enabled() ) {
			return false;
		}

		if ( ! empty( $object_id ) ) {
			return user_can( $user_id, 'edit_post', $object_id );
		}

		return user_can( $user_id, 'edit_listings' );
	}

	/**
	 * filter_listing_rest_response_meta()
	 *
	 * Remove private WPCasa meta from REST responses for users who cannot edit the listing.
	 *
	 * @access public
	 *
	 * @param WP_REST_Response $response REST response object.
	 * @param WP_Post          $post     Listing post object.
	 * @param WP_REST_Request  $request  REST request object.
	 *
	 * @return WP_REST_Response Filtered REST response object.
	 *
	 * @since 1.5.4
	 */
	public function filter_listing_rest_response_meta( WP_REST_Response $response, WP_Post $post, $request ) {
		if ( ! $this->is_rest_api_enabled() || current_user_can( 'edit_post', $post->ID ) ) {
			return $response;
		}

		$data = $response->get_data();

		if ( empty( $data['meta'] ) || ! is_array( $data['meta'] ) ) {
			return $response;
		}

		foreach ( array_keys( $this->get_listing_rest_meta_fields() ) as $meta_key ) {
			unset( $data['meta'][ $meta_key ] );
		}

		$response->set_data( $data );

		return $response;
	}

	/**
	 * sanitize_listing_rest_meta_map()
	 *
	 * Sanitize map coordinates from REST meta requests.
	 *
	 * @access public
	 * @param mixed $value Submitted meta value.
	 * @return array Sanitized map data.
	 *
	 * @since 1.5.4
	 */
	public function sanitize_listing_rest_meta_map( $value ) : array {
		$value = is_array( $value ) ? $value : array();
		$map   = array();

		foreach ( $value as $key => $map_value ) {
			if ( ! is_scalar( $map_value ) ) {
				continue;
			}

			$map[ sanitize_key( $key ) ] = sanitize_text_field( $map_value );
		}

		return $map;
	}

	/**
	 * sanitize_listing_rest_meta_gallery()
	 *
	 * Sanitize gallery attachment IDs and URLs from REST meta requests.
	 *
	 * @access public
	 * @param mixed $value Submitted meta value.
	 * @return array Sanitized gallery data.
	 *
	 * @since 1.5.4
	 */
	public function sanitize_listing_rest_meta_gallery( $value ) : array {
		$value   = is_array( $value ) ? $value : array();
		$gallery = array();

		foreach ( $value as $attachment_id => $url ) {
			$attachment_id = absint( $attachment_id );

			if ( ! $attachment_id || ! is_scalar( $url ) ) {
				continue;
			}

			$gallery[ $attachment_id ] = esc_url_raw( $url );
		}

		return $gallery;
	}
	
	/**
	 * register_post_statuses()
	 *
	 * Register custom post statuses.
 	 *
 	 * @access public
 	 * @uses wpsight_statuses()
 	 * @uses register_post_status()
 	 *
 	 * @since 1.0.0
	 */
	public function register_post_statuses() {
	    global $wp_post_statuses;
	    
	    foreach( wpsight_statuses() as $status => $args ) {
		    
		    if( ! in_array( $status, array_keys( $wp_post_statuses ) ) )		    
	    		register_post_status( $status, $args );
	    	
	    }
	    
    }
	
	/**
	 * template_listing_archive()
	 *
	 * Replace default output on listing archive
	 * pages (taxonomies, search or agents) with
	 * our templated output.
 	 *
 	 * @access public
 	 *
 	 * @param object $query WP_Query of the corresponding loop
 	 *
 	 * @uses $query->is_main_query()
 	 * @uses wpsight_is_listings_archive()
 	 * @uses current_filter()
 	 * @uses wpsight_listings()
 	 *
 	 * @since 1.0.0
	 */
	public function template_listing_archive( object $query ) {
		
		// Make sure this is a main query
		
		if ( ! $query->is_main_query() )
			return;
			
		// Only on listing archives
		
		if( wpsight_is_listings_archive() ) {
			
			// Remove original output
		
			if ( 'loop_start' === current_filter() ) {
			    ob_start();
			} else {
				ob_end_clean();
			}
			
			// Create custom loop output
			
			// Action before listing archive template is called
			do_action( 'wpsight_template_listing_archive_before', $query );
			
			// Get listings for this query	
			wpsight_listings( $query );
			
			// Action after listing archive template is called
			do_action( 'wpsight_template_listing_archive_after', $query );
		
		}
		
	}
	
	/**
	 * template_listing_single()
	 *
	 * Replace default output on single listing
	 * pages with our templated output.
 	 *
 	 * @access public
 	 *
 	 * @param object $query WP_Query of the corresponding loop
 	 *
 	 * @uses $query->is_main_query()
 	 * @uses wpsight_is_listing_archive()
 	 * @uses current_filter()
 	 * @uses wpsight_listing()
 	 *
 	 * @since 1.0.0
	 */
	public function template_listing_single( object $query ) {
		
		// Make sure this is a main query
		
		if ( ! $query->is_main_query() )
			return;
		
		// If we have a single-listing.php template and widgets are active, display them
		
		if( wpsight_locate_template( 'single-listing.php' ) && ( is_active_sidebar( 'listing' ) || is_active_sidebar( 'listing-top' ) || is_active_sidebar( 'listing-bottom' ) ) )
			return;
		
		// Only on single listing pages
		
		if( is_singular( wpsight_post_type() ) ) {
			
			// Remove original output
		
			if ( 'loop_start' === current_filter() ) {
			    ob_start();
			} else {
				ob_end_clean();
			}
			
			// Create custom loop output
			
			// Action before listing archive template is called
			do_action( 'wpsight_template_listing_single_before', $query );
			
			// Get single listing
			wpsight_listing( $query->post->ID );
			
			// Action after listing archive template is called
			do_action( 'wpsight_template_listing_single_after', $query );
		
		}
		
	}
	
	/**
	 * listing_geolocation()
	 *
	 * If a listing is called on the front end,
	 * check if we need to create geolocation data
	 * to ensure backwards compatibiliy with older
	 * WPCasa versions.
 	 *
 	 * @access public
 	 * @uses wpsight_is_listing_single()
 	 * @uses get_post_meta()
 	 * @uses wpSight_Geocode::has_location_data()
 	 * @uses wpSight_Geocode::generate_location_data()
 	 * 
 	 * @since 1.0.0
	 */
    //TODO: delete till wpcasa 1.5
//	public function listing_geolocation() {
//
//		if( ! wpsight_is_listing_single() || is_admin() )
//			return;
//
//		// Update map location information
//		if ( ! wpSight_Geocode::has_location_data( get_the_id() ) && ( $location = get_post_meta( get_the_id(), '_map_address', true ) ) ) {
//			wpSight_Geocode::generate_location_data( get_the_id(), $location );
//		}
//
//	}
	
	/**
	 * maybe_add_default_meta()
	 *
	 * Optionally create default post meta data
	 * when a post is created or saved.
	 *
	 * @access public
	 *
	 * @param int    $post_id Post ID of the corresponding entry
	 * @param object $post    WP_Post object
	 *
	 * @uses wpsight_post_type()
	 * @uses add_post_meta()
	 *
	 * @since 1.0.0
	 */
	public function maybe_add_default_meta( int $post_id, $post = '' ) {
		if ( empty( $post ) || wpsight_post_type() == $post->post_type ) {
			add_post_meta( $post_id, '_listing_not_available', 0, true );
			add_post_meta( $post_id, '_listing_sticky', 0, true );
			add_post_meta( $post_id, '_listing_featured', 0, true );
		}
	}
	
	/**
	 * delete_listing_previews()
	 *
	 * Delete old listings with preview status when active.
	 *
	 * @access public
	 * @uses wpsight_delete_listing_previews()
	 * @see /functions/wpsight-listings.php
	 *
	 * @since 1.0.0
	 */
	public function delete_listing_previews() {
		
		// Delete old listing previews if desired
		if ( apply_filters( 'wpsight_delete_listing_previews', true ) )
			wpsight_delete_listing_previews();
		
	}
    
    /**
	 * custom_statuses_submitdiv()
	 *
	 * Add custom listing statuses to
	 * publish box on listing edit page.
	 *
	 * @access public
	 * @uses wpsight_post_type()
	 * @uses wpsight_statuses()
	 *
	 * @since 1.0.0
	 */
    public function custom_statuses_submitdiv() {
		global $post, $post_type;

		// Only on listing edit pages

		if ( wpsight_post_type() !== $post_type )
			return;
		
		// Get custom statuses
		$statuses = wpsight_statuses();

		// Get all non-builtin post status and add them as <option>

		$options = $display = '';

		foreach ( $statuses as $status => $args ) {

			$selected = selected( $post->post_status, $status, false );
			$name = $args['label'];

			// If one of our custom post status is selected, remember it
			$selected AND $display = $name;

			// Build the options
			$options .= "<option{$selected} value='{$status}'>{$name}</option>";

		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) { //#TEST

				postStatus = $('#post_status');
				status = '<?php echo esc_html( $post->post_status ); ?>';
				
				save_expired = '<?php echo esc_html_x( 'Save as Expired', 'listing post status', 'wpcasa' ); ?>';
				save_preview = '<?php echo esc_html_x( 'Save as Preview', 'listing post status', 'wpcasa' ); ?>';
				save_pending = '<?php echo esc_html_x( 'Save as Pending', 'listing post status', 'wpcasa' ); ?>';

				<?php if ( ! empty( $display ) ) : ?>
                $( '#post-status-display' ).html( '<?php echo esc_html( $display ) ?>' );
				<?php endif; ?>
				
				if ( status == 'pending_payment' )
					$('#save-post').show().val( save_pending );
				
				if ( status == 'expired' )
					$('#save-post').show().val( save_expired );
				
				if ( status == 'preview' )
					$('#save-post').show().val( save_preview );

				var select = $( '#post-status-select' ).find( 'select' );
				$( select ).html( "<?php echo wp_kses( $options, array( 'option' => array(
                    'selected' => array(),
                    'value'    => array(),
                    'disabled' => array(),
                    'label'    => array(),
                ) ) ) ?>" );

				$('#post-status-select').find('.save-post-status').click( function( event ) {
					
					if( $('option:selected', postStatus).val() == 'pending_payment' )
						$('#save-post').show().val( save_pending );

					if ( $('option:selected', postStatus).val() == 'expired' )
						$('#save-post').show().val( save_expired );
					
					if( $('option:selected', postStatus).val() == 'preview' )
						$('#save-post').show().val( save_preview );

				});

			} );
		</script>
		<?php
	}
	
	/**
	 * wpsight_print_query_vars()
	 *
	 * Add print to query vars.
	 *
	 * @return array
 	 *
 	 * @since 1.0.0
	 */
	public function wpsight_print_query_vars( $vars ) {
	
	    $new_vars = array( 'print' );
	    $vars = array_merge( $new_vars, $vars );
	    
	    return $vars;
	}
	
	/**
	 * wpsight_print_redirect()
	 *
	 * Redirect to print view template when
	 * query var 'print' is set.
	 *
	 * @uses wpsight_get_template()
 	 *
 	 * @since 1.0.0
	 */	
	function wpsight_print_redirect() {	    
	    global $wp, $wp_query;
	    
	    if( isset( $wp->query_vars['print'] ) && absint( $wp->query_vars['print'] ) ) {
	        wpsight_get_template( 'listing-print.php' );
	        exit();
	    }

	}
	
	/**
	 * wpsight_head_print_css()
	 *
	 * Add print styles to print header
	 * using wpsight_head_print action hook.
	 *
	 * @since 1.0.0
	 */
	function wpsight_head_print_css() {
		wp_styles();
		wp_enqueue_style( 'wpsight-print', WPSIGHT_PLUGIN_URL . '/assets/css/wpsight-print.css', '', WPSIGHT_VERSION );
		wp_print_styles();
	}
	
	/**
	 * wpsight_head_print_robots()
	 *
	 * Disallow indexing of print pages
	 * using robots:noindex.
	 *
	 * @since 1.0.0
	 */	
	function wpsight_head_print_robots() {	?>
	<meta name="robots" content="noindex" />
	<?php
	}

	/**
	 * wpsight_sanitize_post_title()
	 *
	 * This functions sanitizes the custom post type listing
	 * title to prevent XSS and script in the title
	 *
	 * @access public
	 * @uses wp_kses()
	 *
	 * @since 1.3.1
	 */
	public function wpsight_sanitize_post_title( $data, $postarr ) {

		if( 'listing' == $data['post_type'] ) {

			if( ! empty( $data['post_title'] ) ) {

				$data['post_title'] = wp_kses( $data['post_title'], 'post' );

			}

		}

		return $data;
	}


}
