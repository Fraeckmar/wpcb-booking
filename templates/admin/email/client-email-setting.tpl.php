<?php
$mail_setting = $wpcb_setting->get_setting('email_client');
?>
<div class="row">
    <div class="col-12 mb-4 p-3">
        <?php $wpcb_setting->wpcb_draw_shortcode_list(); ?>
    </div>
    <?php if (!empty($client_email_setting_fields)): ?>
        <?php foreach ($client_email_setting_fields as $section_field): ?>
            <?php  $section_heading = array_key_exists('heading', $section_field) ? $section_field['heading'] : ''; ?>
            <div class="col-md-12">
                <h5><?php esc_html_e($section_heading); ?></h5>
            </div>
            <?php foreach ($section_field['fields'] as $field): ?>
                <div class="col-md-12 mb-2 px-4">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="mr-4"><?php esc_html_e($field['label']); ?></label>
                        </div>
                        <div class="col-md-9">
                            <?php echo WPCB_Form::gen_field($field); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
