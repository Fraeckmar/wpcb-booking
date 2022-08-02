<div id="calendar-post" class="wrap wpcb-booking">
    <form method="POST" action="">
        <?php wp_nonce_field('wpcb_calendar_update_action', 'wpcb_calendar_update_field') ?>
        <h3><?php echo ucwords($action).' Calendar'; ?></h3>
        <div class="form-group">
            <input type="text" name="post_title" class="form-control" value="<?php echo $title; ?>">
        </div>
        <div class="row">
            <div class="col-md-9">
                <div id="calendar-admin-nav">
                    <div class="calendar">
                    <?php 
                        $calendar = new Calendar($calendar_id); 
                        $calendar->has_date_modal = true;
                        $calendar->draw();
                    ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <h5><?php _e('Legend', 'wpcb_booking'); ?></h5>
                    <div id="legend-status" class="d-flex flex-column">
                        <div class="available d-flex mb-1">
                            <div class="color-code mr-1"></div>
                            <div> <?php _e('Available', 'wpcb_booking'); ?> </div>
                        </div>
                        <div class="unavailable d-flex mb-1">
                            <div class="color-code mr-1"></div>
                            <div> <?php _e('Unavailable', 'wpcb_booking'); ?> </div>
                        </div>
                        <div class="booked d-flex mb-1">
                            <div class="color-code mr-1"><i class="fa fa-check"></i></div>
                            <div> <?php _e('Booked', 'wpcb_booking'); ?> </div>                    
                        </div>
                    </div>
                </div>
                <div class="card p-3 text-right">
                    <button type="submit" class="btn btn-info"><?php _e('Save') ?></button>
                </div>
            </div>
        </div>
    </form>
</div>