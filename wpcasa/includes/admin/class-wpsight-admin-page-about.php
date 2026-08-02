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
                                <li class="tab"><a href="#version-1-5-4" class="active">v1.5.4</a></li>
                                <li class="tab"><a href="#version-1-5-3">v1.5.3</a></li>
                                <li class="tab"><a href="#version-1-5-2">v1.5.2</a></li>
                                <li class="tab"><a href="#version-1-5-1">v1.5.1</a></li>
                                <li><a href="https://wordpress.org/plugins/wpcasa/#developers" target="_blank"><?php echo esc_html__( 'More', 'wpcasa' ); ?></a></li>
                            </ul>

                            <section id="first-tab-group" class="tabgroup">
                                <div id="version-1-5-4">
                                    <p>Version: 1.5.4</p>
                                    <table>
                                        <tr>
                                            <td><span class="changelog-entry-new">New</span></td>
                                            <td>Tab navigation on settings page was added</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Not working mobile menu of admin settings page on mobile devices</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Under certain circumstances, not all pages were listed for selection of the Listings Page in the Settings page.</td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="version-1-5-3">
                                    <p>Version: 1.5.3</p>
                                    <table>
                                        <tr>
                                            <td><span class="changelog-entry-tweak">Tweak</span></td>
                                            <td>Improved handling of plugin option</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Fixed "Automatic conversion of false to array is deprecated in includes/class-wpsight-helpers.php"</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Fixed deactivation of WPCasa has affected administrator capabilities</td>
                                        </tr>
                                        <tr>
                                            <td><span class="changelog-entry-fix">Fix</span></td>
                                            <td>Fixed icons in WPCasa Admin Dashboard were shifted</td>
                                        </tr>
                                    </table>
                                </div>
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
