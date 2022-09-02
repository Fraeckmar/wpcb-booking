<?php
add_action('wp_ajax_wpcb_calendar_change', 'wpcb_calendar_change_callback');
function wpcb_calendar_change_callback()
{
    require(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $calendar_id = isset($_GET['calendar_id']) ? $_GET['calendar_id'] : 0;
    $booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : 0;
    $has_date_modal = isset($_GET['has_date_modal']) ? $_GET['has_date_modal'] : false;
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
    $meta_key = isset($_GET['meta_key']) ? $_GET['meta_key'] : '';
    $q = isset($_GET['q']) ? $_GET['q'] : '';

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
