<?php

class WPCB_Sub_Menu
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'wpcb_register_admin_menu'));
        add_action('admin_init', array($this, 'wpcb_manage_booking_display_notif'));
    }

    function wpcb_register_admin_menu()
    {
        add_menu_page(
            wpcb_booking_label(),
            wpcb_booking_label(),
            'manage_options',
            'wpcb-booking',
            array($this, 'wpcb_online_booking_callback'),
            'dashicons-calendar'
        );

        // Manage Booking
        add_submenu_page(
            'wpcb-booking',
            wpcb_manage_booking_label(),
            wpcb_manage_booking_label(),
            'manage_options',
            'wpcb-booking'
        );

        // Settings
        add_submenu_page(
            'wpcb-booking',
            wpcb_setting_label(),
            wpcb_setting_label(),
            'manage_options',
            'wpcb-settings',
            array($this, 'wpcb_settings_callback')
        );

        // Calendars
        add_submenu_page(
            'wpcb-booking',
            wpcb_calendars_label(),
            wpcb_calendars_label(),
            'manage_options',
            'wpcb-calendar',
            array($this, 'wpcb_calendars_callback')
        );

        
    }

    function wpcb_manage_booking_display_notif()
    {
        $booking_id = isset($_GET['id']) ? $_GET['id'] : 0;
        $booking_title = $booking_id ? get_the_title($booking_id) : '';
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        if ($booking_id && !empty($action) && !in_array($action, array('edit')) && isset($_GET['page']) && $_GET['page'] == 'wpcb-booking') {
            if ($action == 'untrash') {
                $action = 'restored';
            }
            wpcb_set_notification("{$booking_title} {$action} successfully!");
        }        
    }

    function wpcb_online_booking_callback()
    {
        require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
        global $wpcb_booking, $wpcb_setting;
        if (!isset($_GET['page'])) { return false; }
        $booking_id = isset($_GET['id']) ? $_GET['id'] : 0;
        $booking_title = $booking_id ? get_the_title($booking_id) : '';
        $calendar_id = get_post_meta($booking_id, 'calendar_id', true);
        $action = isset($_GET['action']) ? $_GET['action'] : '';      
        $status = isset($_GET['status']) ? $_GET['status'] : '';  
        $is_active_booking = !in_array($action, array('untrash', 'delete')) && !in_array($status, array('trash', 'untrash')) ? true : false;

        if (in_array($action, array('new', 'edit'))) {
            $calendar_list = wpcb_get_calendar_list();
            $form_fields = !empty($wpcb_booking->fields()) ? $wpcb_booking->fields($booking_id) : array();
            $title = $action == 'edit' ? get_the_title($booking_id) : '';           
            require_once(wpcb_get_template('booking.tpl', true));
        } else {
            if (in_array($action, array('trash', 'untrash', 'restore')) && $booking_id) {
                wpcb_update_post_status($booking_id, $action);   
            }
            if ($action == 'delete' && $booking_id) {
                if (wpcb_update_calendar_in_book_delete($booking_id)) {
                    wp_delete_post($booking_id, true);
                }             
            }

            $q_booking = isset($_POST['q_booking']) ? $_POST['q_booking']  : '';
            $q_customer_name = isset($_POST['wpcb_customer_name']) ? $_POST['wpcb_customer_name'] : '';
            $post_per_page = isset($_POST['wpcb_per_page']) ? $_POST['wpcb_per_page']  : 10; 
            $meta_query = array();
            if (!empty($q_customer_name)) {
                $meta_query[] = array(
                    'key' => 'wpcb_customer_name',
                    'value' => $q_customer_name,
                    'compare' => '='
                );
            }
            $meta_query = apply_filters( 'wpcb_manage_booking_meta_query', $meta_query);
            $paged = isset($_GET['paged']) && is_numeric($_GET['paged']) ? $_GET['paged'] : 1; 
            $active_args = array(
                'post_type'         => 'wpcb_booking',
                'post_status'       => 'publish',
                'posts_per_page'    => $post_per_page,
                'paged'             => $paged,
                's'                 => $q_booking,
                'meta_query' => array(
                    'relation' => 'AND',
                    $meta_query
                )
            );

            $trash_args = array(
                'post_type'         => 'wpcb_booking',
                'post_status'       => 'trash',
                'posts_per_page'    => $post_per_page,
                'paged'             => $paged,
                's'                 => $q_booking
            );

            $active_args = apply_filters( 'wpcb_manage_booking_args', $active_args );
            $active_bookings  = new WP_Query( $active_args );
            $trash_bookings  = new WP_Query( $trash_args );
            $active_count = $active_bookings->found_posts;
            $trash_count = $trash_bookings->found_posts;
            $bookings =  $is_active_booking ? $active_bookings : $trash_bookings;   
            $number_records = $bookings->found_posts;
            $basis = $paged * $post_per_page;
            $record_end     = $number_records < $basis ? $number_records : $basis ;
            $record_start   = $basis - ( $post_per_page - 1 );   
            $template = wpcb_get_template('manage-booking.tpl', true);
            require_once $template;
        }
    }

    function wpcb_settings_callback()
    {
        global $wpcb_booking, $wpcb_setting;
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        $general_setting_fields = array_key_exists('general', $wpcb_setting->fields()) ? $wpcb_setting->fields()['general'] : array();
        $email_setting_fields = array_key_exists('email', $wpcb_setting->fields()) ? $wpcb_setting->fields()['email'] : array();
        $wpcb_shortcode_list = $wpcb_setting->wpcb_get_shortcode_list();
        ?>
        <div id="wpcb-booking-admin" class="wrap wpcb-booking">
            <h3><?php _e('Settings'); ?></h3>
            <?php require_once(WPCB_BOOKING_PLUGIN_PATH. 'templates/admin/navigation.tpl.php'); ?>
            <div id="wpcb-content">
                <?php foreach($wpcb_setting->menus() as $menu_key => $menu): 
                    $has_form = array_key_exists('has_form', $menu) ? $menu['has_form'] : false;
                ?>
                    <div id="<?php echo $menu_key; ?>-container" class="tab-container p-3 <?php echo ($current_tab == $menu_key) ? 'active' : '';?>">
                    <?php
                        if ($has_form) {
                            WPCB_Form::start(); 
                            wp_nonce_field('wpcb_setting_nonce_action', 'wpcb_setting_nonce_field');
                        }                        
                        require_once($menu['file_path']);
                        if ($has_form) {
                            echo "<button type='submit' class='btn btn-primary'>".__('Save Setting')."</button>";
                            WPCB_Form::end();
                        }                        
                    ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    function wpcb_calendars_callback()
    {
        require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
        $calendar_id = isset($_GET['id']) ? $_GET['id'] : 0;
        $action = isset($_GET['action']) ? $_GET['action'] : '';      
        $status = isset($_GET['status']) ? $_GET['status'] : '';  
        $active_current = $status != 'trash' ? 'current' : '';
        $trash_current = $status == 'trash' ? 'current' : '';   
           
        if (!isset($_GET['page'])) {
            return false;
        }
        if ($action && !in_array($action, array('new', 'untrash')) && $calendar_id && !wpcb_is_calendar_exist($calendar_id)) {
            echo "<h3 class='wpcb-booking'><span class='text-danger'>Calendar not exist!</span></h3>";
            return false;
        }
        
        if (in_array($action, array('new', 'edit'))) {
            $title = get_the_title($calendar_id);
            require_once(wpcb_get_template('calendar.tpl', true));
        } else {
            if (in_array($action, array('trash', 'untrash', 'restore')) && $calendar_id) {
                wpcb_update_post_status($calendar_id, $action);
            }
            if ($action == 'delete') {
                if (wp_delete_post($calendar_id, true)) {
                    wp_redirect(admin_url("admin.php?page=wpcb-calendar&status=trash"));
                }                
            }
            if ($action == 'trash') {
                wp_redirect(admin_url("admin.php?page=wpcb-calendar&status=trash"));
            }
            if ($action == 'untrash') {
                wp_redirect(admin_url("admin.php?page=wpcb-calendar"));
            }       
            
            $s_calendar = isset($_POST['s']) ? $_POST['s']  : '';
            $post_per_page = isset($_POST['wpcb_per_page']) ? $_POST['wpcb_per_page']  : 10;
            $meta_query = array();	    
            $meta_query = apply_filters( 'wpcb_booking_calendar_meta_query', $meta_query );
            $active_args = array(
                'post_type'         => 'wpcb_calendar',
                'post_status'       => 'publish',
                'posts_per_page'    => $post_per_page,
                'paged'             => get_query_var('paged'),
                's'                 => $s_calendar,
                'meta_query' => array(
                    'relation' => 'AND',
                    $meta_query
                )
            );
            $trash_args = array(
                'post_type'         => 'wpcb_calendar',
                'post_status'       => 'trash',
                'posts_per_page'    => $post_per_page,
                'paged'             => get_query_var('paged'),
                's'                 => $s_calendar
            );

            $active_args = apply_filters( 'wpcb_booking_calendar_args', $active_args );                     	
            $active_calendars  = new WP_Query( $active_args );
            $trash_calendars  = new WP_Query( $trash_args );
            $active_count = $active_calendars->found_posts;
            $trash_count = $trash_calendars->found_posts;
            $wpcb_calendars = $status == 'trash' ? $trash_calendars : $active_calendars;
            require_once(WPCB_BOOKING_PLUGIN_PATH. 'templates/admin/calendars.php');
        }
    }
}

new WPCB_Sub_Menu;