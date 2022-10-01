<div id="booking-post" class="wrap wpcb-booking">
    <form method="POST" action="" id="wpcb-booking-admin-form" class="<?php echo wpcb_get_rate_type(); ?>">
        <?php wp_nonce_field('wpcb_booking_update_nonce_action', 'wpcb_booking_update_nonce_field') ?>
        <h3><?php echo esc_html(ucwords($action).' Booking'); ?></h3>
        <div class="form-group">
            <input type="text" name="post_title" class="form-control" value="<?php echo esc_html($title); ?>" required>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="card p-0 border-0">
                    <?php echo WPCB_Form::gen_field(array(
                        'key' => 'wpcb_calendar',
                        'type' => 'select',
                        'placeholder' => '- Choose Calendar -',
                        'required' => true,
                        'options' => $calendar_list,
                        'value' => $calendar_id
                    )) ?>
                </div>
                <div class="<?php echo $action == 'new' ? 'allow-booking' : '' ?> calendar">
                    <?php   
                        $date = date('Y-m-d');                     
                        if ($calendar_id && $booking_id) {
                            $calendar_data = wpcb_get_calendar_dates($calendar_id, $booking_id);
                            $year_month = array_key_first($calendar_data);
                            if (!empty($year_month)) {
                                $date = $year_month;
                            }
                        }
                        $calendar = new Calendar($calendar_id, $date);
                        $calendar->set_booking_id($booking_id);
                        $calendar->draw();
                    ?>
                </div>
                <div class="card p-3">
                    <h5><?php esc_html_e('Legend', 'wpcb_booking'); ?></h5>
                    <div id="legend-status" class="d-flex flex-column">
                        <div class="available d-flex align-items-center mb-1">
                            <div class="color-code mr-1"></div>
                            <div> <?php esc_html_e('Available', 'wpcb_booking'); ?> </div>
                        </div>
                        <div class="unavailable d-flex align-items-center mb-1">
                            <div class="color-code mr-1"></div>
                            <div> <?php esc_html_e('Unavailable', 'wpcb_booking'); ?> </div>
                        </div>
                        <div class="booked d-flex align-items-center mb-1">
                            <div class="color-code mr-1"><i class="fa fa-check"></i></div>
                            <div> <?php esc_html_e('Booked', 'wpcb_booking'); ?> </div>                    
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            <!-- Custom Field Details -->
            <?php if(!empty($form_fields)): ?>
                <?php foreach($form_fields as $section => $fields): ?>
                    <div class="col-sm-12 mb-4">
                        <div class="card p-0 mw-100">
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
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Order Detetails -->
            <?php if (!empty($order_html)): ?>
                <div class="col-sm-12 mb-4">
                    <div class="card p-0 mw-100">
                        <div class="card-header"> 
                            <h5 class="h4 m-0"><?php esc_html_e('Order Details', 'wpcb_booking'); ?> </h5>
                        </div>
                        <div class="card-body">
                            <?php echo $order_html; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
            <!-- Status -->
            <div class="col-md-3">
                <div class="card p-3">
                    <?php echo WPCB_Form::gen_field(array(
                        'key' => 'wpcb_booking_status',
                        'type' => 'select',
                        'label' => '<strong>Status</strong>',
                        'required' => true,
                        'options' => $wpcb_setting->wpcb_status_list(),
                        'value' => $booking_id ? get_post_meta($booking_id, 'wpcb_booking_status', true) : ''
                    ), true) ?>
                </div>
                <div class="card p-3 text-right">
                    <button type="submit" class="btn btn-info"><?php $action == 'new' ? esc_html_e('Save', 'wpcb_booking') :  esc_html_e('Update', 'wpcb_booking') ?></button>
                </div>
            </div>
        </div>
    </form>
</div>