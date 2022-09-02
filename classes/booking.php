<?php

class WPCB_Booking {
    public function fields($booking_id=0)
    {
        $wpcb_name = '';
        $wpcb_email = '';
        $wpcb_phone_number = '';
        if ($booking_id) {
            $wpcb_name = get_post_meta($booking_id, 'wpcb_name', true);
            $wpcb_email = get_post_meta($booking_id, 'wpcb_email', true);
            $wpcb_phone_number = get_post_meta($booking_id, 'wpcb_phone_number', true);
        }
        $wpcb_booking_fields = array(
            'Personal Information' => array(
                'customer_name' => array(
                    'key' => 'wpcb_name',
                    'label' => __('Name', 'wpcb_booking'),
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Your full name',
                    'options' => array(),
                    'value' => $wpcb_name
                ),
                'customer_email' => array(
                    'key' => 'wpcb_email',
                    'label' => __('Email', 'wpcb_booking'),
                    'type' => 'email',
                    'required' => true,
                    'placeholder' => 'example@gmail.com',
                    'options' => array(),
                    'value' => $wpcb_email
                ),
                'customer_phone_number' => array(
                    'key' => 'wpcb_phone_number',
                    'label' => __('Phone Number', 'wpcb_booking'),
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'options' => array(),
                    'value' => $wpcb_phone_number
                )
            )
        );
        return apply_filters('wpcb_booking_fields', $wpcb_booking_fields, $booking_id);
    }

    public function field_class()
    {
        $field_class = array(
            'text' => 'form-control',
            'number' => 'form-control',
            'email' => 'form-control',
            'textarea' => 'form-control',
            'select' => 'browser-default',
            'checkbox' => 'form-check-input',
            'radio' => 'form-check-input',
            'link' => 'btn btn-link',
            'button' => 'btn btn-primary',
            'date' => 'form-control date_picker w-md-50 w-sm-100'
        );
        return apply_filters('wpcb_booking_field_class', $field_class);
    }

    public function get_week_days()
    {
        global $wpcb_setting;
        $week_days = array(
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat'
        );
        $week_starts = $wpcb_setting->get_setting('general', 'week_starts');
        $week_starts = !empty($week_starts) ? array_search($week_starts, $week_days) : 0;
        $new_weeks = [];
        $cutted_days = [];
        foreach ($week_days as $idx => $day) {
            if ($idx >= $week_starts) {
                $new_weeks[] = $day;
            } else {
                $cutted_days[$idx] = $day;
            }
        }
        if (!empty($cutted_days)) {
            foreach ($cutted_days as $day) {
                $new_weeks[] = $day;
            }
        }
        return apply_filters('wpcb_booking_week_days', $new_weeks);
    }

    function wpcb_gen_booking_number()
    {
        $prefix = apply_filters('wpcb_prefix_booking_number', 'BOOK');
        $suffix = apply_filters('wpcb_suffix_booking_number', '');
        $gen_booking_length = apply_filters('gen_booking_length', 4);
        $rand_numbers = $this->gen_rand_numbers($gen_booking_length);
        $booking_number = $prefix.$rand_numbers.$suffix;
        if ($this->wpcb_is_booking_number_exist($booking_number)) {
            $this->wpcb_gen_booking_number();
        }
        return apply_filters('wpcb_generate_booking_number', $booking_number);
    }
    function gen_rand_numbers($length=4)
    {   
        $min = str_pad("", $length, "00");
        $max = pow(10, $length)-1;
        return str_pad(mt_rand($min, $max), $length, "0", STR_PAD_LEFT);
    }

    function wpcb_is_booking_number_exist($booking_number)
    {
        global $wpdb;
        $sql = "SELECT * FROM `{$wpdb->prefix}posts` WHERE post_title = %s AND post_type = 'wpcb_booking' LIMIT 1";
        $result = $wpdb->get_var($wpdb->prepare($sql, $booking_number));
        return !empty($result);
    }

    function wpcb_get_booking_details($booking_id)
    {
        global $wpdb;
        $sql = "SELECT * FROM `{$wpdb->prefix}postmeta` WHERE post_id = %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, $booking_id));
        $details = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $details[$result->meta_key] = maybe_unserialize($result->meta_value);
            }
        }
        return $details;
    }
}
$wpcb_booking = new WPCB_Booking();