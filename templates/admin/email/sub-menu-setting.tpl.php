<?php 
$sub_tab = isset($_GET['sub']) ? wpcb_sanitize_data($_GET['sub']) : 'admin';
?>
<div class="wpcb-sub-navigation wpcb-navigation mb-3">
    <div class="btn-group btn-group-toggle sub-menus" data-toggle="buttons">
        <label class="btn btn-secondary <?php echo $sub_tab == 'admin' ? 'active' : ''; ?>">
            <input type="radio" name="options" id="option1" class="options" autocomplete="off" checked data-tab="admin" data-tab_container="#wpcb-admin-email-setting"/> Admin
        </label>
        <label class="btn btn-secondary <?php echo $sub_tab == 'client' ? 'active' : ''; ?>">
            <input type="radio" name="options" id="option2" class="options" autocomplete="off" data-tab="client" data-tab_container="#wpcb-client-email-setting"/> Client
        </label>
    </div>
</div>