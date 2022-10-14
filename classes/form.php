<?php

class WPCB_Form
{
    public static function start($attributes = array())
    {
        $form_attribute = '';
        if (!array_key_exists('name', $attributes)) {
            $attributes['name'] = 'wpcb_booking';
        }
        if (!array_key_exists('method', $attributes)) {
            $attributes['method']= 'post';
        }

        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $form_attribute .= $name.'='.$value.' ';
            }            
        }
        echo '<form '.esc_html($form_attribute).'>';
    }

    public static function end()
    {
        echo '</form>';

    }

    public static function nonce($nonce=array())
    {
        if (!empty($nonce)) {
            wp_nonce_field($nonce['field'], $nonce['action']);
        }
    }

    public static function gen_button($key, $label, $type='submit', $class='btn btn-sm btn-primary', $extras='')
    {
        echo "<button type='".esc_html($type)."' id='".esc_html($key)."' class='".esc_html($class)."' ".esc_html($extras)."> ".esc_html($label)." </button>";
    }

    public static function gen_field($field=array(), $with_form_group=false)
    {
        global $wpcb_booking;

        if (empty($field)) { return false; }
        $label = array_key_exists('label', $field) ? wp_kses_data($field['label']) : '';
        $type = array_key_exists('type', $field) ? sanitize_key($field['type']) : '';
        $key = array_key_exists('key', $field) ? sanitize_key($field['key']) : '';
        $value = array_key_exists('value', $field) ? wpcb_sanitize_data($field['value']) : '';
        $placeholder = array_key_exists('placeholder', $field) ? sanitize_text_field($field['placeholder']) : '';
        $class = array_key_exists('class', $field) ? wpcb_sanitize_data($field['class']) : '';
        $group_class = array_key_exists('group_class', $field) ? wpcb_sanitize_data($field['group_class']) : '';
        $options = array_key_exists('options', $field) ? wpcb_sanitize_data($field['options']) : '';
        $field_class = $wpcb_booking->field_class()[$type];
        $setting = array_key_exists('setting', $field) ? $field['setting'] : '';
        $field_name = !empty($setting) ? $setting.'['.$key.']' : $key;
        $required = array_key_exists('required', $field) ? $field['required'] : false;
        $extras = array_key_exists('extras', $field) ? $field['extras'] : '';
        $extras .= $required || $required == 'YES' ? 'required' : '';
        $description = array_key_exists('description', $field) ? wp_kses($field['description'], wpcb_allowed_html_tags()) : '';

        if(!empty($class)){
            $field_class .= ' '.$class;
        }

        $html_field = '';
        if ($with_form_group ) {
            if (!in_array($type, ['checkbox', 'radio'])) {
                $html_field .= '<div class="form-group '.esc_html($group_class).'">';
                if (!empty($label)) {
                    $html_field .= '<label for="'.esc_html($key).'" class="form-label d-block">'.wp_kses_data($label).'</label>';
                }  
                if ($description) {
                    $html_field .= '<p class="description small text-secondary mb-1">'.wp_kses_data($description).'</p>';
                }
            }            
        } else {
            if ($description) {
                $html_field .= '<p class="description small text-secondary mb-1">'.wp_kses_data($description).'</p>';
            }
        }

        switch ($type) {
            case 'url':
                $html_field .= '<a href="'.esc_html($value).'" id="'.esc_html($key).'" class="'.esc_html($field_class).'" '.esc_html($extras).'></a>';
                break;
            case 'textarea':
                $html_field .= '<textarea id="'.esc_html($key).'" name="'.esc_html($field_name).'" class="'.esc_html($field_class).'" placeholder="'.esc_html($placeholder).'" '.esc_html($extras).'>'.esc_html($value).'</textarea>';
                break;
            case 'select':
                $brakets = '';
                $placeholder = !empty($placeholder) ? $placeholder : 'Choose...';
                if (!empty($extras) && strpos($extras, 'multiple') !== false) {
                    $brakets = '[]';
                }
                
                $html_field .= '<select id="'.esc_html($key).'" name="'.esc_html($field_name.$brakets).'" class="custom-select '.esc_html($field_class).'" placeholder="'.esc_html($placeholder).'" '.esc_html($extras).'>';
                    $html_field .= '<option value=""> '.esc_html($placeholder).' </option>';
                if (!empty($options) && is_array($options)) {
                    $is_assoc_arr = !array_key_exists(0, $options);
                    foreach ($options as $op_val => $op_label) {
                        if (!$is_assoc_arr) {
                            $op_val = $op_label;
                        }
                        $opt_selected = $op_val == $value ? 'selected' : '';
                        if (is_array($value)) {
                            if (in_array($op_val, $value)) {
                                $opt_selected = 'selected';
                            }
                        }
                        $html_field .= '<option value="'.esc_html($op_val).'" '.esc_html($opt_selected).'> '.esc_html($op_label).' </option>';
                    }
                }
                $html_field .= '</select>';
                break;
            case 'checkbox':
            case 'radio':
                $field_name = $type == 'checkbox' ? $field_name.'[]' : $field_name;
                if ($with_form_group) {
                    $html_field .= '<label class="form-label d-block">'.esc_html($label).'</label>';
                }            
                if (!empty($options)) {
                    $html_field .= '<p class="description small text-secondary mb-1">'.wp_kses_data($description).'</p>';
                    $counter = 0;
                    $is_assoc_arr = !array_key_exists(0, $options);
                    foreach ($options as $op_val => $op_label) {
                        $counter ++;
                        if (!$is_assoc_arr) {
                            $op_val = $op_label;
                        }
                        if ($type == 'checkbox') {
                            $checked = !empty($value) ? checked(in_array($op_val, $value), 1, false) : '';
                        } else {
                            $checked = checked($op_val == $value, 1, false);
                        }
                        $html_field .= '<div class="form-check '.esc_html($group_class).'">';
                            $html_field .= '<input type="'.esc_html($type).'" id="'.esc_html($key).'-'.$counter.'" name="'.esc_html($field_name).'" value="'.esc_html($op_val).'" class="form-check-input '.esc_html($class).'" '.esc_html($extras).' '.esc_html($checked).' type="checkbox">';
                            $html_field .= '<label class="form-check-label" for="'.esc_html($key.'-'.$counter).'">'.esc_html($op_label).'</label>';
                        $html_field .= '</div>';
                    }
                }
                break;
            case 'date':
                $placeholder = strtolower(wpcb_datepicker_format());
                $html_field .= '<input type="text" id="'.esc_html($key).'" class="'.esc_html($field_class).'" name="'.esc_html($field_name).'" value="'.esc_html($value).'" placeholder="'.esc_html($placeholder).'" '.esc_html($extras).'/>';
                break;
            default: 
                $html_field .= '<input type="'.esc_html($type).'" id="'.esc_html($key).'" class="'.esc_html($field_class).'" name="'.esc_html($field_name).'" value="'.esc_html($value).'" placeholder="'.esc_html($placeholder).'" '.esc_html($extras).'/>';
        }

        if ($with_form_group && !in_array($type, ['checkbox', 'radio'])) {
            $html_field .= '</div>';
        }
        return $html_field;
    }

    public static function draw_hidden($name, $value='')
    {
        echo "<input type='hidden' id='".esc_html($name)."' name='".esc_html($name)."' value='".esc_html($value)."'>";
    }

    public static function draw_search_field($meta_key, $value, $label='', $placeholder='', $extras='', $form_group=false)
    {
        $options = !empty($value) ? [$meta_key => $value] : array();
        $fields = [
            'key' => $meta_key,
            'type' => 'select',
            'label' => $label,
            'options' => $options,
            'value' => $value,
            'class' => 'selectize-search',
            'extras' => $extras,
            'placeholder' => $placeholder
        ];
        self::draw_hidden("{$meta_key}_options", base64_encode(json_encode($options)));
        echo self::gen_field($fields, $form_group);
    }
}