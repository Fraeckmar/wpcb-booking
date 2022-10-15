<div id="calendar-list" class="wrap wpcb-booking">
<h2><?php esc_html_e('Calendars', 'wpcb_booking'); ?> <a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url(admin_url("admin.php?page={$submenu_slug}&action=new")) ?>"><?php esc_html_e('Add New', 'wpcb_booking'); ?></a></h2>
<ul class="subsubsub">
	<li class="active"><a class="<?php echo $active_current ?>" href="<?php echo esc_url(admin_url("admin.php?page={$submenu_slug}")) ?>">Active <span class="count">(<?php esc_html_e($active_count) ?>)</span></a> |</li>
	<li class="trash"><a class="<?php echo $trash_current ?>" href="<?php echo esc_url(admin_url("admin.php?page={$submenu_slug}&status=trash")) ?>">Trash <span class="count">(<?php esc_html_e($trash_count) ?>)</span></a></li>
</ul>
<table class="table table-striped">
    <thead class="thead-light">
        <tr>
            <?php do_action('wpcb_calendar_list_head_before_title'); ?>
            <th><?php esc_html_e('Title', 'wpcb-booking'); ?></th>
            <th><?php esc_html_e('Date Created', 'wpcb_booking'); ?></th>
            <th><?php esc_html_e('Author', 'wpcb_booking'); ?></th>
            <th><?php esc_html_e('Shortcode', 'wpcb_booking'); ?></th>            
            <?php do_action('wpcb_calendar_list_head_before_action'); ?>
            <?php do_action('wpcb_calendar_list_head_after_action'); ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($wpcb_calendars->have_posts()): ?>
            <?php while ($wpcb_calendars->have_posts()): 
                $wpcb_calendars->the_post(); 
                $booking_id = get_the_ID();
                $shortcode_id = get_post_meta($booking_id, 'shortcode_id', true);
                $edit_url = admin_url("admin.php?page={$submenu_slug}&action=edit&id={$booking_id}");
                $trash_url = admin_url("admin.php?page={$submenu_slug}&action=trash&id={$booking_id}");
                $restore_url = admin_url("admin.php?page={$submenu_slug}&action=untrash&id={$booking_id}");
                $delete_url = admin_url("admin.php?page={$submenu_slug}&action=delete&id={$booking_id}");
            ?>
            <tr>
                <td>
                    <a class="row-title" href="<?php echo $edit_url ?>"><?php echo get_the_title()?></a>
                    <div class="row-actions">
                        <?php if($status == 'trash'): ?>
                            <span class="untrash"><a href="<?php echo esc_url($restore_url) ?>"><?php esc_html_e('Restore', 'wpcb_calendar') ?></a> | </span>
                            <span class="trash"><a href="<?php echo esc_url($delete_url) ?>"><?php esc_html_e('Delete Permanently', 'wpcb_calendar') ?></a> </span>
                        <?php else: ?>
                            <span class="edit"><a href="<?php echo esc_url($edit_url) ?>"><?php esc_html_e('Edit', 'wpcb_calendar') ?></a> | </span>
                            <span class="trash"><a href="<?php echo esc_url($trash_url) ?>"><?php esc_html_e('Trash', 'wpcb_calendar') ?></a> </span>
                        <?php endif; ?>
                    </div>
                </td>
                <td><?php echo esc_html(get_the_date()); ?></td>
                <td><?php echo esc_html(get_the_author()); ?></td>
                <td class="shortcode"><span><?php echo esc_html('[wpcb_booking id='.$shortcode_id.']'); ?> <i class="fa fa-info-circle" role="button" title="Copy and paste this shortcode into your page."></i></span></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5"><?php esc_html_e('No Calendar Found!', 'wpcb_booking'); ?></td>
            </tr>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
    </tbody>
</table>
</div>