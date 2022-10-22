<?php
$width_unit = $wpcb_setting->get_setting('general', 'width_unit') ?? '%';
$calendar_width = (int)$wpcb_setting->get_setting('general', 'width');
if (!$calendar_width ) {
    $calendar_width = 100;
}
if (empty($width_unit)) {
    $width_unit = '%';
}
?>
<div class="row">
    <?php if (!empty($general_setting_fields)): ?>
        <?php foreach ($general_setting_fields as $section_field): ?>
            <?php  $section_heading = array_key_exists('heading', $section_field) ? wpcb_sanitize_data($section_field['heading']) : ''; ?>
            <div class="col-md-12">
                <h5><?php echo esc_html($section_heading); ?></h5>
            </div>
            <?php if ($section_heading == 'Calendar'): ?>
                <!-- Width -->
                <div class="col-md-12 mb-2 px-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="mr-4"><?php echo esc_html('Width', 'wpcb_booking'); ?></label>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group">
                                <input type="number" name="general[width]" value="<?php echo esc_html($calendar_width); ?>" class="form-control"/> 
                                <div class="input-group-append">
                                    <select name="general[width_unit]">
                                        <option value="px" <?php selected($width_unit, 'px'); ?>><?php echo esc_html('px'); ?></option>
                                        <option value="%" <?php selected($width_unit, '%'); ?>><?php echo esc_html('%'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php foreach ($section_field['fields'] as $field): ?>
                <div class="col-md-12 mb-2 px-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="mr-4"><?php echo esc_html($field['label']); ?></label>
                        </div>
                        <div class="col-md-2">
                            <?php echo WPCB_Form::gen_field($field); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
