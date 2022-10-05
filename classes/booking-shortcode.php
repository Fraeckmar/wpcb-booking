<?php

class Booking_Shortcode
{
    function __construct()
    {
        add_shortcode('wpcb_booking', array($this, 'wpcb_booking_form'));
        add_action('wpcb_after_booking_send_email', array($this, 'wpcb_admin_send_email_notification'), 10, 2);
        add_action('wpcb_after_booking_send_email', array($this, 'wpcb_client_send_email_notification'), 10, 3);
        add_action('wpcb_after_save_booking_post', array($this, 'wpcb_client_send_email_notification'), 10, 3);
        add_action('wpcb_after_booking_save', array($this, 'wpcb_after_booking_save_callback'), 100, 2);
    }

    function wpcb_booking_form($atts)
    {
        global $wpcb_booking;
        if (isset($_POST['wpcb_booking_nonce_field']) && wp_verify_nonce($_POST['wpcb_booking_nonce_field'], 'wpcb_booking_nonce_action')) {
            $calendar_id = isset($_POST['calendar_id']) && is_numeric($_POST['calendar_id']) ? sanitize_text_field($_POST['calendar_id']) : 0;
            $year_month = isset($_POST['year_month']) ? sanitize_text_field($_POST['year_month']) : '';
            $selected_dates = isset($_POST['dates']) ? wpcb_sanitize_data($_POST['dates']) : [];
            $selected_full_dates = [];
            $post_args = array(
                'post_title' => sanitize_text_field(apply_filters('wpcb_booking_number', $wpcb_booking->wpcb_gen_booking_number())),
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
                update_post_meta($booking_id, 'wpcb_booking_status', sanitize_text_field(wpcb_booking_default_status()));
                update_post_meta($booking_id, 'booked_dates', $selected_full_dates);
                if (!empty($wpcb_booking->fields())) {
                    foreach ($wpcb_booking->fields() as $section => $fields) {
                        foreach ($fields as $field_key => $field) {
                            if (isset($_POST[$field['key']])) {
                                if (is_array($_POST[$field['key']])) {
                                    array_walk($_POST[$field['key']], function($value) use ($field){
                                        $value = !is_array($value) ? sanitize_text_field($_POST[$field['key']]) : $value;
                                    });
                                }
                                update_post_meta($booking_id, $field['key'], $_POST[$field['key']]);
                            }
                        }                        
                    }
                }
                
                do_action('wpcb_after_booking_save', $booking_id, $_POST);                
                do_action('wpcb_after_booking_send_email', $booking_id, $_POST, '');
                wpcb_set_notification("Booked Successfully!");
            }
        }

        $attributes = shortcode_atts(array(
            'id' => 0
        ), $atts, 'wpcb_booking');
        $shortcode_id = sanitize_text_field($attributes['id']);
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
            if (!wpcr_is_enable_payment() && $page_id) {
                wp_redirect(esc_url(get_permalink($page_id)));
            }            
        } else if ($page_id) {
            wp_redirect(esc_url(get_permalink($page_id)));
        }
    }

    function wpcb_admin_send_email_notification($booking_id)
    {
        if (get_post_status($booking_id) != 'publish') {
            return false;
        }
        global $wpcb_booking, $wpcb_setting;
        $site_mail = sanitize_email(get_option('new_admin_email'));
        $shortcode_values = $wpcb_setting->wpcb_get_shortcode_values($booking_id);
        $mail_setting = $wpcb_setting->get_setting('email_admin');
        $is_enabled = array_key_exists('admin_enable', $mail_setting) ? $mail_setting['admin_enable'] : true;
        if (!$is_enabled) {
            return false;
        }

        if (!empty($mail_setting) && !empty($shortcode_values)) {
            foreach ($shortcode_values as $shortcode => $shortcode_val) {
                $shortcode_val = sanitize_text_field($shortcode_val);
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
        
        $mail_to = array_key_exists('admin_mail_to', $mail_setting) ? implode(',', wpcb_sanitize_data($mail_setting['admin_mail_to'], 'email')) : '';
        $mail_to = apply_filters('wpcb_admin_mail_to', $mail_to, $booking_id);
        $cc = array_key_exists('admin_cc', $mail_setting) ? implode(',', wpcb_sanitize_data($mail_setting['admin_cc'], 'email')) : '';
        $bcc = array_key_exists('admin_bcc', $mail_setting) ? implode(',', wpcb_sanitize_data($mail_setting['admin_bcc'], 'email')) : '';
        $subject = array_key_exists('admin_subject', $mail_setting) ? $mail_setting['admin_subject'] : '';
        $body = array_key_exists('admin_body', $mail_setting) ? $mail_setting['admin_body'] : '';
        $footer = array_key_exists('admin_footer', $mail_setting) ? $mail_setting['admin_footer'] : '';
        $mail_content = wp_kses($wpcb_setting->wpcb_construct_mail_body($body, $footer), wpcb_allowed_html_tags());

        $headers = array();
        $attachments = apply_filters('wpcb_admin_mail_attachments', array(), $booking_id);
        $headers[] = esc_html('From: ' . get_bloginfo('name') .' <'.$site_mail.'>');

        if(!empty($cc)){
            $headers[] = "cc: {$cc} \r\n";
        }
        if(!empty($bcc)){
            $headers[] = "Bcc: {$bcc} \r\n";
        }
        if (!empty($mail_to)) {
            wp_mail($mail_to, $subject, $mail_content, $headers, $attachments);
        }
    }

    function wpcb_client_send_email_notification($booking_id, $data, $old_status)
    {
        if (get_post_status($booking_id) != 'publish') {
            return false;
        }
        global $wpcb_booking, $wpcb_setting;
        $site_mail = sanitize_email(get_option('new_admin_email'));
        $shortcode_values = $wpcb_setting->wpcb_get_shortcode_values($booking_id);
        $booking_status = sanitize_text_field(get_post_meta($booking_id, 'wpcb_booking_status', true));
        $mail_setting = $wpcb_setting->get_setting('email_client');
        $is_enabled = array_key_exists('client_enable', $mail_setting) ? $mail_setting['client_enable'] : true;
        if (!$is_enabled || $booking_status == $old_status) {
            return false;
        }
        if (!empty($mail_setting) && !empty($shortcode_values)) {
            foreach ($shortcode_values as $shortcode => $shortcode_val) {
                foreach ($mail_setting as $setting => $setting_val) {
                    if (empty($setting_val) && in_array($setting, array('client_body', 'client_footer'))) {
                        if ($setting == 'client_body') {
                            $setting_val = wpcb_get_default_client_mail_body();
                        }
                        if ($setting == 'client_footer') {
                            $setting_val = wpcb_get_default_client_mail_footer();
                        }
                    }
                    $mail_setting[$setting] = str_replace($shortcode, $shortcode_val, $setting_val);
                }
            }            
        }
        
        $mail_to = array_key_exists('client_mail_to', $mail_setting) ? implode(',', wpcb_sanitize_data($mail_setting['client_mail_to'], 'email')) : '';
        $mail_to = apply_filters('wpcb_client_mail_to', $mail_to, $booking_id);
        $cc = array_key_exists('client_cc', $mail_setting) ? implode(',', wpcb_sanitize_data($mail_setting['client_cc'], 'email')) : '';
        $bcc = array_key_exists('client_bcc', $mail_setting) ? implode(',', wpcb_sanitize_data($mail_setting['client_bcc'], 'email')) : '';
        $subject = array_key_exists('client_subject', $mail_setting) ? $mail_setting['client_subject'] : '';
        $body = array_key_exists('client_body', $mail_setting) ? $mail_setting['client_body'] : '';
        $footer = array_key_exists('client_footer', $mail_setting) ? $mail_setting['client_footer'] : '';
        $mail_content = wp_kses($wpcb_setting->wpcb_construct_mail_body($body, $footer), wpcb_allowed_html_tags());

        $headers = array();
        $attachments = apply_filters('wpcb_client_mail_attachments', array(), $booking_id);
        $headers[] = esc_html('From: ' . get_bloginfo('name') .' <'.$site_mail.'>');

        if(!empty($cc)){
            $headers[] = "cc: {$cc} \r\n";
        }
        if(!empty($bcc)){
            $headers[] = "Bcc: {$bcc} \r\n";
        }
        if (!empty($mail_to)) {
            wp_mail($mail_to, $subject, $mail_content, $headers, $attachments);
        }
    }
}
$booking_shortcode = new Booking_Shortcode();