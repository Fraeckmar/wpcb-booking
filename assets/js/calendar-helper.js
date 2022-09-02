jQuery(document).ready(function($){
    window.wpcb_date_select = function(day) {
        if (day.hasClass('selected')) {
            day.removeClass('selected');
            day.find('.date-check').prop('checked', false);
        } else {
            day.addClass('selected');
            day.find('.date-check').prop('checked', true);
        }
    }

    $('.no-rate .allow-booking.calendar').on('click', '.day_num.available:not(.disabled)', function(){
        let _this = $(this);
        wpcb_date_select(_this);
    });    
});