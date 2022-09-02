<?php
// Filters
function wpcb_booking_default_status()
{
    return apply_filters('wpcb_booking_default_status', 'Pending Approval');
}
function wpcb_restrict_booking_in_status()
{
    $status_list = array('Approved', 'Complete');
    return apply_filters('wpcb_restrict_booking_in_status', $status_list);
}
function wpcb_date_format()
{
    return apply_filters('wpcb_date_format', "Y-m-d");
}
function wpcb_number_format($value, $currency=false, $decimals_count=2)
{
    $formatted_number = apply_filters('wpcb_number_format', number_format($value, $decimals_count));
    if ($currency) {
        $currency_symbol = wpcb_get_currency();
        $formatted_number = $currency_symbol.$formatted_number;
    }
    return $formatted_number;
}
function wpcb_encrypt($value)
{
    return base64_encode(maybe_serialize($value));
}
function wpcb_decrypt($value)
{
    return maybe_unserialize(base64_decode($value));
}
function wpcb_has_decimal_value($value)
{
    return fmod($value, 1) != 0;
}
function wpcb_get_currency(){
    return function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
}
function wpcb_allow_multiple_booking()
{
    $llow_multiple_booking = false;
    return apply_filters('wpcb_allow_multiple_booking', $llow_multiple_booking);
}
function wpcb_get_rate_type()
{
    $rate_type = 'no-rate';
    if (function_exists('wpcr_get_rate_type')) {
        $rate_type = strtolower(wpcr_get_rate_type());
    }
    return $rate_type;
}
function wpcb_set_notification($msg, $alert_type='success', $icon='check')
{
    $_POST['wpcb_notification'] = [
        'message' => $msg,
        'type' => $alert_type,
        'icon' => $icon
    ];
}
function wpcb_get_booking_id_by_order($order_id)
{
    global $wpdb;
    $sql = "SELECT item.meta_value AS booking_id
            FROM wp_woocommerce_order_itemmeta item 
            INNER JOIN wp_woocommerce_order_items ord ON item.order_item_id = ord.order_item_id
            WHERE ord.order_id = %d AND item.meta_key = 'BOOKING ID'";
    return $wpdb->get_var($wpdb->prepare($sql, $order_id)) ?? 0;
}
function wpcb_update_post_status($post_id, $status)
{
    if (!$post_id) {
        throw new Exception("Post ID not specified");
    }
    $status = $status == 'untrash' ? 'publish' : $status;
    $time = current_time('mysql'); 
    $args = array (
        'ID' => $post_id,
        'post_status' => $status,
        'post_date' => $time,
        'post_date_gmt' => get_gmt_from_date( $time )
    );
    wp_update_post($args);
}

function wpcb_get_new_calendar_dates($calendar_id, $year_month, $booking_id, $data)
{
    $selected_dates = isset($data['dates']) ? $data['dates'] : array();
    $calendar_dates = get_post_meta($calendar_id, 'dates', true);
    $calendar_dates = empty($calendar_dates) ? array() : $calendar_dates;
    foreach ($selected_dates as $selected_date) {
        $_date = date(wpcb_date_format(), strtotime("{$year_month}-{$selected_date}"));
        $calendar_dates[$year_month][$_date]['status'] = 'booked';
        $calendar_dates[$year_month][$_date]['booking_ids'][] = $booking_id;
    }
    return $calendar_dates;
}

function wpcb_get_calendar_list()
{
    global $wpdb;
    $sql = "SELECT ID, post_title FROM `{$wpdb->prefix}posts` WHERE post_type = 'wpcb_calendar' AND post_status = 'publish'";
    $results = $wpdb->get_results($sql);
    $calendars = [];
    if (!empty($results)) {
        foreach ($results as $calendar) {
            $calendars[$calendar->ID] = $calendar->post_title;
        }
    }
    return $calendars;
}

