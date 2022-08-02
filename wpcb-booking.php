<?php
/*
 * Plugin Name: Online Booking & Notification
 * Plugin URI: http://lerevel-inventory.herokuapp.com
 * Description: Booking Systemt & Notification is a WordPress plug-in designed to provide ideal technology solution for your Cargo and Courier Operations. Whether you are an exporter, freight forwarder, importer, supplier, customs broker, overseas agent, or warehouse operator, WPCBargo helps you to increase the visibility, efficiency, and quality services of your cargo and shipment business.
 * Author: <a href="http://lerevel-inventory.herokuapp.com">Calendar Booking</a>
 * Text Domain: wpcb_booking
 * Domain Path: /languages
 * Version: 3.1.0
 */

 /** 
  * Booking System & Notification
  * Copyright (C) 2022  Calendar Booking
  */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/** Defined constant */
define('WPCB_BOOKING_TEXTDOMAIN', 'wpcb_booking');
define('WPCB_BOOKING_VERSION', '3.1.0');
define('WPCB_BOOKING_DB_VERSION', '1.0.0');
define('WPCB_BOOKING_FILE_DIR', __FILE__);
define('WPCB_BOOKING_PLUGIN_URL', plugin_dir_url( WPCB_BOOKING_FILE_DIR ));
define('WPCB_BOOKING_PLUGIN_PATH', plugin_dir_path( WPCB_BOOKING_FILE_DIR ));

/** enqueue scipts */
require_once(WPCB_BOOKING_PLUGIN_PATH.'includes/functions.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'includes/fields.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'includes/filters.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'includes/hooks.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'classes/admin/setting.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'classes/admin/booking-default.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'classes/booking-shortcode.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'classes/booking.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'classes/form.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'classes/asset.php');
require_once(WPCB_BOOKING_PLUGIN_PATH.'includes/ajax.php');

if (is_admin()) {
  require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/admin/sub-menu.php');
  require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/admin/calendar-post-type.php');
  require_once(WPCB_BOOKING_PLUGIN_PATH. 'classes/admin/booking-post-type.php');  
}

/** Load text Domain */
add_action('plugins_loaded', array('WPCB_Booking_Admin','wpcb_booking_load_textdomain'));

//** Create Booking Form page
register_activation_hook(WPCB_BOOKING_FILE_DIR, array( 'WPCB_Booking_Admin', 'wpcb_booking_add_custom_page'));
