<?php
function wpcb_admin_calendar_form_fields()
{
    $fields = array(
        'dates' => array(
            'label' => esc_html('Dates'),
            'type' => 'modal',
            'required' => true
        )
    );
    $fields = wpcb_sanitize_data(apply_filters('wpcb_admin_calendar_form_fields', $fields));
    return $fields;
}