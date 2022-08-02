jQuery(document).ready(function($){
    $('.allow-booking.calendar').on('click', '.day_num.available:not(.disabled)', function(){
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $(this).find('.date-check').prop('checked', false);
        } else {
            $(this).addClass('selected');
            $(this).find('.date-check').prop('checked', true);
        }
    });
});