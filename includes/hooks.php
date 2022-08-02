<?php
function wpcb_before_calendar_date_callback($calendar_id, $date, $day)
{
    $description = wpcb_get_date_value($calendar_id, $date, 'description');
    if (!empty($description)) {
        echo "<div class='date-tool-tip'>";
            echo "<div class='descryption'>{$description}</div>";
            echo "<div class='arrow-down'></div>";
        echo "</div>";
    }    
}
add_action('wpcb_before_calendar_date', 'wpcb_before_calendar_date_callback', 10, 3);