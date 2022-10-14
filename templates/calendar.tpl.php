<?php
require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/calendar.php');
$calendar = new Calendar($calendar_id);
$calendar->draw();