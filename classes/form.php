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
        echo '<form '.$form_attribute.'>';
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
        echo "<button type='{$type}' id='{$key}' class='{$class}' {$extras}> {$label} </button>";
    }

    public static function gen_field($field=array(), $with_form_group=false)
    {
        global $wpcb_booking;

        if (empty($field)) { return false; }
        $label = array_key_exists('label', $field) ? $field['label'] : '';
        $type = array_key_exists('type', $field) ? $field['type'] : '';
        $key = array_key_exists('key', $field) ? $field['key'] : '';
        $value = array_key_exists('value', $field) ? $field['value'] : '';
        $placeholder = array_key_exists('placeholder', $field) ? $field['placeholder'] : '';
        $class = array_key_exists('class', $field) ? $field['class'] : '';
        $group_class = array_key_exists('group_class', $field) ? $field['group_class'] : '';
        $options = array_key_exists('options', $field) ? $field['options'] : '';
        $field_class = $wpcb_booking->field_class()[$type];
        $setting = array_key_exists('setting', $field) ? $field['setting'] : '';
        $field_name = !empty($setting) ? $setting.'['.$key.']' : $key;
        $required = array_key_exists('required', $field) ? $field['required'] : false;
        $extras = array_key_exists('extras', $field) ? $field['extras'] : '';
        $extras .= $required || $required == 'YES' ? 'required' : '';
        $description = array_key_exists('description', $field) ? $field['description'] : '';

        if(!empty($class)){
            $field_class .= ' '.$class;
        }

        $html_field = '';
        if ($with_form_group ) {
            if (!in_array($type, ['checkbox', 'radio'])) {
                $html_field .= '<div class="form-group '.$group_class.'">';
                if (!empty($label)) {
                    $html_field .= '<label for="'.$key.'" class="form-label d-block">'.$label.'</label>';
                }  
                if ($description) {
                    $html_field .= '<p class="description small text-secondary mb-1">'.$description.'</p>';
                }
            }            
        } else {
            if ($description) {
                $html_field .= '<p class="description small text-secondary mb-1">'.$description.'</p>';
            }
        }

        switch ($type) {
            case 'url':
                $html_field .= '<a href="'.$value.'" id="'.$key.'" class="'.$field_class.'" '.$extras.'></a>';
                break;
            case 'textarea':
                $html_field .= '<textarea id="'.$key.'" name="'.$field_name.'" class="'.$field_class.'" placeholder="'.$placeholder.'" '.$extras.'>'.$value.'</textarea>';
                break;
            case 'select':
                $brakets = '';
                $placeholder = !empty($placeholder) ? $placeholder : 'Choose...';
                if (!empty($extras) && strpos($extras, 'multiple') !== false) {
                    $brakets = '[]';
                }
                
                $html_field .= '<select id="'.$key.'" name="'.$field_name.$brakets.'" class="custom-select '.$field_class.'" placeholder="'.$placeholder.'" '.$extras.'>';
                    $html_field .= '<option value=""> '.$placeholder.' </option>';
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
                        $html_field .= '<option value="'.$op_val.'" '.$opt_selected.'> '.$op_label.' </option>';
                    }
                }
                $html_field .= '</select>';
                break;
            case 'checkbox':
            case 'radio':
                $field_name = $type == 'checkbox' ? $field_name.'[]' : $field_name;
                if ($with_form_group) {
                    $html_field .= '<label class="form-label d-block">'.$label.'</label>';
                }            
                if (!empty($options)) {
                    $html_field .= '<p class="description small text-secondary mb-1">'.$description.'</p>';
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
                        $html_field .= '<div class="form-check '.$group_class.'">';
                            $html_field .= '<input type="'.$type.'" id="'.$key.'-'.$counter.'" name="'.$field_name.'" value="'.$op_val.'" class="form-check-input '.$class.'" '.$extras.' '.$checked.' type="checkbox">';
                            $html_field .= '<label class="form-check-label" for="'.$key.'-'.$counter.'">'.$op_label.'</label>';
                        $html_field .= '</div>';
                    }
                }
                break;
            case 'date':
                $placeholder = strtolower(wpcb_datepicker_format());
                $html_field .= '<input type="text" id="'.$key.'" class="'.$field_class.'" name="'.$field_name.'" value="'.$value.'" placeholder="'.$placeholder.'" '.$extras.'/>';
                break;
            default: 
                $html_field .= '<input type="'.$type.'" id="'.$key.'" class="'.$field_class.'" name="'.$field_name.'" value="'.$value.'" placeholder="'.$placeholder.'" '.$extras.'/>';
        }

        if ($with_form_group && !in_array($type, ['checkbox', 'radio'])) {
            $html_field .= '</div>';
        }
        return $html_field;
    }

    public static function draw_hidden($name, $value='')
    {
        echo "<input type='hidden' id='{$name}' name='{$name}' value='{$value}'>";
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