function wpcb_draw_date_modal($calendar_id, $date, $day)
{
    global $wpcb_setting, $wpcb_booking;
    $status = wpcb_get_date_value($calendar_id, $date, 'status');
    $enabled_days = $wpcb_setting->get_setting('general', 'enable_days');
    $day_name = wpcb_get_day_name($date);
    if (empty($status)) {
        $status = !in_array($day_name, $enabled_days) ? 'unavailable' : 'available';
    }
    $description = wpcb_get_date_value($calendar_id, $date, 'description');
    echo "<div class='modal fade text-left' id='modal-day-{$day}' tabindex='-1' role='dialog' aria-labelledby='dateModalLabel-{$day}' aria-hidden='true'>";
        echo "<div class='modal-dialog modal-dialog-centered'>";
            echo "<div class='modal-content'>";
                echo "<div class='modal-header'>";
                    echo "<h5 class='modal-title' id='dateModalLabel-{$day}'></h5>";
                    //echo "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
                        //echo "<span aria-hidden='true'>&times;</span>";
                    //echo "</button>";
                echo "</div>";
                echo "<div class='modal-body'>";
                    echo "<div class='form-group'>";
                        echo "<div class='form-check form-check-inline'>";
                            echo "<input type='radio' id='available-{$day}' class='form-check-input status' name='dates[{$date}][status]' value='available' ".checked($status == 'available', 1, false)."/>";
                            echo "<label for='available-{$day}' class='form-check-label'>".__('Available')."</label>";
                        echo "</div>";
                        echo "<div class='form-check form-check-inline'>";
                            echo "<input type='radio' id='unavailable-{$day}' class='form-check-input status' name='dates[{$date}][status]' value='unavailable' ".checked($status == 'unavailable', 1, false)."/>";
                            echo "<label for='unavailable-{$day}' class='form-check-label'>".__('Unavailable')."</label>";
                        echo "</div>";
                        echo "<div class='form-check form-check-inline'>";
                            echo "<input type='radio' id='booked-{$day}' class='form-check-input status' name='dates[{$date}][status]' value='booked' ".checked($status == 'booked', 1, false)."/>";
                            echo "<label for='booked-{$day}' class='form-check-label'>".__('Booked')."</label>";
                        echo "</div>";
                    echo "</div>";
                    echo "<div class='form-group'>";
                        echo "<input type='hidden' class='date-day' value='{$day}' />";
                        echo "<label for='description-{$date}'>".__('Description')."</label>";
                        echo "<textarea id='description-{$date}' class='form-control' name='dates[{$date}][description]'>{$description}</textarea>";
                    echo "</div>";
                echo "</div>";
                echo "<div class='modal-footer'>";
                    echo "<button type='button' class='btn btn-sm btn-primary' data-dismiss='modal' aria-label='Close'>".__('OK', 'wpcb_calendar')."</button>";
                echo "</div>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
}

// Calendars
function wpcb_get_day_name($current_date)
{
    return date('D', strtotime($current_date));
}

function wpcb_get_calendar_id($shortcode_id)
{
    global $wpdb;
    $sql = $wpdb->prepare("SELECT p.ID 
                            FROM `{$wpdb->prefix}posts` p 
                            JOIN `{$wpdb->prefix}postmeta` pm ON p.ID = pm.post_id
                            WHERE p.post_type = 'wpcb_calendar' AND p.post_status = 'publish' AND pm.meta_key = 'shortcode_id' AND pm.meta_value = %d", 
    $shortcode_id);
    $result = $wpdb->get_var($sql);
    return $result;
}

function get_next_shortcode_id()
{
    global $wpdb;
    $sql = $wpdb->prepare("SELECT DISTINCT COUNT(p.ID) FROM `{$wpdb->prefix}posts` p 
                            JOIN `{$wpdb->prefix}postmeta` pm ON p.ID = pm.post_id 
                            WHERE p.post_type = %s AND pm.meta_key = 'shortcode_id' AND pm.meta_value IS NOT NULL",
    'wpcb_calendar');
    $result = $wpdb->get_var($sql);
    $result = $result ? ++$result : 1;
    return $result;
}

function wpcb_get_booking_ids_in_calendar($calendar_id, $year='')
{
    global $wpdb;
    $year = !empty($year) ? $year : date('Y');
    $sql = "SELECT post.ID 
            FROM `wp_posts` post
            LEFT JOIN `wp_postmeta` pm1 ON post.ID = pm1.post_id
            LEFT JOIN `wp_postmeta` pm2 ON pm1.post_id = pm2.post_id
            WHERE post.post_type = 'wpcb_booking' 
                AND (pm1.meta_key = 'calendar_id' AND pm1.meta_value = %d)  
                AND (pm2.meta_key = 'booked_dates' AND pm2.meta_value LIKE '%{$year}%')";

    $results = $wpdb->get_results($wpdb->prepare($sql, $calendar_id), ARRAY_A);
    return !empty($results) ? wpcb_compress_single_array($results) : array();
}

function wpcb_compress_single_array($uncompressed_array)
{
    $compressed_array = array();
    if (!empty($uncompressed_array)) {
        foreach ($uncompressed_array as $array) {
            foreach ($array as $key => $value) {
                $compressed_array[] = $value;
            }
        }
    }
    return $compressed_array;
}

