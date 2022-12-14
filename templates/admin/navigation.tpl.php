<?php 
$current_tab = isset($_GET['tab']) ? wpcb_sanitize_data($_GET['tab']) : 'general';
?>
<div id="wpcb-navigation" class="wpcb-navigation">
    <ul class="d-flex flex-row m-0">
        <?php if (!empty($wpcb_setting->menus())): ?>
            <?php foreach ($wpcb_setting->menus() as $menu_key => $menu): 
                $active_class = ($current_tab == $menu_key) ? 'active' : '';
                ?>
                <li class="m-0 mr-1">
                    <span class="btn btn-lg btn-light <?php echo esc_html($active_class); ?>" data-tab="<?php echo esc_html($menu_key); ?>" data-tab_container="#<?php echo esc_html($menu_key); ?>-container"><?php echo esc_html($menu['label']); ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>