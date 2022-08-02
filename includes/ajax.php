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