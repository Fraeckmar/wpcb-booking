<?php 
$calendar_width = sanitize_text_field($wpcb_setting->get_setting('general', 'width'));
$calendar_unit = sanitize_text_field($wpcb_setting->get_setting('general', 'width_unit')) ?? '%';
if (!$calendar_width) {
    $calendar_width = 100;
    $calendar_unit = '%';
}
$dates_width = ($calendar_unit == 'px') ? ($calendar_width-42) / 7 : 14.28;
$day_name_font_size = sanitize_text_field($wpcb_setting->get_setting('general', 'day_name_font_size'));
$date_nos_font_size = sanitize_text_field($wpcb_setting->get_setting('general', 'date_nos_font_size'));

?>
<style>
    :root {
        --calendar-width : <?php esc_html_e($calendar_width.$calendar_unit); ?>;
        --date-width: <?php esc_html_e($dates_width.$calendar_unit); ?>;
        --date-name-fsize: <?php echo !empty($day_name_font_size) ? esc_html($day_name_font_size.'px') : 'inherit'; ?>; 
        --date-nos-fsize: <?php echo !empty($date_nos_font_size) ? esc_html($date_nos_font_size.'px') : 'inherit'; ?>; 
    }
</style>