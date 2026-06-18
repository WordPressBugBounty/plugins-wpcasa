<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'WPSight_About' ) ) :

/**
 * WPSight_About Class
 */
class WPSight_About {

	/**
	 * Handles output of the reports page in admin.
	 */
	public function output() : void {

		/**
		 * About This Version administration panel.
		 *
		 * @package WPCasa
		 * @subpackage Administration
		 */
		
		list( $display_version ) = explode( '-', WPSIGHT_VERSION );
		
		?>
        
			<div class="wpcasa-about wrap full-width-layout">
                        
            	<div class="wrap-inner">
                
				<a href="https://wpcasa.com" target="_blank" class="wp-badge">
                    <?php
                    /* translators: %s: is the current version */
                    printf( esc_html__( 'Version %s', 'wpcasa' ), esc_html( $display_version ) ); ?></a>
                
                <section id="section-intro" class="section section-intro">
                    
                    <div class="section-wrap">
                    
                        <div class="intro-text">
                            <h1><?php
                                /* translators: %s: is the current version */
                                printf( esc_html__( 'Welcome to WPCasa&nbsp;%s', 'wpcasa' ), esc_html( $display_version ) ); ?></h1>
                            <p><?php
                                /* translators: %s: is the current version */
                                printf( esc_html__( 'Thank you for updating to the latest version! WPCasa %s will smooth your user experience and includes new features and improvements.', 'wpcasa' ), esc_html( $display_version ) ); ?></p>
                        </div>              

                        <div class="hero-image">
                            <img src="<?php echo esc_url( WPSIGHT_PLUGIN_URL . '/assets/img/wpcasa-update-1.png' ); ?>" />
                        </div>
                        
                    </div>
                    
                </section>

                <section id="section-changelog" class="section section-changelog">
                
                    <div class="section-wrap">

                        <div class="changelog">
                            
                            <h3><?php echo esc_html__( 'Changelog', 'wpcasa' ) ?></h3>

                            <ul class="tabs" data-tabgroup="first-tab-group">
                                <li class="tab"><a href="#version-1-5-2" class="active">v1.5.2</a></li>
                                <li class="tab"><a href="#version-1-5-1">v1.5.1</a></li>
                                <li class="tab"><a href="#version-1-5-0">v1.5.0</a></li>
                                <li class="tab"><a href="#version-1-4-3">v1.4.3</a></li>
                                <li><a href="https://wordpress.org/plugins/wpcasa/#developers" target="_blank"><?php echo esc_html__( 'More', 'wpcasa' ); ?></a></li>
                            </ul>

                            <section id="first-tab-group" class="tabgroup">
                                <div id="version-1-5-2">
                                    <p>Version: 1.5.2</p>
                                    <table>
                                        <tr>
                                            <td><span class="changelog-entry-new">New</span></td>
                                            <td>Added promotional message for translation.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Added redirect to about page after an update.</td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="version-1-5-1">
                                    <p>Version: 1.5.1</p>
                                    <table>
                                        <tr>
                                            <td><span class="changelog-entry-new">New</span></td>
                                            <td>max_nr attribute for 'wpsight_listings' shortcode to limit listings output without pagination.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Fixed warning 'open_basedir' restriction when no listing was found.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Improved RTL (right-to-left) on WPCasa settings pages in WordPress backend.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Added notice when editing agent information on user page.</td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="version-1-5-0">
                                    <p>Version: 1.5.0</p>
                                    <table>
                                        <tr>
                                            <td><span class="changelog-entry-new">New</span></td>
                                            <td>An option has been added to delete the listings media files when a listing is deleted.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-new">New</span></td>
                                            <td>An admin notice was added for outdated WPCasa themes that require an update to keep the single listing location output working.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-new">New</span></td>
                                            <td>A notice was added inviting users to <a href="https://wordpress.org/plugins/wpcasa/#reviews" target="_blank">review WPCasa</a> on WordPress.org.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-new">New</span></td>
                                            <td>Helpful links were added to the plugin row meta on the plugins screen.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Creating WPCasa user roles is now handled on activation and remove them on deactivation.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Load Google Maps API only when an API key is available</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Improved Google Maps API loading with async callback handling for listing maps</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Improved message handling for discontinued WPCasa add-ons</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>The file uninstall.php was introduced to remove all WPCasa data when uninstalling the plugin.</td>
                                        </tr>                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Improved PHP 8 compatibility</td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="version-1-4-3">
                                    <p>Version: 1.4.3</p>
                                    <table>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Not showing message when no listing is available</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Badge for new add-ons on add-on page</td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="version-1-4-2">
                                    <p>Version: 1.4.2</p>
                                    <table>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Hotfix</span></td>
                                            <td>Vulnerable to cross site scripting (XSS) with shortcodes 'wpsight_listings_map' reported by Muhammad Yudha - DJ at Patchstack</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Hotfix</span></td>
                                            <td>Vulnerable to API code injection reported by mikemyers from Wordfence</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Deprecated message "Creation of dynamic property"</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>"Trying to access array offset on false" on settings page</td>
                                        </tr>
                                    </table>
                                </div>
                            </section>

                            <script type="text/javascript">
                            jQuery(document).ready(function($) {                      
                                $('.tabgroup > div').hide();
                                $('.tabgroup > div:first-of-type').show();
                                $('.tabs .tab a').click(function(e){
                                    e.preventDefault();
                                    var $this = $(this),
                                    tabgroup = '#'+$this.parents('.tabs').data('tabgroup'),
                                    others = $this.closest('.tab').siblings().children('a'),
                                    target = $this.attr('href');
                                    others.removeClass('active');
                                    $this.addClass('active');
                                    $(tabgroup).children('div').hide();
                                    $(target).show();
                                })
                            });
                            </script>

                        </div>

                    </div>
                    
                </section>
                    
			</div>
		<?php
		
	}
}

endif;

return new WPSight_About();
