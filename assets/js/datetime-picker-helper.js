jQuery(document).ready(function($){
    var date_picker_format = WPCBBookingAjax.date_picker_format
    var datetime_picker_format = WPCBBookingAjax.datetime_picker_format;
    console.log(date_picker_format);
    $('.wpcb-timepicker').datetimepicker(datetime_picker_format);
    $('.wpcb-datepicker').datetimepicker(date_picker_format);

    $('.wpcb-booking').on('keydown', '.wpcb-timepicker', function(){
        return false;
    });
});