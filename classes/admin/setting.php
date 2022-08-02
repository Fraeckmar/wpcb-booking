<?php

class WPCB_Setting{
    
    function __construct()
    {
        add_action('admin_init', array($this, 'wpcb_admin_nonce_form'));
    }

    function wpcb_admin_nonce_form()
    {
        global $wpcb_booking;
        if (isset($_POST['wpcb_setting_nonce_field']) && wp_verify_nonce($_POST['wpcb_setting_nonce_field'], 'wpcb_setting_nonce_action')) {
            if (!empty($this->setting_keys())) {
                foreach ($_POST as $key => $value) {
                    if (in_array($key, $this->setting_keys()) && !empty($value)) {
                        $this->update_setting($key, $value);
                    }
                }
            }
            do_action('wpcb_after_save_settings', $_POST);
        }

        // Save calendar
        if (isset($_POST['wpcb_calendar_update_field']) && wp_verify_nonce($_POST['wpcb_calendar_update_field'], 'wpcb_calendar_update_action')) {
            $calendar_id = isset($_GET['id']) ? $_GET['id'] : 0;
            $action = isset($_GET['action']) ? $_GET['action'] : '';
            if ($action) {
                $post_args = array(
                    'post_title' => $_POST['post_title']
                );

                if ($action == 'edit') {
                    if ($calendar_id) {
                        $post_args['ID'] = $calendar_id;
                        $calendar_id = wp_update_post($post_args);
                    }                    
                } else if ($action == 'new') {
                    $post_args['post_type'] = 'wpcb_calendar';
                    $post_args['post_status'] = 'publish';
                    $post_args['post_author'] = get_current_user_id();
                    $calendar_id = wp_insert_post($post_args);
                    if ($calendar_id) {
                        update_post_meta($calendar_id, 'shortcode_id', get_next_shortcode_id());
                    }
                }
                
                $form_fields = wpcb_admin_calendar_form_fields();
                $year_month = isset($_POST['year_month']) ? $_POST['year_month'] : '';
                if ($calendar_id) {                
                    if (!empty($form_fields) && !empty($year_month)) {
                        foreach ($form_fields as $field_key => $fields) {
                            if (isset($_POST[$field_key])) {
                                $calendar_dates = get_post_meta($calendar_id, $field_key, true);
                                $calendar_dates = !empty($calendar_dates) ? $calendar_dates : [];
                                if ($field_key == 'dates') {
                                    $_dates = $_POST[$field_key];
                                    foreach ($_dates as $_date => $_data) {
                                        $calendar_dates[$year_month][$_date]['status'] = $_data['status'] ?? '';
                                        $calendar_dates[$year_month][$_date]['description'] = $_data['description'] ?? '';
                                    }
                                }
                                update_post_meta($calendar_id, $field_key, $calendar_dates);
                            }
                        }
                    }
                }
            }

            do_action('wpcb_after_save_calendar', $_POST);
            if ($action == 'new') {
                wp_redirect(admin_url("admin.php?page=wpcb-calendar&action=edit&id={$calendar_id}"));
            }            
        }

        // Manage Bookings
        if (isset($_POST['wpcb_booking_update_nonce_field']) 
            && wp_verify_nonce($_POST['wpcb_booking_update_nonce_field'], 'wpcb_booking_update_nonce_action')
        ) {
            $booking_fields = $wpcb_booking->fields();
            $booking_id = isset($_GET['id']) ? $_GET['id'] : 0;
            $calendar_id = $booking_id ? get_post_meta($booking_id, 'calendar_id', true) : 0;
            $action = isset($_GET['action']) ? $_GET['action'] : '';
            if ($action) {
                $post_args = array(
                    'post_title' => $_POST['post_title']
                );

                if ($action == 'edit') {
                    if ($booking_id) {
                        $post_args['ID'] = $booking_id;
                        wp_update_post($post_args);
                    }                    
                } else if ($action == 'new') {
                    $post_args['post_type'] = 'wpcb_booking';
                    $post_args['post_status'] = 'publish';
                    $post_args['post_author'] = get_current_user_id();
                    $booking_id = wp_insert_post($post_args);
                    $calendar_id = isset($_POST['calendar_id']) ? $_POST['calendar_id'] : 0;
                    update_post_meta($booking_id, 'calendar_id', $calendar_id);

                    if ($calendar_id && $booking_id) {
                        $selected_full_dates = [];
                        $year_month = isset($_POST['year_month']) ? $_POST['year_month'] : '';
                        $calendar_dates = wpcb_get_new_calendar_dates($calendar_id, $year_month, $booking_id, $_POST);
                        $selected_dates = isset($_POST['dates']) ? $_POST['dates'] : [];
                        if (!empty($selected_dates)) {
                            foreach ($selected_dates as $selected_date) {
                                $_date = date(wpcb_date_format(), strtotime("{$year_month}-{$selected_date}"));
                                $selected_full_dates[] = $_date;
                            }
                        }
                        update_post_meta($calendar_id, 'dates', $calendar_dates);
                        update_post_meta($booking_id, 'booked_dates', $selected_full_dates);
                    }
                }

                if ($booking_id) {
                    if (!empty($booking_fields)) {
                        foreach ($booking_fields as $section => $fields) {
                            foreach ($fields as $field_key => $field_atts) {
                                if (isset($_POST[$field_key])) {
                                    update_post_meta($booking_id, $field_key, $_POST[$field_key]);
                                }
                            }
                        }
                    }
                    if (isset($_POST['wpcb_booking_status'])) {
                        update_post_meta($booking_id, 'wpcb_booking_status', $_POST['wpcb_booking_status']);
                    }
                    do_action('wpcb_after_save_booking', $booking_id, $_POST);
                    $notif_action = $action == 'edit' ? 'updated' : 'added';
                    $booking_title = $booking_id ? get_the_title($booking_id) : '';
                    wpcb_set_notification("<strong>{$booking_title}</strong> {$notif_action} successfully!");
                }
            }
        }
    }

