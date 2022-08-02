<div id="booking-list" class="wrap wpcb-booking advanced-table">
<h2><?php _e('Manage Booking', 'wpcb_booking'); ?> <a class="btn btn-sm btn-outline-secondary" href="<?php echo admin_url('admin.php?page=wpcb-booking&action=new') ?>"><?php _e('Add New', 'wpcb_booking'); ?></a></h2>
<ul class="subsubsub">
	<li class="active"><a class="<?php echo $is_active_booking ? 'current' : '' ?>" href="<?php echo admin_url("admin.php?page=wpcb-booking") ?>">Active <span class="count">(<?php echo $active_count ?>)</span></a> |</li>
	<li class="trash"><a class="<?php echo $is_active_booking ? '' : 'current' ?>" href="<?php echo admin_url("admin.php?page=wpcb-booking&status=trash") ?>">Trash <span class="count">(<?php echo $trash_count ?>)</span></a></li>
</ul>
<table class="table">
    <thead>
        <tr>
            <?php do_action('wpcb_manage_booking_before_column_head'); ?>
            <th><?php _e('Booking Number', 'wpcb-booking'); ?></th>
            <th><?php _e('Customer Name', 'wpcb_booking'); ?></th>
            <th><?php _e('Booked Date(s)', 'wpcb_booking'); ?></th>
            <th><?php _e('Status', 'wpcb_booking'); ?></th>
            <th><?php _e('Date Created', 'wpcb_booking'); ?></th>
            <?php do_action('wpcb_manage_booking_after_column_head'); ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($bookings->have_posts()): ?>
            <?php while ($bookings->have_posts()): 
                $bookings->the_post(); 
                $booking_id = get_the_ID();
                $meta_values = $wpcb_booking->wpcb_get_booking_details($booking_id);
                $calendar_id = array_key_exists('calendar_id', $meta_values) ? $meta_values['calendar_id'] : 0;                
                $booked_dates = array_key_exists('booked_dates', $meta_values) ? $meta_values['booked_dates'] : ''; 
                $booked_dates_str = "";
                if (!empty($booked_dates)) {
                    foreach ($booked_dates as $idx => $booked_date) {
                        $booked_date = date('F j, Y', strtotime($booked_date));
                        $idx++;
                        $separator = count($booked_dates) != $idx ? '|' : '';
                        $br = $idx % 2 == 0 ? '<br>' : '';
                        $booked_dates_str .= "<span class='booked-date'>{$booked_date}</span>{$separator}{$br}";
                    }
                }
                $edit_url = admin_url("admin.php?page=wpcb-booking&action=edit&id={$booking_id}");
                $trash_url = admin_url("admin.php?page=wpcb-booking&action=trash&id={$booking_id}");
                $restore_url = admin_url("admin.php?page=wpcb-booking&action=untrash&id={$booking_id}");
                $delete_url = admin_url("admin.php?page=wpcb-booking&action=delete&id={$booking_id}");
            ?>
            <tr>
                <?php do_action('wpcb_manage_booking_before_column_data', $booking_id); ?>
                <td>
                <a class="row-title" href="<?php echo $edit_url ?>"><?php echo get_the_title()?></a>
                    <div class="row-actions">
                        <?php if($is_active_booking): ?>
                            <span class="edit"><a href="<?php echo $edit_url ?>"><?php _e('Edit', 'wpcb_calendar') ?></a> | </span>
                            <span class="trash"><a href="<?php echo $trash_url ?>"><?php _e('Trash', 'wpcb_calendar') ?></a> </span>
                        <?php else: ?>
                            <span class="untrash"><a href="<?php echo $restore_url ?>"><?php _e('Restore', 'wpcb_calendar') ?></a> | </span>
                            <span class="trash"><a href="<?php echo $delete_url ?>"><?php _e('Delete Permanently', 'wpcb_calendar') ?></a> </span>
                        <?php endif; ?>
                    </div>
                </td>
                <td><?php echo $meta_values['wpcb_customer_name'] ?? '' ?></td>
                <td><?php echo $booked_dates_str; ?></td>
                <td><span class="status"><?php echo $meta_values['wpcb_booking_status'] ?? '' ?></span></td>
                <td><?php echo get_the_date() ?></td>
                <?php do_action('wpcb_manage_booking_after_column_data', $booking_id); ?>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5"><?php _e('No Booking Found!'); ?></td>
            </tr>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
    </tbody>
</table>
</div>