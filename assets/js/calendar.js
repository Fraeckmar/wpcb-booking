jQuery(document).ready(function($){
    var ajaxurl = WPCBBookingAjax.ajaxurl;
    var is_admin = WPCBBookingAjax.is_admin;
    var notification = WPCBBookingAjax.notification;
    var loading_html = '<div class="wpcb-loading text-info text-center h3"> <span class="spinner-grow spinner-border-sm"></span></div>';

    window.wpcb_show_loading = function() {
        $('.wpcb-booking').append(loading_html);
    }
    window.wpcb_hide_loading = function() {
        $('body').find('.wpcb-loading').remove();
    }
    window.showNotification = function(message, type, icon = "check"){
    	$('body').append(
            '<div class="wpcb-booking">'+
                '<div class="wpcb-notif alert alert-'+type+'" role="alert">'+
                    '<span class="fa fa-lg fa-'+icon+'-circle"></span> '+ message +
                    '<span class="wpcb-notif-dismiss">&times;</span>'+
                '</div>'+
            '</div>'
        );
        setTimeout(function(){
            $('body .wpcb-notif').remove();
        }, 15000);
	}

    $('body').on('click', '.wpcb-notif-dismiss', function(){
        $('body').find('.wpcb-notif').remove();
    });

    window.force_hide_modal = ()=> {
        $('body').removeAttr('style');
    }

	if( notification && notification.length != 0 ){
		showNotification( notification.message, notification.type, notification.icon );
	}
    
    function display_goto_dates(display_option) {
        let moModal = new bootstrap.Modal('#calendar-modal', {  keyboard: false });
        display_option = '.'+display_option;
        let modal = $('.calendar').find('#calendar-modal');
        modal.find('.option').addClass('d-none');
        modal.find(display_option).removeClass('animate-scale');
        modal.find(display_option).removeClass('d-none');
        moModal.show();
        setTimeout(function(){
            modal.find(display_option).addClass('animate-scale');
        }, 100);
    }

    function update_calendar_date_heights() {
        if ($('.calendar').length) {
            var date_width = $('.calendar').find('.day_num ').width();
            if (date_width) {
                $('.calendar').find('.day_num ').css('height', date_width+'px');
            }
        }
    }  

    if (!is_admin) {
        update_calendar_date_heights();
    }    

    window.update_calendar = function (date, calendar_id='', booking_id='') {
        let has_date_modal = $('.calendar').find('#has_date_modal').val();
        
        $.get({
            url: ajaxurl,
            data: {
                action: 'wpcb_calendar_change',
                date : date,
                calendar_id : calendar_id,
                booking_id : booking_id,
                has_date_modal : has_date_modal
            },
            beforeSend:function(){
                $('body').find('.calendar .card-body').append(loading_html);
            },
            success:function(response){
                wpcb_hide_loading();
                $('.calendar').hide().html(response).fadeIn(500);
                if (!is_admin) {
                    update_calendar_date_heights();
                    reset_summary();
                }                
            }
        });
    }

    $('.calendar').on('click', '.update', function(){
        let date = $(this).data('date');
        let calendar_id = $('#calendar_id').val();
        let booking_id = $('#booking_id').val();
        update_calendar(date, calendar_id, booking_id);
    });
     
    $('.calendar').on('click', '.go-to-date .option .item', function(){ 
        let calendar_id = $('.calendar').find('#calendar_id').val();
        let booking_id = $('.calendar').find('#booking_id').val();
        let value = $(this).data('value');
        let next = $(this).data('next');
        let item_name = $(this).data('item');
        $('#go_to_date').data(item_name, value);

        if (next != 'done') {
            display_goto_dates(next);
        } else {
            let goto_year = $('#go_to_date').data('year');
            let goto_month = $('#go_to_date').data('month');
            let goto_date = goto_year+'-'+goto_month;
            update_calendar(goto_date, calendar_id, booking_id);
        }        
    });

    $('.calendar').on('click', '#month-year', function(){  
        let display_option = 'months';
        if (is_admin) {
            display_option = 'years';
        }     
        display_goto_dates(display_option);        
    });

    function reset_summary()
    {
        $('body').find('#order-summary').addClass('d-none');
    }

    $('.calendar').on('mouseover', '.day_num', function(){
        if ($(this).find('.date-tool-tip').length) {
            let date_height = $(this).height();
            let width = {};
            $(this).find('.date-tool-tip').css({'padding': '0 5px', 'bottom': date_height+'px'});
            let desc = $(this).find('.descryption').text();
            if (desc.length < 25) {
                width = {'width':'auto'};
            }
            if (desc.length > 25 && desc.length < 115) {
                let width_val = (parseInt(desc.length) * 2)+'px';
                width = {'width': width_val};
            }
            if (width) {
                $(this).find('.date-tool-tip').css(width);
            }
        }
    });
});