function wpcb_get_calendar_dates($calendar_id, $booking_id=null)
    {
        $dates = [];
        if ($booking_id) {
            $booked_dates = get_post_meta($booking_id, 'booked_dates', true);
            if (!empty($booked_dates)) {
                foreach ($booked_dates as $booked_date) {
                    $year_month = date('Y-m', strtotime($booked_date));
                    $dates[$year_month][$booked_date]['status'] = 'booked';
                }
            }
        } else {
            $dates = !empty($calendar_id) ? get_post_meta($calendar_id, 'dates', true) : array();
            if (!empty($dates)) {
                foreach ($dates as $_month => $month_dates) {
                    foreach ($month_dates as $_date => $data) {
                        $booking_ids = array_key_exists('booking_ids', $data) ? $data['booking_ids'] : array();
                        if ($data['status'] == 'booked' && !empty($booking_ids)) {
                            foreach ($booking_ids as $booking_id) {
                                if (get_post_status($booking_id) != 'publish') {
                                    $data['status'] = 'available';
                                    $booking_id_idx = array_search($booking_id, $booking_ids);
                                    unset($booking_ids[$booking_id_idx]);
                                }
                            }
                        }
                        $data['booking_ids'] = $booking_ids;
                        $month_dates[$_date] = $data;
                    }
                    $dates[$_month] = $month_dates;
                }
            }
        }
        return $dates;
    }
function wpcb_get_date_value($calendar_id, $date, $key)
{
    $year_month = date('Y-m', strtotime($date));
    $dates = wpcb_get_calendar_dates($calendar_id);
    $dates = !empty($dates) && array_key_exists($year_month, $dates) ? $dates[$year_month] : [];
    $result = '';
    if (!empty($dates) && array_key_exists($date, $dates)) {
        if (array_key_exists($key, $dates[$date])) {
            $result = $dates[$date][$key];
        }
    }
    return $result;
}

function wpcb_get_template( $file_name, $admin_tpl=false ){
    $file_slug = strtolower( preg_replace('/\s+/', '_', trim( str_replace( '.tpl', '', $file_name ) ) ) );
    $file_slug = preg_replace('/[^A-Za-z0-9_]/', '_', $file_slug );    
    $admin_folder = $admin_tpl ? 'admin/' : '';
    $template_path   = get_stylesheet_directory()."/wpcb_booking/{$admin_folder}{$file_name}.php";

    if (!file_exists($template_path)) {
        $template_path  = WPCB_BOOKING_PLUGIN_PATH."templates/{$admin_folder}{$file_name}.php";
        $template_path  = apply_filters( "wpcb_locate_template_{$file_slug}", $template_path );
    }
	return $template_path;
}

function wpcb_is_calendar_exist($calendar_id)
{
    global $wpdb;
    $sql = "SELECT * FROM `{$wpdb->prefix}posts` WHERE ID = %d AND post_type = 'wpcb_calendar' LIMIT 1";
    $result = $wpdb->get_row($wpdb->prepare($sql, $calendar_id));
    return $result;
}

function wpcb_format_calendar_data($calendar_dates)
{
    $formatted_dates = array();
    if (!empty($calendar_dates)) {
        foreach ($calendar_dates as $calendar_date) {
            $year_month = date('Y-m', strtotime($calendar_date));
            $formatted_dates[$year_month][] = $calendar_date;
        }
    }
    return $formatted_dates;
}
// Bookings
function wpcb_update_calendar_in_book_delete($booking_id)
{
    if (empty($booking_id)) {return false;}
    $calendar_id = get_post_meta($booking_id, 'calendar_id', true);
    $booked_dates = get_post_meta($booking_id, 'booked_dates', true);
    $booked_dates = !empty($booked_dates) ? wpcb_format_calendar_data($booked_dates) : array();
    $calendar_dates = wpcb_get_calendar_dates($calendar_id);
    if (!empty($booked_dates) && !empty($calendar_dates)) {
        foreach ($booked_dates as $year_month => $booked_date) {
            if (array_key_exists($year_month, $calendar_dates)) {
                $year_month_dates = $calendar_dates[$year_month];
                foreach ($year_month_dates as $month_date => $date_info) {
                    $booking_ids = array_key_exists('booking_ids', $date_info) ? $date_info['booking_ids'] : array();
                    if (!empty($booking_ids) && in_array($booking_id, $booking_ids) && count($booking_ids) == 1) {
                        unset($calendar_dates[$year_month][$month_date]);
                    }
                }
            }
        }
    }
    update_post_meta($calendar_id, 'dates', $calendar_dates);
    return true;
}

