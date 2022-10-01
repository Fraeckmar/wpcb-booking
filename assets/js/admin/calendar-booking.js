jQuery(document).ready(function($){
    const customer_field = WPCBBookingAjax.customer_field;

    var base_url = window.location.href.split('&')[0];
    $('.wpcb-color-field').wpColorPicker();
    $('#wpcb-navigation').on('click', '.btn', function(){
        let tab_container = $(this).data('tab_container');
        let current_tab = $(this).data('tab');
        let newUrl = base_url+'&tab='+current_tab;
        change_current_url(newUrl);

        $('#wpcb-booking-admin .tab-container').removeClass('active');
        $('#wpcb-booking-admin').find(tab_container).addClass('active');
        $('#wpcb-navigation .btn').removeClass('active');
        $(this).addClass('active');
    }); 
    $('.wpcb-sub-navigation').on('click', '.btn', function(){
        let base_url = window.location.href.split('&');
        base_url = base_url[0]+'&'+base_url[1];
        let tab_container = $(this).find('.options').data('tab_container');
        let current_tab = $(this).find('.options').data('tab');
        let newUrl = base_url+'&sub='+current_tab;
        change_current_url(newUrl);
        
        let container = $(this).closest('.wpcb-sub-container');
        container.find('.wpcb-sub-content').removeClass('active');
        container.find(tab_container).addClass('active');
    });
    
    function change_current_url(newUrl) {
        if (newUrl) {
            history.replaceState({}, null, newUrl);
        }
    }

    $('#calendar-post').on('click', '.calendar .day_num:not(.ignore)', function(){
        var date_day = $(this).find('.date-day').val();
        $(this).find('.modal-title').text('Day '+date_day);
        let modal = new bootstrap.Modal('#modal-day-'+date_day, {  keyboard: false });
        modal.show();        
    });

    $('#calendar-post').on('hidden.bs.modal', '.calendar .status-modal', function(e){
        let status = $(this).find('.status:checked').val();
        let day_num = $(this).closest('.day_num');
        let booked_icon_class = status == 'booked' ? 'd-block' : 'd-none';
        day_num.removeClass('unavailable booked disabled available');
        day_num.addClass(status);
        day_num.find('.booked-status').removeClass('d-block d-none');
        day_num.find('.booked-status').addClass(booked_icon_class);

        
        $('body').removeAttr('style');  
    });

    // Calendar
    $('#wpcb-booking-admin-form').on('submit', function(e){
        if ($('#wpcb-booking-admin-form .allow-booking').length) {
            let has_selected_dates = false;
            $(this).find('input.date-check').each(function(){
                if ($(this).prop('checked')) {
                    has_selected_dates = true;
                }
            });
            if (!has_selected_dates) {
                e.preventDefault();
                showNotification('Please select prefered dates.', 'danger', 'info');
            }
        }        
    });
    $('#booking-post').on('click', '.day_num.available', function(e){
        let selected_calendar = $('#wpcb_calendar').val();
        if (!selected_calendar) {
            showNotification('Please choose calendar first.', 'danger', 'info');
            $('#wpcb_calendar').trigger('focus');
            e.preventDefault();
        }
    });
    
    $('#booking-post #wpcb_calendar').on('change', function(){
        let calendar_id = $(this).val();
        let date = new Date();
        let formated_date = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
        if (calendar_id) {
            update_calendar(formated_date, calendar_id);
        }
    });

    // Manage Booking
    $('#post_per_page').on('change', function(){
        if ($(this).val()) {
            $(this).closest('form').trigger('submit');
        }
    });

    $('.all-booking').on('click', function(){
        $(this).closest('table').find('.booking-item').prop('checked', $(this).prop('checked'));
    });

    $('.bulk-update-post-status').on('click', function(){
        let status = $(this).data('status');
        let booking_ids = [];
        $('#booking-list .booking-item:checked').each(function(){
            if ($(this).val()) {
                booking_ids.push($(this).val());
            }            
        });
        if (!booking_ids.length) {
            showNotification('Please select booking(s) to delete.', 'danger', 'info');
            return false;
        }
        if (!status) {
            showNotification('Status is not set for this action.', 'danger', 'info');
            return false;
        }
        if (confirm('Are you sure to '+status+' the selected booking(s)?')) {
            $.post({
                url: WPCBBookingAjax.ajaxurl,
                data: {
                    action: 'wpcb_bulk_trash_booking',
                    booking_ids,
                    status
                },
                beforeSend: function() {
                    wpcb_show_loading();
                },
                success: function(response) {
                    if (WPCBBookingAjax.is_debug != 0) {
                        console.log(response);
                    }                    
                    data = JSON.parse(response);
                    if (data.status == 'error') {
                        showNotification(data.error, 'danger', 'info');
                    } else {
                        showNotification(data.msg, 'success', 'info');
                        $('#booking-list-table').find('.booking-item').each(function(){
                            if ($.inArray($(this).val(), booking_ids) !== -1) {
                                $(this).closest('tr').remove();
                            }
                        });
                    }
                    wpcb_hide_loading();
                }
            });
        }
        
    });

    // Generate Report
    $('#wpcb_export_form').on('submit', function(e){
        e.preventDefault();
        let date_from = $(this).find('#date_from').val();
        let date_to = $(this).find('#date_to').val();
        let status = $(this).find('#wpcb_booking_status').val();
        let customer = $(this).find('#'+customer_field.key).val();

        if (date_from && date_to) {
            $.post({
                url: WPCBBookingAjax.ajaxurl,
                data: {
                    action: 'wpcb_generate_report',
                    date_from,
                    date_to,
                    status,
                    customer
                },
                beforeSend: function() {
                    wpcb_show_loading();
                },
                success: function(response) {
                    data = JSON.parse(response);
                    $('#wpcb_export_form .alert').removeClass('d-none alert-danger alert-success');
                    if (data.status == 'error') {
                        $('#wpcb_export_form .alert').addClass('alert-danger');
                        $('#wpcb_export_form .alert').html(data.error);
                    } else {
                        $('#wpcb_export_form .alert').addClass('alert-success');
                        $('#wpcb_export_form .alert').html(data.msg);
                        window.open(data.file_url, '_blank');
                    }
                    wpcb_hide_loading();
                }
            });
        }
    });
});