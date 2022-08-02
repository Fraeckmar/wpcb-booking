jQuery(document).ready(function($){
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
        $(this).find('.modal').modal('show');
        $(this).find('.modal-title').text('Day '+date_day);
    });

    $('body').on('hidden.bs.modal', '.calendar .modal', function(e){
        let status = $(this).find('.status:checked').val();
        let day_num = $(this).closest('.day_num');
        let booked_icon_class = status == 'booked' ? 'd-block' : 'd-none';
        day_num.removeClass('unavailable booked disabled available');
        day_num.addClass(status);
        day_num.find('.booked-status').removeClass('d-block d-none');
        day_num.find('.booked-status').addClass(booked_icon_class);
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
});