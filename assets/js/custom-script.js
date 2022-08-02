jQuery(document).ready(function($){
    $('#wpcb-booking-form').on('submit', function(e){
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
    });
});