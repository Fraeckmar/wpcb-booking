<?php 
$calendar_width = $wpcb_setting->get_setting('general', 'width');
$calendar_unit = $wpcb_setting->get_setting('general', 'width_unit') ?? '%';
if (!$calendar_width) {
    $calendar_width = 100;
    $calendar_unit = '%';
}
$dates_width = ($calendar_unit == 'px') ? ($calendar_width-42) / 7 : 14.28;
$day_name_font_size = $wpcb_setting->get_setting('general', 'day_name_font_size');
$date_nos_font_size = $wpcb_setting->get_setting('general', 'date_nos_font_size');

?>
<style>
    :root {
        --calendar-width : <?php echo $calendar_width.$calendar_unit; ?>;
        --date-width: <?php echo $dates_width.$calendar_unit; ?>;
        --date-name-fsize: <?php echo !empty($day_name_font_size) ? $day_name_font_size.'px' : 'inherit'; ?>; 
        --date-nos-fsize: <?php echo !empty($date_nos_font_size) ? $date_nos_font_size.'px' : 'inherit'; ?>; 
    }
</style>