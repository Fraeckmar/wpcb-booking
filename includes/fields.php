<?php
function wpcb_admin_calendar_form_fields()
{
    $fields = array(
        'dates' => array(
            'label' => 'Dates',
            'type' => 'modal',
            'required' => true
        )
    );
    $fields = apply_filters('wpcb_admin_calendar_form_fields', $fields);
    return $fields;
}