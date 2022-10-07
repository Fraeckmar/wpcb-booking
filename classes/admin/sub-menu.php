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
            wpcb_plugin_slug(),
            array($this, 'wpcb_online_booking_callback'),
            'dashicons-calendar',
            3
        );

        // Manage Booking
        add_submenu_page(
            wpcb_plugin_slug(),
            wpcb_manage_booking_label(),
            wpcb_manage_booking_label(),
            'manage_options',
            wpcb_plugin_slug()
        );

        // Settings
        add_submenu_page(
            wpcb_plugin_slug(),
            wpcb_setting_label(),
            wpcb_setting_label(),
            'manage_options',
            wpcb_plugin_slug().'-setting',
            array($this, 'wpcb_settings_callback')
        );

        // Calendars
        add_submenu_page(
            wpcb_plugin_slug(),
            wpcb_calendars_label(),
            wpcb_calendars_label(),
            'manage_options',
            wpcb_plugin_slug().'-calendars',
            array($this, 'wpcb_calendars_callback')
        );

        // Reports
        add_submenu_page(
            wpcb_plugin_slug(),
            esc_html__('Generate Report', 'wpcb_booking'),
            esc_html__('Generate Report', 'wpcb_booking'),
            'manage_options',
            wpcb_plugin_slug().'-report',
            array($this, 'wpcb_report_callback')
        );
    }

    function wpcb_manage_booking_display_notif()
    {
        $booking_id = isset($_GET['id']) && is_numeric(sanitize_text_field($_GET['id'])) ? sanitize_text_field($_GET['id']) : 0;
        $booking_title = $booking_id ? get_the_title($booking_id) : '';
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        if ($booking_id && !empty($action) && !in_array($action, array('edit')) && isset($_GET['page']) && sanitize_text_field($_GET['page']) == wpcb_plugin_slug()) {
            if ($action == 'untrash') {
                $action = 'restored';
            }
            wpcb_set_notification("{$booking_title} {$action} successfully!");
        }        
    }

    function wpcb_online_booking_callback()
    {
        if (!isset($_GET['page']) && sanitize_text_field($_GET['page']) != wpcb_plugin_slug()) { 
            return false; 
        }
        require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
        global $wpcb_booking, $wpcb_setting;        

        $plugin_slug = wpcb_plugin_slug();
        $booking_id = isset($_GET['id']) && is_numeric(sanitize_text_field($_GET['id'])) ? sanitize_text_field($_GET['id']) : 0;
        $booking_title = $booking_id ? get_the_title($booking_id) : '';
        $calendar_id = get_post_meta($booking_id, 'calendar_id', true);
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';      
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';  
        $is_active_booking = !in_array($action, array('untrash', 'delete')) && !in_array($status, array('trash', 'untrash')) ? true : false;

        if (in_array($action, array('new', 'edit'))) {
            $order_id = get_post_meta($booking_id, 'order_id', true);
            $calendar_list = wpcb_get_calendar_list();
            $form_fields = !empty($wpcb_booking->fields()) ? $wpcb_booking->fields($booking_id) : array();
            $title = $action == 'edit' ? get_the_title($booking_id) : '';   

            $booked_amount = get_post_meta($booking_id, 'booked_amount', true) ?? 0;
            $order_html = $booking_id ? wpcb_get_order_details_html($booking_id) : '';
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

            $q_booking = isset($_POST['q_booking']) && !empty($_POST['q_booking']) ? sanitize_text_field($_POST['q_booking'])  : '';
            $q_customer_name = isset($_POST[wpcb_customer_field('key')]) ? sanitize_text_field($_POST[wpcb_customer_field('key')]) : '';
            $q_wpcb_booking_status = isset($_POST['wpcb_booking_status']) ? sanitize_text_field($_POST['wpcb_booking_status']) : '';
            $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
            $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
            $post_per_page = isset($_GET['post_per_page']) && is_numeric($_GET['post_per_page']) ? sanitize_text_field($_GET['post_per_page'])  : 10;
            $entries_options = array(10, 25, 50);
            $meta_query = array();

            if (!empty($q_customer_name)) {
                $meta_query[] = array(
                    'key' => wpcb_customer_field('key'),
                    'value' => $q_customer_name,
                    'compare' => '='
                );
            }
            if (!empty($q_wpcb_booking_status)) {
                $meta_query[] = array(
                    'key' => 'wpcb_booking_status',
                    'value' => $q_wpcb_booking_status,
                    'compare' => '='
                );
            }
            
            $meta_query = apply_filters( 'wpcb_manage_booking_meta_query', $meta_query);
            $paged = isset($_GET['paged']) && is_numeric($_GET['paged']) ? sanitize_text_field($_GET['paged']) : 1; 
            
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

            if (!empty($date_from) || !empty($date_to)) {
                $active_args['date_query'] = array();
                if (!empty($date_from)) {
                    $active_args['date_query']['after'] = array(
                        'year' => date('Y', strtotime($date_from)),
                        'month' => date('m', strtotime($date_from)),
                        'day' => date('d', strtotime($date_from))
                    );
                }
                if (!empty($date_to)) {
                    $active_args['date_query']['before'] = array(
                        'year' => date('Y', strtotime($date_to)),
                        'month' => date('m', strtotime($date_to)),
                        'day' => date('d', strtotime($date_to))
                    );
                }
                $active_args['date_query']['inclusive'] = true;
            }

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
            $bulk_update_label = $is_active_booking ? 'Bulk Trash' : 'Bulk Delete';
            $status_attr = $is_active_booking ? 'data-status=trash' : 'data-status=delete';
            $template = wpcb_get_template('manage-booking.tpl', true);
            require_once $template;
        }
    }

    function wpcb_settings_callback()
    {
        global $wpcb_booking, $wpcb_setting;
        $current_tab = isset($_GET['tab']) && !empty($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        $general_setting_fields = array_key_exists('general', $wpcb_setting->fields()) ? $wpcb_setting->fields()['general'] : array();
        $admin_email_setting_fields = array_key_exists('email_admin', $wpcb_setting->fields()) ? $wpcb_setting->fields()['email_admin'] : array();
        $client_email_setting_fields = array_key_exists('email_client', $wpcb_setting->fields()) ? $wpcb_setting->fields()['email_client'] : array();
        $wpcb_shortcode_list = $wpcb_setting->wpcb_get_shortcode_list();
        ?>
        <div id="wpcb-booking-admin" class="wrap wpcb-booking">
            <h3><?php esc_html_e('Settings', 'wpcb_booking'); ?></h3>
            <?php require_once(WPCB_BOOKING_PLUGIN_PATH. 'templates/admin/navigation.tpl.php'); ?>
            <div id="wpcb-content">
                <?php foreach($wpcb_setting->menus() as $menu_key => $menu): 
                    $menu_key = sanitize_key($menu_key);
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
                            echo "<button type='submit' class='btn btn-primary'>".esc_html__('Save Setting', 'wpcb_booking')."</button>";
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
        $submenu_slug = sanitize_key(wpcb_plugin_slug().'-calendars');
        if (!isset($_GET['page']) && sanitize_key($_GET['page']) == $submenu_slug) {
            return false;
        }
        require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
        $calendar_id = isset($_GET['id']) && is_numeric($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';      
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';  
        $active_current = $status != 'trash' ? 'current' : '';
        $trash_current = $status == 'trash' ? 'current' : '';   
           
        if ($action && !in_array($action, array('new', 'untrash')) && $calendar_id && !wpcb_is_calendar_exist($calendar_id)) {
            echo "<h3 class='wpcb-booking'><span class='text-danger'>" .esc_html('Calendar not exist!'). "</span></h3>";
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
                    wp_redirect(esc_url(admin_url("admin.php?page={$submenu_slug}&status=trash")));
                }                
            }
            if ($action == 'trash') {
                wp_redirect(esc_url(admin_url("admin.php?page={$submenu_slug}&status=trash")));
            }
            if ($action == 'untrash') {
                wp_redirect(esc_url(admin_url("admin.php?page={$submenu_slug}")));
            }       
            
            $s_calendar = isset($_POST['s']) ? sanitize_text_field($_POST['s'])  : '';
            $post_per_page = isset($_POST['post_per_page']) && is_numeric($_POST['post_per_page']) ? sanitize_text_field($_POST['post_per_page'])  : -1;
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

    function wpcb_report_callback()
    {
        $customer_name = isset($_POST['wpcb_customer_name']) ? sanitize_text_field($_POST['wpcb_customer_name']) : '';
        $wpcb_booking_status = isset($_POST['wpcb_booking_status']) ? sanitize_text_field($_POST['wpcb_booking_status']) : '';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        require_once(WPCB_BOOKING_PLUGIN_PATH. 'templates/admin/report.php');
    }
}

new WPCB_Sub_Menu;