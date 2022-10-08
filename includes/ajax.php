<?php
add_action('wp_ajax_wpcb_calendar_change', 'wpcb_calendar_change_callback');
add_action('wp_ajax_nopriv_wpcb_calendar_change', 'wpcb_calendar_change_callback');
function wpcb_calendar_change_callback()
{
    require(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
    $date = isset($_GET['date']) ? wpcb_sanitize_data($_GET['date']) : '';
    $calendar_id = isset($_GET['calendar_id']) && is_numeric($_GET['calendar_id']) ? wpcb_sanitize_data($_GET['calendar_id']) : 0;
    $booking_id = isset($_GET['booking_id']) && is_numeric($_GET['booking_id']) ? wpcb_sanitize_data($_GET['booking_id']) : 0;
    $has_date_modal = isset($_GET['has_date_modal']) ? wpcb_sanitize_data($_GET['has_date_modal']) : false;
    $calendar = new Calendar($calendar_id, $date);
    $calendar->has_date_modal = $has_date_modal;
    $calendar->set_booking_id($booking_id);
    ob_start();
    $calendar->draw();
    echo ob_get_clean();
    die();
}

add_action('wp_ajax_wpcb_selectize_search', 'wp_ajax_wpcb_selectize_search_callback');
add_action('wp_ajax_nopriv_wpcb_selectize_search', 'wp_ajax_wpcb_selectize_search_callback');
function wp_ajax_wpcb_selectize_search_callback()
{
    global $wpdb;
    $meta_key = isset($_GET['meta_key']) ? wpcb_sanitize_data($_GET['meta_key']) : '';
    $q = isset($_GET['q']) ? wpcb_sanitize_data($_GET['q']) : '';

    $sql = "SELECT DISTINCT pm.meta_value FROM `{$wpdb->prefix}posts` p INNER JOIN `{$wpdb->prefix}postmeta` pm ON p.ID = pm.post_id WHERE p.post_type = 'wpcb_booking' AND p.post_status = 'publish' AND pm.meta_key = '{$meta_key}' AND pm.meta_value LIKE '%{$q}%'";
    $results = $wpdb->get_results($sql);
    if (!empty($results)) {
        $new_result = array();
        foreach ($results as $result) {
            $new_result[][$meta_key] = $result->meta_value;
        }
        $results = $new_result;
    }
    echo json_encode($results);
    die();
}


// Generate Report
add_action('wp_ajax_wpcb_generate_report', 'wpcb_generate_report_callback');
add_action('wp_ajax_nopriv_wpcb_generate_report', 'wpcb_generate_report_callback');
function wpcb_generate_report_callback()
{
    global $wpcb_booking;
    $customer = isset($_POST['customer']) ? sanitize_text_field($_POST['customer']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

    $meta_query = array();
    if (!empty($customer)) {
        $meta_query[] = array(
            'key' => wpcb_customer_field('key'),
            'value' => $customer,
            'compare' => '='
        );
    }
    if (!empty($status)) {
        $meta_query[] = array(
            'key' => 'wpcb_booking_status',
            'value' => $status,
            'compare' => '='
        );
    }
    $args = array(
        'post_type' => 'wpcb_booking',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            $meta_query
        )
    );

    if (!empty($date_from) || !empty($date_to)) {
        $args['date_query'] = array();
        if (!empty($date_from)) {
            $args['date_query']['after'] = array(
                'year' => date('Y', strtotime($date_from)),
                'month' => date('m', strtotime($date_from)),
                'day' => date('d', strtotime($date_from))
            );
        }
        if (!empty($date_to)) {
            $args['date_query']['before'] = array(
                'year' => date('Y', strtotime($date_to)),
                'month' => date('m', strtotime($date_to)),
                'day' => date('d', strtotime($date_to))
            );
        }
        $args['date_query']['inclusive'] = true;
    }

    $result = array('status'=>'error');
    $posts = get_posts($args);
    $custom_fields = $wpcb_booking->fields();
    if (empty($posts)) {
        $result['error'] = esc_html__('No record(s) found.', 'wpcb_booking');
    }
    if (empty($custom_fields)) {
        $result['error'] = esc_html__('Your custom fields is empty.', 'wpcb_booking');
    }
    $report_headers = array(
        esc_html__('Booking Number', 'wpcb_booking'), 
        esc_html__('Date Created', 'wpcb_booking'),
        esc_html__('Status', 'wpcb_booking')
    );
    $report_data = array();

    if (!empty($posts) && !empty($custom_fields)) {
        foreach ($posts as $post) {
            $meta_values = $wpcb_booking->wpcb_get_booking_details($post->ID);
            $report_data[$post->ID]['booking_number'] = $post->post_title;
            $report_data[$post->ID]['post_date'] = date(wpcb_date_format(), strtotime($post->post_date));
            $report_data[$post->ID]['status'] = get_post_meta($post->ID, 'wpcb_booking_status', true);
            foreach ($custom_fields as $section => $fields) {
                foreach ($fields as $field) {
                    $meta_value = array_key_exists($field['key'], $meta_values) ? $meta_values[$field['key']] : '';
                    $meta_value = apply_filters('wpcb_report_data', $meta_value, $field['key'], $post->ID);
                    $report_data[$post->ID][$field['key']] = $meta_value;
                    if (!in_array($field['label'], $report_headers)) {
                        $report_headers[] = $field['label'];
                    }
                }
            }
           
            $booked_dates = array_key_exists('booked_dates', $meta_values) ? $meta_values['booked_dates'] : ''; 
            $booked_dates_str = !empty($booked_dates) ? implode(' | ', $booked_dates) : '';
            $report_data[$post->ID]['booked_dates'] = $booked_dates_str;
            $report_data[$post->ID]['booked_amount'] = array_key_exists('booked_amount', $meta_values) ? wpcb_number_format($meta_values['booked_amount']) : ''; 
        }
        $report_headers[] = esc_html__('Booked Date(s)', 'wpcb_booking');
        $report_headers[] = esc_html__('Amount', 'wpcb_booking');
    }
    $format = wpcb_sanitize_data(apply_filters('wpcb_report_file_format', 'csv'));
    $report_headers = wpcb_sanitize_data(apply_filters('wpcb_report_hearders', $report_headers, $custom_fields));
    $report_data = wpcb_sanitize_data(apply_filters('wpcb_report_data', $report_data, $posts));
    $report_file_url = wpcb_create_report($report_headers, $report_data, $format);   
    if (!array_key_exists('error', $result) && $report_file_url) {
        $result['status'] = 'ok';
        $result['file_url'] = $report_file_url;
        $result['msg'] = '('.count($posts).') record(s) generated.';
    }
    
    echo json_encode($result);
    die();
}

add_action('wp_ajax_wpcb_bulk_trash_booking', 'wpcb_bulk_trash_booking_callback');
function wpcb_bulk_trash_booking_callback()
{
    $booking_ids = isset($_POST['booking_ids']) ? wpcb_sanitize_data($_POST['booking_ids']) : array();
    $post_status = isset($_POST['status']) ? wp_kses_data($_POST['status']) : '';
    $result = array('status' => 'error');
    if (empty($booking_ids) || empty($post_status)) {
        if (empty($booking_ids)) {
            $result['error'] = esc_html('No booking(s) selected.');
        }
        if (empty($post_status)) {
            $result['error'] = esc_html('Status is not set for this action.');
        }
    } else {
        try {
            foreach ($booking_ids as $booking_id) {
                if ($post_status == 'delete') {
                    if (wpcb_update_calendar_in_book_delete($booking_id)) {
                        wp_delete_post($booking_id, true);
                    }
                } else {
                    wpcb_update_post_status($booking_id, $post_status);
                }                
            }
            $result['status'] = 'ok';
            $post_status = $post_status == 'publish' ? 'restore' : $post_status;
            $result['msg'] = esc_html('Selected booking(s) '.$post_status.' successfully.');
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }        
    }
    echo json_encode($result);
    die();
}