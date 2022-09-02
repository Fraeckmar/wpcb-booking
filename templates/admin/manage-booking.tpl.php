<div id="booking-list" class="wrap wpcb-booking advanced-table">
    <h2><?php _e('Manage Booking', 'wpcb_booking'); ?> <a class="btn btn-sm btn-outline-secondary" href="<?php echo admin_url('admin.php?page=wpcb-booking&action=new') ?>"><?php _e('Add New', 'wpcb_booking'); ?></a></h2>
    
    <div class="row my-1" id="booking-filter">
        <div class="col-lg-2 col-md-3 col-sm-12">
            <ul class="subsubsub">
                <li class="active"><a class="<?php echo $is_active_booking ? 'current' : '' ?>" href="<?php echo admin_url("admin.php?page=wpcb-booking") ?>">Active <span class="count">(<?php echo $active_count ?>)</span></a> |</li>
                <li class="trash"><a class="<?php echo $is_active_booking ? '' : 'current' ?>" href="<?php echo admin_url("admin.php?page=wpcb-booking&status=trash") ?>">Trash <span class="count">(<?php echo $trash_count ?>)</span></a></li>
            </ul>
        </div>
        <div class="col-lg-7 col-md-5 col-sm-12">
            <form method="POST" class="row justify-content-end">
                <div class="col-md-3 p-0">
                    <?php echo WPCB_Form::gen_field(['key'=>'date_from', 'type'=>'date', 'class'=>'w-100', 'placeholder'=>'Date From']); ?>
                </div>
                <div class="col-md-3 p-0">
                    <?php WPCB_Form::draw_search_field('wpcb_customer_name', $q_customer_name, '', 'Customer Name'); ?>
                </div>
                <div class="col-md-2 p-0">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-filter"></i> Filter</button>
                </div>
            </form>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-12">
            <form method="POST">
                <div class="input-group bg-white">
                    <input type="text" class="form-control" name="q_booking" placeholder="Search Booking" required value="<?php echo $_POST['q_booking'] ?? ''; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="table-responsive">
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
            </tbody>
        </table>
    </div>
    <div class="calendar-pagination row">
        <div class="col-md-3 p-1 text-center pr-3">
            <?php
                printf(
                    '<p class="note note-primary m-0">Showing %s to %s of %s entries.</p>',
                    $record_start,
                    $record_end,
                    number_format($number_records)
                );
            ?>
        </div>
        <div class="col-md-6"><?php wpcb_bootstrap_pagination(array('custom_query' => $bookings)); ?></div>
        <div class="col-md-3">
    </div>
</div>