<div id="booking-list" class="wrap wpcb-booking advanced-table">
    <h2><?php esc_html_e('Manage Booking', 'wpcb_booking'); ?> <a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url(admin_url("admin.php?page={$plugin_slug}&action=new")) ?>"><?php esc_html_e('Add New', 'wpcb_booking'); ?></a></h2>
    <div id="booking-status-nav" class="row">
        <ul class="subsubsub col-sm-12">
            <li class="active"><a class="<?php echo $is_active_booking ? 'current' : '' ?>" href="<?php echo esc_url(admin_url("admin.php?page={$plugin_slug}")) ?>">Active <span class="count">(<?php esc_html_e($active_count) ?>)</span></a> |</li>
            <li class="trash"><a class="<?php echo $is_active_booking ? '' : 'current' ?>" href="<?php echo esc_url(admin_url("admin.php?page={$plugin_slug}&status=trash")) ?>">Trash <span class="count">(<?php esc_html_e($trash_count) ?>)</span></a></li>
        </ul>
    </div>
    <div class="row mb-2" id="booking-filter">
        <!-- Filters -->
        <?php if($is_active_booking): ?>
        <div class="col-lg-9 col-md-8 col-sm-12">
            <form method="POST" class="row justify-content-end">
                <div class="col-md-2 p-0 pl-3">
                    <?php echo WPCB_Form::gen_field(array('key'=>'date_from', 'type'=>'date', 'placeholder'=>'Date From', 'value'=>$date_from)); ?>
                </div>
                <div class="col-md-2 p-0">
                    <?php echo WPCB_Form::gen_field(array('key'=>'date_to', 'type'=>'date', 'placeholder'=>'Date To', 'value'=>$date_to)); ?>
                </div>
                <div class="col-md-3 p-0">
                    <?php WPCB_Form::draw_search_field('wpcb_booking_status', $q_wpcb_booking_status, '', 'Enter Status'); ?>
                </div>
                <div class="col-md-3 p-0">
                    <?php WPCB_Form::draw_search_field(wpcb_customer_field('key'), $q_customer_name, '', wpcb_customer_field('label')); ?>
                </div>
                <div class="col-md-2 p-0">
                    <button class="btn btn-secondary" type="submit"><i class="fa fa-filter"></i> Filter</button>
                </div>
            </form>
        </div>
        <!-- Search -->
        <div class="col-lg-3 col-md-4 col-sm-12">
            <form method="POST">
                <div class="input-group bg-white">
                    <input type="text" class="form-control" name="q_booking" placeholder="Search Booking" required value="<?php esc_html_e($q_booking); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <!-- Bulk Options -->
    <div class="row align-items-end mb-2">
        <div class="col-md-10 col-sm-12">
            <div class="tablenav top">
                <?php if(!$is_active_booking): ?>
                    <?php echo WPCB_Form::gen_button('', 'Restore', 'button', 'bulk-update-post-status btn btn-sm btn-info', 'data-status=publish'); ?>
                <?php endif; ?>
                <?php echo WPCB_Form::gen_button('', $bulk_update_label, 'button', 'bulk-update-post-status btn btn-sm btn-danger', $status_attr); ?>
                <?php do_action('table_nav_top'); ?>
            </div>
        </div>
        <div class="col-md-2 col-sm-12">
            <form method="GET">
                <?php WPCB_Form::draw_hidden('page', wpcb_plugin_slug()) ?>
                <?php echo WPCB_Form::gen_field(array('key'=>'post_per_page', 'type'=>'select', 'label'=>'Show Entries', 'options'=>$entries_options, 'value'=>$post_per_page, 'class'=>'ml-2', 'group_class'=>'form-group row justify-content-end form-inline m-0', 'required'=>true), true); ?>
                <?php if(isset($_GET['paged'])): ?>
                    <?php WPCB_Form::draw_hidden('paged', sanitize_text_field($_GET['paged'])) ?>
                <?php endif; ?>
                <?php if(isset($_GET['status'])): ?>
                    <?php WPCB_Form::draw_hidden('status', sanitize_text_field($_GET['status'])) ?>
                <?php endif; ?>                      
            </form>
        </div>
    </div>    
    <div class="table-responsive">
        <table id="booking-list-table" class="table mb-1">
            <thead>
                <tr>
                    <th width="10px"><input type="checkbox" class="all-booking"/></th>
                    <?php do_action('wpcb_manage_booking_before_column_head'); ?>
                    <th><?php esc_html_e('Booking Number', 'wpcb-booking'); ?></th>
                    <th><?php esc_html_e('Customer Name', 'wpcb_booking'); ?></th>
                    <th><?php esc_html_e('Booked Date(s)', 'wpcb_booking'); ?></th>
                    <th><?php esc_html_e('Status', 'wpcb_booking'); ?></th>
                    <th><?php esc_html_e('Date Created', 'wpcb_booking'); ?></th>
                    <?php do_action('wpcb_manage_booking_after_column_head'); ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings->have_posts()): ?>
                    <?php while ($bookings->have_posts()): 
                        $bookings->the_post(); 
                        $booking_id = get_the_ID();
                        $meta_values = $wpcb_booking->wpcb_get_booking_details($booking_id);
                        $calendar_id = array_key_exists('calendar_id', $meta_values) ? wpcb_sanitize_data($meta_values['calendar_id']) : 0;                
                        $booked_dates = array_key_exists('booked_dates', $meta_values) ? wpcb_sanitize_data($meta_values['booked_dates']) : ''; 
                        //$booked_dates_str = !empty($booked_dates) ? implode('<br>', $booked_dates) : '';
                        $booked_dates_str = '';
                        if (!empty($booked_dates)) {
                            foreach ($booked_dates as $idx => $booked_date) {
                                $booked_date = date(wpcb_date_format(), strtotime($booked_date));
                                $booked_dates_str .= "<span class='booked-date'>".esc_html($booked_date)."</span><br>";
                            }
                        }
                        $edit_url = esc_url(admin_url("admin.php?page={$plugin_slug}&action=edit&id={$booking_id}"));
                        $trash_url = esc_url(admin_url("admin.php?page={$plugin_slug}&action=trash&id={$booking_id}"));
                        $restore_url = esc_url(admin_url("admin.php?page={$plugin_slug}&action=untrash&id={$booking_id}"));
                        $delete_url = esc_url(admin_url("admin.php?page={$plugin_slug}&action=delete&id={$booking_id}"));
                    ?>
                    <tr>
                        <td><input type="checkbox" class="booking-item" value="<?php esc_html_e($booking_id); ?>"/></td>
                        <?php do_action('wpcb_manage_booking_before_column_data', $booking_id); ?>
                        <td>
                        <a class="row-title" href="<?php echo $edit_url ?>"><?php echo get_the_title()?></a>
                            <div class="row-actions">
                                <?php if($is_active_booking): ?>
                                    <span class="edit"><a href="<?php echo $edit_url ?>"><?php esc_html_e('Edit', 'wpcb_calendar') ?></a> | </span>
                                    <span class="trash"><a href="<?php echo $trash_url ?>"><?php esc_html_e('Trash', 'wpcb_calendar') ?></a> </span>
                                <?php else: ?>
                                    <span class="untrash"><a href="<?php echo $restore_url ?>"><?php esc_html_e('Restore', 'wpcb_calendar') ?></a> | </span>
                                    <span class="trash"><a href="<?php echo $delete_url ?>"><?php esc_html_e('Delete Permanently', 'wpcb_calendar') ?></a> </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php esc_html_e($meta_values[wpcb_customer_field('key')]) ?? '' ?></td>
                        <td><?php echo $booked_dates_str; ?></td>
                        <td><span class="status"><?php esc_html_e($meta_values['wpcb_booking_status']) ?? '' ?></span></td>
                        <td><?php echo get_the_date(wpcb_date_format()) ?></td>
                        <?php do_action('wpcb_manage_booking_after_column_data', $booking_id); ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e('No Booking Found!'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Bulk Options -->
    <div class="tablenav bottom">
        <?php if(!$is_active_booking): ?>
            <?php echo WPCB_Form::gen_button('', 'Restore', 'button', 'bulk-update-post-status btn-sm btn-info', 'data-status="publish"'); ?>
        <?php endif; ?>
        <?php echo WPCB_Form::gen_button('', $bulk_update_label, 'button', 'bulk-update-post-status btn btn-sm btn-danger', $status_attr); ?>
        <?php do_action('table_nav_bottom'); ?>
    </div>
    <div class="calendar-pagination row m-0">
        <div class="col-md-3 p-0 pt-3 text-center">
            <?php
                printf(
                    '<p class="note note-primary m-0">Showing %s to %s of %s entries.</p>',
                    $record_start,
                    $record_end,
                    number_format($number_records)
                );
            ?>
        </div>
        <div class="col-md-6 pt-3"><?php wpcb_bootstrap_pagination(array('custom_query' => $bookings)); ?></div>
    </div>
</div>