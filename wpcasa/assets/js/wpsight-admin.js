jQuery(document).ready(function($) {

	// Tooltips

    $( ".tips, .help_tip" ).tipTip({
        'attribute' : 'data-tip',
        'fadeIn' : 50,
        'fadeOut' : 50,
        'delay' : 200,
        'defaultPosition' : 'top'
    });

    // Fade out the save message
    $('.fade').delay(2500).fadeOut(250);

    // Switches option sections

    $('.settings_panel').hide();
    var active_tab = '';

    // Get active tab from local storage

    if (typeof(localStorage) != 'undefined' ) {
        active_tab = localStorage.getItem("active_tab");
    }

    // Get active tab from URL hash

    var url  = window.location.href;
    var hash = url.substring(url.indexOf("#")+1);

    if( hash.substring( 0, 9 ) == 'settings-' ) {
        active_tab = '#' + hash;
    }

    // If no active tab, fade in first

    if (active_tab != '' && $(active_tab).length ) {
        $(active_tab).fadeIn(200);
    } else {
        $('.settings_panel:first').fadeIn(200);
    }

    if (active_tab != '' && $(active_tab + '-tab').length ) {
        $(active_tab + '-tab').addClass('nav-tab-active');
    }
    else {
        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
    }

    // Switch tab on click

    $('.nav-tab-wrapper a').click(function(evt) {
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
        var clicked_group = $(this).attr('href');
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("active_tab", $(this).attr('href'));
        }
        $('.settings_panel').hide();
        $(clicked_group).fadeIn(200);
        evt.preventDefault();

    });

    $('.wpsight-addons .type-download a, .wpsight-themes .type-download a').attr('target','_blank');
    $('.download-wrapper .type-download .download-meta-price-details a').addClass('button');


    // Switch navbar on click

    $('.wpsight-admin-main-btn-toggle').click( function(e) {
        e.preventDefault();

        $(this).toggleClass("wpsight-admin-main-btn-toggle-active");

        $(".wpsight-settings-wrap").toggleClass("wpsight-settings-wrap-hide-navbar");
    });

    // Show color picker

    jQuery(document).ready(function($){
        $('.wpsight-settings-colorpicker').wpColorPicker();
    });

    // Working with listing fields

    var workWithListingFields = function () {
        var totoggle_currency = '.setting-' + WPCASA_SETTINGS.name + '_currency_other-tr, .setting-' + WPCASA_SETTINGS.name + '_currency_other_ent-tr';

        if( jQuery('#setting-' + WPCASA_SETTINGS.name + '_currency').val() === 'other' ) {
            jQuery(totoggle_currency).fadeIn(150);
        }

        jQuery('#setting-' + WPCASA_SETTINGS.name + '_currency').change(function() {
            if( jQuery(this).val() === 'other' ) {
                jQuery(totoggle_currency).fadeIn(150);
            } else {
                jQuery(totoggle_currency).fadeOut(150);
            }
        });

        var totoggle_details = $('.setting' + WPCASA_SETTINGS.name + 'heading_details-tr ~[class^=setting' + WPCASA_SETTINGS.name + 'details_]');

        jQuery('#setting' + WPCASA_SETTINGS.name + 'listing_features').click(function() {
            totoggle_details.fadeToggle(150);
        });

        if (jQuery('#setting' + WPCASA_SETTINGS.name + 'listing_features:checked').val() !== undefined) {
            totoggle_details.show();
        }

        var totoggle_periods = $('.setting' + WPCASA_SETTINGS.name + 'heading_rental_periods-tr ~[class^=setting' + WPCASA_SETTINGS.name + 'rental_period_]');

        jQuery('#setting' + WPCASA_SETTINGS.name + 'rental_periods').click(function() {
            totoggle_periods.fadeToggle(150);
        });

        if (jQuery('#setting' + WPCASA_SETTINGS.name + 'rental_periods:checked').val() !== undefined) {
            totoggle_periods.show();
        }

        jQuery('.wrap-addons-tabs > a').click(function(e) {
            jQuery('.wrap-addons-tabs > a').removeClass("active");
            $(this).addClass("active");
        });

        jQuery('#addons-all').click(function(e) {
            e.preventDefault();
            jQuery('.addon-active').show();
            jQuery('.addon-inactive').show();
        });

        jQuery('#addons-active').click(function(e) {
            e.preventDefault();
            jQuery('.addon-active').show();
            jQuery('.addon-inactive').hide();
        });

        jQuery('#addons-inactive').click(function(e) {
            e.preventDefault();
            jQuery('.addon-active').hide();
            jQuery('.addon-inactive').show();
        });
    };

    workWithListingFields();

    var promoSlider = function () {
        var $wrapSlider = $("[swiper]");
        var $slider = $("[swiper-container]");
        var $tab = $("#settings-overview-tab");

        var initSlider = function () {
            const swiper = new Swiper('[swiper-container]', {
                spaceBetween: 0,
                loop: true,
                speed: 600,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });

            $slider.hover(function () {
                swiper.autoplay.stop();
            }, function () {
                setTimeout(function () {
                    swiper.autoplay.start();
                }, 600);
            });

        };

        var setEventOnTab = function () {
            $tab.on('click', initSlider);
        };

        if ($slider.length && $tab.hasClass("nav-tab-active")) initSlider();
        else if ($tab.length) setEventOnTab();
    };

    promoSlider();


    var accordionMobileAddons = function () {
        var $btns = $(".addons-info-mobile .content-top");
        var $addons = $(".addons-info-mobile .content-bottom");

        $btns.click(function() {
            var isActive = $(this).hasClass("active");
            var $nextEl = $(this).next();

            $addons.slideUp();
            $btns.removeClass("active");

            $(this).addClass("active");

            if ( isActive ) {
                $(this).removeClass("active");
            } else {
                $nextEl.slideDown();
            }

        });
    };

    accordionMobileAddons();
    
    var WPCasaAdminUIAccordion = function() {
        
        var animTime = 300,
        clickPolice = false;

        $(document).on('touchstart click', '.acc-btn', function(){
        if(!clickPolice){
        clickPolice = true;

        var currIndex = $(this).index('.acc-btn'),
          targetHeight = $('.acc-content-inner').eq(currIndex).outerHeight();

        $('.acc-btn h1').removeClass('selected');
        $(this).find('h1').addClass('selected');

        $('.acc-content').stop().animate({ height: 0 }, animTime);
        $('.acc-content').eq(currIndex).stop().animate({ height: targetHeight }, animTime);

        setTimeout(function(){ clickPolice = false; }, animTime);
        }

        });
        
        
    }
    
    WPCasaAdminUIAccordion()

});

