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
                    $value = wpcb_sanitize_data($value);
                    if (in_array($key, $this->setting_keys()) && !empty($value)) {
                        $this->update_setting($key, $value);
                    }
                }
            }
            do_action('wpcb_after_save_settings', $_POST);
        }

        // Save calendar
        if (isset($_POST['wpcb_calendar_update_field']) && wp_verify_nonce($_POST['wpcb_calendar_update_field'], 'wpcb_calendar_update_action')) {
            $calendar_id = isset($_GET['id']) && is_numeric($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;
            $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
            if ($action) {
                $post_args = array(
                    'post_title' => sanitize_text_field($_POST['post_title'])
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
                $year_month = isset($_POST['year_month']) ? sanitize_text_field($_POST['year_month']) : '';
                if ($calendar_id) {                
                    if (!empty($form_fields) && !empty($year_month)) {
                        foreach ($form_fields as $field_key => $fields) {
                            if (isset($_POST[$field_key])) {
                                $calendar_dates = get_post_meta($calendar_id, $field_key, true);
                                $calendar_dates = !empty($calendar_dates) ? $calendar_dates : [];
                                if ($field_key == 'dates') {
                                    $_dates = wpcb_sanitize_data($_POST[$field_key]);
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
                wp_redirect(esc_url(admin_url("admin.php?page=wpcb-calendar&action=edit&id={$calendar_id}")));
            }            
        }

        // Manage Bookings
        if (isset($_POST['wpcb_booking_update_nonce_field']) 
            && wp_verify_nonce($_POST['wpcb_booking_update_nonce_field'], 'wpcb_booking_update_nonce_action')
        ) {
            $booking_fields = $wpcb_booking->fields();
            $booking_id = isset($_GET['id']) && is_numeric($_GET['id']) ? wpcb_sanitize_data($_GET['id']) : 0;
            $calendar_id = $booking_id ? get_post_meta($booking_id, 'calendar_id', true) : 0;
            $action = isset($_GET['action']) ? wpcb_sanitize_data($_GET['action']) : '';
            $old_status = '';
            if ($action) {
                $post_args = array(
                    'post_title' => wpcb_sanitize_data($_POST['post_title'])
                );

                if ($action == 'edit') {
                    if ($booking_id) {
                        $post_args['ID'] = $booking_id;
                        wp_update_post($post_args);
                        $old_status = get_post_meta($booking_id, 'wpcb_booking_status',  true);
                    }                    
                } else if ($action == 'new') {
                    $post_args['post_type'] = 'wpcb_booking';
                    $post_args['post_status'] = 'publish';
                    $post_args['post_author'] = get_current_user_id();
                    $booking_id = wp_insert_post($post_args);
                    $calendar_id = isset($_POST['calendar_id']) ? wpcb_sanitize_data($_POST['calendar_id']) : 0;
                    update_post_meta($booking_id, 'calendar_id', $calendar_id);

                    if ($calendar_id && $booking_id) {
                        $selected_full_dates = [];
                        $year_month = isset($_POST['year_month']) ? wpcb_sanitize_data($_POST['year_month']) : '';
                        $calendar_dates = wpcb_sanitize_data(wpcb_get_new_calendar_dates($calendar_id, $year_month, $booking_id, $_POST));
                        $selected_dates = isset($_POST['dates']) ? wpcb_sanitize_data($_POST['dates']) : [];
                        if (!empty($selected_dates)) {
                            foreach ($selected_dates as $selected_date) {
                                $_date = date(wpcb_date_format(), strtotime("{$year_month}-{$selected_date}"));
                                $selected_full_dates[] = wpcb_sanitize_data($_date);
                            }
                        }
                        update_post_meta($calendar_id, 'dates', $calendar_dates);
                        update_post_meta($booking_id, 'booked_dates', $selected_full_dates);
                    }
                }

                if ($booking_id) {                   
                    if (!empty($booking_fields)) {
                        foreach ($booking_fields as $section => $fields) {
                            foreach ($fields as $field_key => $field) {
                                if (isset($_POST[$field['key']])) {
                                    $meta_value = wpcb_sanitize_data($_POST[$field['key']]);
                                    update_post_meta($booking_id, sanitize_key($field['key']), $meta_value);
                                }
                            }
                        }
                    }
                    if (isset($_POST['wpcb_booking_status'])) {
                        update_post_meta($booking_id, 'wpcb_booking_status', wpcb_sanitize_data($_POST['wpcb_booking_status']));
                    }
                    do_action('wpcb_after_save_booking_post', $booking_id, $_POST, $old_status);
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
        $result = ($all_fields) ? $wpcb_settings : 0;
        if (empty($field) && empty($result)) {
            $result = array();
        }
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
        return wpcb_sanitize_data($result);
    }

    function menus()
    {
        $setting_menus = array(
            'general' => array(
                'label' => esc_html__('General Setting', 'wpcb_booking'),
                'file_path' => wpcb_get_template('general-setting.tpl', true),
                'has_form' => true,
                'in_save_setting' => true
            ),
            'email_admin' => array(
                'label' => esc_html__('Admin Email', 'wpcb_booking'),
                'file_path' => wpcb_get_template('email/admin-email-setting.tpl', true),
                'has_form' => true,
                'in_save_setting' => true
            ),
            'email_client' => array(
                'label' => esc_html__('Client Email', 'wpcb_booking'),
                'file_path' => wpcb_get_template('email/client-email-setting.tpl', true),
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
        return wpcb_sanitize_data(apply_filters('wpcb_setting_keys', $setting_keys));
    }

    function fields()
    {
        global $wpcb_booking, $wpcb_setting;
        $pages = wpcb_get_pages();
        $booking_status_list = $wpcb_setting->wpcb_status_list();
        $admin_default_email = sanitize_email(get_option('new_admin_email'));
        $wpcb_setting_fields = array(
            'general' => array(
                array(
                    'fields' => array(
                        array(
                            'key' => 'company_logo',
                            'label' => esc_html__('Company Logo', 'wpcb_booking'),
                            'placeholder' => 'https://www.yourdomain.com/image/sample.jpg',
                            'description' => 'Image url only.',
                            'type' => 'text',
                            'required' => false,
                            'class' => 'form-control',
                            'value' => $this->get_setting('general', 'company_logo'),
                            'setting' => 'general'
                        ),
                        array(
                            'key' => 'thankyou_page',
                            'label' => esc_html__('Thank you Page', 'wpcb_booking'),
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
                    'heading' => esc_html__('Calendar', 'wpcb_booking'),
                    'fields' => array(
                        array(
                            'key' => 'day_name_font_size',
                            'label' => esc_html__('Day name font size (px)', 'wpcb_booking'),
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
                            'label' => esc_html__('Date numbers font size (px)', 'wpcb_booking'),
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
                            'label' => esc_html__('Day starts on', 'wpcb_booking'),
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
                            'label' => esc_html__('Add Calendar Status', 'wpcb_booking'),
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
                    'heading' => esc_html__('Availability', 'wpcb_booking'),
                    'fields' => array(
                        array(
                            'key' => 'enable_days',
                            'label' => esc_html__('Enable days', 'wpcb_booking'),
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
            'email_admin' => array(
                array(
                    'heading' => esc_html__('Admin Email Setting', 'wpcb_booking'),
                    'fields' => array(
                        array(
                            'key' => 'admin_enable',
                            'label' => esc_html__('Enable?', 'wpcb_booking'),
                            'type' => 'radio',
                            'required' => true,
                            'class' => '',
                            'group_class' => 'form-check-inline',
                            'options' => array('Yes', 'No'),
                            'value' => !empty($this->get_setting('email_admin', 'admin_enable')) ? $this->get_setting('email_admin', 'admin_enable') : 'Yes',
                            'setting' => 'email_admin'
                        ),
                        array(
                            'key' => 'admin_mail_to',
                            'label' => esc_html__('Mail To', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => true,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email_admin', 'admin_mail_to') ?? array($admin_default_email),
                            'value' => $this->get_setting('email_admin', 'admin_mail_to') ?? $admin_default_email,
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_cc',
                            'label' => esc_html__('Cc', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email_admin', 'admin_cc'),
                            'value' => $this->get_setting('email_admin', 'admin_cc'),
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_bcc',
                            'label' => esc_html__('Bcc', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email_admin', 'admin_bcc'),
                            'value' => $this->get_setting('email_admin', 'admin_bcc'),
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'placeholder' => 'sample@gmail.com',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_subject',
                            'label' => esc_html__('Subject', 'wpcb_booking'),
                            'type' => 'text',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email_admin', 'admin_subject') ?? 'New Booking',     
                            'placeholder' => 'New Booking',                       
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_body',
                            'label' => esc_html__('Body', 'wpcb_booking'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email_admin', 'admin_body'),     
                            'placeholder' => wpcb_get_default_admin_mail_body(),                       
                            'extras' => 'rows="6"',
                            'setting' => 'email_admin',
                        ),
                        array(
                            'key' => 'admin_footer',
                            'label' => esc_html__('Footer', 'wpcb_booking'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email_admin', 'admin_footer'),
                            'placeholder' => wpcb_get_default_admin_mail_footer(),
                            'setting' => 'email_admin',
                        )
                    )
                )
            ),
            'email_client' => array(
                array(
                    'heading' => esc_html__('Client Email Setting', 'wpcb_booking'),
                    'fields' => array(
                        array(
                            'key' => 'client_enable',
                            'label' => esc_html__('Enable?', 'wpcb_booking'),
                            'type' => 'radio',
                            'required' => true,
                            'class' => '',
                            'group_class' => 'form-check-inline',
                            'options' => array('Yes', 'No'),
                            'value' => !empty($this->get_setting('email_client', 'client_enable')) ? $this->get_setting('email_client', 'client_enable') : 'Yes',
                            'setting' => 'email_client'
                        ),
                        array(
                            'key' => 'client_mail_to',
                            'label' => esc_html__('Mail To', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => true,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email_client', 'client_mail_to') ?? array($admin_default_email),
                            'value' => $this->get_setting('email_client', 'client_mail_to') ?? $admin_default_email,
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_cc',
                            'label' => esc_html__('Cc', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email_client', 'client_cc'),
                            'value' => $this->get_setting('email_client', 'client_cc'),
                            'placeholder' => 'sample@gmail.com',
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_bcc',
                            'label' => esc_html__('Bcc', 'wpcb_booking'),
                            'type' => 'select',
                            'required' => false,
                            'class' => 'selectize',
                            'options' => $this->get_setting('email_client', 'client_bcc'),
                            'value' => $this->get_setting('email_client', 'client_bcc'),
                            'description' => '<strong>Note:</strong> Type and select to add new item',
                            'placeholder' => 'sample@gmail.com',
                            'extras' => 'multiple data-has_remove="true" data-allow_create="true"',
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_subject',
                            'label' => esc_html__('Subject', 'wpcb_booking'),
                            'type' => 'text',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email_client', 'client_subject') ?? 'Booking Number #{wpcb_booking_number}',     
                            'placeholder' => 'Booking Number #{wpcb_booking_number}',                       
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_body',
                            'label' => esc_html__('Body', 'wpcb_booking'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email_client', 'client_body'),     
                            'placeholder' => wpcb_get_default_client_mail_body(),                       
                            'extras' => 'rows="6"',
                            'setting' => 'email_client',
                        ),
                        array(
                            'key' => 'client_footer',
                            'label' => esc_html__('Footer', 'wpcb_booking'),
                            'type' => 'textarea',
                            'required' => true,
                            'class' => 'form-control',
                            'value' => $this->get_setting('email_client', 'client_footer'),
                            'placeholder' => wpcb_get_default_client_mail_footer(),
                            'setting' => 'email_client',
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
                        $str_value = "<ul style='list-style-type: disc; list-style-position: inside;'>";
                        foreach ($shortcode_value as $_value) {
                            $str_value .= "<li>".esc_html($_value)."</li>";
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
                            echo "<td class='p-2'><strong>".esc_html__('Shortcode', 'wpcb_booking')."</strong></td>";
                            echo "<td class='p-2'><strong>".esc_html__('Description', 'wpcb_booking')."</strong></td>";
                        echo "<tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    if (!empty($shortcodes_list)) {
                        foreach ($shortcodes_list as $heading => $shortcodes) {
                            $heading = ucwords(str_replace('_', ' ', $heading));
                            echo "<tr class='heading'>";
                                echo "<td colspan='2' class='p-2'><strong>".esc_html($heading)."</strong></td>";
                            echo "</tr>";
                            foreach ($shortcodes as $shortcode => $description) {
                                echo "<tr>";
                                    echo "<td width='40%' class='p-2'><span class='shortcode'> ".esc_html($shortcode)." </span></td>";
                                    echo "<td width='60%' class='p-2'> ".esc_html($description)." </td>";
                                echo "</tr>";
                            }
                        }
                    }
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
        echo "</div>";
    }

    // Email Setting
    function wpcb_construct_mail_body($email_body, $email_footer)
    {
        $email_header = $this->wpcb_get_email_header_html();
        $html = "<table width='100%' border='1' style='font-family: sans-serif; padding: 3em; background: #efefef; border-collapse: collapse;'>";
            $html .= "<tr>";
                $html .= "<td>{$email_header}</td>";
            $html .= "</tr>";
            $html .= "<tr>";
                $html .= "<td>{$email_body}</td>";
            $html .= "</tr>";
            $html .= "<tr>";
                $html .= "<td align='center' style='font-size: 10px;'>{$email_footer}</td>";
            $html .= "</tr>";
        $html .= "</table>";
        return wp_kses(apply_filters('wpcb_email_content_html', $html), wpcb_allowed_html_tags());
    }

    function wpcb_get_email_header_html()
    {
        $company_logo = $this->get_setting('general', 'company_logo');
        $html = "<table width='100%'>";
            $styles = empty($company_logo) ? "padding: 10px; font-size: 22px;" : "font-size: 14px;";
            if (!empty($company_logo)) {
                $html .= "<tr>";
                    $html .= "<td align='center'><img src='{$company_logo}' width='150px' height='auto' /></td>";
                $html .= "</tr>";
            }
            $html .= "<tr>";
                $html .= "<td align='center'><h5 style='margin: 0; {$styles}'>". get_bloginfo('name') ."</h5></td>";
            $html .= "</tr>";
        $html .= "</table>";
        return apply_filters('wpcb_email_header_html', $html, $company_logo);
    }

    function wpcb_status_list()
    {
        global $wpcb_setting;
        $status_list = array(
            wpcb_booking_default_status(),
            esc_html__('Booked', 'wpcb_booking'),
            esc_html__('Approved', 'wpcb_booking')
        );
        $setting_status =  $wpcb_setting->get_setting('general', 'booking_status_list');
        $setting_status = empty($setting_status) ? array() : $setting_status;
        $status_list = array_unique(array_merge($status_list, $setting_status));
        return wpcb_sanitize_data(apply_filters('wpcb_status_list', $status_list));
    }
}
$wpcb_setting = new WPCB_Setting();