    function wpcb_settings()
    {
        $wpcb_settings = get_option('wpcb_settings');
        return !empty($wpcb_settings) ? $wpcb_settings : array();
    }

    function update_setting($setting_key, $value)
    {
        $wpcb_settings = $this->wpcb_settings();
        $wpcb_settings[$setting_key] = $value;
        update_option('wpcb_settings', $wpcb_settings);
    }

    function get_setting($setting_key='', $field='', $all_fields=false)
    {
        $wpcb_settings = $this->wpcb_settings();
        $result = ($all_fields) ? $wpcb_settings : null;
        if (array_key_exists($setting_key, $wpcb_settings)) {
            $settings = $wpcb_settings[$setting_key];
            if (array_key_exists($field, $settings)) {
                $result = $settings[$field];
            } else if ($field == 'booking_status_list'){
                $result = array('Pending Approval', 'Pending Payment', 'Approved');
            } else if (!empty($setting_key) && empty($field)){
                $result = $settings;
            }
        }
        return $result;
    }

    function menus()
    {
        $setting_menus = array(
            'general' => array(
                'label' => __('General Setting', 'wpcb_booking'),
                'file_path' => wpcb_get_template('general-setting.tpl', true),
                'has_form' => true,
                'in_save_setting' => true
            ),
            'email' => array(
                'label' => __('Email Setting', 'wpcb_booking'),
                'file_path' => wpcb_get_template('email-setting.tpl', true),
                'has_form' => true,
                'in_save_setting' => true
            )
        );
        return apply_filters('wpcb_setting_menus', $setting_menus);
    }

    function setting_keys()
    {
        $setting_keys = [];
        if (!empty($this->menus())) {
            foreach ($this->menus() as $menu => $option) {
                if (in_array('in_save_setting', $option) && $option['in_save_setting']) {
                    $setting_keys[] = $menu;
                }
            }
        }
        return apply_filters('wpcb_setting_keys', $setting_keys);
    }

