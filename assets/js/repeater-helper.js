jQuery(document).ready(function($){
    $('.repeater').repeater({
        show: function () {
            $(this).slideDown();
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