<div id="calendar-list" class="wrap wpcb-booking">
<h2><?php _e('Calendars', 'wpcb_booking'); ?> <a class="btn btn-sm btn-outline-secondary" href="<?php echo admin_url('admin.php?page=wpcb-calendar&action=new') ?>"><?php _e('Add New', 'wpcb_booking'); ?></a></h2>
<ul class="subsubsub">
	<li class="active"><a class="<?php echo $active_current ?>" href="<?php echo admin_url("admin.php?page=wpcb-calendar") ?>">Active <span class="count">(<?php echo $active_count ?>)</span></a> |</li>
	<li class="trash"><a class="<?php echo $trash_current ?>" href="<?php echo admin_url("admin.php?page=wpcb-calendar&status=trash") ?>">Trash <span class="count">(<?php echo $trash_count ?>)</span></a></li>
</ul>
<table class="table table-striped">
    <thead class="thead-light">
        <tr>
            <?php do_action('wpcb_calendar_list_head_before_title'); ?>
            <th><?php _e('Title', 'wpcb-booking'); ?></th>
            <th><?php _e('Date Created', 'wpcb_booking'); ?></th>
            <th><?php _e('Author', 'wpcb_booking'); ?></th>
            <th><?php _e('Shortcode', 'wpcb_booking'); ?></th>            
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
                $edit_url = admin_url("admin.php?page=wpcb-calendar&action=edit&id={$booking_id}");
                $trash_url = admin_url("admin.php?page=wpcb-calendar&action=trash&id={$booking_id}");
                $restore_url = admin_url("admin.php?page=wpcb-calendar&action=untrash&id={$booking_id}");
                $delete_url = admin_url("admin.php?page=wpcb-calendar&action=delete&id={$booking_id}");
            ?>
            <tr>
                <td>
                    <a class="row-title" href="<?php echo $edit_url ?>"><?php echo get_the_title()?></a>
                    <div class="row-actions">
                        <?php if($status == 'trash'): ?>
                            <span class="untrash"><a href="<?php echo $restore_url ?>"><?php _e('Restore', 'wpcb_calendar') ?></a> | </span>
                            <span class="trash"><a href="<?php echo $delete_url ?>"><?php _e('Delete Permanently', 'wpcb_calendar') ?></a> </span>
                        <?php else: ?>
                            <span class="edit"><a href="<?php echo $edit_url ?>"><?php _e('Edit', 'wpcb_calendar') ?></a> | </span>
                            <span class="trash"><a href="<?php echo $trash_url ?>"><?php _e('Trash', 'wpcb_calendar') ?></a> </span>
                        <?php endif; ?>
                    </div>
                </td>
                <td><?php echo get_the_date(); ?></td>
                <td><?php echo get_the_author(); ?></td>
                <td class="shortcode"><?php echo '[wpcb_booking id='.$shortcode_id.']'; ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5"><?php _e('No Calendar Found!'); ?></td>
            </tr>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
    </tbody>
</table>
</div>