function wpcb_get_pages()
{
    global $wpdb;
    $sql = "SELECT ID, post_title FROM `{$wpdb->prefix}posts` WHERE post_status = 'publish' AND post_type = %s ORDER BY post_title";
    $results = $wpdb->get_results($wpdb->prepare($sql, 'page'));
    $pages = array();
    if (!empty($results)) {
        foreach ($results as $page) {
            $pages[$page->ID] = $page->post_title;
        }
    }
    return !empty($pages) ? $pages : array();
}

function wpcb_get_default_admin_mail_body()
{
    $body = "<p>Dear Admin,</p>";
    $body .= "<p>New booking was created <strong>#{wpcb_booking_number}</strong></p>";
    return $body;
}
function wpcb_get_default_admin_mail_footer()
{
    $footer = "<p>Your Company Name</p>";
    return $footer;
}

function wpcb_error_handler($error)
{
    echo "<p class='wpcb-error'> {$error} </p>";
    ?>
    <style>
        .wpcb-error {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        padding: 5px 10px;
        border-radius: 3px;
    }
    </style>
    <?php
    die();
}

// Custom Pagination
function wpcb_bootstrap_pagination( $args = array() ) {
    $defaults = array(
        'range'           => 4,
        'custom_query'    => FALSE,
        'previous_string' => __( 'Previous', 'wpcb_booking' ),
        'next_string'     => __( 'Next', 'wpcb_booking' ),
        'before_output'   => '<nav class="post-nav" aria-label="'.__('Booking Pagination', 'wpcb_booking').'"><ul class="pagination pg-blue justify-content-center">',
        'after_output'    => '</ul></nav>'
    );
    
    $args = wp_parse_args( 
        $args, 
        apply_filters( 'wp_bootstrap_pagination_defaults', $defaults )
    );
    
    $args['range'] = (int) $args['range'] - 1;
    if ( !$args['custom_query'] )
        $args['custom_query'] = @$GLOBALS['wp_query'];
    $count = (int) $args['custom_query']->max_num_pages;
    $page = isset($_GET['paged']) && is_numeric($_GET['paged']) ? $_GET['paged'] : 1;
    $ceil  = ceil( $args['range'] / 2 );
    
    if ( $count <= 1 )
        return FALSE;
    
    if ( !$page )
        $page = 1;
    
    if ( $count > $args['range'] ) {
        if ( $page <= $args['range'] ) {
            $min = 1;
            $max = $args['range'] + 1;
        } elseif ( $page >= ($count - $ceil) ) {
            $min = $count - $args['range'];
            $max = $count;
        } elseif ( $page >= $args['range'] && $page < ($count - $ceil) ) {
            $min = $page - $ceil;
            $max = $page + $ceil;
        }
    } else {
        $min = 1;
        $max = $count;
    }
    
    $echo = '';
    $previous = intval($page) - 1;
    $previous = esc_attr( get_pagenum_link($previous) );
    
    $firstpage = esc_attr( get_pagenum_link(1) );
    if ( $firstpage && (1 != $page) )
        $echo .= '<li class="previous page-item"><a class="page-link waves-effect waves-effect" href="' . $firstpage . '">' . __( 'First', 'wpcb_booking' ) . '</a></li>';
    if ( $previous && (1 != $page) )
        $echo .= '<li class="page-item" ><a class="page-link waves-effect waves-effect" href="' . $previous . '" title="' . __( 'previous', 'wpcb_booking') . '">' . $args['previous_string'] . '</a></li>';
    
    if ( !empty($min) && !empty($max) ) {
        for( $i = $min; $i <= $max; $i++ ) {
            if ($page == $i) {
                $echo .= '<li class="page-item active"><span class="page-link waves-effect waves-effect">' . str_pad( (int)$i, 2, '0', STR_PAD_LEFT ) . '</span></li>';
            } else {
                $echo .= sprintf( '<li class="page-item"><a class="page-link waves-effect waves-effect" href="%s">%002d</a></li>', esc_attr( get_pagenum_link($i) ), $i );
            }
        }
    }
    
    $next = intval($page) + 1;
    $next = esc_attr( get_pagenum_link($next) );
    if ($next && ($count != $page) )
        $echo .= '<li class="page-item"><a class="page-link waves-effect waves-effect" href="' . $next . '" title="' . __( 'next', 'wpcb_booking') . '">' . $args['next_string'] . '</a></li>';
    
    $lastpage = esc_attr( get_pagenum_link($count) );
    if ( $lastpage ) {
        $echo .= '<li class="next page-item"><a class="page-link waves-effect waves-effect" href="' . $lastpage . '">' . __( 'Last', 'wpcb_booking' ) . '</a></li>';
    }
    if ( isset($echo) )
        echo $args['before_output'] . $echo . $args['after_output'];
}