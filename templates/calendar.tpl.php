<?php
require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
$calendar = new Calendar($calendar_id);
//$calendar->add_event('Birthday', $date_today, 1, 'green');
//$calendar->add_event('Doctors', $date_today, 1, 'red');
//$calendar->add_event('Holiday', $date_today, 7);
$calendar->draw();