define([
    'jquery',
    'jquery/ui',
    'Magento_Bundle/js/slide'
], function($){

    $.widget('quickview.slide', $.mage.slide, {
       
        _show: function() {
			
            $(this.options.bundleOptionsContainer).slideDown(100);
            $('html, body').animate({
                scrollTop: $(this.options.bundleOptionsContainer).offset().top
            }, 400);
			setTimeout($(this).colorbox.resize,100);
            $('#product-options-wrapper > fieldset').focus();
        },
        _hide: function() {
			
            $('html, body').animate({
                scrollTop: 0
            }, 300);
			setTimeout($(this).colorbox.resize,300);
            $(this.options.bundleOptionsContainer).slideUp(100);
        }
    });

    return $.quickview.slide;
});