<div id="wcpb-report" class="wrap wpcb-booking">
    <h2><?php _e('Generate Report', 'wpcb_booking'); ?></h2>
    <?php WPCB_Form::start(['id'=>'wpcb_export_form']) ?>
    <?php wp_nonce_field('wpcb_report_nonce_action', 'wpcb_report_nonce_name') ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="alert d-none">
            </div>
        </div>
        <div class="col-lg-6 col-sm-12">
            <div class="card p-1">
                <div class="card-body">
                    <?php echo WPCB_Form::gen_field(['type'=>'date', 'key'=>'date_from', 'label'=>'Date From', 'value'=>$date_from, 'required'=>true], true); ?>
                    <?php echo WPCB_Form::gen_field(['type'=>'date', 'key'=>'date_to', 'label'=>'Date To', 'value'=>$date_to, 'required'=>true], true); ?>
                    <?php WPCB_Form::draw_search_field('wpcb_booking_status', $wpcb_booking_status, __('Enter Status', 'wpcb_booking'), __('Status', 'wpcb_booking'), '', true); ?>
                    <?php WPCB_Form::draw_search_field(wpcb_customer_field('key'), $customer_name, wpcb_customer_field('label'), __('Customer Names', 'wpcb_booking'), '', true); ?>
                    <?php WPCB_Form::gen_button('gen_report', __('Generate Report', 'wpcb_booking')) ?>
                </div>
            </div>
        </div>
    </div>
    <?php WPCB_Form::end() ?>
</div>