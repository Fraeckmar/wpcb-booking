<?php

class Booking_Shortcode
{
    function __construct()
    {
        add_shortcode('wpcb_booking', array($this, 'wpcb_booking_form'));
        add_action('wpcb_after_booking_save', array($this, 'wpcb_admin_send_email_notification'), 10, 2);
        add_action('wpcb_after_booking_save', array($this, 'wpcb_after_booking_save_callback'), 100, 2);
    }

    function wpcb_booking_form($atts)
    {
        global $wpcb_booking;
        if (isset($_POST['wpcb_booking_nonce_field']) && wp_verify_nonce($_POST['wpcb_booking_nonce_field'], 'wpcb_booking_nonce_action')) {
            $calendar_id = isset($_POST['calendar_id']) ? $_POST['calendar_id'] : 0;
            $year_month = isset($_POST['year_month']) ? $_POST['year_month'] : '';
            $selected_dates = isset($_POST['dates']) ? $_POST['dates'] : [];
            $selected_full_dates = [];
            $post_args = array(
                'post_title' => apply_filters('wpcb_booking_number', $wpcb_booking->wpcb_gen_booking_number()),
                'post_type' => 'wpcb_booking',
                'post_status' => 'publish'
            );
            $booking_id = wp_insert_post($post_args);
            if ($booking_id && $calendar_id && !empty($selected_dates)) {
                $calendar_dates = wpcb_get_new_calendar_dates($calendar_id, $year_month, $booking_id, $_POST);
                foreach ($selected_dates as $selected_date) {
                    $_date = date(wpcb_date_format(), strtotime("{$year_month}-{$selected_date}"));
                    $selected_full_dates[] = $_date;
                }
                update_post_meta($calendar_id, 'dates', $calendar_dates);
                update_post_meta($booking_id, 'calendar_id', $calendar_id);
                update_post_meta($booking_id, 'wpcb_booking_status', wpcb_booking_default_status());
                update_post_meta($booking_id, 'booked_dates', $selected_full_dates);
                if (!empty($wpcb_booking->fields())) {
                    foreach ($wpcb_booking->fields() as $section => $fields) {
                        foreach ($fields as $meta_key => $field_info) {
                            if (array_key_exists($meta_key, $_POST)) {
                                update_post_meta($booking_id, $meta_key, $_POST[$meta_key]);
                            }
                        }                        
                    }
                }

                do_action('wpcb_after_booking_save', $booking_id, $_POST);
                wpcb_set_notification("Booked Successfully!");
            }
        }

        $attributes = shortcode_atts(array(
            'id' => 0
        ), $atts, 'wpcb_booking');
        $shortcode_id = $attributes['id'];
        $calendar_id = wpcb_get_calendar_id($shortcode_id);
        if (wpcb_is_calendar_exist($calendar_id)) {
            $form_fields = !empty($wpcb_booking->fields()) ? $wpcb_booking->fields() : array();
            ob_start();
            require(WPCB_BOOKING_PLUGIN_PATH. 'templates/booking-form.tpl.php');
            return ob_get_clean();
        }        
    }

    function wpcb_after_booking_save_callback($booking_id, $data)
    {
        global $wpcb_setting;
        $page_id = $wpcb_setting->get_setting('general', 'thankyou_page');
        if (function_exists('wpcr_is_enable_payment')) {
            if (!wpcr_is_enable_payment()) {
                wp_redirect(get_permalink($page_id));
            }            
        } else if ($page_id) {
            wp_redirect(get_permalink($page_id));
        }
    }

    function wpcb_admin_send_email_notification($booking_id)
    {
        global $wpcb_booking, $wpcb_setting;
        $site_mail = get_option('new_admin_email');
        $shortcode_values = $wpcb_setting->wpcb_get_shortcode_values($booking_id);
        $mail_setting = $wpcb_setting->get_setting('email');
        if (!empty($mail_setting) && !empty($shortcode_values)) {
            foreach ($shortcode_values as $shortcode => $shortcode_val) {
                foreach ($mail_setting as $setting => $setting_val) {
                    if (empty($setting_val) && in_array($setting, array('admin_body', 'admin_footer'))) {
                        if ($setting == 'admin_body') {
                            $setting_val = wpcb_get_default_admin_mail_body();
                        }
                        if ($setting == 'admin_footer') {
                            $setting_val = wpcb_get_default_admin_mail_footer();
                        }
                    }
                    $mail_setting[$setting] = str_replace($shortcode, $shortcode_val, $setting_val);
                }
            }            
        }
        $is_enabled = array_key_exists('admin_enable', $mail_setting) ? $mail_setting['admin_enable'] : false;
        $mail_to = array_key_exists('admin_mail_to', $mail_setting) ? implode(',', $mail_setting['admin_mail_to']) : '';
        $mail_to = apply_filters('wpcb_admin_mail_to', $mail_to, $booking_id);
        $cc = array_key_exists('admin_cc', $mail_setting) ? implode(',', $mail_setting['admin_cc']) : '';
        $bcc = array_key_exists('admin_bcc', $mail_setting) ? implode(',', $mail_setting['admin_bcc']) : '';
        $subject = array_key_exists('admin_subject', $mail_setting) ? $mail_setting['admin_subject'] : '';
        $body = array_key_exists('admin_body', $mail_setting) ? $mail_setting['admin_body'] : '';
        $footer = array_key_exists('admin_footer', $mail_setting) ? $mail_setting['admin_footer'] : '';
        $mail_content = $this->wpcb_construct_mail_body($body, $footer);

        $headers = array();
        $attachments = apply_filters('wpcb_admin_mail_attachments', array(), $booking_id);
        $headers[] = 'From: ' . get_bloginfo('name') .' <'.$site_mail.'>';

        if(!empty($cc)){
            $headers[] = "cc: {$cc} \r\n";
        }
        if(!empty($bcc)){
            $headers[] = "Bcc: {$bcc} \r\n";
        }
        if ($is_enabled && !empty($mail_to)) {
            wp_mail($mail_to, $subject, $mail_content, $headers, $attachments);
        }
    }

    function wpcb_construct_mail_body($email_body, $email_footer)
    {
        $content = "<div class='wpc-email-notification-wrap' style='width: 100%; font-family: sans-serif;'>";
            $content .= "<div class='wpc-email-notification' style='padding: 3em; background: #efefef;'>";
                $content .= "<div class='wpc-email-notification-content' style='padding: 2em 2em 1em 2em; font-size: 18px;'>";
                    $content .= $email_body;
                $content .= "</div>";
                $content .= "<div class='wpc-email-notification-footer' style='font-size: 10px; text-align: center; margin: 0 auto;'>";
                    $content .= $email_footer;
                $content .= "</div>";
            $content .= "</div>";
        $content .= "</div>";
    return $content;
    }
}
$booking_shortcode = new Booking_Shortcode();