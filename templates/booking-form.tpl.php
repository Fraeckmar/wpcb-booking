<div class="wpcb-booking">
    <?php do_action('wpcb_before_booking_form') ?>
    <form method="POST" id="wpcb-booking-form" class="<?php echo wpcb_get_rate_type(); ?>">
        <?php wp_nonce_field('wpcb_booking_nonce_action', 'wpcb_booking_nonce_field') ?>
        <div id="booking-info">
            <div class="calendar-container col-12 mb-3">
                <div class="allow-booking calendar">
                    <?php require(WPCB_BOOKING_PLUGIN_PATH.'templates/calendar.tpl.php'); ?>
                </div>
            </div>
            <div class="col-12 mb-5">
                <div id="legend-status" class="d-flex">
                    <p class="h4 mr-2"><?php _e('Legend', 'wpcb_booking') ?>: </p>
                    <div class="available d-flex mb-1">
                        <div class="color-code mr-1"></div>
                        <div> <?php _e('Available', 'wpcb_booking'); ?> </div>
                    </div>
                    <div class="unavailable d-flex mb-1 mx-3">
                        <div class="color-code mr-1"></div>
                        <div> <?php _e('Unavailable', 'wpcb_booking'); ?> </div>
                    </div>
                    <div class="booked d-flex mb-1">
                        <div class="color-code mr-1"><i class="fa fa-check"></i></div>
                        <div> <?php _e('Booked', 'wpcb_booking'); ?> </div>                    
                    </div>
                </div>
            </div>
            <?php do_action('wpcb_before_booking_info') ?>
            <?php if(!empty($form_fields)): ?>
                <?php foreach($form_fields as $section => $fields): ?>
                    <?php if(!empty($fields)): ?>
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header"> 
                                    <h5 class="h4 m-0"><?php echo $section; ?> </h5>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($fields as $field): ?>
                                        <?php echo WPCB_Form::gen_field($field, true); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php do_action('wpcb_after_booking_info') ?>          
            <div class="col-12">
                <button class="btn btn-primary waves-effect w-100 m-0"><?php _e('SUBMIT'); ?></button>
            </div>
        </div>
    </form>
    <?php do_action('wpcb_after_booking_form') ?>
</div>