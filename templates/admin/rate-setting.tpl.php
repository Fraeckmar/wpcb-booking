<div class="row">
    <!-- Width -->
    <div class="col-md-12 mb-2">
        <h5><?php _e('Rate', 'wpcb_booking'); ?></h5>
        <div class="row">
            <div class="col-md-3">
                <label class="mr-4"><?php _e('Service', 'wpcb_booking'); ?></label>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="text" name="rate[service]" value="<?php echo $wpcb_setting->get_setting('rate', 'service'); ?>" class="form-control"/> 
                </div>
            </div>
        </div>
    </div>
</div>
