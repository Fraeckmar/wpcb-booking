<?php

class WPCB_Booking_Asset
{
    function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'wpcb_frontend_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'wpcb_admin_enqueue_scripts'));
    }
    
    function wpcb_frontend_enqueue_scripts()
    {
        global $post, $wpcb_setting;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wpcb_booking')) {
            // Styles
            wp_enqueue_style('booking-bootstrap', WPCB_BOOKING_PLUGIN_URL. 'assets/css/main.min.css', array(), WPCB_BOOKING_VERSION);
            wp_enqueue_style('booking-styles', WPCB_BOOKING_PLUGIN_URL. 'assets/css/booking-styles.css', array(), WPCB_BOOKING_VERSION);
            wp_enqueue_style('calendar-styles', WPCB_BOOKING_PLUGIN_URL. 'assets/css/calendar.css', array(), WPCB_BOOKING_VERSION);
            wp_enqueue_style('mdb-styles', WPCB_BOOKING_PLUGIN_URL. 'assets/css/custom-mdb.css', array(), WPCB_BOOKING_VERSION);
            wp_enqueue_style('fontawesome', WPCB_BOOKING_PLUGIN_URL. 'assets/css/font-awesome.min.css', array(), WPCB_BOOKING_VERSION);
            wp_enqueue_style('bootstrap-datetimepicker-css', WPCB_BOOKING_PLUGIN_URL. 'assets/css/bootstrap-datetimepicker.min.css', array(), WPCB_BOOKING_VERSION);

            // Scripts
            wp_enqueue_script('moment');
            wp_enqueue_script('bootstrap-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/bootstrap.min.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('repeater-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/repeater.min.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('repeater-helper-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/repeater-helper.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('calendar-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/calendar.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('calendar-helper-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/calendar-helper.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('calendar-common-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/calendar-common.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('bootstrap-datetimepicker-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/bootstrap-datetimepicker.min.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('datetime-picker-helper', WPCB_BOOKING_PLUGIN_URL. 'assets/js/datetime-picker-helper.js', array('jquery'), WPCB_BOOKING_VERSION, true);
            wp_enqueue_script('custom-script-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/custom-script.js', array('calendar-js'), WPCB_BOOKING_VERSION, true);
            

            $datetime_picker_format = new stdClass;
            $datetime_picker_format->format = 'hh:mm a';
            $date_picker_format = new stdClass;
            $date_picker_format->format = wpcb_datepicker_format();
            // Localize
            $translation = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'datetime_picker_format' => $datetime_picker_format,
                'date_picker_format' => $date_picker_format,
                'notification' => isset($_POST['wpcb_notification']) && !empty($_POST['wpcb_notification']) ? wpcb_sanitize_data($_POST['wpcb_notification']) : [],
                'is_debug' => isset($_GET['debug']) ? 1 : 0
            );

            require_once(WPCB_BOOKING_PLUGIN_PATH. 'assets/css-root.php');
            wp_localize_script('calendar-js', 'WPCBBookingAjax', $translation);
        }
    }

    function wpcb_admin_enqueue_scripts()
    {
        global $post, $wpcb_setting;
        require_once(WPCB_BOOKING_PLUGIN_PATH. 'assets/css-root.php');
        // Styles
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style('booking-bootstrap', WPCB_BOOKING_PLUGIN_URL. 'assets/css/main.min.css', array(), WPCB_BOOKING_VERSION);
        wp_enqueue_style('booking-styles', WPCB_BOOKING_PLUGIN_URL. 'assets/css/admin/booking-styles.css', array(), WPCB_BOOKING_VERSION);
        wp_enqueue_style('selectize', WPCB_BOOKING_PLUGIN_URL. 'assets/css/admin/selectize.bootstrap3.min.css', array(), WPCB_BOOKING_VERSION);
        wp_enqueue_style('selectize-helper', WPCB_BOOKING_PLUGIN_URL. 'assets/css/admin/selectize-helper.css', array(), WPCB_BOOKING_VERSION);
        wp_enqueue_style('calendar-styles', WPCB_BOOKING_PLUGIN_URL. 'assets/css/calendar.css', array(), WPCB_BOOKING_VERSION);
        wp_enqueue_style('fontawesome', WPCB_BOOKING_PLUGIN_URL. 'assets/css/font-awesome.min.css', array(), WPCB_BOOKING_VERSION);
        wp_enqueue_style('bootstrap-datetimepicker-css', WPCB_BOOKING_PLUGIN_URL. 'assets/css/bootstrap-datetimepicker.min.css', array(), WPCB_BOOKING_VERSION);
        
        // Scripts
        wp_enqueue_script('moment');
        wp_enqueue_script('bootstrap', WPCB_BOOKING_PLUGIN_URL. 'assets/js/bootstrap.min.js', array('jquery'), WPCB_BOOKING_VERSION );
        wp_enqueue_script('calendar-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/calendar.js', array('jquery'), WPCB_BOOKING_VERSION );
        wp_enqueue_script('calendar-helper-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/calendar-helper.js', array('jquery'), WPCB_BOOKING_VERSION );
        wp_enqueue_script('selectize', WPCB_BOOKING_PLUGIN_URL. 'assets/js/admin/selectize.min.js', array('jquery'), WPCB_BOOKING_VERSION );
        wp_enqueue_script('selectize-helper', WPCB_BOOKING_PLUGIN_URL. 'assets/js/admin/selectize-helper.js', array(), WPCB_BOOKING_VERSION );
        wp_enqueue_script('calendar-admin-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/admin/calendar-booking.js', array( 'wp-color-picker' ), WPCB_BOOKING_VERSION );
        wp_enqueue_script('calendar-common-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/calendar-common.js', array('jquery'), WPCB_BOOKING_VERSION, true);
        wp_enqueue_script('repeater-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/repeater.min.js', array('jquery'), WPCB_BOOKING_VERSION, true);
        wp_enqueue_script('repeater-helper-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/repeater-helper.js', array('jquery'), WPCB_BOOKING_VERSION, true);
        wp_enqueue_script('bootstrap-datetimepicker-js', WPCB_BOOKING_PLUGIN_URL. 'assets/js/bootstrap-datetimepicker.min.js', array('jquery'), WPCB_BOOKING_VERSION, true);
        wp_enqueue_script('datetime-picker-helper', WPCB_BOOKING_PLUGIN_URL. 'assets/js/datetime-picker-helper.js', array('jquery'), WPCB_BOOKING_VERSION, true);

        $datetime_picker_format = new stdClass;
        $datetime_picker_format->format = 'hh:mm a';
        $date_picker_format = new stdClass;
        $date_picker_format->format = wpcb_datepicker_format();
        // Localize
        $translation = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'is_admin' => is_admin() ? true : false,
            'notification' => isset($_POST['wpcb_notification']) && !empty($_POST['wpcb_notification']) ? wpcb_sanitize_data($_POST['wpcb_notification']) : [],
            'datetime_picker_format' => $datetime_picker_format,
            'date_picker_format' => $date_picker_format,
            'customer_field' => wpcb_customer_field(),
            'is_debug' => isset($_GET['debug']) ? 1 : 0
        );
        wp_localize_script('calendar-admin-js', 'WPCBBookingAjax', $translation);
    }
}
new WPCB_Booking_Asset;