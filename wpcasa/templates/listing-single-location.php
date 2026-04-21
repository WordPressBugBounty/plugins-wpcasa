<?php if ( ! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Template: Single Listing Location
 */
global $listing;

$lat  = get_post_meta( $listing->ID, '_geolocation_lat', true );
$long = get_post_meta( $listing->ID, '_geolocation_long', true );

$hide                  = get_post_meta( $listing->ID, '_map_hide', true );
$has_google_maps_key   = ! empty( wpsight_get_option( 'google_maps_api_key' ) );

if( $lat && $long && ! $hide ) { ?>

	<div class="wpsight-listing-section wpsight-listing-section-location">
		
		<?php if ( $has_google_maps_key ) : ?>
		<style>
	      #map-canvas {
	        width: 100%;
	        height: 400px;
	      }
	      #map-canvas img {
		      max-width: none;
	      }
	    </style>
	    <?php
		    
		    // Set map default options
		    
		    $map_defaults = array(
				'map_type'		=> 'ROADMAP',
				'control_type'	=> 'true',
				'control_nav'	=> 'true',
				'scrollwheel'	=> 'false',
				'streetview'	=> 'true',
				'map_zoom'		=> '14'
		    );
		    
		    // Get map listing options

		    $map_options = array(
			    '_map_type' 			=> get_post_meta( $listing->ID, '_map_type', true ),
			    '_map_zoom' 			=> get_post_meta( $listing->ID, '_map_zoom', true ),
			    '_map_no_streetview' 	=> get_post_meta( $listing->ID, '_map_no_streetview', true )
		    );
		    
		    $map_args = array(
			    'map_type' 		=> ! empty( $map_options['_map_type'] ) ? $map_options['_map_type'] : $map_defaults['map_type'],
			    'map_zoom' 		=> ! empty( $map_options['_map_zoom'] ) ? $map_options['_map_zoom'] : $map_defaults['map_zoom'],
			    'streetview'	=> ! empty( $map_options['_map_no_streetview'] ) ? 'false' : 'true'
		    );

			// Parse map args and apply filter		    
		    $map_args = apply_filters( 'wpsight_listing_map_args', wp_parse_args( $map_args, $map_defaults ) );
		    
		?>
	    <script>
	      function initialize() {
			  var myLatlng = new google.maps.LatLng(<?php echo esc_js( $lat ); ?>,<?php echo esc_js( $long ); ?>);
			  var mapOptions = {
			    zoom: 				<?php echo esc_js( $map_args['map_zoom'] ); ?>,
			    mapTypeId: 			google.maps.MapTypeId.<?php echo esc_js( $map_args['map_type'] ); ?>,
			    mapTypeControl: 	<?php echo esc_js( $map_args['control_type'] ); ?>,
			    navigationControl: 	<?php echo esc_js( $map_args['control_nav'] ); ?>,
			    scrollwheel: 		<?php echo esc_js( $map_args['scrollwheel'] ); ?>,
			    streetViewControl: 	<?php echo esc_js( $map_args['streetview'] ); ?>,
			    center: myLatlng
			  }
			  var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
			
			  var marker = new google.maps.Marker({
			      position: myLatlng,
			      map: map,
			      title: '<?php echo esc_attr( $listing->post_title ); ?>'
			  });
			}
			
			if ( 'function' === typeof window.wpsightOnGoogleMapsReady ) {
				window.wpsightOnGoogleMapsReady( initialize );
			} else if ( window.google && window.google.maps ) {
				initialize();
			}
	    </script>
	    <?php endif; ?>
	    
	    <div itemprop="availableAtOrFrom" itemscope itemtype="https://schema.org/Place">
		
			<?php do_action( 'wpsight_listing_single_location_before', $listing->ID ); ?>
			
			<div class="wpsight-listing-location" itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates">
			
				<?php if ( $has_google_maps_key ) : ?>
				<div id="map-canvas"></div>
				<?php endif; ?>
				
				<meta itemprop="latitude" content="<?php echo esc_attr( $lat ); ?>" />
				<meta itemprop="longitude" content="<?php echo esc_attr( $long ); ?>" />
				
				<?php if( ! empty( $listing->_map_note ) && $has_google_maps_key ) : ?>
				<div class="wpsight-listing-location-note">
					<?php echo wp_kses_post( $listing->_map_note ); ?>
				</div>
				<?php endif; ?>
				
			</div>
			
			<?php do_action( 'wpsight_listing_single_location_after', $listing->ID ); ?>
		
	    </div>
	
	</div><!-- .wpsight-listing-section -->

<?php } // endif $location ?>