    function fields()
    {
        global $wpcb_booking;
        $pages = wpcb_get_pages();
        $booking_status_list = $this->get_setting('general', 'booking_status_list');
        $admin_default_email = get_option('new_admin_email');
        $wpcb_setting_fields = array(
            'general' => array(
                array(
                    'fields' => array(
                        array(
                            'key' => 'thankyou_page',
                            'label' => __('Thank you Page'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'form-control selectize',
                            'options' => $pages,
                            'value' => $this->get_setting('general', 'thankyou_page'),
                            'setting' => 'general'
                        )
                    )
                        ),
                array(
                    'heading' => __('Calendar', 'wpcb_booking'),
                    'fields' => array(
                        array(
                            'key' => 'day_name_font_size',
                            'label' => __('Day name font size (px)', 'wpcb_booking'),
                            'type' => 'number',
                            'required' => false,
                            'placeholder' => 'auto',
                            'class' => 'form-control',
                            'options' => array(),
                            'value' => $this->get_setting('general', 'day_name_font_size'),
                            'setting' => 'general',
                            'extras' => ''
                        ),
                        array(
                            'key' => 'date_nos_font_size',
                            'label' => __('Date numbers font size (px)', 'wpcb_booking'),
                            'type' => 'number',
                            'required' => false,
                            'placeholder' => 'auto',
                            'class' => 'form-control',
                            'options' => array(),
                            'value' => $this->get_setting('general', 'date_nos_font_size'),
                            'setting' => 'general',
                            'extras' => ''
                        ),
                        array(
                            'key' => 'week_starts',
                            'label' => __('Day starts on', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'placeholder' => '',
                            'class' => 'form-control selectize',
                            'options' => $wpcb_booking->get_week_days(),
                            'value' => $this->get_setting('general', 'week_starts'),
                            'setting' => 'general',
                        ),
                        // array(
                        //     'key' => 'can_select_nultiple',
                        //     'label' => __('Allow customer can select multiple dates?', 'wpcb_booking'),
                        //     'type' => 'checkbox',
                        //     'required' => false,
                        //     'placeholder' => '',
                        //     'class' => 'form-check-input',
                        //     'options' => array('Yes' => 'Yes'),
                        //     'value' => $this->get_setting('general', 'can_select_nultiple') ?? array(),
                        //     'setting' => 'general',
                        //     'extras' => ''
                        // ),
                        array(
                            'key' => 'booking_status_list',
                            'label' => __('Add Calendar Status', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'placeholder' => '',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'class' => 'selectize rounded border form-control',
                            'options' => $booking_status_list,
                            'value' => $booking_status_list,
                            'setting' => 'general',
                            'extras' => 'multiple data-allow_create=true data-has_remove=true',
                        )
                    )
                ),
                array(
                    'heading' => __('Availability', 'wpcb_booking'),
                    'fields' => array(
                        array(
                            'key' => 'enable_days',
                            'label' => __('Enable days', 'wpcb_booking'),
                            'type' => 'checkbox',
                            'required' => false,
                            'placeholder' => '',
                            'options' => $wpcb_booking->get_week_days(),
                            'value' => $this->get_setting('general', 'enable_days') ?? array(),
                            'setting' => 'general',
                            'extras' => ''
                        )
                    )
                )
            ),
            'email' => array(
                array(
                    'heading' => __('Admin Email Setting', 'wpcb_booking'),
                    'fields' => array(
                        array(
                            'key' => 'admin_enable',
                            'label' => __('Enable?', 'wpcb_booking'),
                            'type' => 'radio',
                            'required' => true,
                            'class' => '',
                            'group_class' => 'form-check-inline',
                            'options' => array('Yes', 'No'),
                            'value' => $this->get_setting('email', 'admin_enable') ?? 'Yes',
                            'setting' => 'email'
                        ),
                        array(
                            'key' => 'admin_mail_to',
                            'label' => __('Mail To', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => true,
                            'class' => 'selectize',
                            'options' => array($admin_default_email),
                            'value' => $this->get_setting('email', 'admin_mail_to') ?? $admin_default_email,
                            'placeholder' => 'Cc',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email',
                        ),
                        array(
                            'key' => 'admin_cc',
                            'label' => __('Cc', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email', 'admin_cc'),
                            'value' => $this->get_setting('email', 'admin_cc'),
                            'placeholder' => 'Cc',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email',
                        ),
                        array(
                            'key' => 'admin_bcc',
                            'label' => __('Bcc', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email', 'admin_bcc'),
                            'value' => $this->get_setting('email', 'admin_bcc'),
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'placeholder' => 'Bcc',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email',
                        ),
                        array(
                            'key' => 'admin_subject',
                            'label' => __('Subject', 'wpcb_booking'),
                            'type' => 'text',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email', 'admin_subject') ?? 'New Booking',     
                            'placeholder' => 'New Booking',                       
                            'setting' => 'email',
                        ),
                        array(
                            'key' => 'admin_body',
                            'label' => __('Body', 'wpcb_booking'),
                            'type' => 'textarea',
                            'required' => false,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email', 'admin_body'),     
                            'placeholder' => wpcb_get_default_admin_mail_body(),                       
                            'extras' => 'rows="6"',
                            'setting' => 'email',
                        ),
                        array(
                            'key' => 'admin_footer',
                            'label' => __('Footer', 'wpcb_booking'),
                            'type' => 'textarea',
                            'required' => false,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email', 'admin_footer'),
                            'placeholder' => wpcb_get_default_admin_mail_footer(),
                            'setting' => 'email',
                        )
                    )
                )
            ),
        );
        return apply_filters('wpcb_setting_fields', $wpcb_setting_fields);
    }    

    function wpcb_get_shortcode_list()
    {
        global $wpcb_booking;
        $shortcodes = array(
            'general' => array(
                '{wpcb_booking_number}' => 'Booking Number',
                '{wpcb_booking_status}' => 'Booking Status',
            )
        );
        if (!empty($wpcb_booking->fields())) {
            foreach ($wpcb_booking->fields() as $section => $fields) {
                if (empty($fields)) {
                    continue;
                }
                if ($section == 'Personal Information') {
                    $section = 'Customer Information';
                }
                $section = strtolower(str_replace(' ', '_', $section));
                foreach ($fields as $field_key => $field) {
                    $shortcodes[$section]['{'.$field_key.'}'] = $field['label'];
                }                
            }
        }
        return apply_filters('wpcb_shortcode_list', $shortcodes);
    }

    function wpcb_get_shortcode_values($booking_id)
    {
        global $wpcb_booking;
        $meta_values = $wpcb_booking->wpcb_get_booking_details($booking_id);
        $shortcodes_list = $this->wpcb_get_shortcode_list();
        $shortcodes_data = [];
        if (!empty($shortcodes_list)) {
            foreach ($shortcodes_list as $heading => $shortcodes) {
                if (empty($shortcodes)) { continue; }
                foreach ($shortcodes as $shortcode => $description) {
                    $shortcode = str_replace(['{','}'], '', $shortcode);
                    $shortcode_value = array_key_exists($shortcode, $meta_values) ? $meta_values[$shortcode] : '{'.$shortcode.'}';
                    if ($shortcode == 'wpcb_booking_number') {
                        $shortcode_value = get_the_title($booking_id);
                    }
                    if (is_array($shortcode_value)) {
                        $str_value = "<ul>";
                        foreach ($shortcode_value as $_value) {
                            $str_value .= "<li> {$_value} </li>";
                        }
                        $str_value .= '</ul>';
                        $shortcode_value = $str_value;
                    }
                    $shortcodes_data['{'.$shortcode.'}'] = $shortcode_value;
                }
            }
        }
        return apply_filters('wpcb_shortcode_values', $shortcodes_data, $booking_id);
    }

    function wpcb_draw_shortcode_list()
    {
        $shortcodes_list = $this->wpcb_get_shortcode_list();
        echo "<div class='row'>";
            echo "<div id='shortcode-list' class='col-lg-6 col-md-12'>";
                echo "<table class='table table-bordered'>";
                    echo "<thead>";
                        echo "<tr>";
                            echo "<td class='p-2'><strong>".__('Shortcode', 'wpcb_booking')."</strong></td>";
                            echo "<td class='p-2'><strong>".__('Description', 'wpcb_booking')."</strong></td>";
                        echo "<tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    if (!empty($shortcodes_list)) {
                        foreach ($shortcodes_list as $heading => $shortcodes) {
                            $heading = ucwords(str_replace('_', ' ', $heading));
                            echo "<tr class='heading'>";
                                echo "<td colspan='2' class='p-2'><strong>{$heading}</strong></td>";
                            echo "</tr>";
                            foreach ($shortcodes as $shortcode => $description) {
                                echo "<tr>";
                                    echo "<td width='40%' class='p-2'><span class='shortcode'> {$shortcode} </span></td>";
                                    echo "<td width='60%' class='p-2'> {$description} </td>";
                                echo "</tr>";
                            }
                        }
                    }
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
        echo "</div>";
    }
}
$wpcb_setting = new WPCB_Setting();