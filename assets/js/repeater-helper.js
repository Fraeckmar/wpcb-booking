jQuery(document).ready(function($){
    $('.repeater').repeater({
        initEmpty: false,
        defaultValues: {},
        isFirstItemUndeletable: false,
        repeaters: [{
            initEmpty: false,
            defaultValues: {},
            // (Required)
            // Specify the jQuery selector for this nested repeater
            selector: '.inner-repeater',
            show: function () {
                $(this).slideDown();
                if ($('body').find('.wpcb-timepicker').length) {
                    $('body').find('.wpcb-timepicker').each(function(){
                        $(this).datetimepicker(WPCBBookingAjax.datetime_picker_format);
                    });                
                }
            },
        }],
        show: function () {
            $(this).slideDown();
            if ($('body').find('.wpcb-timepicker').length) {
                $('body').find('.wpcb-timepicker').each(function(){
                    $(this).datetimepicker(WPCBBookingAjax.datetime_picker_format);
                });                
            }
        },
        hide: function (deleteElement) {
            var item_label = $(this).closest('.repeater').attr('item-label');
            if (!item_label) {
                item_label = 'item';
            }
            if(confirm('Are you sure to delete this '+item_label+'?')) {
                $(this).slideUp(deleteElement);
            }
        },
        ready: function (setIndexes) {
        }
    });
});