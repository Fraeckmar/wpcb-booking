jQuery(document).ready(function($){
    $('.selectize').find('select').each(function(){
        selectize_elem($(this));
    });
    var selectize_dropdown = $('body').find('select.selectize');
    if (selectize_dropdown.length) {
        selectize_dropdown.each(function(){
            selectize_elem($(this));
        });
        
    }

    function selectize_elem(elem)
    {
        var allow_create = elem.data('allow_create');
        let has_remove = elem.data('has_remove');
        let plugins = has_remove ? ['remove_button'] : [];
        elem.selectize({
            create : allow_create,
            plugins: plugins
        });
    }
});